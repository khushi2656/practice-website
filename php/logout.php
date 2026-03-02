<?php
require_once 'config.php';

// Get POST data
$sessionToken = isset($_POST['sessionToken']) ? $_POST['sessionToken'] : '';
$userId = isset($_POST['userId']) ? $_POST['userId'] : '';

try {
    // Get Redis connection
    $redis = getRedisConnection();
    if ($redis) {
        // Delete session from Redis
        $redis->del('session:' . $sessionToken);
    }
    
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}
?>
