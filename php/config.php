<?php
// MySQL Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_auth_db');

// MongoDB Configuration
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', 27017);
define('MONGO_DB', 'user_profiles_db');

// Redis Configuration
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);

// Function to get MySQL connection
function getMySQLConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("MySQL Connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

// Function to get MongoDB connection
function getMongoDBConnection() {
    try {
        $mongoClient = new MongoDB\Driver\Manager("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
        return $mongoClient;
    } catch (Exception $e) {
        error_log("MongoDB Connection failed: " . $e->getMessage());
        return null;
    }
}

// Function to get Redis connection
function getRedisConnection() {
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        return $redis;
    } catch (Exception $e) {
        error_log("Redis Connection failed: " . $e->getMessage());
        return null;
    }
}

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Function to generate session token
function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

// Function to validate session
function validateSession($sessionToken, $userId) {
    $redis = getRedisConnection();
    if (!$redis) {
        return false;
    }
    
    $storedUserId = $redis->get('session:' . $sessionToken);
    
    if ($storedUserId && $storedUserId == $userId) {
        // Extend session expiry
        $redis->expire('session:' . $sessionToken, 3600); // 1 hour
        return true;
    }
    
    return false;
}
?>
