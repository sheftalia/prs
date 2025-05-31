<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Check for login errors
$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

// Check for registration success
$success = '';
if (isset($_SESSION['register_success'])) {
    $success = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pandemic Resilience System</title>
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <link rel="icon" href="assets/images/favicon.png" type="image/png">

    <!-- Theme toggle -->
    <script>
        // Check if dark mode is preferred
        const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDarkMode)) {
            document.documentElement.classList.add('dark-theme');
        }
    </script>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="assets/images/prs-logo.png" alt="Pandemic Resilience System" class="auth-logo">
                <h1>Sign In</h1>
                <p>Enter your credentials to access the system</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="auth/login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
        
        <div class="theme-toggle-container">
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
        </div>
    </div>
    
    <script>
        // Theme toggle functionality
        document.getElementById('theme-toggle').addEventListener('click', function() {
            document.documentElement.classList.toggle('dark-theme');
            const isDarkTheme = document.documentElement.classList.contains('dark-theme');
            localStorage.setItem('theme', isDarkTheme ? 'dark' : 'light');
        });
    </script>
</body>
</html>