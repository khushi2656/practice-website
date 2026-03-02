<?php
/**
 * API Test Endpoint - Database Connectivity Demo
 * This file demonstrates how to connect to all three databases
 * Access: http://localhost/practice-website/php/api_test.php
 */

require_once 'config.php';

// Set JSON response
header('Content-Type: application/json');

// Get action parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'all';

$response = [
    'success' => false,
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => []
];

switch($action) {
    case 'mysql':
        $response['results']['mysql'] = testMySQLConnection();
        break;
    case 'mongodb':
        $response['results']['mongodb'] = testMongoDBConnection();
        break;
    case 'redis':
        $response['results']['redis'] = testRedisConnection();
        break;
    case 'all':
    default:
        $response['results']['mysql'] = testMySQLConnection();
        $response['results']['mongodb'] = testMongoDBConnection();
        $response['results']['redis'] = testRedisConnection();
        break;
}

// Check if all connections are successful
$allSuccess = true;
foreach($response['results'] as $result) {
    if (!$result['connected']) {
        $allSuccess = false;
        break;
    }
}
$response['success'] = $allSuccess;

echo json_encode($response, JSON_PRETTY_PRINT);

// ============================================
// Database Connection Test Functions
// ============================================

function testMySQLConnection() {
    $result = [
        'name' => 'MySQL',
        'connected' => false,
        'message' => '',
        'details' => []
    ];
    
    try {
        $conn = getMySQLConnection();
        
        if ($conn) {
            // Get version
            $versionQuery = $conn->query("SELECT VERSION() as version");
            $version = $versionQuery->fetch_assoc()['version'];
            
            // Check database
            $dbQuery = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
            $dbExists = $dbQuery->num_rows > 0;
            
            // Check users table
            $tableQuery = $conn->query("SHOW TABLES LIKE 'users'");
            $tableExists = $tableQuery->num_rows > 0;
            
            // Count users
            $userCount = 0;
            if ($tableExists) {
                $countQuery = $conn->query("SELECT COUNT(*) as count FROM users");
                $userCount = $countQuery->fetch_assoc()['count'];
            }
            
            $conn->close();
            
            $result['connected'] = true;
            $result['message'] = 'Successfully connected to MySQL';
            $result['details'] = [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'version' => $version,
                'database_exists' => $dbExists,
                'users_table_exists' => $tableExists,
                'total_users' => $userCount
            ];
        } else {
            $result['message'] = 'Failed to connect to MySQL';
            $result['details'] = ['error' => 'Connection returned null'];
        }
    } catch (Exception $e) {
        $result['message'] = 'MySQL Error: ' . $e->getMessage();
    }
    
    return $result;
}

function testMongoDBConnection() {
    $result = [
        'name' => 'MongoDB',
        'connected' => false,
        'message' => '',
        'details' => []
    ];
    
    try {
        if (!extension_loaded('mongodb')) {
            $result['message'] = 'MongoDB extension not loaded';
            $result['details'] = ['error' => 'Please install mongodb PHP extension'];
            return $result;
        }
        
        $mongoClient = getMongoDBConnection();
        
        if ($mongoClient) {
            // Ping MongoDB
            $command = new MongoDB\Driver\Command(['ping' => 1]);
            $pingResult = $mongoClient->executeCommand('admin', $command);
            
            // Get build info
            $buildInfoCommand = new MongoDB\Driver\Command(['buildInfo' => 1]);
            $buildInfo = $mongoClient->executeCommand('admin', $buildInfoCommand);
            $info = current($buildInfo->toArray());
            
            // Count profiles
            $filter = [];
            $options = [];
            $query = new MongoDB\Driver\Query($filter, $options);
            $namespace = MONGO_DB . '.profiles';
            
            try {
                $cursor = $mongoClient->executeQuery($namespace, $query);
                $profiles = iterator_to_array($cursor);
                $profileCount = count($profiles);
            } catch (Exception $e) {
                $profileCount = 0;
            }
            
            $result['connected'] = true;
            $result['message'] = 'Successfully connected to MongoDB';
            $result['details'] = [
                'host' => MONGO_HOST,
                'port' => MONGO_PORT,
                'database' => MONGO_DB,
                'version' => $info->version,
                'total_profiles' => $profileCount
            ];
        } else {
            $result['message'] = 'Failed to connect to MongoDB';
        }
    } catch (Exception $e) {
        $result['message'] = 'MongoDB Error: ' . $e->getMessage();
    }
    
    return $result;
}

function testRedisConnection() {
    $result = [
        'name' => 'Redis',
        'connected' => false,
        'message' => '',
        'details' => []
    ];
    
    try {
        if (!extension_loaded('redis')) {
            $result['message'] = 'Redis extension not loaded';
            $result['details'] = ['error' => 'Please install redis PHP extension'];
            return $result;
        }
        
        $redis = getRedisConnection();
        
        if ($redis) {
            $pong = $redis->ping();
            $info = $redis->info();
            
            // Count active sessions
            $sessionKeys = $redis->keys('session:*');
            $sessionCount = count($sessionKeys);
            
            $result['connected'] = true;
            $result['message'] = 'Successfully connected to Redis';
            $result['details'] = [
                'host' => REDIS_HOST,
                'port' => REDIS_PORT,
                'version' => isset($info['redis_version']) ? $info['redis_version'] : 'Unknown',
                'ping_response' => $pong,
                'active_sessions' => $sessionCount,
                'uptime_seconds' => isset($info['uptime_in_seconds']) ? $info['uptime_in_seconds'] : 0
            ];
        } else {
            $result['message'] = 'Failed to connect to Redis';
        }
    } catch (Exception $e) {
        $result['message'] = 'Redis Error: ' . $e->getMessage();
    }
    
    return $result;
}
?>
