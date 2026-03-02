<?php
require_once 'config.php';

$test = isset($_POST['test']) ? $_POST['test'] : '';

switch ($test) {
    case 'mysql':
        testMySQL();
        break;
    case 'mongodb':
        testMongoDB();
        break;
    case 'redis':
        testRedis();
        break;
    case 'extensions':
        testExtensions();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid test type']);
}

function testMySQL() {
    try {
        $conn = getMySQLConnection();
        if ($conn) {
            $result = $conn->query("SELECT VERSION() as version");
            if ($result) {
                $row = $result->fetch_assoc();
                $version = $row['version'];
                
                // Check if database exists
                $dbCheck = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
                $dbExists = $dbCheck && $dbCheck->num_rows > 0;
                
                // Check if users table exists
                $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
                $tableExists = $tableCheck && $tableCheck->num_rows > 0;
                
                $conn->close();
                
                $message = "MySQL Version: $version\n";
                $message .= "Database '" . DB_NAME . "': " . ($dbExists ? "EXISTS ✓" : "NOT FOUND ✗") . "\n";
                $message .= "Users table: " . ($tableExists ? "EXISTS ✓" : "NOT FOUND ✗");
                
                if (!$dbExists || !$tableExists) {
                    $message .= "\n\n⚠️  Please run database_setup.sql to create the database and tables";
                }
                
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not query MySQL']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not connect to MySQL. Check credentials in php/config.php']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'MySQL Error: ' . $e->getMessage()]);
    }
}

function testMongoDB() {
    try {
        if (!extension_loaded('mongodb')) {
            echo json_encode(['success' => false, 'message' => 'MongoDB extension not loaded. Please install: pecl install mongodb']);
            return;
        }
        
        $mongoClient = getMongoDBConnection();
        if ($mongoClient) {
            $command = new MongoDB\Driver\Command(['ping' => 1]);
            $result = $mongoClient->executeCommand('admin', $command);
            
            $response = current($result->toArray());
            if (isset($response->ok) && $response->ok == 1) {
                // Try to get server info
                $buildInfoCommand = new MongoDB\Driver\Command(['buildInfo' => 1]);
                $buildInfo = $mongoClient->executeCommand('admin', $buildInfoCommand);
                $info = current($buildInfo->toArray());
                
                $message = "MongoDB Version: " . $info->version . "\n";
                $message .= "Host: " . MONGO_HOST . ":" . MONGO_PORT . "\n";
                $message .= "Database: " . MONGO_DB . "\n";
                $message .= "Connection: OK ✓";
                
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['success' => false, 'message' => 'MongoDB ping failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not connect to MongoDB. Make sure MongoDB is running.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'MongoDB Error: ' . $e->getMessage() . '\n\nMake sure MongoDB is running (net start MongoDB)']);
    }
}

function testRedis() {
    try {
        if (!extension_loaded('redis')) {
            echo json_encode(['success' => false, 'message' => 'Redis extension not loaded. Please install: pecl install redis']);
            return;
        }
        
        $redis = getRedisConnection();
        if ($redis) {
            $pong = $redis->ping();
            $info = $redis->info();
            
            $message = "Redis Version: " . (isset($info['redis_version']) ? $info['redis_version'] : 'Unknown') . "\n";
            $message .= "Host: " . REDIS_HOST . ":" . REDIS_PORT . "\n";
            $message .= "Ping Response: " . $pong . "\n";
            $message .= "Connection: OK ✓";
            
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not connect to Redis. Make sure Redis is running (redis-server)']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Redis Error: ' . $e->getMessage() . '\n\nMake sure Redis is running (redis-server)']);
    }
}

function testExtensions() {
    $extensions = [
        'mysqli' => extension_loaded('mysqli'),
        'mongodb' => extension_loaded('mongodb'),
        'redis' => extension_loaded('redis'),
        'json' => extension_loaded('json'),
        'openssl' => extension_loaded('openssl')
    ];
    
    $message = "PHP Version: " . phpversion() . "\n\n";
    $message .= "Required Extensions:\n";
    
    $allLoaded = true;
    foreach ($extensions as $ext => $loaded) {
        $status = $loaded ? '✓ Loaded' : '✗ NOT LOADED';
        $message .= "  $ext: $status\n";
        if (!$loaded && ($ext === 'mysqli' || $ext === 'mongodb' || $ext === 'redis')) {
            $allLoaded = false;
        }
    }
    
    if (!$allLoaded) {
        $message .= "\n⚠️  Some required extensions are not loaded.";
        $message .= "\nPlease enable them in php.ini and restart Apache.";
    }
    
    echo json_encode(['success' => $allLoaded, 'message' => $message]);
}
?>
