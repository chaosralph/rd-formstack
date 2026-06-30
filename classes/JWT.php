<?php
/**
 * JWT Token Handling
 */
require_once __DIR__ . '/../config/config.php';

class JWT {
    /**
     * Erstellt einen JWT Token
     */
    public static function encode($payload) {
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];
        
        $payload['exp'] = time() + JWT_EXPIRATION;
        $payload['iat'] = time();
        
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, JWT_SECRET, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    /**
     * Dekodiert und validiert einen JWT Token
     */
    public static function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Signatur prüfen
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, JWT_SECRET, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }
        
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        // Ablaufzeit prüfen
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
    
    /**
     * Holt Token aus verschiedenen Quellen
     */
    public static function getTokenFromHeader() {
        $token = null;
        
        // Authorization Header
        $authHeader = null;
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (!empty($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            } elseif (!empty($headers['authorization'])) {
                $authHeader = $headers['authorization'];
            }
        }
        
        if (!$authHeader && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }
        
        // X-Auth-Token Header
        if (!$token && !empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            $token = trim($_SERVER['HTTP_X_AUTH_TOKEN']);
        }
        
        // Cookie
        if (!$token && !empty($_COOKIE['auth_token'])) {
            $token = $_COOKIE['auth_token'];
        }
        
        return $token ?: null;
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
