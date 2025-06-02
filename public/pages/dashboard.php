<?php
// Temporary debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<script>
// Debug API responses
const originalFetch = window.fetch;
window.fetch = function(...args) {
    return originalFetch(...args)
        .then(response => {
            console.log(`Fetch to ${args[0]}:`, response.clone());
            return response;
        })
        .catch(error => {
            console.error(`Fetch error for ${args[0]}:`, error);
            throw error;
        });
};
</script>

<?php
// Get user role for role-specific content
$userRole = $_SESSION['user']['role_id'];
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p class="page-description">Welcome back, <?php echo $_SESSION['user']['full_name']; ?>!</p>
</div>

<?php if ($userRole <= 2): // Admin & Government Officials ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">System Overview</h2>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-value" id="total-users">--</div>
                <div class="stat-label">Registered Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19.82 2H4.18A2.18 2.18 0 0 0 2 4.18v15.64A2.18 2.18 0 0 0 4.18 22h15.64A2.18 2.18 0 0 0 22 19.82V4.18A2.18 2.18 0 0 0 19.82 2Z"></path>
                        <path d="M7 2v20"></path>
                        <path d="M17 2v20"></path>
                        <path d="M2 12h20"></path>
                        <path d="M2 7h5"></path>
                        <path d="M2 17h5"></path>
                        <path d="M17 17h5"></path>
                        <path d="M17 7h5"></path>
                    </svg>
                </div>
                <div class="stat-value" id="total-vaccinations">--</div>
                <div class="stat-label">Vaccinations Recorded</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
                <div class="stat-value" id="total-items-sold">--</div>
                <div class="stat-label">Critical Items Sold</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21V8l9-4 9 4v13"></path>
                        <path d="M9 21v-8h6v8"></path>
                    </svg>
                </div>
                <div class="stat-value" id="total-stock">--</div>
                <div class="stat-label">Items in Stock</div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Vaccination Trend</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="vaccination-trend-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Vaccine Distribution</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="vaccine-distribution-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Purchase Trend</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="purchase-trend-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Inventory by Category</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="item-distribution-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Unverified Vaccinations</h2>
                <a href="index.php?page=vaccinations" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Vaccine</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="unverified-vaccinations">
                            <tr>
                                <td colspan="4" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Low Stock Items</h2>
                <a href="index.php?page=inventory" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Location</th>
                                <th>Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="low-stock-items">
                            <tr>
                                <td colspan="4" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($userRole === 3): // Merchant ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Inventory Overview</h2>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
                <div class="stat-value" id="merchant-total-items">--</div>
                <div class="stat-label">Total Items</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="stat-value" id="merchant-total-sales">--</div>
                <div class="stat-label">Total Sales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <polyline points="17 11 19 13 23 9"></polyline>
                    </svg>
                </div>
                <div class="stat-value" id="merchant-total-customers">--</div>
                <div class="stat-label">Unique Customers</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="15" rx="2"></rect>
                        <polyline points="17 2 12 7 7 2"></polyline>
                    </svg>
                </div>
                <div class="stat-value" id="merchant-low-stock">--</div>
                <div class="stat-label">Low Stock Items</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Your Locations</h3>
                <a href="index.php?page=inventory" class="btn btn-primary btn-sm">Manage Inventory</a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Location Name</th>
                                <th>Address</th>
                                <th>Items in Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="merchant-locations">
                            <tr>
                                <td colspan="4" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Sales</h3>
                <a href="index.php?page=purchases" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recent-sales">
                            <tr>
                                <td colspan="4" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: // Public User ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Your Dashboard</h2>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19.82 2H4.18A2.18 2.18 0 0 0 2 4.18v15.64A2.18 2.18 0 0 0 4.18 22h15.64A2.18 2.18 0 0 0 22 19.82V4.18A2.18 2.18 0 0 0 19.82 2Z"></path>
                        <path d="M7 2v20"></path>
                        <path d="M17 2v20"></path>
                        <path d="M2 12h20"></path>
                        <path d="M2 7h5"></path>
                        <path d="M2 17h5"></path>
                        <path d="M17 17h5"></path>
                        <path d="M17 7h5"></path>
                    </svg>
                </div>
                <div class="stat-value" id="user-vaccination-count">--</div>
                <div class="stat-label">Your Vaccinations</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-value" id="user-family-count">--</div>
                <div class="stat-label">Family Members</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="stat-value" id="user-purchase-count">--</div>
                <div class="stat-label">Your Purchases</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="stat-value" id="user-verified-count">--</div>
                <div class="stat-label">Verified Records</div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Your Vaccination Records</h3>
                        <a href="index.php?page=vaccinations" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Vaccine</th>
                                        <th>Dose</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="user-vaccinations">
                                    <tr>
                                        <td colspan="4" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Purchases</h3>
                        <a href="index.php?page=purchases" class="btn btn-primary btn-sm">View History</a>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Location</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="user-purchases">
                                    <tr>
                                        <td colspan="4" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Find Critical Items</h3>
            </div>
            <div class="card-body">
                <p>Search for essential items and find nearby locations with available stock.</p>
                <div class="critical-items-grid" id="critical-items-list">
                    <div class="text-center">Loading critical items...</div>
                </div>
            </div>
            <div class="card-footer">
                <a href="index.php?page=find-items" class="btn btn-primary">Find Items</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Load dashboard data
document.addEventListener('DOMContentLoaded', function() {
    // Load statistics based on user role
    const userRole = <?php echo $userRole; ?>;
    
    if (userRole <= 2) {
        // Admin & Government Officials
        loadAdminDashboard();
    } else if (userRole === 3) {
        // Merchant
        loadMerchantDashboard();
    } else {
        // Public User
        loadPublicDashboard();
    }
});

// Load Admin/Government Dashboard
function loadAdminDashboard() {
    // Load system statistics
    fetch('/prs/api/stats/dashboard')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update statistics
                document.getElementById('total-users').textContent = data.data.user_stats.total_users;
                document.getElementById('total-vaccinations').textContent = data.data.vaccination_stats.total_records;
                document.getElementById('total-items-sold').textContent = data.data.purchase_stats.total_items_sold;
                document.getElementById('total-stock').textContent = data.data.inventory_stats.total_stock;
            }
        })
        .catch(error => {
            console.error('Error loading dashboard statistics:', error);
        });
    
    // Load unverified vaccinations
    fetch('/prs/api/vaccinations?action=unverified')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const tbody = document.getElementById('unverified-vaccinations');
                
                if (data.data.records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No unverified vaccinations</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                data.data.records.slice(0, 5).forEach(record => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${record.user_name} (${record.prs_id})</td>
                        <td>${record.vaccine_name}</td>
                        <td>${new Date(record.date_administered).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-success btn-sm verify-btn" data-record-id="${record.record_id}">
                                Verify
                            </button>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to verify buttons
                const verifyButtons = document.querySelectorAll('.verify-btn');
                verifyButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const recordId = this.getAttribute('data-record-id');
                        verifyVaccinationRecord(recordId, this);
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error loading unverified vaccinations:', error);
            document.getElementById('unverified-vaccinations').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
    
    // Load low stock items
    fetch('/prs/api/inventory?action=low-stock')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const tbody = document.getElementById('low-stock-items');
                
                if (data.data.items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No low stock items</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                data.data.items.slice(0, 5).forEach(item => {
                    const row = document.createElement('tr');
                    
                    // Determine status based on quantity
                    let status = '';
                    let statusClass = '';
                    
                    if (item.quantity_available === 0) {
                        status = 'Out of Stock';
                        statusClass = 'badge-error';
                    } else if (item.quantity_available <= 5) {
                        status = 'Critical';
                        statusClass = 'badge-warning';
                    } else {
                        status = 'Low';
                        statusClass = 'badge-info';
                    }
                    
                    row.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.location_name} (${item.business_name})</td>
                        <td>${item.quantity_available}</td>
                        <td><span class="badge ${statusClass}">${status}</span></td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => {
            console.error('Error loading low stock items:', error);
            document.getElementById('low-stock-items').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
}

// Load Merchant Dashboard
function loadMerchantDashboard() {
    // Load merchant statistics
    const userId = <?php echo $_SESSION['user']['user_id']; ?>;
    
    // Fetch merchant locations
    fetch(`/prs/api/merchant/locations?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const locations = data.data.locations;
                
                // Update statistics
                document.getElementById('merchant-total-items').textContent = data.data.summary.total_items || 0;
                document.getElementById('merchant-total-sales').textContent = data.data.summary.total_sales || 0;
                document.getElementById('merchant-total-customers').textContent = data.data.summary.unique_customers || 0;
                document.getElementById('merchant-low-stock').textContent = data.data.summary.low_stock_items || 0;
                
                // Update locations table
                const tbody = document.getElementById('merchant-locations');
                
                if (locations.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No locations found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                locations.forEach(location => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${location.location_name}</td>
                        <td>${location.address_line1}, ${location.city}, ${location.postal_code}</td>
                        <td>${location.items_in_stock || 0}</td>
                        <td>
                            <a href="index.php?page=inventory&location_id=${location.location_id}" class="btn btn-primary btn-sm">
                                Manage
                            </a>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => {
            console.error('Error loading merchant locations:', error);
            document.getElementById('merchant-locations').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
    
    // Fetch recent sales
    fetch(`/prs/api/merchant/sales?user_id=${userId}&limit=5`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const sales = data.data.sales;
                const tbody = document.getElementById('recent-sales');
                
                if (sales.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No recent sales</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                sales.forEach(sale => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${sale.item_name}</td>
                        <td>${sale.quantity}</td>
                        <td>${sale.location_name}</td>
                        <td>${new Date(sale.purchase_date).toLocaleString()}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => {
            console.error('Error loading recent sales:', error);
            document.getElementById('recent-sales').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
}

// Load Public User Dashboard
function loadPublicDashboard() {
    const userId = <?php echo $_SESSION['user']['user_id']; ?>;
    
    // Fetch user statistics
    fetch(`/prs/api/users/profile`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update statistics
                document.getElementById('user-vaccination-count').textContent = data.data.vaccination_count || 0;
                document.getElementById('user-family-count').textContent = data.data.family_members?.length || 0;
                document.getElementById('user-purchase-count').textContent = data.data.purchase_count || 0;
                document.getElementById('user-verified-count').textContent = data.data.verified_count || 0;
            }
        })
        .catch(error => {
            console.error('Error loading user statistics:', error);
        });
    
    // Fetch user vaccinations
    fetch(`/prs/api/vaccinations?action=user`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const records = data.data.records;
                const tbody = document.getElementById('user-vaccinations');
                
                if (records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No vaccination records found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                records.slice(0, 5).forEach(record => {
                    const row = document.createElement('tr');
                    
                    let status = '';
                    let statusClass = '';
                    
                    if (record.verified) {
                        status = 'Verified';
                        statusClass = 'badge-success';
                    } else {
                        status = 'Pending';
                        statusClass = 'badge-warning';
                    }
                    
                    row.innerHTML = `
                        <td>${record.vaccine_name}</td>
                        <td>${record.dose_number}</td>
                        <td>${new Date(record.date_administered).toLocaleDateString()}</td>
                        <td><span class="badge ${statusClass}">${status}</span></td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => {
            console.error('Error loading user vaccinations:', error);
            document.getElementById('user-vaccinations').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
    
    // Fetch user purchases
    fetch(`/prs/api/purchases?action=history`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const purchases = data.data.purchases;
                const tbody = document.getElementById('user-purchases');
                
                if (purchases.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No purchase history found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = '';
                
                purchases.slice(0, 5).forEach(purchase => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${purchase.item_name}</td>
                        <td>${purchase.quantity}</td>
                        <td>${purchase.location_name}</td>
                        <td>${new Date(purchase.purchase_date).toLocaleString()}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => {
            console.error('Error loading user purchases:', error);
            document.getElementById('user-purchases').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
    
    // Fetch critical items
    fetch('/prs/api/items?action=active')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const items = data.data.items;
                const container = document.getElementById('critical-items-list');
                
                if (items.length === 0) {
                    container.innerHTML = '<div class="text-center">No critical items found</div>';
                    return;
                }
                
                container.innerHTML = '';
                
                // Display only up to 4 items
                items.slice(0, 4).forEach(item => {
                    const itemCard = document.createElement('div');
                    itemCard.className = 'critical-item-card';
                    
                    itemCard.innerHTML = `
                        <h4>${item.item_name}</h4>
                        <p>${item.item_description || 'No description available'}</p>
                        <p><strong>Category:</strong> ${item.item_category}</p>
                        <p><strong>Purchase Limit:</strong> ${item.purchase_limit} per ${item.purchase_frequency}</p>
                        <a href="index.php?page=find-items&item_id=${item.item_id}" class="btn btn-primary btn-sm">Find Locations</a>
                    `;
                    
                    container.appendChild(itemCard);
                });
            }
        })
        .catch(error => {
            console.error('Error loading critical items:', error);
            document.getElementById('critical-items-list').innerHTML = 
                '<div class="text-center">Failed to load critical items</div>';
        });
}

// Verify Vaccination Record
function verifyVaccinationRecord(recordId, button) {
    // Disable button while processing
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner-sm"></span> Verifying...';
    
    // Send verification request
    fetch('/prs/api/vaccinations?action=verify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            record_id: recordId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update button to show success
            button.innerHTML = 'Verified ✓';
            button.classList.remove('btn-success');
            button.classList.add('btn-secondary');
            
            // Remove row after a short delay
            setTimeout(() => {
                button.closest('tr').remove();
                
                // Check if there are any remaining rows
                const tbody = document.getElementById('unverified-vaccinations');
                if (tbody.children.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No unverified vaccinations</td></tr>';
                }
            }, 1000);
        } else {
            // Show error
            button.innerHTML = 'Failed';
            button.classList.remove('btn-success');
            button.classList.add('btn-error');
            
            // Reset after a short delay
            setTimeout(() => {
                button.innerHTML = 'Verify';
                button.classList.remove('btn-error');
                button.classList.add('btn-success');
                button.disabled = false;
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error verifying vaccination record:', error);
        
        // Show error
        button.innerHTML = 'Failed';
        button.classList.remove('btn-success');
        button.classList.add('btn-error');
        
        // Reset after a short delay
        setTimeout(() => {
            button.innerHTML = 'Verify';
            button.classList.remove('btn-error');
            button.classList.add('btn-success');
            button.disabled = false;
        }, 2000);
    });
}
</script>