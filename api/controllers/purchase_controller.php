<?php
class PurchaseController {
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
                if ($action === 'history') {
                    $this->getPurchaseHistory($user);
                } else if ($action === 'stats' && $id && in_array($user->role_id, [1, 2, 3])) {
                    $this->getLocationStats($id, $user);
                } else if ($action === 'top-selling' && in_array($user->role_id, [1, 2])) {
                    $this->getTopSellingItems($user);
                } else {
                    handleError('Invalid request', 400);
                }
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);
                
                if ($action === 'record') {
                    $this->recordPurchase($data, $user);
                } else {
                    handleError('Invalid action', 400);
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }
    
    private function getPurchaseHistory($user) {
        include_once 'models/Purchase.php';
        $purchase = new Purchase($this->db);
        
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
            $purchase->user_id = $userId;
            $stmt = $purchase->readByUser($page, $limit);
            
            $purchases = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $purchases[] = [
                    'purchase_id' => $row['purchase_id'],
                    'item_name' => $row['item_name'],
                    'location_name' => $row['location_name'],
                    'business_name' => $row['business_name'],
                    'quantity' => $row['quantity'],
                    'purchase_date' => $row['purchase_date']
                ];
            }
            
            // Log activity
            logActivity($user->user_id, 'VIEW_PURCHASES', 'purchases', $userId);
            
            sendResponse('success', 'Purchase history retrieved', [
                'purchases' => $purchases
            ]);
        } else {
            handleError('Unauthorized access', 403);
        }
    }
    
    private function recordPurchase($data, $user) {
        validateRequiredParams($data, ['user_id', 'item_id', 'location_id', 'quantity']);
        
        // Check if user has permission to record purchases
        // Admins, government officials, and merchants can record purchases
        if ($user->role_id > 3) {
            handleError('Unauthorized access', 403);
        }
        
        // If merchant, check if they're associated with the location
        if ($user->role_id == 3) {
            $locationId = $data['location_id'];
            
            $query = "SELECT * FROM merchant_locations WHERE location_id = ? AND manager_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $locationId);
            $stmt->bindParam(2, $user->user_id);
            $stmt->execute();
            
            $hasPermission = $stmt->rowCount() > 0;
            
            if (!$hasPermission) {
                // Check if merchant is associated with the business that owns this location
                $query = "SELECT mb.business_id FROM merchant_locations ml
                        JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                        WHERE ml.location_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $locationId);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $businessId = $row['business_id'];
                    
                    // Check if user is associated with this business
                    $query = "SELECT * FROM merchant_locations WHERE business_id = ? AND manager_id = ?";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(1, $businessId);
                    $stmt->bindParam(2, $user->user_id);
                    $stmt->execute();
                    
                    $hasPermission = $stmt->rowCount() > 0;
                }
            }
            
            if (!$hasPermission) {
                handleError('Unauthorized access', 403);
            }
        }
        
        // Check if the item exists and is active
        $query = "SELECT * FROM critical_items WHERE item_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $data['item_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            handleError('Item not found or inactive', 404);
        }
        
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if the user exists
        $query = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $data['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            handleError('User not found', 404);
        }
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check DOB restrictions
        if (!empty($item['dob_restriction'])) {
            include_once 'models/CriticalItem.php';
            $criticalItem = new CriticalItem($this->db);
            $criticalItem->item_id = $data['item_id'];
            $criticalItem->readOne();
            
            if (!$criticalItem->canPurchase($userData['dob'])) {
                handleError('User cannot purchase this item today based on DOB restrictions', 400);
            }
        }
        
        // Check purchase limits
        if (!empty($item['purchase_limit'])) {
            include_once 'models/CriticalItem.php';
            $criticalItem = new CriticalItem($this->db);
            $criticalItem->item_id = $data['item_id'];
            $criticalItem->readOne();
            
            if ($criticalItem->hasReachedLimit($data['user_id'])) {
                handleError('User has reached the purchase limit for this item', 400);
            }
        }
        
        // Check if there's enough stock
        include_once 'models/Inventory.php';
        $inventory = new Inventory($this->db);
        $inventory->item_id = $data['item_id'];
        $inventory->location_id = $data['location_id'];
        
        // Attempt to decrease stock
        if (!$inventory->decreaseStock($data['quantity'])) {
            handleError('Insufficient stock available', 400);
        }
        
        // Record the purchase
        include_once 'models/Purchase.php';
        $purchase = new Purchase($this->db);
        $purchase->user_id = $data['user_id'];
        $purchase->item_id = $data['item_id'];
        $purchase->location_id = $data['location_id'];
        $purchase->quantity = $data['quantity'];
        
        if ($purchase->create()) {
            // Log activity
            logActivity($user->user_id, 'RECORD_PURCHASE', 'purchases', $purchase->purchase_id);
            
            sendResponse('success', 'Purchase recorded successfully', [
                'purchase_id' => $purchase->purchase_id
            ], 201);
        } else {
            handleError('Failed to record purchase', 500);
        }
    }
    
    private function getLocationStats($locationId, $user) {
        // Check if user has permission to view stats for this location
        $hasPermission = in_array($user->role_id, [1, 2]);
        
        if (!$hasPermission && $user->role_id == 3) {
            // Check if merchant is associated with this location
            $query = "SELECT * FROM merchant_locations WHERE location_id = ? AND manager_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $locationId);
            $stmt->bindParam(2, $user->user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $hasPermission = true;
            } else {
                // Check if merchant is associated with the business that owns this location
                $query = "SELECT mb.business_id FROM merchant_locations ml
                        JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                        WHERE ml.location_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $locationId);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $businessId = $row['business_id'];
                    
                    // Check if user is associated with this business
                    $query = "SELECT * FROM merchant_locations WHERE business_id = ? AND manager_id = ?";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(1, $businessId);
                    $stmt->bindParam(2, $user->user_id);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $hasPermission = true;
                    }
                }
            }
        }
        
        if ($hasPermission) {
            include_once 'models/Purchase.php';
            $purchase = new Purchase($this->db);
            
            $stmt = $purchase->getLocationStats($locationId);
            
            $stats = [];
            $totalTransactions = 0;
            $totalItemsSold = 0;
            $uniqueCustomers = 0;
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats[] = [
                    'date' => $row['date'],
                    'daily_transactions' => $row['daily_transactions']
                ];
                
                // Update totals from the first row
                if (empty($totalTransactions)) {
                    $totalTransactions = $row['total_transactions'];
                    $totalItemsSold = $row['total_items_sold'];
                    $uniqueCustomers = $row['unique_customers'];
                }
            }
            
            // Get location details
            $query = "SELECT ml.*, mb.business_name
                    FROM merchant_locations ml
                    JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                    WHERE ml.location_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $locationId);
            $stmt->execute();
            
            $location = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse('success', 'Location statistics retrieved', [
                'location' => $location,
                'summary' => [
                    'total_transactions' => $totalTransactions,
                    'total_items_sold' => $totalItemsSold,
                    'unique_customers' => $uniqueCustomers
                ],
                'daily_stats' => $stats
            ]);
        } else {
            handleError('Unauthorized access', 403);
        }
    }
    
    private function getTopSellingItems($user) {
        include_once 'models/Purchase.php';
        $purchase = new Purchase($this->db);
        
        // Get optional location_id parameter
        $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
        
        // Get limit parameter
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        
        $stmt = $purchase->getTopSellingItems($locationId, $limit);
        
        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'item_id' => $row['item_id'],
                'item_name' => $row['item_name'],
                'item_category' => $row['item_category'],
                'total_sold' => $row['total_sold']
            ];
        }
        
        sendResponse('success', 'Top selling items retrieved', [
            'items' => $items
        ]);
    }
}
?>