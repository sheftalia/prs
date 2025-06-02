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
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="9"></rect>
                                    <rect x="14" y="3" width="7" height="5"></rect>
                                    <rect x="14" y="12" width="7" height="9"></rect>
                                    <rect x="3" y="16" width="7" height="5"></rect>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        
                        <?php if ($userRole <= 2): // Admin & Government Officials ?>
                        <li class="<?php echo $page === 'users' ? 'active' : ''; ?>">
                            <a href="index.php?page=users">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                Users
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="<?php echo $page === 'vaccinations' ? 'active' : ''; ?>">
                            <a href="index.php?page=vaccinations">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19.82 2H4.18A2.18 2.18 0 0 0 2 4.18v15.64A2.18 2.18 0 0 0 4.18 22h15.64A2.18 2.18 0 0 0 22 19.82V4.18A2.18 2.18 0 0 0 19.82 2Z"></path>
                                    <path d="M7 2v20"></path>
                                    <path d="M17 2v20"></path>
                                    <path d="M2 12h20"></path>
                                    <path d="M2 7h5"></path>
                                    <path d="M2 17h5"></path>
                                    <path d="M17 17h5"></path>
                                    <path d="M17 7h5"></path>
                                </svg>
                                Vaccinations
                            </a>
                        </li>
                        
                        <li class="<?php echo $page === 'critical-items' ? 'active' : ''; ?>">
                            <a href="index.php?page=critical-items">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                                Critical Items
                            </a>
                        </li>
                        
                        <?php if ($userRole <= 3): // Admin, Government & Merchants ?>
                        <li class="<?php echo $page === 'inventory' ? 'active' : ''; ?>">
                            <a href="index.php?page=inventory">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path>
                                    <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path>
                                    <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path>
                                </svg>
                                Inventory
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($userRole <= 3): // Admin, Government & Merchants ?>
                        <li class="<?php echo $page === 'purchases' ? 'active' : ''; ?>">
                            <a href="index.php?page=purchases">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                Purchases
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($userRole === 4): // Public Users ?>
                        <li class="<?php echo $page === 'find-items' ? 'active' : ''; ?>">
                            <a href="index.php?page=find-items">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                Find Items
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="<?php echo $page === 'profile' ? 'active' : ''; ?>">
                            <a href="index.php?page=profile">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
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

    <!-- API Authentication -->
    <script>
    /**
    * API Authentication Helper
    * Automatically adds JWT token to all fetch requests
    */
    (function() {
        // Store the original fetch function
        const originalFetch = window.fetch;
    
        // Override fetch with our version that adds the Authorization header
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
            return originalFetch(url, options);
        };
    })();
    </script>
</body>
</html>