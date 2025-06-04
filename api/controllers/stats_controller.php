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
        
        // Allow admins, government officials, and merchants to access stats
        if ($user->role_id > 3) {
            handleError('Unauthorized access', 403);
        }
        
        switch ($method) {
            case 'GET':
                if ($action === 'dashboard') {
                    $this->getDashboardStats($user);
                } else if ($action === 'vaccinations') {
                    // Only admins and government officials can access vaccination stats
                    if ($user->role_id > 2) {
                        handleError('Unauthorized access', 403);
                    }
                    $this->getVaccinationStats();
                } else if ($action === 'inventory') {
                    $this->getInventoryStats($user);
                } else if ($action === 'purchases') {
                    $this->getPurchaseStats($user);
                } else {
                    // Default action - return basic stats
                    $this->getDashboardStats($user);
                }
                break;
                
            default:
                handleError('Method not allowed', 405);
                break;
        }
    }
    
private function getDashboardStats($user) {
    // Initialize with defaults
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
        'total_users' => 0
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
    
    // For merchants
    if ($user->role_id == 3) {
        try {
            // Fixed inventory count for merchant
            $query = "SELECT COUNT(*) as total_items FROM inventory";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $inventoryStats['total_items'] = intval($result['total_items']);
            }
            
            // Fixed purchase count
            $query = "SELECT COUNT(*) as total_purchases, COUNT(DISTINCT user_id) as unique_customers FROM purchases";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $purchaseStats['total_purchases'] = intval($result['total_purchases']);
                $purchaseStats['unique_customers'] = intval($result['unique_customers']);
            }
            
            // Get total stock
            $query = "SELECT COALESCE(SUM(quantity_available), 0) as total_stock FROM inventory";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $inventoryStats['total_stock'] = intval($result['total_stock']);
            }
            
            // Get location count
            $query = "SELECT COUNT(DISTINCT location_id) as total_locations FROM inventory";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $inventoryStats['total_locations'] = intval($result['total_locations']);
            }
            
            // Get total items sold
            $query = "SELECT COALESCE(SUM(quantity), 0) as total_items_sold FROM purchases";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $purchaseStats['total_items_sold'] = intval($result['total_items_sold']);
            }
            
            error_log("Merchant stats - Items: " . $inventoryStats['total_items'] . ", Purchases: " . $purchaseStats['total_purchases'] . ", Customers: " . $purchaseStats['unique_customers']);
            
        } catch (PDOException $e) {
            error_log("Error fetching merchant stats: " . $e->getMessage());
        }
    } else {
        // For admins and government officials
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
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $userStats = [
                    'total_users' => intval($result['total_users']),
                    'admins' => intval($result['admins']),
                    'officials' => intval($result['officials']),
                    'merchants' => intval($result['merchants']),
                    'public_users' => intval($result['public_users'])
                ];
            }
        } catch (PDOException $e) {
            error_log("Error fetching user stats: " . $e->getMessage());
        }
        
        // Vaccination statistics
        try {
            $query = "SELECT 
                        COUNT(*) as total_records,
                        SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_records,
                        COUNT(DISTINCT user_id) as total_users
                    FROM vaccination_records";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $vaccinationStats = [
                    'total_records' => intval($result['total_records']),
                    'verified_records' => intval($result['verified_records']),
                    'total_users' => intval($result['total_users'])
                ];
            }
        } catch (PDOException $e) {
            error_log("Error fetching vaccination stats: " . $e->getMessage());
        }
        
        // Inventory statistics for admins
        try {
            $query = "SELECT 
                        COUNT(*) as total_items,
                        COALESCE(SUM(quantity_available), 0) as total_stock,
                        COUNT(DISTINCT location_id) as total_locations
                    FROM inventory";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $inventoryStats = [
                    'total_items' => intval($result['total_items']),
                    'total_stock' => intval($result['total_stock']),
                    'total_locations' => intval($result['total_locations'])
                ];
            }
        } catch (PDOException $e) {
            error_log("Error fetching inventory stats: " . $e->getMessage());
        }
        
        // Purchase statistics for admins
        try {
            $query = "SELECT 
                        COUNT(*) as total_purchases,
                        COALESCE(SUM(quantity), 0) as total_items_sold,
                        COUNT(DISTINCT user_id) as unique_customers
                    FROM purchases";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $purchaseStats = [
                    'total_purchases' => intval($result['total_purchases']),
                    'total_items_sold' => intval($result['total_items_sold']),
                    'unique_customers' => intval($result['unique_customers'])
                ];
            }
        } catch (PDOException $e) {
            error_log("Error fetching purchase stats: " . $e->getMessage());
        }
    }
    
    // Recent trends (simplified for merchants)
    $registrationTrend = [];
    $purchaseTrend = [];
    
    // Only get trends for admins and government officials
    if ($user->role_id <= 2) {
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
    
    private function getInventoryStats($user) {
        $categoryDistribution = [];
        $topItems = [];
        $topLocations = [];
        
        // Get inventory distribution by item category (filtered for merchants)
        try {
            if ($user->role_id <= 2) {
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
            } else if ($user->role_id == 3) {
                $query = "SELECT 
                            ci.item_category,
                            COUNT(i.inventory_id) as location_count,
                            SUM(i.quantity_available) as total_stock
                        FROM inventory i
                        JOIN critical_items ci ON i.item_id = ci.item_id
                        JOIN merchant_locations ml ON i.location_id = ml.location_id
                        WHERE ml.manager_id = ? OR ml.location_id IN (
                            SELECT location_id FROM merchant_locations 
                            WHERE business_id IN (
                                SELECT business_id FROM merchant_locations WHERE manager_id = ?
                            )
                        )
                        GROUP BY ci.item_category
                        ORDER BY total_stock DESC";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $user->user_id);
                $stmt->bindParam(2, $user->user_id);
                $stmt->execute();
            }
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categoryDistribution[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching category distribution: " . $e->getMessage());
        }
        
        // Get top items by availability (filtered for merchants)
        try {
            if ($user->role_id <= 2) {
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
            } else if ($user->role_id == 3) {
                $query = "SELECT 
                            ci.item_name,
                            SUM(i.quantity_available) as total_stock,
                            COUNT(i.location_id) as location_count
                        FROM inventory i
                        JOIN critical_items ci ON i.item_id = ci.item_id
                        JOIN merchant_locations ml ON i.location_id = ml.location_id
                        WHERE ml.manager_id = ? OR ml.location_id IN (
                            SELECT location_id FROM merchant_locations 
                            WHERE business_id IN (
                                SELECT business_id FROM merchant_locations WHERE manager_id = ?
                            )
                        )
                        GROUP BY i.item_id
                        ORDER BY total_stock DESC
                        LIMIT 10";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $user->user_id);
                $stmt->bindParam(2, $user->user_id);
                $stmt->execute();
            }
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $topItems[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching top items: " . $e->getMessage());
        }
        
        // Get locations with most items (filtered for merchants)
        try {
            if ($user->role_id <= 2) {
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
            } else if ($user->role_id == 3) {
                $query = "SELECT 
                            ml.location_name,
                            mb.business_name,
                            COUNT(i.item_id) as item_count,
                            SUM(i.quantity_available) as total_stock
                        FROM inventory i
                        JOIN merchant_locations ml ON i.location_id = ml.location_id
                        JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                        WHERE ml.manager_id = ? OR ml.location_id IN (
                            SELECT location_id FROM merchant_locations 
                            WHERE business_id IN (
                                SELECT business_id FROM merchant_locations WHERE manager_id = ?
                            )
                        )
                        GROUP BY i.location_id
                        ORDER BY item_count DESC
                        LIMIT 10";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $user->user_id);
                $stmt->bindParam(2, $user->user_id);
                $stmt->execute();
            }
            
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
    
    private function getPurchaseStats($user) {
        $purchaseTrend = [];
        $topItems = [];
        $topLocations = [];
        
        // Get purchase trend by day for the last 30 days (filtered for merchants)
        try {
            if ($user->role_id <= 2) {
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
            } else if ($user->role_id == 3) {
                $query = "SELECT 
                            DATE_FORMAT(p.purchase_date, '%Y-%m-%d') as date,
                            COUNT(*) as transaction_count,
                            SUM(p.quantity) as item_count
                        FROM purchases p
                        JOIN merchant_locations ml ON p.location_id = ml.location_id
                        WHERE p.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        AND (ml.manager_id = ? OR ml.location_id IN (
                            SELECT location_id FROM merchant_locations 
                            WHERE business_id IN (
                                SELECT business_id FROM merchant_locations WHERE manager_id = ?
                            )
                        ))
                        GROUP BY DATE_FORMAT(p.purchase_date, '%Y-%m-%d')
                        ORDER BY date ASC";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $user->user_id);
                $stmt->bindParam(2, $user->user_id);
                $stmt->execute();
            }
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $purchaseTrend[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching purchase trend: " . $e->getMessage());
        }
        
        // Get top selling items (filtered for merchants)
        try {
            if ($user->role_id <= 2) {
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
            } else if ($user->role_id == 3) {
                $query = "SELECT 
                            ci.item_name,
                            ci.item_category,
                            SUM(p.quantity) as quantity_sold,
                            COUNT(DISTINCT p.user_id) as unique_customers
                        FROM purchases p
                        JOIN critical_items ci ON p.item_id = ci.item_id
                        JOIN merchant_locations ml ON p.location_id = ml.location_id
                        WHERE ml.manager_id = ? OR ml.location_id IN (
                            SELECT location_id FROM merchant_locations 
                            WHERE business_id IN (
                                SELECT business_id FROM merchant_locations WHERE manager_id = ?
                            )
                        )
                        GROUP BY p.item_id
                        ORDER BY quantity_sold DESC
                        LIMIT 10";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $user->user_id);
                $stmt->bindParam(2, $user->user_id);
                $stmt->execute();
            }
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $topItems[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error fetching top items: " . $e->getMessage());
        }
        
        // Get top locations by sales (filtered for merchants)
        try {
            if ($user->role_id <= 2) {
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
            } else if ($user->role_id == 3) {
                $query = "SELECT 
                            ml.location_name,
                            mb.business_name,
                            COUNT(p.purchase_id) as transaction_count,
                            SUM(p.quantity) as quantity_sold,
                            COUNT(DISTINCT p.user_id) as unique_customers
                        FROM purchases p
                        JOIN merchant_locations ml ON p.location_id = ml.location_id
                        JOIN merchant_businesses mb ON ml.business_id = mb.business_id
                        WHERE ml.manager_id = ? OR ml.location_id IN (
                            SELECT location_id FROM merchant_locations 
                            WHERE business_id IN (
                                SELECT business_id FROM merchant_locations WHERE manager_id = ?
                            )
                        )
                        GROUP BY p.location_id
                        ORDER BY transaction_count DESC
                        LIMIT 10";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $user->user_id);
                $stmt->bindParam(2, $user->user_id);
                $stmt->execute();
            }
            
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