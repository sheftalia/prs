<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("Processing request: " . $_SERVER['REQUEST_URI']);

// Debug incoming request headers
$allHeaders = getallheaders();
error_log("All request headers: " . json_encode($allHeaders));
if (isset($allHeaders['Authorization'])) {
    error_log("Authorization header is present!");
} else {
    error_log("Authorization header is NOT present!");
}

// Also check $_SERVER
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    error_log("HTTP_AUTHORIZATION server var is present!");
} else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    error_log("REDIRECT_HTTP_AUTHORIZATION server var is present!");
} else {
    error_log("No authorization server vars found");
}

// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle OPTIONS method for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include database and other required files
include_once 'config/database.php';
include_once 'config/config.php';
include_once 'helpers/jwt_helper.php';
include_once 'helpers/response_helper.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$endpoints = explode('/', trim($request_uri, '/'));

// Remove 'prs' and 'api' from the beginning of the path if present
$api_index = array_search('api', $endpoints);
if ($api_index !== false) {
    $endpoints = array_slice($endpoints, $api_index + 1);
}

// Get the endpoint and ID (if provided)
$endpoint = isset($endpoints[0]) ? strtolower($endpoints[0]) : '';
$id = isset($endpoints[1]) ? $endpoints[1] : null;
$action = isset($endpoints[2]) ? $endpoints[2] : null;

// IMPORTANT: Also check for action in query parameters if not in URL path
if (!$action && isset($_GET['action'])) {
    $action = $_GET['action'];
}

// Handle different endpoints
switch ($endpoint) {
    case 'stats':
        error_log("Stats endpoint called with action: " . $action);
        include_once 'controllers/stats_controller.php';
        $controller = new StatsController($db);
        $controller->processRequest($id, $action);
        break;

    case 'auth':
        include_once 'controllers/auth_controller.php';
        $controller = new AuthController($db);
        $controller->processRequest();
        break;
        
    case 'users':
        include_once 'controllers/user_controller.php';
        $controller = new UserController($db);
        
        // Handle profile and activity endpoints specifically
        if (count($endpoints) > 1 && $endpoints[1] == 'profile') {
            $controller->processRequest(null, 'profile');
        } 
        else if (count($endpoints) > 1 && $endpoints[1] == 'activity') {
            $controller->processRequest(null, 'activity');
        }
        else if ($id && $action) {
            $controller->processRequest($id, $action);
        } 
        else if ($id) {
            $controller->processRequest($id);
        } 
        else {
            $controller->processRequest();
        }
        break;
        
    case 'vaccinations':
        include_once 'controllers/vaccination_controller.php';
        $controller = new VaccinationController($db);
        $controller->processRequest($id, $action);
        break;
        
    case 'items':
        include_once 'controllers/item_controller.php';
        $controller = new ItemController($db);
        $controller->processRequest($id, $action);
        break;
        
    case 'inventory':
        include_once 'controllers/inventory_controller.php';
        $controller = new InventoryController($db);
        $controller->processRequest($id, $action);
        break;
        
    case 'purchases':
        include_once 'controllers/purchase_controller.php';
        $controller = new PurchaseController($db);
        $controller->processRequest($id, $action);
        break;
        
    case 'documents':
        include_once 'controllers/document_controller.php';
        $controller = new DocumentController($db);
        $controller->processRequest($id, $action);
        break;
        
    default:
        // Add debugging
        error_log("Unrecognized endpoint: " . $endpoint);
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Endpoint not found: " . $endpoint]);
        break;
}
?>