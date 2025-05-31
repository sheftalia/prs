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
                    handleError('Invalid action', 400);
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }
    
    private function getDashboardStats() {
        // User statistics
        $query = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role_id = 1 THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN role_id = 2 THEN 1 ELSE 0 END) as officials,
                    SUM(CASE WHEN role_id = 3 THEN 1 ELSE 0 END) as merchants,
                    SUM(CASE WHEN role_id = 4 THEN 1 ELSE 0 END) as public_users
                FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vaccination statistics
        $query = "SELECT 
                    COUNT(*) as total_records,
                    SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_records,
                    COUNT(DISTINCT user_id) as vaccinated_users
                FROM vaccination_records";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $vaccinationStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Inventory statistics
        $query = "SELECT 
                    COUNT(*) as total_items,
                    SUM(quantity_available) as total_stock,
                    COUNT(DISTINCT location_id) as total_locations
                FROM inventory";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $inventoryStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Purchase statistics
        $query = "SELECT 
                    COUNT(*) as total_purchases,
                    SUM(quantity) as total_items_sold,
                    COUNT(DISTINCT user_id) as unique_customers
                FROM purchases";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $purchaseStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Recent user registrations (last 7 days)
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as count
                FROM users
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                ORDER BY date ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $registrationTrend = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $registrationTrend[] = $row;
        }
        
        // Recent purchases (last 7 days)
        $query = "SELECT DATE_FORMAT(purchase_date, '%Y-%m-%d') as date, COUNT(*) as count
                FROM purchases
                WHERE purchase_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE_FORMAT(purchase_date, '%Y-%m-%d')
                ORDER BY date ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $purchaseTrend = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $purchaseTrend[] = $row;
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
        // Get vaccination distribution by vaccine type
        $query = "SELECT 
                    vaccine_name,
                    COUNT(*) as count
                FROM vaccination_records
                GROUP BY vaccine_name
                ORDER BY count DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $vaccineDistribution = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vaccineDistribution[] = $row;
        }
        
        // Get vaccination distribution by dose number
        $query = "SELECT 
                    dose_number,
                    COUNT(*) as count
                FROM vaccination_records
                GROUP BY dose_number
                ORDER BY dose_number ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $doseDistribution = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $doseDistribution[] = $row;
        }
        
        // Get vaccination trend by month
        $query = "SELECT 
                    DATE_FORMAT(date_administered, '%Y-%m') as month,
                    COUNT(*) as count
                FROM vaccination_records
                GROUP BY DATE_FORMAT(date_administered, '%Y-%m')
                ORDER BY month ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $vaccinationTrend = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vaccinationTrend[] = $row;
        }
        
        // Get verification statistics
        $query = "SELECT 
                    COUNT(*) as total_records,
                    SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_records,
                    (SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as verification_rate
                FROM vaccination_records";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $verificationStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse('success', 'Vaccination statistics retrieved', [
            'vaccine_distribution' => $vaccineDistribution,
            'dose_distribution' => $doseDistribution,
            'vaccination_trend' => $vaccinationTrend,
            'verification_stats' => $verificationStats
        ]);
    }
    
    private function getInventoryStats() {
        // Get inventory distribution by item category
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
        
        $categoryDistribution = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categoryDistribution[] = $row;
        }
        
        // Get top 10 items by availability
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
        
        $topItems = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $topItems[] = $row;
        }
        
        // Get locations with most items
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
        
        $topLocations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $topLocations[] = $row;
        }
        
        sendResponse('success', 'Inventory statistics retrieved', [
            'category_distribution' => $categoryDistribution,
            'top_items' => $topItems,
            'top_locations' => $topLocations
        ]);
    }
    
    private function getPurchaseStats() {
        // Get purchase trend by day for the last 30 days
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
        
        $purchaseTrend = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $purchaseTrend[] = $row;
        }
        
        // Get top selling items
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
        
        $topItems = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $topItems[] = $row;
        }
        
        // Get top locations by sales
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
        
        $topLocations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $topLocations[] = $row;
        }
        
        sendResponse('success', 'Purchase statistics retrieved', [
            'purchase_trend' => $purchaseTrend,
            'top_items' => $topItems,
            'top_locations' => $topLocations
        ]);
    }
}
?>