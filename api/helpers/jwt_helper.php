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
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        return null;
    }
    
    $authHeader = $headers['Authorization'];
    
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