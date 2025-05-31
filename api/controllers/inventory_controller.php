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
                    handleError('Invalid request', 400);
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
            $query = "SELECT ml.*, mb.business_name
                    FROM merchant_locations ml
                    JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                    WHERE ml.location_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $locationId);
            $stmt->execute();
            
            $location = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse('success', 'Inventory retrieved', [
                'location' => $location,
                'inventory' => $items
            ]);
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
        $item->readOne();
        
        // Check if the user can purchase the item based on DOB restrictions
        $canPurchase = true;
        
        if ($user->role_id == 4) {
            // Get user's DOB
            $query = "SELECT dob FROM users WHERE user_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $user->user_id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $userDob = $row['dob'];
            
            $canPurchase = $item->canPurchase($userDob);
        }
        
        // Check if the user has reached purchase limit
        $hasReachedLimit = false;
        
        if ($user->role_id == 4) {
            $hasReachedLimit = $item->hasReachedLimit($user->user_id);
        }
        
        sendResponse('success', 'Locations retrieved', [
            'item' => [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_description' => $item->item_description,
                'item_category' => $item->item_category,
                'purchase_limit' => $item->purchase_limit,
                'purchase_frequency' => $item->purchase_frequency
            ],
            'can_purchase_today' => $canPurchase,
            'has_reached_limit' => $hasReachedLimit,
            'locations' => $locations
        ]);
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
    }
}
?>