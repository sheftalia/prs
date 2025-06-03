<?php
class ItemController {
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
                    $this->getItem($id);
                } else if ($action === 'active') {
                    $this->getActiveItems();
                } else {
                    $this->listItems($user);
                }
                break;
                
            case 'POST':
                // Only admins and government officials can create items
                if ($user->role_id > 2) {
                    handleError('Unauthorized access', 403);
                }
                
                $data = json_decode(file_get_contents("php://input"), true);
                $this->createItem($data, $user);
                break;
                
            case 'PUT':
                // Only admins and government officials can update items
                if ($user->role_id > 2) {
                    handleError('Unauthorized access', 403);
                }
                
                if ($id) {
                    $data = json_decode(file_get_contents("php://input"), true);
                    $this->updateItem($id, $data, $user);
                } else {
                    handleError('Item ID not provided', 400);
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }
    
    private function getItem($id) {
        include_once 'models/CriticalItem.php';
        $item = new CriticalItem($this->db);
        $item->item_id = $id;
        
        if ($item->readOne()) {
            sendResponse('success', 'Item retrieved successfully', [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_description' => $item->item_description,
                'item_category' => $item->item_category,
                'purchase_limit' => $item->purchase_limit,
                'purchase_frequency' => $item->purchase_frequency,
                'dob_restriction' => $item->dob_restriction,
                'is_active' => $item->is_active,
                'created_at' => $item->created_at,
                'created_by' => $item->created_by,
                'creator_name' => $item->creator_name
            ]);
        } else {
            handleError('Item not found', 404);
        }
    }
    
    private function getActiveItems() {
        include_once 'models/CriticalItem.php';
        $item = new CriticalItem($this->db);
        
        try {
            $stmt = $item->readActive();
            
            $items = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $items[] = [
                    'item_id' => $row['item_id'],
                    'item_name' => $row['item_name'],
                    'item_description' => $row['item_description'],
                    'item_category' => $row['item_category'],
                    'purchase_limit' => $row['purchase_limit'],
                    'purchase_frequency' => $row['purchase_frequency'],
                    'dob_restriction' => $row['dob_restriction']
                ];
            }
            
            // Even if there are no items, we still return success with an empty array
            sendResponse('success', 'Active items retrieved', [
                'items' => $items
            ]);
        } catch (PDOException $e) {
            error_log("Error retrieving active items: " . $e->getMessage());
            sendResponse('success', 'Active items retrieved', [
                'items' => [] // Return empty array in case of error
            ]);
        }
    }
    
    private function listItems($user) {
        include_once 'models/CriticalItem.php';
        $item = new CriticalItem($this->db);
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        try {
            $stmt = $item->readAll($page, $limit);
            
            $items = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $items[] = [
                    'item_id' => $row['item_id'],
                    'item_name' => $row['item_name'],
                    'item_category' => $row['item_category'],
                    'purchase_limit' => $row['purchase_limit'],
                    'purchase_frequency' => $row['purchase_frequency'],
                    'is_active' => $row['is_active'],
                    'created_at' => $row['created_at'],
                    'creator_name' => $row['creator_name']
                ];
            }
            
            // Get total count for pagination
            $total = $item->countAll();
            
            sendResponse('success', 'Items retrieved', [
                'items' => $items,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (PDOException $e) {
            error_log("Error retrieving items: " . $e->getMessage());
            sendResponse('success', 'Items retrieved', [
                'items' => [],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => 0,
                    'pages' => 0
                ]
            ]);
        }
    }
    
    private function createItem($data, $user) {
        validateRequiredParams($data, ['item_name', 'item_category', 'purchase_limit', 'purchase_frequency']);
        
        include_once 'models/CriticalItem.php';
        $item = new CriticalItem($this->db);
        
        // Set item properties
        $item->item_name = $data['item_name'];
        $item->item_description = $data['item_description'] ?? null;
        $item->item_category = $data['item_category'];
        $item->purchase_limit = $data['purchase_limit'];
        $item->purchase_frequency = $data['purchase_frequency'];
        $item->dob_restriction = $data['dob_restriction'] ?? null;
        $item->created_by = $user->user_id;
        
        if ($item->create()) {
            // Log activity
            logActivity($user->user_id, 'CREATE', 'critical_items', $item->item_id);
            
            sendResponse('success', 'Item created successfully', [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name
            ], 201);
        } else {
            handleError('Failed to create item', 500);
        }
    }
    
    private function updateItem($id, $data, $user) {
        include_once 'models/CriticalItem.php';
        $item = new CriticalItem($this->db);
        $item->item_id = $id;
        
        if ($item->readOne()) {
            // Update item properties
            $item->item_name = $data['item_name'] ?? $item->item_name;
            $item->item_description = $data['item_description'] ?? $item->item_description;
            $item->item_category = $data['item_category'] ?? $item->item_category;
            $item->purchase_limit = $data['purchase_limit'] ?? $item->purchase_limit;
            $item->purchase_frequency = $data['purchase_frequency'] ?? $item->purchase_frequency;
            $item->dob_restriction = $data['dob_restriction'] ?? $item->dob_restriction;
            $item->is_active = isset($data['is_active']) ? $data['is_active'] : $item->is_active;
            
            if ($item->update()) {
                // Log activity
                logActivity($user->user_id, 'UPDATE', 'critical_items', $id);
                
                sendResponse('success', 'Item updated successfully');
            } else {
                handleError('Failed to update item', 500);
            }
        } else {
            handleError('Item not found', 404);
        }
    }
}
?>