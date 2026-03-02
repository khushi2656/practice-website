<?php
// ============================================
// LOAD ENVIRONMENT VARIABLES FROM .ENV FILE
// ============================================
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Set as environment variable and make it available via getenv()
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Load .env file from the parent directory
loadEnv(__DIR__ . '/../.env');

// ============================================
// AUTOMATIC ENVIRONMENT DETECTION
// Works on both localhost and online (Railway)
// ============================================

// Detect if running locally or online
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']) 
           || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false
           || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;

// ============================================
// MYSQL CONFIGURATION
// ============================================
if ($isLocal) {
    // LOCAL: XAMPP MySQL
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'user_auth_db');
    define('DB_PORT', 3306);
} else {
    // ONLINE: Railway MySQL (Update these when you deploy)
    define('DB_HOST', getenv('DB_HOST') ?: 'containers-us-west-123.railway.app');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: 'your-railway-mysql-password');
    define('DB_NAME', getenv('DB_NAME') ?: 'railway');
    define('DB_PORT', getenv('DB_PORT') ?: 6543);
}

// ============================================
// MONGODB CONFIGURATION (Cloud - Atlas)
// ============================================
// Using MongoDB Atlas for BOTH local and online
// Loads from .env file automatically
define('MONGO_URI', getenv('MONGO_URI') ?: 'mongodb://localhost:27017');
define('MONGO_DB', 'user_profiles_db');

// Uncomment below to use local MongoDB instead:
// if ($isLocal) {
//     define('MONGO_URI', 'mongodb://localhost:27017');
// }

// ============================================
// REDIS CONFIGURATION (Cloud)
// ============================================
// Using Redis Cloud for BOTH local and online
// Replace with your actual Redis Cloud credentials
if ($isLocal) {
    // Option 1: Use local Redis if installed
    define('REDIS_HOST', getenv('REDIS_HOST') ?: '127.0.0.1');
    define('REDIS_PORT', getenv('REDIS_PORT') ?: 6379);
    define('REDIS_PASSWORD', getenv('REDIS_PASSWORD') ?: null);
    
    // Option 2: Use Redis Cloud even locally (comment above, uncomment below)
    // define('REDIS_HOST', 'redis-12345.c123.us-east-1-1.ec2.cloud.redislabs.com');
    // define('REDIS_PORT', 12345);
    // define('REDIS_PASSWORD', 'your-redis-cloud-password');
} else {
    // ONLINE: Redis Cloud
    define('REDIS_HOST', getenv('REDIS_HOST') ?: 'redis-12345.c123.us-east-1-1.ec2.cloud.redislabs.com');
    define('REDIS_PORT', getenv('REDIS_PORT') ?: 12345);
    define('REDIS_PASSWORD', getenv('REDIS_PASSWORD') ?: 'your-redis-cloud-password');
}

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// ============================================
// CONNECTION FUNCTIONS
// ============================================

// Function to get MySQL connection
function getMySQLConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($conn->connect_error) {
            throw new Exception("MySQL Connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        throw new Exception("Database connection error");
    }
}

// Function to get MongoDB connection
function getMongoDBConnection() {
    try {
        $manager = new MongoDB\Driver\Manager(MONGO_URI);
        
        // Test connection with ping
        $command = new MongoDB\Driver\Command(['ping' => 1]);
        $manager->executeCommand('admin', $command);
        
        return $manager;
    } catch (Exception $e) {
        error_log("MongoDB Connection failed: " . $e->getMessage());
        throw new Exception("MongoDB connection error");
    }
}

// Function to get Redis connection
function getRedisConnection() {
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        
        // Authenticate if password is set
        if (REDIS_PASSWORD) {
            $redis->auth(REDIS_PASSWORD);
        }
        
        // Test connection
        $redis->ping();
        
        return $redis;
    } catch (Exception $e) {
        error_log("Redis Connection failed: " . $e->getMessage());
        throw new Exception("Redis connection error");
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
    try {
        $redis = getRedisConnection();
        $storedUserId = $redis->get('session:' . $sessionToken);
        
        if ($storedUserId && $storedUserId == $userId) {
            // Extend session expiry
            $redis->expire('session:' . $sessionToken, SESSION_TIMEOUT);
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Session validation failed: " . $e->getMessage());
        return false;
    }
}

// Debug function - tells you which environment you're in
function getEnvironmentInfo() {
    global $isLocal;
    return [
        'environment' => $isLocal ? 'localhost' : 'online',
        'mysql_host' => DB_HOST,
        'mongo_uri' => substr(MONGO_URI, 0, 20) . '...',
        'redis_host' => REDIS_HOST
    ];
}
?>
