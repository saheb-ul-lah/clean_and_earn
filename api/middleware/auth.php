<?php
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/response.php';

class Auth {
    private $jwt;
    private $response;

    public function __construct() {
        $this->jwt = new JWT();
        $this->response = new Response();
    }

    public function authenticate() {
        // Get headers
        $headers = getallheaders();
        
        // Check if Authorization header exists
        if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
            $this->response->sendError('Unauthorized', 'Authorization header not found', 401);
            exit();
        }
        
        // Get the Authorization header
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
        
        // Check if it's a Bearer token
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->response->sendError('Unauthorized', 'Invalid authorization format', 401);
            exit();
        }
        
        $token = $matches[1];
        
        // Validate token
        try {
            $decoded = $this->jwt->validate($token);
            return $decoded;
        } catch (Exception $e) {
            $this->response->sendError('Unauthorized', $e->getMessage(), 401);
            exit();
        }
    }
    
    public function checkRole($requiredRoles) {
        $decoded = $this->authenticate();
        
        if (!isset($decoded->role)) {
            $this->response->sendError('Forbidden', 'Role information missing', 403);
            exit();
        }
        
        if (!is_array($requiredRoles)) {
            $requiredRoles = [$requiredRoles];
        }
        
        if (!in_array($decoded->role, $requiredRoles)) {
            $this->response->sendError('Forbidden', 'You do not have permission to access this resource', 403);
            exit();
        }
        
        return $decoded;
    }
}
?>