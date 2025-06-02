<?php
session_start();

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Email and password are required';
        header('Location: ../login.php');
        exit;
    }
    
    // Prepare API request
    $apiUrl = 'http://localhost/prs/api/auth';
    
    $data = [
        'action' => 'login',
        'email' => $email,
        'password' => $password
    ];
    
    // Initialize cURL session
    $ch = curl_init($apiUrl);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Execute cURL session
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $_SESSION['login_error'] = 'Failed to connect to the server: ' . curl_error($ch);
        header('Location: ../login.php');
        exit;
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Parse response
    $result = json_decode($response, true);
    
    if (isset($result['status']) && $result['status'] === 'success') {
        // Store user data in session
        $_SESSION['user'] = $result['data']['user'];
        $_SESSION['token'] = $result['data']['token'];
        
        //store token in sessionStorage
        echo '<script>
            sessionStorage.setItem("jwt_token", "' . $result['data']['token'] . '");
        </script>';

        // Redirect to dashboard
        header('Location: ../index.php');
        exit;
    } else {
        // Set error message
        $_SESSION['login_error'] = $result['message'] ?? 'Invalid email or password';
        header('Location: ../login.php');
        exit;
    }
} else {
    // Redirect to login page if accessed directly
    header('Location: ../login.php');
    exit;
}
?>