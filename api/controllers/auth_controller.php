<?php
class AuthController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function processRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);
                
                if (isset($data['action'])) {
                    switch ($data['action']) {
                        case 'login':
                            $this->login($data);
                            break;
                        case 'register':
                            $this->register($data);
                            break;
                        default:
                            handleError("Invalid action", 400);
                    }
                } else {
                    handleError("Action not specified", 400);
                }
                break;
                
            default:
                handleError("Method not allowed", 405);
                break;
        }
    }
    
    private function login($data) {
        // Validate required fields
        validateRequiredParams($data, ['email', 'password']);
        
        // Include user model
        include_once 'models/User.php';
        $user = new User($this->db);
        
        // Set email property
        $user->email = $data['email'];
        
        // Check if user exists
        if ($user->readByEmail()) {
            // Verify password
            if (password_verify($data['password'], $user->password_hash)) {
                // Update last login timestamp
                $user->updateLastLogin();
                
                // Generate JWT token
                $token = generateJWT($user->user_id, $user->role_id);
                
                // Log activity
                logActivity($user->user_id, 'LOGIN', 'users', $user->user_id);
                
                // Return success with token and user info
                sendResponse('success', 'Login successful', [
                    'token' => $token,
                    'user' => [
                        'user_id' => $user->user_id,
                        'full_name' => $user->full_name,
                        'email' => $user->email,
                        'prs_id' => $user->prs_id,
                        'role_id' => $user->role_id
                    ]
                ]);
            } else {
                handleError('Invalid email or password', 401);
            }
        } else {
            handleError('Invalid email or password', 401);
        }
    }
    
    private function register($data) {
        // Validate required fields
        validateRequiredParams($data, ['full_name', 'email', 'password', 'dob']);
        
        // Include user model
        include_once 'models/User.php';
        $user = new User($this->db);
        
        // Set user properties
        $user->full_name = $data['full_name'];
        $user->email = $data['email'];
        $user->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $user->phone = $data['phone'] ?? null;
        $user->national_id = $data['national_id'] ?? null;
        $user->dob = $data['dob'];
        $user->role_id = 4; // Default role is Public User
        
        // Check if email already exists
        $user->email = $data['email'];
        if ($user->readByEmail()) {
            handleError('Email already exists', 400);
        }
        
        // Create the user
        if ($user->create()) {
            // Generate JWT token
            $token = generateJWT($user->user_id, $user->role_id);
            
            // Log activity
            logActivity($user->user_id, 'REGISTER', 'users', $user->user_id);
            
            // Return success with token and user info
            sendResponse('success', 'Registration successful', [
                'token' => $token,
                'user' => [
                    'user_id' => $user->user_id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'prs_id' => $user->prs_id,
                    'role_id' => $user->role_id
                ]
            ], 201);
        } else {
            handleError('Registration failed', 500);
        }
    }
}
?>