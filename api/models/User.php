<?php
class User {
    private $conn;
    private $table_name = "users";
    
    // Object properties
    public $user_id;
    public $full_name;
    public $email;
    public $password_hash;
    public $phone;
    public $national_id;
    public $dob;
    public $prs_id;
    public $role_id;
    public $created_at;
    public $last_login;
    public $account_status;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get a single user by ID
    public function readOne() {
        $query = "SELECT u.*, r.role_name 
                FROM " . $this->table_name . " u
                LEFT JOIN roles r ON u.role_id = r.role_id
                WHERE u.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->national_id = $row['national_id'];
            $this->dob = $row['dob'];
            $this->prs_id = $row['prs_id'];
            $this->role_id = $row['role_id'];
            $this->created_at = $row['created_at'];
            $this->last_login = $row['last_login'];
            $this->account_status = $row['account_status'];
            
            return true;
        }
        
        return false;
    }
    
    // Get a user by email
    public function readByEmail() {
        $query = "SELECT u.*, r.role_name 
                FROM " . $this->table_name . " u
                LEFT JOIN roles r ON u.role_id = r.role_id
                WHERE u.email = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->user_id = $row['user_id'];
            $this->full_name = $row['full_name'];
            $this->password_hash = $row['password_hash'];
            $this->phone = $row['phone'];
            $this->national_id = $row['national_id'];
            $this->dob = $row['dob'];
            $this->prs_id = $row['prs_id'];
            $this->role_id = $row['role_id'];
            $this->created_at = $row['created_at'];
            $this->last_login = $row['last_login'];
            $this->account_status = $row['account_status'];
            
            return true;
        }
        
        return false;
    }
    
    // Create a new user
    public function create() {
        // Generate a unique PRS-ID
        $this->prs_id = $this->generatePRSID();
        
        $query = "INSERT INTO " . $this->table_name . "
                (full_name, email, password_hash, phone, national_id, dob, prs_id, role_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->national_id = htmlspecialchars(strip_tags($this->national_id));
        
        // Bind parameters
        $stmt->bindParam(1, $this->full_name);
        $stmt->bindParam(2, $this->email);
        $stmt->bindParam(3, $this->password_hash);
        $stmt->bindParam(4, $this->phone);
        $stmt->bindParam(5, $this->national_id);
        $stmt->bindParam(6, $this->dob);
        $stmt->bindParam(7, $this->prs_id);
        $stmt->bindParam(8, $this->role_id);
        
        if ($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update user details
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET 
                    full_name = :full_name,
                    phone = :phone,
                    account_status = :account_status
                WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->account_status = htmlspecialchars(strip_tags($this->account_status));
        
        // Bind parameters
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':account_status', $this->account_status);
        $stmt->bindParam(':user_id', $this->user_id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Update last login timestamp
    public function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . "
                SET last_login = CURRENT_TIMESTAMP
                WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        
        return $stmt->execute();
    }
    
    // Change user password
    public function changePassword() {
        $query = "UPDATE " . $this->table_name . "
                SET password_hash = ?
                WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->password_hash);
        $stmt->bindParam(2, $this->user_id);
        
        return $stmt->execute();
    }
    
    // Generate unique PRS-ID
    private function generatePRSID() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM " . $this->table_name);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $nextId = $row['count'] + 10006; // Starting from PRS10006 (after our sample data)
        return 'PRS' . $nextId;
    }
    
    // List users with pagination
    public function readAll($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT u.*, r.role_name 
                FROM " . $this->table_name . " u
                LEFT JOIN roles r ON u.role_id = r.role_id
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Search users
    public function search($keyword, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT u.*, r.role_name 
                FROM " . $this->table_name . " u
                LEFT JOIN roles r ON u.role_id = r.role_id
                WHERE u.full_name LIKE :keyword OR u.email LIKE :keyword OR u.prs_id LIKE :keyword
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $keyword = "%{$keyword}%";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':keyword', $keyword);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Count total users
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
}
?>