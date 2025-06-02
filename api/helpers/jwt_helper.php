<?php
require_once __DIR__ . '/../config/config.php';

// Function to generate a JWT token
function generateJWT($user_id, $role_id) {
    $issuedAt = time();
    $expirationTime = $issuedAt + JWT_EXPIRY;
    
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'user_id' => $user_id,
        'role_id' => $role_id
    ];
    
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];
    
    $headerEncoded = base64UrlEncode(json_encode($header));
    $payloadEncoded = base64UrlEncode(json_encode($payload));
    
    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET, true);
    $signatureEncoded = base64UrlEncode($signature);
    
    return "$headerEncoded.$payloadEncoded.$signatureEncoded";
}

// Function to validate a JWT token
function validateJWT($token) {
    $tokenParts = explode('.', $token);
    
    if (count($tokenParts) != 3) {
        return false;
    }
    
    list($headerEncoded, $payloadEncoded, $signatureEncoded) = $tokenParts;
    
    $signature = base64UrlDecode($signatureEncoded);
    $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET, true);
    
    if (!hash_equals($signature, $expectedSignature)) {
        return false;
    }
    
    $payload = json_decode(base64UrlDecode($payloadEncoded));
    
    if ($payload->exp < time()) {
        return false;
    }
    
    return $payload;
}

// Function to get current user from JWT token
function getCurrentUser() {
    // Different ways to get the Authorization header (Apache can be tricky)
    $headers = getallheaders();
    $authHeader = null;
    
    // Method 1: Direct from headers
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } 
    // Method 2: Apache mod_rewrite specific
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    // Method 3: Apache with PHP as CGI
    else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    // Method 4: Try uppercase (some servers normalize header names)
    else if (isset($headers['AUTHORIZATION'])) {
        $authHeader = $headers['AUTHORIZATION'];
    }

    error_log("Auth header value found: " . ($authHeader ? 'YES' : 'NO'));
    if ($authHeader) error_log("Auth header: " . substr($authHeader, 0, 30) . "...");
    
    if (!$authHeader) {
        return null;
    }
    
    if (strpos($authHeader, 'Bearer ') !== 0) {
        return null;
    }
    
    $token = substr($authHeader, 7);
    return validateJWT($token);
}

// Base64Url encode
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Base64Url decode
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}
?>