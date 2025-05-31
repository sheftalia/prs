<?php
class Purchase {
    private $conn;
    private $table_name = "purchases";
    
    // Object properties
    public $purchase_id;
    public $user_id;
    public $item_id;
    public $location_id;
    public $quantity;
    public $purchase_date;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create a new purchase
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, item_id, location_id, quantity)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $this->user_id);
        $stmt->bindParam(2, $this->item_id);
        $stmt->bindParam(3, $this->location_id);
        $stmt->bindParam(4, $this->quantity);
        
        if ($stmt->execute()) {
            $this->purchase_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Get purchase history for a user
    public function readByUser($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT p.*, ci.item_name, ml.location_name, mb.business_name
                FROM " . $this->table_name . " p
                JOIN critical_items ci ON p.item_id = ci.item_id
                JOIN merchant_locations ml ON p.location_id = ml.location_id
                JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                WHERE p.user_id = :user_id
                ORDER BY p.purchase_date DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get purchase statistics for a merchant location
    public function getLocationStats($location_id) {
        $query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(quantity) as total_items_sold,
                    COUNT(DISTINCT user_id) as unique_customers,
                    DATE_FORMAT(purchase_date, '%Y-%m-%d') as date,
                    COUNT(*) as daily_transactions
                FROM " . $this->table_name . "
                WHERE location_id = ?
                GROUP BY DATE_FORMAT(purchase_date, '%Y-%m-%d')
                ORDER BY date DESC
                LIMIT 30";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $location_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get top selling items
    public function getTopSellingItems($location_id = null, $limit = 5) {
        $location_filter = $location_id ? "WHERE p.location_id = ?" : "";
        
        $query = "SELECT 
                    p.item_id,
                    ci.item_name,
                    ci.item_category,
                    SUM(p.quantity) as total_sold
                FROM " . $this->table_name . " p
                JOIN critical_items ci ON p.item_id = ci.item_id
                " . $location_filter . "
                GROUP BY p.item_id
                ORDER BY total_sold DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        
        if ($location_id) {
            $stmt->bindParam(1, $location_id);
            $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        } else {
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt;
    }
}
?>