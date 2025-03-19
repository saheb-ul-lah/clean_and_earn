<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/job/caev0/'); // Ensure it ends with /
}

// Automatically detect if running on localhost or live server
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . $host . BASE_PATH);
}
?>
