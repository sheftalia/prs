<?php
class UserController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function processRequest($id = null, $action = null) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Check authentication for all requests except specific public endpoints
        $user = getCurrentUser();
        if (!$user && !($method === 'GET' && $action === 'public')) {
            handleError('Unauthorized access', 401);
        }
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getUser($id, $user);
                } else if ($action === 'profile') {
                    $this->getProfile($user);
                } else if ($action === 'public') {
                    $this->getPublicData();
                } else {
                    $this->listUsers($user);
                }
                break;
                
            case 'PUT':
                if ($id) {
                    $data = json_decode(file_get_contents("php://input"), true);
                    $this->updateUser($id, $data, $user);
                } else {
                    handleError('User ID not provided', 400);
                }
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);
                
                if ($action === 'change-password') {
                    $this->changePassword($data, $user);
                } else if ($action === 'add-family') {
                    $this->addFamilyMember($data, $user);
                } else {
                    handleError('Invalid action', 400);
                }
                break;
                
            case 'DELETE':
                if ($id && $action === 'family') {
                    $this->removeFamilyMember($id, $user);
                } else {
                    handleError('Invalid request', 400);
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }
    
    private function getUser($id, $user) {
        // Only admins and government officials can view other users' details
        if ($user->user_id != $id && $user->role_id > 2) {
            handleError('Unauthorized access', 403);
        }
        
        include_once 'models/User.php';
        $userModel = new User($this->db);
        $userModel->user_id = $id;
        
        if ($userModel->readOne()) {
            // Log activity
            logActivity($user->user_id, 'VIEW', 'users', $id);
            
            sendResponse('success', 'User details retrieved', [
                'user_id' => $userModel->user_id,
                'full_name' => $userModel->full_name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'prs_id' => $userModel->prs_id,
                'role_id' => $userModel->role_id,
                'created_at' => $userModel->created_at,
                'account_status' => $userModel->account_status
            ]);
        } else {
            handleError('User not found', 404);
        }
    }
    
    private function getProfile($user) {
        include_once 'models/User.php';
        $userModel = new User($this->db);
        $userModel->user_id = $user->user_id;
        
        if ($userModel->readOne()) {
            // Get family members if applicable
            $familyMembers = [];
            
            if ($userModel->role_id == 4) { // Public User
                $query = "SELECT u.user_id, u.full_name, u.prs_id, u.dob, fr.relation_type
                        FROM family_relations fr
                        JOIN users u ON fr.child_id = u.user_id
                        WHERE fr.parent_id = ?";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $user->user_id);
                $stmt->execute();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $familyMembers[] = $row;
                }
            }
            
            sendResponse('success', 'Profile retrieved', [
                'user_id' => $userModel->user_id,
                'full_name' => $userModel->full_name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'national_id' => $userModel->national_id,
                'dob' => $userModel->dob,
                'prs_id' => $userModel->prs_id,
                'role_id' => $userModel->role_id,
                'created_at' => $userModel->created_at,
                'account_status' => $userModel->account_status,
                'family_members' => $familyMembers
            ]);
        } else {
            handleError('User not found', 404);
        }
    }
    
    private function listUsers($user) {
        // Only admins and government officials can list users
        if ($user->role_id > 2) {
            handleError('Unauthorized access', 403);
        }
        
        include_once 'models/User.php';
        $userModel = new User($this->db);
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        // Get search parameter
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        
        if ($search) {
            $stmt = $userModel->search($search, $page, $limit);
        } else {
            $stmt = $userModel->readAll($page, $limit);
        }
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = [
                'user_id' => $row['user_id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'prs_id' => $row['prs_id'],
                'role_id' => $row['role_id'],
                'role_name' => $row['role_name'],
                'created_at' => $row['created_at'],
                'account_status' => $row['account_status']
            ];
        }
        
        // Get total count for pagination
        $total = $userModel->countAll();
        
        sendResponse('success', 'Users retrieved', [
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    private function updateUser($id, $data, $user) {
        // Users can only update their own profile, except admins
        if ($user->user_id != $id && $user->role_id > 1) {
            handleError('Unauthorized access', 403);
        }
        
        include_once 'models/User.php';
        $userModel = new User($this->db);
        $userModel->user_id = $id;
        
        if ($userModel->readOne()) {
            // Update user properties
            $userModel->full_name = $data['full_name'] ?? $userModel->full_name;
            $userModel->phone = $data['phone'] ?? $userModel->phone;
            
            // Only admins can update account status
            if ($user->role_id == 1 && isset($data['account_status'])) {
                $userModel->account_status = $data['account_status'];
            }
            
            if ($userModel->update()) {
                // Log activity
                logActivity($user->user_id, 'UPDATE', 'users', $id);
                
                sendResponse('success', 'User updated successfully');
            } else {
                handleError('Failed to update user', 500);
            }
        } else {
            handleError('User not found', 404);
        }
    }
    
    private function changePassword($data, $user) {
        validateRequiredParams($data, ['current_password', 'new_password']);
        
        include_once 'models/User.php';
        $userModel = new User($this->db);
        $userModel->user_id = $user->user_id;
        
        if ($userModel->readOne()) {
            // Verify current password
            if (password_verify($data['current_password'], $userModel->password_hash)) {
                // Update password
                $userModel->password_hash = password_hash($data['new_password'], PASSWORD_DEFAULT);
                
                if ($userModel->changePassword()) {
                    // Log activity
                    logActivity($user->user_id, 'PASSWORD_CHANGE', 'users', $user->user_id);
                    
                    sendResponse('success', 'Password changed successfully');
                } else {
                    handleError('Failed to change password', 500);
                }
            } else {
                handleError('Current password is incorrect', 401);
            }
        } else {
            handleError('User not found', 404);
        }
    }
    
    private function addFamilyMember($data, $user) {
        validateRequiredParams($data, ['full_name', 'dob', 'relation_type']);
        
        // Only public users can add family members
        if ($user->role_id != 4) {
            handleError('Only public users can add family members', 403);
        }
        
        include_once 'models/User.php';
        $childUser = new User($this->db);
        
        // Set child user properties
        $childUser->full_name = $data['full_name'];
        $childUser->email = $user->user_id . '_family_' . time() . '@prs.system'; // Generate a unique email
        $childUser->password_hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT); // Random password
        $childUser->dob = $data['dob'];
        $childUser->role_id = 4; // Public User
        
        // Create the child user
        if ($childUser->create()) {
            // Create family relation
            $query = "INSERT INTO family_relations (parent_id, child_id, relation_type) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $user->user_id);
            $stmt->bindParam(2, $childUser->user_id);
            $stmt->bindParam(3, $data['relation_type']);
            
            if ($stmt->execute()) {
                // Log activity
                logActivity($user->user_id, 'ADD_FAMILY', 'users', $childUser->user_id);
                
                sendResponse('success', 'Family member added successfully', [
                    'user_id' => $childUser->user_id,
                    'full_name' => $childUser->full_name,
                    'prs_id' => $childUser->prs_id,
                    'relation_type' => $data['relation_type']
                ], 201);
            } else {
                handleError('Failed to create family relation', 500);
            }
        } else {
            handleError('Failed to create family member', 500);
        }
    }
    
    private function removeFamilyMember($id, $user) {
        // Only public users can remove family members
        if ($user->role_id != 4) {
            handleError('Only public users can remove family members', 403);
        }
        
        // Check if the family relation exists
        $query = "SELECT * FROM family_relations WHERE parent_id = ? AND child_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $user->user_id);
        $stmt->bindParam(2, $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Remove the family relation
            $query = "DELETE FROM family_relations WHERE parent_id = ? AND child_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $user->user_id);
            $stmt->bindParam(2, $id);
            
            if ($stmt->execute()) {
                // Log activity
                logActivity($user->user_id, 'REMOVE_FAMILY', 'users', $id);
                
                sendResponse('success', 'Family member removed successfully');
            } else {
                handleError('Failed to remove family member', 500);
            }
        } else {
            handleError('Family relation not found', 404);
        }
    }
    
    private function getPublicData() {
        // This endpoint is public and provides general data for the landing page
        
        // Get system settings
        $query = "SELECT setting_name, setting_value FROM settings WHERE setting_group = 'general'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
        
        // Get vaccination statistics
        $query = "SELECT COUNT(*) as total_records FROM vaccination_records";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $vaccinationStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get number of registered users
        $query = "SELECT COUNT(*) as total_users FROM users WHERE role_id = 4";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse('success', 'Public data retrieved', [
            'system_name' => $settings['system_name'] ?? 'Pandemic Resilience System',
            'total_vaccinations' => $vaccinationStats['total_records'],
            'total_users' => $userStats['total_users']
        ]);
    }
}
?>