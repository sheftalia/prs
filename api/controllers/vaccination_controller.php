<?php
class VaccinationController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function processRequest($id = null, $action = null) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Check authentication
        $user = getCurrentUser();
        if (!$user) {
            handleError('Unauthorized access', 401);
        }
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getVaccinationRecord($id, $user);
                } else if ($action === 'user') {
                    $this->getUserVaccinations($user);
                } else if ($action === 'unverified' && in_array($user->role_id, [1, 2])) {
                    $this->getUnverifiedRecords();
                } else if ($action === 'stats' && in_array($user->role_id, [1, 2])) {
                    $this->getVaccinationStats();
                } else {
                    handleError('Invalid request', 400);
                }
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);
                
                if ($action === 'upload') {
                    $this->uploadVaccinationRecord($data, $user);
                } else if ($action === 'verify' && in_array($user->role_id, [1, 2])) {
                    $this->verifyVaccinationRecord($data, $user);
                } else if ($action === 'upload-document' && isset($_FILES['document'])) {
                    $this->uploadVaccinationDocument($id, $user);
                } else {
                    handleError('Invalid request', 400);
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }
    
    private function getVaccinationRecord($id, $user) {
        include_once 'models/VaccinationRecord.php';
        $record = new VaccinationRecord($this->db);
        $record->record_id = $id;
        
        if ($record->readOne()) {
            // Check if user has permission to view this record
            // Admins, government officials can view any record
            // Public users can only view their own or family members' records
            $hasPermission = in_array($user->role_id, [1, 2]);
            
            if (!$hasPermission) {
                // Check if it's the user's own record
                if ($record->user_id == $user->user_id) {
                    $hasPermission = true;
                } else {
                    // Check if it's a family member's record
                    $query = "SELECT * FROM family_relations WHERE parent_id = ? AND child_id = ?";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(1, $user->user_id);
                    $stmt->bindParam(2, $record->user_id);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $hasPermission = true;
                    }
                }
            }
            
            if ($hasPermission) {
                // Get associated documents
                $documents = [];
                try {
                    $query = "SELECT document_id, file_name, file_type, upload_date FROM documents 
                            WHERE related_entity = 'vaccination_records' AND related_record_id = ?";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(1, $id);
                    $stmt->execute();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $documents[] = $row;
                    }
                } catch (PDOException $e) {
                    error_log("Error fetching documents: " . $e->getMessage());
                }
                
                // Log activity
                logActivity($user->user_id, 'VIEW', 'vaccination_records', $id);
                
                sendResponse('success', 'Vaccination record retrieved', [
                    'record_id' => $record->record_id,
                    'user_id' => $record->user_id,
                    'user_name' => $record->user_name,
                    'vaccine_name' => $record->vaccine_name,
                    'date_administered' => $record->date_administered,
                    'dose_number' => $record->dose_number,
                    'provider' => $record->provider,
                    'lot_number' => $record->lot_number,
                    'expiration_date' => $record->expiration_date,
                    'verified' => $record->verified,
                    'verified_by' => $record->verified_by,
                    'verifier_name' => $record->verifier_name,
                    'verified_date' => $record->verified_date,
                    'created_at' => $record->created_at,
                    'documents' => $documents
                ]);
            } else {
                handleError('Unauthorized access', 403);
            }
        } else {
            handleError('Vaccination record not found', 404);
        }
    }
    
    private function getUserVaccinations($user) {
        include_once 'models/VaccinationRecord.php';
        $record = new VaccinationRecord($this->db);
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        // Get user_id from query parameter or use the authenticated user's ID
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $user->user_id;
        
        // Check if user has permission to view these records
        $hasPermission = in_array($user->role_id, [1, 2]) || $userId == $user->user_id;
        
        if (!$hasPermission && $user->role_id == 4) {
            // Check if it's a family member's record
            $query = "SELECT * FROM family_relations WHERE parent_id = ? AND child_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $user->user_id);
            $stmt->bindParam(2, $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $hasPermission = true;
            }
        }
        
        if ($hasPermission) {
            $record->user_id = $userId;
            
            try {
                $stmt = $record->readByUser($page, $limit);
                
                $records = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $records[] = [
                        'record_id' => $row['record_id'],
                        'vaccine_name' => $row['vaccine_name'],
                        'date_administered' => $row['date_administered'],
                        'dose_number' => $row['dose_number'],
                        'provider' => $row['provider'],
                        'verified' => $row['verified'],
                        'verifier_name' => $row['verifier_name'],
                        'verified_date' => $row['verified_date']
                    ];
                }
                
                // Get total count for pagination
                $total = $record->countByUser($userId);
                
                // Log activity
                logActivity($user->user_id, 'VIEW_ALL', 'vaccination_records', $userId);
                
                sendResponse('success', 'Vaccination records retrieved', [
                    'records' => $records,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            } catch (PDOException $e) {
                error_log("Error retrieving vaccination records: " . $e->getMessage());
                sendResponse('success', 'Vaccination records retrieved', [
                    'records' => [],
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => 0,
                        'pages' => 0
                    ]
                ]);
            }
        } else {
            handleError('Unauthorized access', 403);
        }
    }
    
    private function getUnverifiedRecords() {
        include_once 'models/VaccinationRecord.php';
        $record = new VaccinationRecord($this->db);
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        try {
            $stmt = $record->readUnverified($page, $limit);
            
            $records = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $records[] = [
                    'record_id' => $row['record_id'],
                    'user_id' => $row['user_id'],
                    'user_name' => $row['user_name'],
                    'prs_id' => $row['prs_id'],
                    'vaccine_name' => $row['vaccine_name'],
                    'date_administered' => $row['date_administered'],
                    'dose_number' => $row['dose_number'],
                    'provider' => $row['provider'],
                    'created_at' => $row['created_at']
                ];
            }
            
            // Get total count for pagination
            $total = $record->countUnverified();
            
            sendResponse('success', 'Unverified vaccination records retrieved', [
                'records' => $records,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (PDOException $e) {
            error_log("Error retrieving unverified records: " . $e->getMessage());
            sendResponse('success', 'Unverified vaccination records retrieved', [
                'records' => [],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => 0,
                    'pages' => 0
                ]
            ]);
        }
    }
    
    private function uploadVaccinationRecord($data, $user) {
        validateRequiredParams($data, ['vaccine_name', 'date_administered', 'dose_number', 'provider']);
        
        include_once 'models/VaccinationRecord.php';
        $record = new VaccinationRecord($this->db);
        
        // Set record properties
        $record->user_id = isset($data['user_id']) ? $data['user_id'] : $user->user_id;
        $record->vaccine_name = $data['vaccine_name'];
        $record->date_administered = $data['date_administered'];
        $record->dose_number = $data['dose_number'];
        $record->provider = $data['provider'];
        $record->lot_number = $data['lot_number'] ?? null;
        $record->expiration_date = $data['expiration_date'] ?? null;
        
        // Check if user has permission to upload record for this user_id
        $hasPermission = in_array($user->role_id, [1, 2]) || $record->user_id == $user->user_id;
        
        if (!$hasPermission && $user->role_id == 4) {
            // Check if it's a family member's record
            $query = "SELECT * FROM family_relations WHERE parent_id = ? AND child_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $user->user_id);
            $stmt->bindParam(2, $record->user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $hasPermission = true;
            }
        }
        
        if ($hasPermission) {
            if ($record->create()) {
                // Log activity
                logActivity($user->user_id, 'CREATE', 'vaccination_records', $record->record_id);
                
                sendResponse('success', 'Vaccination record created successfully', [
                    'record_id' => $record->record_id
                ], 201);
            } else {
                handleError('Failed to create vaccination record', 500);
            }
        } else {
            handleError('Unauthorized access', 403);
        }
    }
    
    private function verifyVaccinationRecord($data, $user) {
        validateRequiredParams($data, ['record_id']);
        
        include_once 'models/VaccinationRecord.php';
        $record = new VaccinationRecord($this->db);
        $record->record_id = $data['record_id'];
        
        if ($record->readOne()) {
            $record->verified_by = $user->user_id;
            
            if ($record->verify()) {
                // Log activity
                logActivity($user->user_id, 'VERIFY', 'vaccination_records', $record->record_id);
                
                sendResponse('success', 'Vaccination record verified successfully');
            } else {
                handleError('Failed to verify vaccination record', 500);
            }
        } else {
            handleError('Vaccination record not found', 404);
        }
    }
    
    private function uploadVaccinationDocument($id, $user) {
        // Validate the record exists
        include_once 'models/VaccinationRecord.php';
        $record = new VaccinationRecord($this->db);
        $record->record_id = $id;
        
        if (!$record->readOne()) {
            handleError('Vaccination record not found', 404);
        }
        
        // Check if user has permission to upload document
        $hasPermission = in_array($user->role_id, [1, 2]) || $record->user_id == $user->user_id;
        
        if (!$hasPermission && $user->role_id == 4) {
            // Check if it's a family member's record
            $query = "SELECT * FROM family_relations WHERE parent_id = ? AND child_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $user->user_id);
            $stmt->bindParam(2, $record->user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $hasPermission = true;
            }
        }
        
        if ($hasPermission) {
            // Handle file upload
            $file = $_FILES['document'];
            $fileName = $file['name'];
            $fileType = $file['type'];
            $fileSize = $file['size'];
            $fileTmpPath = $file['tmp_name'];
            
            // Create uploads directory if it doesn't exist
            $uploadsDir = __DIR__ . '/../uploads/vaccination';
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            // Generate unique filename
            $uniqueFileName = uniqid() . '_' . $fileName;
            $uploadPath = $uploadsDir . '/' . $uniqueFileName;
            
            // Check file type (allow PDF, JPG, PNG)
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!in_array($fileType, $allowedTypes)) {
                handleError('Invalid file type. Only PDF, JPG, and PNG are allowed', 400);
            }
            
            // Check file size (limit to 5MB)
            if ($fileSize > 5 * 1024 * 1024) {
                handleError('File size too large. Maximum allowed is 5MB', 400);
            }
            
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                // Save document info to database
                $query = "INSERT INTO documents (file_name, file_type, file_path, file_size, uploaded_by, related_entity, related_record_id)
                        VALUES (?, ?, ?, ?, ?, 'vaccination_records', ?)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $fileName);
                $stmt->bindParam(2, $fileType);
                $stmt->bindParam(3, $uniqueFileName);
                $stmt->bindParam(4, $fileSize);
                $stmt->bindParam(5, $user->user_id);
                $stmt->bindParam(6, $id);
                
                if ($stmt->execute()) {
                    $documentId = $this->db->lastInsertId();
                    
                    // Log activity
                    logActivity($user->user_id, 'UPLOAD_DOCUMENT', 'documents', $documentId);
                    
                    sendResponse('success', 'Document uploaded successfully', [
                        'document_id' => $documentId,
                        'file_name' => $fileName,
                        'file_type' => $fileType
                    ], 201);
                } else {
                    handleError('Failed to save document information', 500);
                }
            } else {
                handleError('Failed to upload file', 500);
            }
        } else {
            handleError('Unauthorized access', 403);
        }
    }
    
    private function getVaccinationStats() {
        include_once 'models/VaccinationRecord.php';
        $record = new VaccinationRecord($this->db);
        
        // Default empty statistics
        $stats = [
            'total_records' => 0,
            'verified_records' => 0,
            'total_users' => 0,
            'vaccine_types' => 0
        ];
        
        $vaccineDistribution = [];
        $vaccinationTrend = [];
        
        try {
            // Get overall statistics
            $stats = $record->getStatistics() ?: $stats;
            
            // Get vaccination distribution by vaccine type
            $stmt = $record->getVaccineDistribution();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vaccineDistribution[] = [
                    'vaccine_name' => $row['vaccine_name'],
                    'count' => $row['count']
                ];
            }
            
            // Get vaccination trend
            $stmt = $record->getVaccinationTrend();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vaccinationTrend[] = [
                    'month' => $row['month'],
                    'count' => $row['count']
                ];
            }
        } catch (PDOException $e) {
            error_log("Error fetching vaccination statistics: " . $e->getMessage());
        }
        
        sendResponse('success', 'Vaccination statistics retrieved', [
            'stats' => $stats,
            'vaccine_distribution' => $vaccineDistribution,
            'vaccination_trend' => $vaccinationTrend
        ]);
    }
}
?>