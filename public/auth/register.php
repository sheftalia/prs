<?php
session_start();

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $nationalId = $_POST['national_id'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Validate input
    $errors = [];
    
    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($dob)) {
        $errors[] = 'Date of birth is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!$terms) {
        $errors[] = 'You must agree to the Terms of Service';
    }
    
    if (!empty($errors)) {
        $_SESSION['register_error'] = implode(', ', $errors);
        header('Location: ../register.php');
        exit;
    }
    
    // Prepare API request
    $apiUrl = 'http://localhost/prs/api/auth';
    
    $data = [
        'action' => 'register',
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'national_id' => $nationalId,
        'dob' => $dob,
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
        $_SESSION['register_error'] = 'Failed to connect to the server: ' . curl_error($ch);
        header('Location: ../register.php');
        exit;
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Parse response
    $result = json_decode($response, true);
    
    if (isset($result['status']) && $result['status'] === 'success') {
        // Store success message
        $_SESSION['register_success'] = 'Registration successful! You can now sign in.';
        header('Location: ../login.php');
        exit;
    } else {
        // Set error message
        $_SESSION['register_error'] = $result['message'] ?? 'Registration failed. Please try again.';
        header('Location: ../register.php');
        exit;
    }
} else {
    // Redirect to registration page if accessed directly
    header('Location: ../register.php');
    exit;
}
?>