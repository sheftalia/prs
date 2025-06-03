<?php
class CriticalItem {
    private $conn;
    private $table_name = "critical_items";
    
    // Object properties
    public $item_id;
    public $item_name;
    public $item_description;
    public $item_category;
    public $purchase_limit;
    public $purchase_frequency;
    public $dob_restriction;
    public $is_active;
    public $created_at;
    public $created_by;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create a new critical item
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (item_name, item_description, item_category, purchase_limit, purchase_frequency, dob_restriction, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->item_name = htmlspecialchars(strip_tags($this->item_name));
        $this->item_description = htmlspecialchars(strip_tags($this->item_description));
        $this->item_category = htmlspecialchars(strip_tags($this->item_category));
        $this->purchase_frequency = htmlspecialchars(strip_tags($this->purchase_frequency));
        $this->dob_restriction = htmlspecialchars(strip_tags($this->dob_restriction));
        
        // Bind parameters
        $stmt->bindParam(1, $this->item_name);
        $stmt->bindParam(2, $this->item_description);
        $stmt->bindParam(3, $this->item_category);
        $stmt->bindParam(4, $this->purchase_limit);
        $stmt->bindParam(5, $this->purchase_frequency);
        $stmt->bindParam(6, $this->dob_restriction);
        $stmt->bindParam(7, $this->created_by);
        
        if ($stmt->execute()) {
            $this->item_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Read a single critical item
    public function readOne() {
        $query = "SELECT ci.*, u.full_name as creator_name
                FROM " . $this->table_name . " ci
                LEFT JOIN users u ON ci.created_by = u.user_id
                WHERE ci.item_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->item_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->item_name = $row['item_name'];
            $this->item_description = $row['item_description'];
            $this->item_category = $row['item_category'];
            $this->purchase_limit = $row['purchase_limit'];
            $this->purchase_frequency = $row['purchase_frequency'];
            $this->dob_restriction = $row['dob_restriction'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->created_by = $row['created_by'];
            $this->creator_name = $row['creator_name'];
            
            return true;
        }
        
        return false;
    }
    
    // Update a critical item
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET 
                    item_name = :item_name,
                    item_description = :item_description,
                    item_category = :item_category,
                    purchase_limit = :purchase_limit,
                    purchase_frequency = :purchase_frequency,
                    dob_restriction = :dob_restriction,
                    is_active = :is_active
                WHERE item_id = :item_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->item_name = htmlspecialchars(strip_tags($this->item_name));
        $this->item_description = htmlspecialchars(strip_tags($this->item_description));
        $this->item_category = htmlspecialchars(strip_tags($this->item_category));
        $this->purchase_frequency = htmlspecialchars(strip_tags($this->purchase_frequency));
        $this->dob_restriction = htmlspecialchars(strip_tags($this->dob_restriction));
        
        // Bind parameters
        $stmt->bindParam(':item_name', $this->item_name);
        $stmt->bindParam(':item_description', $this->item_description);
        $stmt->bindParam(':item_category', $this->item_category);
        $stmt->bindParam(':purchase_limit', $this->purchase_limit);
        $stmt->bindParam(':purchase_frequency', $this->purchase_frequency);
        $stmt->bindParam(':dob_restriction', $this->dob_restriction);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':item_id', $this->item_id);
        
        return $stmt->execute();
    }
    
    // List all critical items with pagination
    public function readAll($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT ci.*, u.full_name as creator_name
                FROM " . $this->table_name . " ci
                LEFT JOIN users u ON ci.created_by = u.user_id
                ORDER BY ci.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // List active critical items
    public function readActive() {
        $query = "SELECT *
                FROM " . $this->table_name . "
                WHERE is_active = 1
                ORDER BY item_name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Check if a user can purchase an item based on DOB restrictions
    public function canPurchase($user_dob) {
        if (empty($this->dob_restriction)) {
            return true; // No restrictions
        }
        
        $day_of_week = date('l'); // Current day of the week
        $last_digit = substr($user_dob, -1); // Get last digit of birth year
        
        $restrictions = explode(';', $this->dob_restriction);
        
        foreach ($restrictions as $restriction) {
            list($digits, $day) = explode(':', $restriction);
            $allowed_digits = explode(',', $digits);
            
            if (in_array($last_digit, $allowed_digits) && $day == $day_of_week) {
                return true; // User can purchase on this day
            }
        }
        
        return false; // User cannot purchase today
    }
    
    // Check if a user has reached purchase limit
    public function hasReachedLimit($user_id) {
        $frequency_clause = "";
        
        switch ($this->purchase_frequency) {
            case 'daily':
                $frequency_clause = "AND DATE(purchase_date) = CURDATE()";
                break;
            case 'weekly':
                $frequency_clause = "AND purchase_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'monthly':
                $frequency_clause = "AND purchase_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            default:
                $frequency_clause = "";
        }
        
        $query = "SELECT SUM(quantity) as total_purchased
                FROM purchases
                WHERE user_id = ? AND item_id = ? " . $frequency_clause;
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $this->item_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_purchased = $row['total_purchased'] ?: 0;
        
        return $total_purchased >= $this->purchase_limit;
    }

    // Count total items
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ?? 0;
    }
}
?>