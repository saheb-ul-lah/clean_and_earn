<?php
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/config.php';

class RateLimit {
    private $response;
    private $redis;
    private $useRedis = false;

    public function __construct() {
        $this->response = new Response();
        
        // Try to use Redis if available
        if (class_exists('Redis')) {
            try {
                $this->redis = new Redis();
                $this->redis->connect('127.0.0.1', 6379);
                $this->useRedis = true;
            } catch (Exception $e) {
                // Fall back to file-based rate limiting
                $this->useRedis = false;
            }
        }
    }

    public function check() {
        // Get client IP
        $ip = $this->getClientIP();
        
        if ($this->useRedis) {
            return $this->checkRedis($ip);
        } else {
            return $this->checkFile($ip);
        }
    }
    
    private function checkRedis($ip) {
        $key = "rate_limit:$ip";
        $count = $this->redis->get($key);
        
        if (!$count) {
            // First request, set count to 1 with expiry of 60 seconds
            $this->redis->setex($key, 60, 1);
            return true;
        }
        
        // Increment count
        $count = $this->redis->incr($key);
        
        // Check if limit exceeded
        if ($count > RATE_LIMIT) {
            $ttl = $this->redis->ttl($key);
            $this->response->sendError('Too Many Requests', "Rate limit exceeded. Try again in $ttl seconds.", 429);
            exit();
        }
        
        return true;
    }
    
    private function checkFile($ip) {
        $file = sys_get_temp_dir() . "/rate_limit_$ip.json";
        
        if (!file_exists($file)) {
            // First request
            $data = [
                'count' => 1,
                'timestamp' => time()
            ];
            file_put_contents($file, json_encode($data));
            return true;
        }
        
        // Read existing data
        $data = json_decode(file_get_contents($file), true);
        $now = time();
        
        // Reset if more than 60 seconds have passed
        if ($now - $data['timestamp'] > 60) {
            $data = [
                'count' => 1,
                'timestamp' => $now
            ];
        } else {
            // Increment count
            $data['count']++;
        }
        
        // Check if limit exceeded
        if ($data['count'] > RATE_LIMIT) {
            $timeLeft = 60 - ($now - $data['timestamp']);
            $this->response->sendError('Too Many Requests', "Rate limit exceeded. Try again in $timeLeft seconds.", 429);
            exit();
        }
        
        // Update file
        file_put_contents($file, json_encode($data));
        return true;
    }
    
    private function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
?>