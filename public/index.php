<?php
// Redirect to login page if not authenticated
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Get user role for conditional content
$userRole = $_SESSION['user']['role_id'];
$roleName = '';

switch ($userRole) {
    case 1:
        $roleName = 'Administrator';
        break;
    case 2:
        $roleName = 'Government Official';
        break;
    case 3:
        $roleName = 'Merchant';
        break;
    case 4:
        $roleName = 'Public User';
        break;
}

// Get current page from URL parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pandemic Resilience System - <?php echo ucfirst($page); ?></title>
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">

    <!-- Theme toggle - load appropriate theme based on user preference -->
    <script>
        // Check if dark mode is preferred
        const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDarkMode)) {
            document.documentElement.classList.add('dark-theme');
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/prs-logo.png" alt="Pandemic Resilience System">
                </a>
            </div>
            <div class="header-right">
                <button id="theme-toggle" class="theme-toggle" aria-label="Toggle dark mode">
                    <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['user']['full_name']; ?></span>
                    <span class="user-role"><?php echo $roleName; ?></span>
                </div>
                <a href="logout.php" class="logout-btn">Sign Out</a>
            </div>
        </header>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Sidebar -->
            <aside class="sidebar">
                <nav class="nav">
                    <ul>
                        <li class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                            <a href="index.php?page=dashboard">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <?php if ($userRole <= 2): // Admin & Government Officials ?>
                        <li class="<?php echo $page === 'users' ? 'active' : ''; ?>">
                            <a href="index.php?page=users">
                                <i class="fas fa-users"></i>
                                Users
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($userRole <= 2 || $userRole === 4): // Admin, Government Officials, and Public Users ?>
                        <li class="<?php echo $page === 'vaccinations' ? 'active' : ''; ?>">
                            <a href="index.php?page=vaccinations">
                                <i class="fas fa-syringe"></i>
                                Vaccinations
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="<?php echo $page === 'critical-items' ? 'active' : ''; ?>">
                            <a href="index.php?page=critical-items">
                                <i class="fas fa-box-open"></i>
                                Critical Items
                            </a>
                        </li>
                        
                        <?php if ($userRole <= 3): // Admin, Government & Merchants ?>
                        <li class="<?php echo $page === 'inventory' ? 'active' : ''; ?>">
                            <a href="index.php?page=inventory">
                                <i class="fas fa-warehouse"></i>
                                Inventory
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($userRole === 4): // Only Public Users ?>
                        <li class="<?php echo $page === 'purchases' ? 'active' : ''; ?>">
                            <a href="index.php?page=purchases">
                                <i class="fas fa-shopping-cart"></i>
                                Purchase History
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($userRole === 4): // Public Users ?>
                        <li class="<?php echo $page === 'find-items' ? 'active' : ''; ?>">
                            <a href="index.php?page=find-items">
                                <i class="fas fa-search-location"></i>
                                Find Items
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="<?php echo $page === 'profile' ? 'active' : ''; ?>">
                            <a href="index.php?page=profile">
                                <i class="fas fa-user-circle"></i>
                                My Profile
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>
            
            <!-- Content Area -->
            <main class="content">
                <?php
                // Include the appropriate page content
                $pagePath = 'pages/' . $page . '.php';
                if (file_exists($pagePath)) {
                    include $pagePath;
                } else {
                    echo '<div class="alert alert-error">Page not found.</div>';
                }
                ?>
            </main>
        </div>
        
        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Pandemic Resilience System. All rights reserved.</p>
            <p>Version 1.0.0</p>
        </footer>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Theme toggle functionality
        document.getElementById('theme-toggle').addEventListener('click', function() {
            document.documentElement.classList.toggle('dark-theme');
            const isDarkTheme = document.documentElement.classList.contains('dark-theme');
            localStorage.setItem('theme', isDarkTheme ? 'dark' : 'light');
        });
    </script>

    <!-- API Authentication with 401 Handling -->
    <script>
    /**
     * API Authentication Helper
     * Automatically adds JWT token to all fetch requests and handles 401 errors
     */
    (function() {
        // Store the original fetch function (only once)
        const originalFetch = window.fetch;
        
        // Override fetch with our enhanced version
        window.fetch = function(url, options = {}) {
            // Get the token from session
            const token = '<?php echo isset($_SESSION["token"]) ? $_SESSION["token"] : ""; ?>';
            
            // Set up headers if they don't exist
            if (!options.headers) {
                options.headers = {};
            }
            
            // Only add Authorization if we have a token
            if (token) {
                options.headers['Authorization'] = 'Bearer ' + token;
            }
            
            // Call the original fetch with our enhanced options
            return originalFetch(url, options)
                .then(response => {
                    // Handle 401 errors
                    if (response.status === 401) {
                        console.log('Unauthorized request detected, redirecting to login');
                        // Redirect to login page after a short delay
                        setTimeout(() => {
                            window.location.href = 'login.php?session_expired=1';
                        }, 100);
                    }
                    return response;
                });
        };
    })();
    </script>

    <script src="assets/js/session-timeout.js"></script>
</body>
</html>