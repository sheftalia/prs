<?php
class DocumentController {
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
                    $this->getDocument($id, $user);
                } else {
                    handleError('Document ID not provided', 400);
                }
                break;
                
            case 'DELETE':
                if ($id) {
                    $this->deleteDocument($id, $user);
                } else {
                    handleError('Document ID not provided', 400);
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }
    
    private function getDocument($id, $user) {
        // Check if document exists
        $query = "SELECT d.*, u.full_name as uploader_name 
                FROM documents d
                JOIN users u ON d.uploaded_by = u.user_id
                WHERE d.document_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            handleError('Document not found', 404);
        }
        
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if user has permission to view this document
        $hasPermission = in_array($user->role_id, [1, 2]);
        
        if (!$hasPermission) {
            if ($document['uploaded_by'] == $user->user_id) {
                $hasPermission = true;
            } else if ($document['related_entity'] === 'vaccination_records') {
                // Check if it's the user's own vaccination record
                $query = "SELECT user_id FROM vaccination_records WHERE record_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $document['related_record_id']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($row['user_id'] == $user->user_id) {
                        $hasPermission = true;
                    } else if ($user->role_id == 4) {
                        // Check if it's a family member's record
                        $query = "SELECT * FROM family_relations WHERE parent_id = ? AND child_id = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->bindParam(1, $user->user_id);
                        $stmt->bindParam(2, $row['user_id']);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            $hasPermission = true;
                        }
                    }
                }
            }
        }
        
        if ($hasPermission) {
            $filePath = __DIR__ . '/../uploads/' . $document['related_entity'] . '/' . $document['file_path'];
            
            if (file_exists($filePath)) {
                // Log activity
                logActivity($user->user_id, 'VIEW_DOCUMENT', 'documents', $id);
                
                // Output file
                header('Content-Type: ' . $document['file_type']);
                header('Content-Disposition: inline; filename="' . $document['file_name'] . '"');
                header('Content-Length: ' . $document['file_size']);
                readfile($filePath);
                exit;
            } else {
                handleError('Document file not found', 404);
            }
        } else {
            handleError('Unauthorized access', 403);
        }
    }
    
    private function deleteDocument($id, $user) {
        // Check if document exists
        $query = "SELECT * FROM documents WHERE document_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            handleError('Document not found', 404);
        }
        
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if user has permission to delete this document
        $hasPermission = in_array($user->role_id, [1, 2]);
        
        if (!$hasPermission && $document['uploaded_by'] == $user->user_id) {
            $hasPermission = true;
        }
        
        if ($hasPermission) {
            // Delete file
            $filePath = __DIR__ . '/../uploads/' . $document['related_entity'] . '/' . $document['file_path'];
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete database record
            $query = "DELETE FROM documents WHERE document_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $id);
            
            if ($stmt->execute()) {
                // Log activity
                logActivity($user->user_id, 'DELETE_DOCUMENT', 'documents', $id);
                
                sendResponse('success', 'Document deleted successfully');
            } else {
                handleError('Failed to delete document', 500);
            }
        } else {
            handleError('Unauthorized access', 403);
        }
    }
}
?>