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
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="total-users">--</div>
                <div class="stat-label">Registered Users</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-syringe"></i>
                </div>
                <div class="stat-value" id="total-vaccinations">--</div>
                <div class="stat-label">Vaccinations Recorded</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-value" id="total-items-sold">--</div>
                <div class="stat-label">Critical Items Sold</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-warehouse"></i>
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
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-value" id="merchant-total-items">--</div>
                <div class="stat-label">Total Items</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value" id="merchant-total-sales">--</div>
                <div class="stat-label">Total Sales</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="merchant-total-customers">--</div>
                <div class="stat-label">Unique Customers</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value" id="merchant-low-stock">--</div>
                <div class="stat-label">Low Stock Items</div>
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
                    <i class="fas fa-syringe"></i>
                </div>
                <div class="stat-value" id="user-vaccination-count">--</div>
                <div class="stat-label">Your Vaccinations</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="user-family-count">--</div>
                <div class="stat-label">Family Members</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value" id="user-purchase-count">--</div>
                <div class="stat-label">Your Purchases</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
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
// Store chart instances to prevent canvas reuse errors
let chartInstances = {};

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
    console.log('Loading admin dashboard...');
    
    // Load system statistics
    fetch('/prs/api/stats?action=dashboard')
        .then(response => {
            console.log('Stats response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Stats data:', data);
            if (data.status === 'success') {
                // Update statistics
                document.getElementById('total-users').textContent = data.data.user_stats.total_users || 0;
                document.getElementById('total-vaccinations').textContent = data.data.vaccination_stats.total_records || 0;
                document.getElementById('total-items-sold').textContent = data.data.purchase_stats.total_items_sold || 0;
                document.getElementById('total-stock').textContent = data.data.inventory_stats.total_stock || 0;
                
                // Create charts after stats are loaded
                setTimeout(() => {
                    createVaccinationTrendChart();
                }, 100);

                setTimeout(() => {
                    createVaccineDistributionChart();
                }, 200);

                setTimeout(() => {
                    createPurchaseTrendChart();
                }, 300);

                setTimeout(() => {
                    createItemDistributionChart();
                }, 400);
            }
        })
        .catch(error => {
            console.error('Error loading dashboard statistics:', error);
            // Set default values
            document.getElementById('total-users').textContent = '0';
            document.getElementById('total-vaccinations').textContent = '0';
            document.getElementById('total-items-sold').textContent = '0';
            document.getElementById('total-stock').textContent = '0';
            
            // Still create charts even if stats fail
            setTimeout(() => {
                createVaccinationTrendChart();
            }, 100);

            setTimeout(() => {
                createVaccineDistributionChart();
            }, 200);

            setTimeout(() => {
                createPurchaseTrendChart();
            }, 300);

            setTimeout(() => {
                createItemDistributionChart();
            }, 400);
        });
    
    // Load unverified vaccinations
    loadUnverifiedVaccinations();
    
    // Load low stock items
    loadLowStockItems();
}

// Create Vaccination Trend Chart (Monthly data for 2023)
function createVaccinationTrendChart() {
    const ctx = document.getElementById('vaccination-trend-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (chartInstances['vaccination-trend-chart']) {
        chartInstances['vaccination-trend-chart'].destroy();
    }
    
    // Fetch vaccination trend data for 2023
    fetch('/prs/api/stats?action=vaccinations')
        .then(response => response.json())
        .then(data => {
            console.log('Vaccination trend data:', data);
            
            if (data.status === 'success' && data.data.vaccination_trend) {
                const trend = data.data.vaccination_trend;
                
                // Filter for 2023 data and sort by month
                const trend2023 = trend.filter(item => item.month.startsWith('2023')).sort();
                
                let labels, values;
                
                if (trend2023.length > 0) {
                    // Use real data
                    labels = trend2023.map(item => {
                        const [year, month] = item.month.split('-');
                        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                          'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        return monthNames[parseInt(month) - 1];
                    });
                    values = trend2023.map(item => parseInt(item.count));
                } else {
                    // No data for 2023, show empty chart with month labels
                    labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    values = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                }
                
                // Create the chart
                chartInstances['vaccination-trend-chart'] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Vaccinations',
                            data: values,
                            borderColor: '#005eb8',
                            backgroundColor: 'rgba(0, 94, 184, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Vaccination Trend 2023'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error fetching vaccination trend data:', error);
            // Create empty chart on error
            chartInstances['vaccination-trend-chart'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Vaccinations',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        borderColor: '#005eb8',
                        backgroundColor: 'rgba(0, 94, 184, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        title: { display: true, text: 'Vaccination Trend 2023 (No Data)' }
                    },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        });
}

// Create Vaccine Distribution Chart (COVID-19 vaccines only)
function createVaccineDistributionChart() {
    const ctx = document.getElementById('vaccine-distribution-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (chartInstances['vaccine-distribution-chart']) {
        chartInstances['vaccine-distribution-chart'].destroy();
    }
    
    // Fetch vaccine distribution data
    fetch('/prs/api/stats?action=vaccinations')
        .then(response => response.json())
        .then(data => {
            console.log('Vaccine distribution data:', data);
            
            if (data.status === 'success' && data.data.vaccine_distribution) {
                const distribution = data.data.vaccine_distribution;
                
                // Filter for COVID-19 vaccines only and shorten labels
                const covidVaccines = distribution.filter(item => 
                    item.vaccine_name.includes('COVID-19')
                ).map(item => {
                    let shortName = item.vaccine_name;
                    if (shortName.includes('AstraZeneca')) shortName = 'AstraZeneca';
                    else if (shortName.includes('Pfizer')) shortName = 'Pfizer';
                    else if (shortName.includes('Moderna')) shortName = 'Moderna';
                    
                    return {
                        vaccine_name: shortName,
                        count: parseInt(item.count)
                    };
                });
                
                let labels, values;
                
                if (covidVaccines.length > 0) {
                    labels = covidVaccines.map(item => item.vaccine_name);
                    values = covidVaccines.map(item => item.count);
                } else {
                    // No COVID-19 vaccine data
                    labels = ['No Data Available'];
                    values = [1];
                }
                
                const backgroundColors = [
                    '#005eb8', // NHS Blue - AstraZeneca
                    '#41b6e6', // Light Blue - Pfizer
                    '#330072', // Purple - Moderna
                    '#ae2573', // Pink
                    '#00a499', // Teal
                ];
                
                // Create the chart
                chartInstances['vaccine-distribution-chart'] = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: backgroundColors.slice(0, labels.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            title: {
                                display: true,
                                text: 'COVID-19 Vaccine Distribution'
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error fetching vaccine distribution data:', error);
            // Create empty chart on error
            chartInstances['vaccine-distribution-chart'] = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['No Data Available'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#cccccc'],
                        borderWidth: 1,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' },
                        title: { display: true, text: 'COVID-19 Vaccine Distribution (No Data)' }
                    }
                }
            });
        });
}

// Create Purchase Trend Chart (Daily for last 30 days)
function createPurchaseTrendChart() {
    console.log('createPurchaseTrendChart called');
    const ctx = document.getElementById('purchase-trend-chart');
    if (!ctx) {
        console.log('Canvas not found');
        return;
    }
    
    // Use Chart.js built-in method to check for existing chart
    const existingChart = Chart.getChart(ctx);
    console.log('Canvas found, existing chart:', existingChart);
    
    // Destroy existing chart if it exists
    if (existingChart) {
        console.log('Destroying existing chart');
        existingChart.destroy();
    }
    
    // Fetch purchase trend data
    fetch('/prs/api/stats?action=purchases')
        .then(response => response.json())
        .then(data => {
            console.log('Purchase trend data:', data);
            
            if (data.status === 'success' && data.data.purchase_trend) {
                const trend = data.data.purchase_trend;
                
                let labels, transactionCounts, itemCounts;
                
                if (trend.length > 0) {
                    // Sort by date
                    trend.sort((a, b) => new Date(a.date) - new Date(b.date));
                    
                    // Prepare data for the chart
                    labels = trend.map(item => {
                        const date = new Date(item.date);
                        return date.toLocaleDateString('en-GB', { 
                            month: 'short', 
                            day: 'numeric' 
                        });
                    });
                    transactionCounts = trend.map(item => parseInt(item.transaction_count) || 0);
                    itemCounts = trend.map(item => parseInt(item.item_count) || 0);
                } else {
                    // No purchase data available
                    labels = ['No Data'];
                    transactionCounts = [0];
                    itemCounts = [0];
                }
                
                // Create the chart and store in our tracking object
                console.log('About to create chart...');
                try {
                    chartInstances['purchase-trend-chart'] = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Transactions',
                                    data: transactionCounts,
                                    backgroundColor: '#005eb8', // NHS Blue
                                    borderWidth: 0
                                },
                                {
                                    label: 'Items Sold',
                                    data: itemCounts,
                                    backgroundColor: '#41b6e6', // Light Blue
                                    borderWidth: 0
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Purchase Trend (Last 30 Days)'
                                },
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                x: {
                                    ticks: {
                                        maxRotation: 45
                                    }
                                }
                            }
                        }
                    });
                    console.log('Chart created successfully:', chartInstances['purchase-trend-chart']);
                } catch (error) {
                    console.error('Error creating chart:', error);
                    throw error;
                }
            }
        })
        .catch(error => {
            console.error('Error fetching purchase trend data:', error);
            // Create empty chart on error
            try {
                chartInstances['purchase-trend-chart'] = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['No Data'],
                        datasets: [
                            {
                                label: 'Transactions',
                                data: [0],
                                backgroundColor: '#005eb8',
                                borderWidth: 0
                            },
                            {
                                label: 'Items Sold',
                                data: [0],
                                backgroundColor: '#41b6e6',
                                borderWidth: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: { display: true, text: 'Purchase Trend (No Data)' },
                            legend: { display: true, position: 'top' }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { ticks: { maxRotation: 45 } }
                        }
                    }
                });
            } catch (fallbackError) {
                console.error('Error creating fallback chart:', fallbackError);
            }
        });
}

// Create Item Distribution Chart (Total stock by category)
function createItemDistributionChart() {
    const ctx = document.getElementById('item-distribution-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (chartInstances['item-distribution-chart']) {
        chartInstances['item-distribution-chart'].destroy();
    }
    
    // Fetch inventory distribution data
    fetch('/prs/api/stats?action=inventory')
        .then(response => response.json())
        .then(data => {
            console.log('Inventory distribution data:', data);
            
            if (data.status === 'success' && data.data.category_distribution) {
                const distribution = data.data.category_distribution;
                
                let labels, values;
                
                if (distribution.length > 0) {
                    // Prepare data for the chart
                    labels = distribution.map(item => item.item_category);
                    values = distribution.map(item => parseInt(item.total_stock) || 0);
                } else {
                    // No inventory data
                    labels = ['No Data Available'];
                    values = [1];
                }
                
                const backgroundColors = [
                    '#005eb8', // NHS Blue
                    '#330072', // Purple
                    '#00a499', // Teal
                    '#78be20', // Green
                    '#ed8b00', // Orange
                    '#d5281b'  // Red
                ];
                
                // Create the chart
                chartInstances['item-distribution-chart'] = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: backgroundColors.slice(0, labels.length),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            title: {
                                display: true,
                                text: 'Inventory by Category'
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error fetching inventory distribution data:', error);
            // Create empty chart on error
            chartInstances['item-distribution-chart'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['No Data Available'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#cccccc'],
                        borderWidth: 1,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' },
                        title: { display: true, text: 'Inventory by Category (No Data)' }
                    }
                }
            });
        });
}

// Load unverified vaccinations
function loadUnverifiedVaccinations() {
    fetch('/prs/api/vaccinations?action=unverified&limit=5')
        .then(response => response.json())
        .then(data => {
            console.log('Unverified vaccinations:', data);
            const tbody = document.getElementById('unverified-vaccinations');
            
            if (data.status === 'success' && data.data.records.length > 0) {
                tbody.innerHTML = '';
                
                data.data.records.forEach(record => {
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
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No unverified vaccinations</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading unverified vaccinations:', error);
            document.getElementById('unverified-vaccinations').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
}

// Load low stock items
function loadLowStockItems() {
    fetch('/prs/api/inventory?action=low-stock&threshold=20')
        .then(response => response.json())
        .then(data => {
            console.log('Low stock items:', data);
            const tbody = document.getElementById('low-stock-items');
            
            if (data.status === 'success' && data.data.items.length > 0) {
                tbody.innerHTML = '';
                
                data.data.items.slice(0, 5).forEach(item => {
                    const row = document.createElement('tr');
                    
                    // Determine status
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
                        <td>${item.location_name}</td>
                        <td>${item.quantity_available}</td>
                        <td><span class="badge ${statusClass}">${status}</span></td>
                    `;
                    
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No low stock items</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading low stock items:', error);
            document.getElementById('low-stock-items').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
}

// Load Public User Dashboard
function loadPublicDashboard() {
    // Load user vaccinations
    fetch('/prs/api/vaccinations?action=user&limit=5')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('user-vaccinations');
            
            if (data.status === 'success' && data.data.records.length > 0) {
                tbody.innerHTML = '';
                
                data.data.records.forEach(record => {
                    const row = document.createElement('tr');
                    
                    let status = record.verified ? 
                        '<span class="badge badge-success">Verified</span>' : 
                        '<span class="badge badge-warning">Pending</span>';
                    
                    row.innerHTML = `
                        <td>${record.vaccine_name}</td>
                        <td>${record.dose_number}</td>
                        <td>${new Date(record.date_administered).toLocaleDateString()}</td>
                        <td>${status}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No vaccination records found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading user vaccinations:', error);
            document.getElementById('user-vaccinations').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
    
    // Load user purchases
    fetch('/prs/api/purchases?action=history&limit=5')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('user-purchases');
            
            if (data.status === 'success' && data.data.purchases.length > 0) {
                tbody.innerHTML = '';
                
                data.data.purchases.forEach(purchase => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${purchase.item_name}</td>
                        <td>${purchase.quantity}</td>
                        <td>${purchase.location_name}</td>
                        <td>${new Date(purchase.purchase_date).toLocaleString()}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No purchase history found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading user purchases:', error);
            document.getElementById('user-purchases').innerHTML = 
                '<tr><td colspan="4" class="text-center">Failed to load data</td></tr>';
        });
    
    // Load critical items
    fetch('/prs/api/items?action=active')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('critical-items-list');
            
            if (data.status === 'success' && data.data.items.length > 0) {
                container.innerHTML = '';
                
                // Display only up to 4 items
                data.data.items.slice(0, 4).forEach(item => {
                    const itemCard = document.createElement('div');
                    itemCard.className = 'critical-item-card';
                    itemCard.style.cssText = 'border: 1px solid var(--border-color); padding: 15px; margin: 10px; border-radius: 8px; background: var(--surface-color); color: var(--text-color);';
                    
                    itemCard.innerHTML = `
                        <h4>${item.item_name}</h4>
                        <p>${item.item_description || 'No description available'}</p>
                        <p><strong>Category:</strong> ${item.item_category}</p>
                        <p><strong>Purchase Limit:</strong> ${item.purchase_limit} per ${item.purchase_frequency}</p>
                        <a href="index.php?page=find-items&item_id=${item.item_id}" class="btn btn-primary btn-sm">Find Locations</a>
                    `;
                    
                    container.appendChild(itemCard);
                });
            } else {
                container.innerHTML = '<div class="text-center">No critical items found</div>';
            }
        })
        .catch(error => {
            console.error('Error loading critical items:', error);
            document.getElementById('critical-items-list').innerHTML = 
                '<div class="text-center">Failed to load critical items</div>';
        });
    
    // Set default values for stats
    document.getElementById('user-vaccination-count').textContent = '0';
    document.getElementById('user-family-count').textContent = '0';
    document.getElementById('user-purchase-count').textContent = '0';
    document.getElementById('user-verified-count').textContent = '0';
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