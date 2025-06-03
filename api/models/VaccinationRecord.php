<?php
class VaccinationRecord {
    private $conn;
    private $table_name = "vaccination_records";
    
    // Object properties
    public $record_id;
    public $user_id;
    public $vaccine_name;
    public $date_administered;
    public $dose_number;
    public $provider;
    public $lot_number;
    public $expiration_date;
    public $verified;
    public $verified_by;
    public $verified_date;
    public $created_at;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create a new vaccination record
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, vaccine_name, date_administered, dose_number, provider, lot_number, expiration_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->vaccine_name = htmlspecialchars(strip_tags($this->vaccine_name));
        $this->provider = htmlspecialchars(strip_tags($this->provider));
        $this->lot_number = htmlspecialchars(strip_tags($this->lot_number));
        
        // Bind parameters
        $stmt->bindParam(1, $this->user_id);
        $stmt->bindParam(2, $this->vaccine_name);
        $stmt->bindParam(3, $this->date_administered);
        $stmt->bindParam(4, $this->dose_number);
        $stmt->bindParam(5, $this->provider);
        $stmt->bindParam(6, $this->lot_number);
        $stmt->bindParam(7, $this->expiration_date);
        
        if ($stmt->execute()) {
            $this->record_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Read a single vaccination record
    public function readOne() {
        $query = "SELECT vr.*, u.full_name as user_name, v.full_name as verifier_name
                FROM " . $this->table_name . " vr
                LEFT JOIN users u ON vr.user_id = u.user_id
                LEFT JOIN users v ON vr.verified_by = v.user_id
                WHERE vr.record_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->record_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->user_id = $row['user_id'];
            $this->vaccine_name = $row['vaccine_name'];
            $this->date_administered = $row['date_administered'];
            $this->dose_number = $row['dose_number'];
            $this->provider = $row['provider'];
            $this->lot_number = $row['lot_number'];
            $this->expiration_date = $row['expiration_date'];
            $this->verified = $row['verified'];
            $this->verified_by = $row['verified_by'];
            $this->verified_date = $row['verified_date'];
            $this->created_at = $row['created_at'];
            
            // Additional fields
            $this->user_name = $row['user_name'];
            $this->verifier_name = $row['verifier_name'];
            
            return true;
        }
        
        return false;
    }
    
    // Get vaccination records for a user
    public function readByUser($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT vr.*, u.full_name as verifier_name
                FROM " . $this->table_name . " vr
                LEFT JOIN users u ON vr.verified_by = u.user_id
                WHERE vr.user_id = :user_id
                ORDER BY vr.date_administered DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Verify a vaccination record
    public function verify() {
        $query = "UPDATE " . $this->table_name . "
                SET verified = 1, verified_by = ?, verified_date = CURRENT_TIMESTAMP
                WHERE record_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->verified_by);
        $stmt->bindParam(2, $this->record_id);
        
        return $stmt->execute();
    }
    
    // List all unverified vaccination records
    public function readUnverified($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT vr.*, u.full_name as user_name, u.prs_id
                FROM " . $this->table_name . " vr
                JOIN users u ON vr.user_id = u.user_id
                WHERE vr.verified = 0
                ORDER BY vr.created_at ASC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get vaccination statistics
    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_records,
                    SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_records,
                    COUNT(DISTINCT user_id) as total_users,
                    COUNT(DISTINCT vaccine_name) as vaccine_types
                FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get vaccination distribution by vaccine type
    public function getVaccineDistribution() {
        $query = "SELECT 
                    vaccine_name,
                    COUNT(*) as count
                FROM " . $this->table_name . "
                GROUP BY vaccine_name
                ORDER BY count DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get vaccination trend (records per month)
    public function getVaccinationTrend() {
        $query = "SELECT 
                    DATE_FORMAT(date_administered, '%Y-%m') as month,
                    COUNT(*) as count
                FROM " . $this->table_name . "
                GROUP BY DATE_FORMAT(date_administered, '%Y-%m')
                ORDER BY month ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Count records for a specific user
    public function countByUser($userId) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    // Count unverified records
    public function countUnverified() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE verified = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
}
?>