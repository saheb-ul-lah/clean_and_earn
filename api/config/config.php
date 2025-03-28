<?php
// API configuration
define('API_VERSION', '1.0.0');
define('JWT_SECRET', 'your_jwt_secret_key_change_this_in_production'); // Change this in production
define('JWT_EXPIRATION', 3600); // Token expiration time in seconds (1 hour)
define('RATE_LIMIT', 100); // Maximum requests per minute
define('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,https://cleanearnindia.com'); // Allowed origins for CORS

// Error reporting
error_reporting(E_ALL);
// Don't display errors in production
// ini_set('display_errors', 0); 

// Set timezone
date_default_timezone_set('Asia/Kolkata');
?>