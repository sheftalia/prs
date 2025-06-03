<?php
class StatsController {
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
        
        // Only admins and government officials can access stats
        if ($user->role_id > 2) {
            handleError('Unauthorized access', 403);
        }
        
        switch ($method) {
            case 'GET':
                if ($action === 'dashboard') {
                    $this->getDashboardStats();
                } else if ($action === 'vaccinations') {
                    $this->getVaccinationStats();
                } else if ($action === 'inventory') {
                    $this->getInventoryStats();
                } else if ($action === 'purchases') {
                    $this->getPurchaseStats();
                } else {
                    // Default action - return basic stats
                    $this->getDashboardStats(); // Use dashboard as default
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }
    
    private function getDashboardStats() {
        // Default empty stats structure
        $userStats = [
            'total_users' => 0,
            'admins' => 0,
            'officials' => 0,
            'merchants' => 0,
            'public_users' => 0
        ];
        
        $vaccinationStats = [
            'total_records' => 0,
            'verified_records' => 0,
            'vaccinated_users' => 0
        ];
        
        $inventoryStats = [
            'total_items' => 0,
            'total_stock' => 0,
            'total_locations' => 0
        ];
        
        $purchaseStats = [
            'total_purchases' => 0,
            'total_items_sold' => 0,
            'unique_customers' => 0
        ];
        
        $registrationTrend = [];
        $purchaseTrend = [];
        
        // User statistics
        try {
            $query = "SELECT 
                        COUNT(*) as total_users,
                        SUM(CASE WHEN role_id = 1 THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN role_id = 2 THEN 1 ELSE 0 END) as officials,
                        SUM(CASE WHEN role_id = 3 THEN 1 ELSE 0 END) as merchants,
                        SUM(CASE WHEN role_id = 4 THEN 1 ELSE 0 END) as public_users
                    FROM users";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // Log error but continue with default values
            error_log("Error fetching user stats: " . $e->getMessage());
        }
        
        // Vaccination statistics
        try {
            $query = "SELECT 
                        COUNT(*) as total_records,
                        SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_records,
                        COUNT(DISTINCT user_id) as vaccinated_users
                    FROM vaccination_records";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $vaccinationStats = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error fetching vaccination stats: " . $e->getMessage());
        }
        
        // Inventory statistics
        try {
            $query = "SELECT 
                        COUNT(*) as total_items,
                        SUM(quantity_available) as total_stock,
                        COUNT(DISTINCT location_id) as total_locations
                    FROM inventory";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $inventoryStats = $stmt->fetch(PDO::FETCH_ASSOC);
                // Handle null values that might come from SUM on empty tables
                $inventoryStats['total_stock'] = $inventoryStats['total_stock'] ?? 0;
            }
        } catch (PDOException $e) {
            error_log("Error fetching inventory stats: " . $e->getMessage());
        }
        
        // Purchase statistics
        try {
            $query = "SELECT 
                        COUNT(*) as total_purchases,
                        SUM(quantity) as total_items_sold,
                        COUNT(DISTINCT user_id) as unique_customers
                    FROM purchases";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $purchaseStats = $stmt->fetch(PDO::FETCH_ASSOC);
                // Handle null values that might come from SUM on empty tables
                $purchaseStats['total_items_sold'] = $purchaseStats['total_items_sold'] ?? 0;
            }
        } catch (PDOException $e) {
            error_log("Error fetching purchase stats: " . $e->getMessage());
        }
        
        // Recent user registrations (last 7 days)
        try {
            $query = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as count
                    FROM users
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                    ORDER BY date ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $registrationTrend[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching registration trend: " . $e->getMessage());
        }
        
        // Recent purchases (last 7 days)
        try {
            $query = "SELECT DATE_FORMAT(purchase_date, '%Y-%m-%d') as date, COUNT(*) as count
                    FROM purchases
                    WHERE purchase_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DATE_FORMAT(purchase_date, '%Y-%m-%d')
                    ORDER BY date ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $purchaseTrend[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching purchase trend: " . $e->getMessage());
        }
        
        sendResponse('success', 'Dashboard statistics retrieved', [
            'user_stats' => $userStats,
            'vaccination_stats' => $vaccinationStats,
            'inventory_stats' => $inventoryStats,
            'purchase_stats' => $purchaseStats,
            'registration_trend' => $registrationTrend,
            'purchase_trend' => $purchaseTrend
        ]);
    }
    
    private function getVaccinationStats() {
        $vaccineDistribution = [];
        $doseDistribution = [];
        $vaccinationTrend = [];
        $verificationStats = [
            'total_records' => 0,
            'verified_records' => 0,
            'verification_rate' => 0
        ];
        
        // Get vaccination distribution by vaccine type
        try {
            $query = "SELECT 
                        vaccine_name,
                        COUNT(*) as count
                    FROM vaccination_records
                    GROUP BY vaccine_name
                    ORDER BY count DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vaccineDistribution[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching vaccine distribution: " . $e->getMessage());
        }
        
        // Get vaccination distribution by dose number
        try {
            $query = "SELECT 
                        dose_number,
                        COUNT(*) as count
                    FROM vaccination_records
                    GROUP BY dose_number
                    ORDER BY dose_number ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $doseDistribution[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching dose distribution: " . $e->getMessage());
        }
        
        // Get vaccination trend by month
        try {
            $query = "SELECT 
                        DATE_FORMAT(date_administered, '%Y-%m') as month,
                        COUNT(*) as count
                    FROM vaccination_records
                    GROUP BY DATE_FORMAT(date_administered, '%Y-%m')
                    ORDER BY month ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vaccinationTrend[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching vaccination trend: " . $e->getMessage());
        }
        
        // Get verification statistics
        try {
            $query = "SELECT 
                        COUNT(*) as total_records,
                        SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_records
                    FROM vaccination_records";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $verificationStats['total_records'] = $result['total_records'];
                $verificationStats['verified_records'] = $result['verified_records'] ?? 0;
                
                // Calculate verification rate (avoid division by zero)
                if ($result['total_records'] > 0) {
                    $verificationStats['verification_rate'] = 
                        ($verificationStats['verified_records'] / $result['total_records']) * 100;
                }
            }
        } catch (PDOException $e) {
            error_log("Error fetching verification stats: " . $e->getMessage());
        }
        
        sendResponse('success', 'Vaccination statistics retrieved', [
            'vaccine_distribution' => $vaccineDistribution,
            'dose_distribution' => $doseDistribution,
            'vaccination_trend' => $vaccinationTrend,
            'verification_stats' => $verificationStats
        ]);
    }
    
    private function getInventoryStats() {
        $categoryDistribution = [];
        $topItems = [];
        $topLocations = [];
        
        // Get inventory distribution by item category
        try {
            $query = "SELECT 
                        ci.item_category,
                        COUNT(i.inventory_id) as location_count,
                        SUM(i.quantity_available) as total_stock
                    FROM inventory i
                    JOIN critical_items ci ON i.item_id = ci.item_id
                    GROUP BY ci.item_category
                    ORDER BY total_stock DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categoryDistribution[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching category distribution: " . $e->getMessage());
        }
        
        // Get top 10 items by availability
        try {
            $query = "SELECT 
                        ci.item_name,
                        SUM(i.quantity_available) as total_stock,
                        COUNT(i.location_id) as location_count
                    FROM inventory i
                    JOIN critical_items ci ON i.item_id = ci.item_id
                    GROUP BY i.item_id
                    ORDER BY total_stock DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $topItems[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching top items: " . $e->getMessage());
        }
        
        // Get locations with most items
        try {
            $query = "SELECT 
                        ml.location_name,
                        mb.business_name,
                        COUNT(i.item_id) as item_count,
                        SUM(i.quantity_available) as total_stock
                    FROM inventory i
                    JOIN merchant_locations ml ON i.location_id = ml.location_id
                    JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                    GROUP BY i.location_id
                    ORDER BY item_count DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $topLocations[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching top locations: " . $e->getMessage());
        }
        
        sendResponse('success', 'Inventory statistics retrieved', [
            'category_distribution' => $categoryDistribution,
            'top_items' => $topItems,
            'top_locations' => $topLocations
        ]);
    }
    
    private function getPurchaseStats() {
        $purchaseTrend = [];
        $topItems = [];
        $topLocations = [];
        
        // Get purchase trend by day for the last 30 days
        try {
            $query = "SELECT 
                        DATE_FORMAT(purchase_date, '%Y-%m-%d') as date,
                        COUNT(*) as transaction_count,
                        SUM(quantity) as item_count
                    FROM purchases
                    WHERE purchase_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE_FORMAT(purchase_date, '%Y-%m-%d')
                    ORDER BY date ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $purchaseTrend[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching purchase trend: " . $e->getMessage());
        }
        
        // Get top selling items
        try {
            $query = "SELECT 
                        ci.item_name,
                        ci.item_category,
                        SUM(p.quantity) as quantity_sold,
                        COUNT(DISTINCT p.user_id) as unique_customers
                    FROM purchases p
                    JOIN critical_items ci ON p.item_id = ci.item_id
                    GROUP BY p.item_id
                    ORDER BY quantity_sold DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $topItems[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching top items: " . $e->getMessage());
        }
        
        // Get top locations by sales
        try {
            $query = "SELECT 
                        ml.location_name,
                        mb.business_name,
                        COUNT(p.purchase_id) as transaction_count,
                        SUM(p.quantity) as quantity_sold,
                        COUNT(DISTINCT p.user_id) as unique_customers
                    FROM purchases p
                    JOIN merchant_locations ml ON p.location_id = ml.location_id
                    JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                    GROUP BY p.location_id
                    ORDER BY transaction_count DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $topLocations[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching top locations: " . $e->getMessage());
        }
        
        sendResponse('success', 'Purchase statistics retrieved', [
            'purchase_trend' => $purchaseTrend,
            'top_items' => $topItems,
            'top_locations' => $topLocations
        ]);
    }
}
?>