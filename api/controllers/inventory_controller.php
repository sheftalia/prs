<?php
class InventoryController {
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
                        if ($action === 'location' && $id) {
                            $this->getLocationInventory($id, $user);
                        } else if ($action === 'find') {
                            $this->findItemLocations($user);
                        } else if ($action === 'low-stock' && in_array($user->role_id, [1, 2, 3])) {
                            $this->getLowStockItems($user);
                        } else {
                            // Default action - return all inventory items
                            $this->getAllInventory($user);
                        }
                        break;
                
            case 'POST':
                // Only merchants and admins can update inventory
                if ($user->role_id > 3) {
                    handleError('Unauthorized access', 403);
                }
                
                $data = json_decode(file_get_contents("php://input"), true);
                
                if ($action === 'update') {
                    $this->updateInventory($data, $user);
                } else {
                    handleError('Invalid action', 400);
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }

    private function getAllInventory($user) {
        // Create empty response structure
        $inventoryData = [
            'items' => [],
            'pagination' => [
                'page' => 1,
                'limit' => 10,
                'total' => 0,
                'pages' => 0
            ]
        ];
        
        try {
            // Get inventory items with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            $query = "SELECT i.*, ci.item_name, ci.item_category, ml.location_name 
                    FROM inventory i
                    JOIN critical_items ci ON i.item_id = ci.item_id
                    JOIN merchant_locations ml ON i.location_id = ml.location_id
                    LIMIT :limit OFFSET :offset";
            
            $offset = ($page - 1) * $limit;
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $items = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $items[] = [
                    'inventory_id' => $row['inventory_id'],
                    'item_id' => $row['item_id'],
                    'item_name' => $row['item_name'],
                    'item_category' => $row['item_category'],
                    'location_id' => $row['location_id'],
                    'location_name' => $row['location_name'],
                    'quantity_available' => $row['quantity_available'],
                    'last_updated' => $row['last_updated']
                ];
            }
            
            // Count total items for pagination
            $query = "SELECT COUNT(*) as total FROM inventory";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $row['total'] ?? 0;
            
            $inventoryData = [
                'items' => $items,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            // Still return the empty structure
        }
        
        sendResponse('success', 'Inventory retrieved', $inventoryData);
    }
    
    private function getLocationInventory($locationId, $user) {
        // Check if user has permission to view this location's inventory
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
            include_once 'models/Inventory.php';
            $inventory = new Inventory($this->db);
            $inventory->location_id = $locationId;
            
            try {
                $stmt = $inventory->readByLocation();
                
                $items = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $items[] = [
                        'inventory_id' => $row['inventory_id'],
                        'item_id' => $row['item_id'],
                        'item_name' => $row['item_name'],
                        'item_category' => $row['item_category'],
                        'quantity_available' => $row['quantity_available'],
                        'purchase_limit' => $row['purchase_limit'],
                        'purchase_frequency' => $row['purchase_frequency'],
                        'last_updated' => $row['last_updated']
                    ];
                }
                
                // Get location details
                $location = [];
                try {
                    $query = "SELECT ml.*, mb.business_name
                            FROM merchant_locations ml
                            JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                            WHERE ml.location_id = ?";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(1, $locationId);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $location = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                } catch (PDOException $e) {
                    error_log("Error fetching location details: " . $e->getMessage());
                }
                
                sendResponse('success', 'Inventory retrieved', [
                    'location' => $location,
                    'inventory' => $items
                ]);
            } catch (PDOException $e) {
                error_log("Error retrieving inventory: " . $e->getMessage());
                sendResponse('success', 'Inventory retrieved', [
                    'location' => [],
                    'inventory' => []
                ]);
            }
        } else {
            handleError('Unauthorized access', 403);
        }
    }
    
    private function findItemLocations($user) {
        // Validate required parameters
        if (!isset($_GET['item_id'])) {
            handleError('Item ID not provided', 400);
        }
        
        $itemId = (int)$_GET['item_id'];
        $userLat = $_GET['lat'] ?? null;
        $userLng = $_GET['lng'] ?? null;
        $radius = $_GET['radius'] ?? 10; // Default 10 km radius
        
        // If location is not provided, try to get a default location
        if (!$userLat || !$userLng) {
            // For simplicity, use a default location (London)
            $userLat = 51.5074;
            $userLng = -0.1278;
        }
        
        include_once 'models/Inventory.php';
        $inventory = new Inventory($this->db);
        
        try {
            $stmt = $inventory->findLocationsWithStock($itemId, $userLat, $userLng, $radius);
            
            $locations = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $locations[] = [
                    'location_id' => $row['location_id'],
                    'location_name' => $row['location_name'],
                    'business_name' => $row['business_name'],
                    'address' => $row['address_line1'] . ($row['address_line2'] ? ', ' . $row['address_line2'] : ''),
                    'city' => $row['city'],
                    'postal_code' => $row['postal_code'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'quantity_available' => $row['quantity_available'],
                    'distance' => round($row['distance'], 2) // Distance in km, rounded to 2 decimal places
                ];
            }
            
            // Get item details
            include_once 'models/CriticalItem.php';
            $item = new CriticalItem($this->db);
            $item->item_id = $itemId;
            
            // Default item values if the item cannot be found
            $itemData = [
                'item_id' => $itemId,
                'item_name' => 'Unknown Item',
                'item_description' => '',
                'item_category' => '',
                'purchase_limit' => 0,
                'purchase_frequency' => 'daily'
            ];
            
            $canPurchase = true;
            $hasReachedLimit = false;
            
            if ($item->readOne()) {
                $itemData = [
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_description' => $item->item_description,
                    'item_category' => $item->item_category,
                    'purchase_limit' => $item->purchase_limit,
                    'purchase_frequency' => $item->purchase_frequency
                ];
                
                // Check if the user can purchase the item based on DOB restrictions
                if ($user->role_id == 4) {
                    // Get user's DOB
                    try {
                        $query = "SELECT dob FROM users WHERE user_id = ?";
                        $stmt = $this->db->prepare($query);
                        $stmt->bindParam(1, $user->user_id);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $userDob = $row['dob'];
                            $canPurchase = $item->canPurchase($userDob);
                        }
                    } catch (PDOException $e) {
                        error_log("Error fetching user DOB: " . $e->getMessage());
                    }
                    
                    // Check if the user has reached purchase limit
                    $hasReachedLimit = $item->hasReachedLimit($user->user_id);
                }
            }
            
            sendResponse('success', 'Locations retrieved', [
                'item' => $itemData,
                'can_purchase_today' => $canPurchase,
                'has_reached_limit' => $hasReachedLimit,
                'locations' => $locations
            ]);
        } catch (PDOException $e) {
            error_log("Error finding item locations: " . $e->getMessage());
            sendResponse('success', 'Locations retrieved', [
                'item' => [
                    'item_id' => $itemId,
                    'item_name' => 'Unknown Item',
                    'item_description' => '',
                    'item_category' => '',
                    'purchase_limit' => 0,
                    'purchase_frequency' => 'daily'
                ],
                'can_purchase_today' => true,
                'has_reached_limit' => false,
                'locations' => []
            ]);
        }
    }
    
    private function updateInventory($data, $user) {
        validateRequiredParams($data, ['location_id', 'items']);
        
        // Check if user has permission to update this location's inventory
        $locationId = $data['location_id'];
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
            include_once 'models/Inventory.php';
            $inventory = new Inventory($this->db);
            $inventory->location_id = $locationId;
            $inventory->updated_by = $user->user_id;
            
            $successCount = 0;
            $failCount = 0;
            
            foreach ($data['items'] as $item) {
                if (isset($item['item_id']) && isset($item['quantity'])) {
                    $inventory->item_id = $item['item_id'];
                    $inventory->quantity_available = $item['quantity'];
                    
                    if ($inventory->createOrUpdate()) {
                        $successCount++;
                        
                        // Log activity
                        logActivity($user->user_id, 'UPDATE_INVENTORY', 'inventory', $inventory->inventory_id);
                    } else {
                        $failCount++;
                    }
                }
            }
            
            sendResponse('success', 'Inventory updated', [
                'success_count' => $successCount,
                'fail_count' => $failCount
            ]);
        } else {
            handleError('Unauthorized access', 403);
        }
    }
    
    private function getLowStockItems($user) {
        // Get threshold parameter
        $threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 10;
        
        include_once 'models/Inventory.php';
        $inventory = new Inventory($this->db);
        
        try {
            $stmt = $inventory->getLowStockItems($threshold);
            
            $items = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $items[] = [
                    'inventory_id' => $row['inventory_id'],
                    'item_id' => $row['item_id'],
                    'item_name' => $row['item_name'],
                    'location_id' => $row['location_id'],
                    'location_name' => $row['location_name'],
                    'business_name' => $row['business_name'],
                    'quantity_available' => $row['quantity_available'],
                    'last_updated' => $row['last_updated']
                ];
            }
            
            sendResponse('success', 'Low stock items retrieved', [
                'threshold' => $threshold,
                'items' => $items
            ]);
        } catch (PDOException $e) {
            error_log("Error retrieving low stock items: " . $e->getMessage());
            sendResponse('success', 'Low stock items retrieved', [
                'threshold' => $threshold,
                'items' => []
            ]);
        }
    }
}
?>