<?php
require_once __DIR__ . '/../config/config.php';

class JWT {
    public function generate($payload) {
        // Create token header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        
        // Add expiration to payload
        $payload['exp'] = time() + JWT_EXPIRATION;
        $payload['iat'] = time();
        
        // Encode Header
        $base64UrlHeader = $this->base64UrlEncode($header);
        
        // Encode Payload
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        
        // Create Signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        return $jwt;
    }
    
    public function validate($token) {
        // Split the token
        $tokenParts = explode('.', $token);
        
        if (count($tokenParts) != 3) {
            throw new Exception('Invalid token format');
        }
        
        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $signatureProvided = $tokenParts[2];
        
        // Check the signature
        $signature = hash_hmac('sha256', $header . "." . $payload, JWT_SECRET, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        if ($base64UrlSignature !== $signatureProvided) {
            throw new Exception('Invalid signature');
        }
        
        // Decode payload
        $decodedPayload = json_decode($this->base64UrlDecode($payload));
        
        // Check if token is expired
        if (isset($decodedPayload->exp) && $decodedPayload->exp < time()) {
            throw new Exception('Token has expired');
        }
        
        return $decodedPayload;
    }
    
    private function base64UrlEncode($data) {
        $base64 = base64_encode($data);
        $base64Url = strtr($base64, '+/', '-_');
        return rtrim($base64Url, '=');
    }
    
    private function base64UrlDecode($data) {
        $base64Url = strtr($data, '-_', '+/');
        $base64 = str_pad($base64Url, strlen($data) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($base64);
    }
}
?>