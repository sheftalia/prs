<?php
class Inventory {
    private $conn;
    private $table_name = "inventory";
    
    // Object properties
    public $inventory_id;
    public $location_id;
    public $item_id;
    public $quantity_available;
    public $last_updated;
    public $updated_by;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create or update inventory
    public function createOrUpdate() {
        // Check if inventory entry exists
        $query = "SELECT inventory_id FROM " . $this->table_name . "
                WHERE location_id = ? AND item_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->location_id);
        $stmt->bindParam(2, $this->item_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Update existing inventory
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->inventory_id = $row['inventory_id'];
            
            $query = "UPDATE " . $this->table_name . "
                    SET quantity_available = ?, updated_by = ?
                    WHERE inventory_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->quantity_available);
            $stmt->bindParam(2, $this->updated_by);
            $stmt->bindParam(3, $this->inventory_id);
            
            return $stmt->execute();
        } else {
            // Create new inventory entry
            $query = "INSERT INTO " . $this->table_name . "
                    (location_id, item_id, quantity_available, updated_by)
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->location_id);
            $stmt->bindParam(2, $this->item_id);
            $stmt->bindParam(3, $this->quantity_available);
            $stmt->bindParam(4, $this->updated_by);
            
            if ($stmt->execute()) {
                $this->inventory_id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        }
    }
    
    // Read inventory for a location
    public function readByLocation() {
        $query = "SELECT i.*, ci.item_name, ci.item_category, ci.purchase_limit, ci.purchase_frequency
                FROM " . $this->table_name . " i
                JOIN critical_items ci ON i.item_id = ci.item_id
                WHERE i.location_id = ? AND ci.is_active = 1
                ORDER BY ci.item_name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->location_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Find locations with available stock for a specific item
    public function findLocationsWithStock($item_id, $user_lat, $user_lng, $radius = 10) {
        // Find locations within radius that have stock
        $query = "SELECT 
                    ml.location_id, ml.location_name, ml.address_line1, ml.address_line2, 
                    ml.city, ml.postal_code, ml.latitude, ml.longitude, 
                    mb.business_name, i.quantity_available,
                    (
                        6371 * acos(
                            cos(radians(?)) * cos(radians(ml.latitude)) * cos(radians(ml.longitude) - radians(?)) + 
                            sin(radians(?)) * sin(radians(ml.latitude))
                        )
                    ) AS distance
                FROM inventory i
                JOIN merchant_locations ml ON i.location_id = ml.location_id
                JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                WHERE i.item_id = ? AND i.quantity_available > 0
                HAVING distance < ?
                ORDER BY distance ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_lat);
        $stmt->bindParam(2, $user_lng);
        $stmt->bindParam(3, $user_lat);
        $stmt->bindParam(4, $item_id);
        $stmt->bindParam(5, $radius);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update inventory after purchase
    public function decreaseStock($quantity) {
        $query = "UPDATE " . $this->table_name . "
                SET quantity_available = quantity_available - ?
                WHERE location_id = ? AND item_id = ? AND quantity_available >= ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantity);
        $stmt->bindParam(2, $this->location_id);
        $stmt->bindParam(3, $this->item_id);
        $stmt->bindParam(4, $quantity);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
    
    // Get low stock items
    public function getLowStockItems($threshold = 10) {
        $query = "SELECT i.*, ci.item_name, ml.location_name, mb.business_name
                FROM " . $this->table_name . " i
                JOIN critical_items ci ON i.item_id = ci.item_id
                JOIN merchant_locations ml ON i.location_id = ml.location_id
                JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                WHERE i.quantity_available <= ? AND ci.is_active = 1
                ORDER BY i.quantity_available ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $threshold);
        $stmt->execute();
        
        return $stmt;
    }
}
?>