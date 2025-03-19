<?php
require_once __DIR__ . '/../config/config.php';

class CORS {
    public function handle() {
        // Get the origin
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        // Check if the origin is allowed
        $allowedOrigins = explode(',', CORS_ALLOWED_ORIGINS);
        
        if (in_array($origin, $allowedOrigins) || CORS_ALLOWED_ORIGINS === '*') {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: " . $allowedOrigins[0]);
        }
        
        // Allow credentials
        header("Access-Control-Allow-Credentials: true");
        
        // Allow methods
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        
        // Allow headers
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        
        // Max age
        header("Access-Control-Max-Age: 86400"); // 24 hours
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }
}
?>