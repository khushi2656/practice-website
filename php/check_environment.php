<?php
require_once 'config.php';

// Check which environment we're in
$envInfo = getEnvironmentInfo();

// Test all connections
$connections = [
    'mysql' => false,
    'mongodb' => false,
    'redis' => false
];

$errors = [];

// Test MySQL
try {
    $mysql = getMySQLConnection();
    $connections['mysql'] = true;
    $result = $mysql->query("SELECT VERSION() as version");
    $row = $result->fetch_assoc();
    $mysqlVersion = $row['version'];
    $mysql->close();
} catch (Exception $e) {
    $errors['mysql'] = $e->getMessage();
    $mysqlVersion = 'N/A';
}

// Test MongoDB
try {
    $mongo = getMongoDBConnection();
    $connections['mongodb'] = true;
    $command = new MongoDB\Driver\Command(['buildInfo' => 1]);
    $cursor = $mongo->executeCommand('admin', $command);
    $info = current($cursor->toArray());
    $mongoVersion = $info->version;
} catch (Exception $e) {
    $errors['mongodb'] = $e->getMessage();
    $mongoVersion = 'N/A';
}

// Test Redis
try {
    $redis = getRedisConnection();
    $connections['redis'] = true;
    $redisInfo = $redis->info();
    $redisVersion = $redisInfo['redis_version'] ?? 'Unknown';
    $redis->close();
} catch (Exception $e) {
    $errors['redis'] = $e->getMessage();
    $redisVersion = 'N/A';
}

// Prepare response
$response = [
    'success' => true,
    'environment' => $envInfo,
    'connections' => $connections,
    'versions' => [
        'mysql' => $mysqlVersion,
        'mongodb' => $mongoVersion,
        'redis' => $redisVersion
    ],
    'errors' => $errors,
    'server_info' => [
        'php_version' => phpversion(),
        'host' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ],
    'all_connected' => !in_array(false, $connections)
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
