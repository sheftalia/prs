<?php
// Function to send a JSON response
function sendResponse($status, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

// Function to handle errors
function handleError($message, $statusCode = 500) {
    sendResponse('error', $message, null, $statusCode);
}

// Function to check if required fields are present
function validateRequiredParams($params, $requiredFields) {
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($params[$field]) || empty($params[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        handleError('Missing required fields: ' . implode(', ', $missingFields), 400);
    }
    
    return true;
}

// Function to sanitize input data
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
    } else {
        $data = htmlspecialchars(strip_tags(trim($data)));
    }
    
    return $data;
}
?>