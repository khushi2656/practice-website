<?php
require_once 'config.php';

// Get POST data
$sessionToken = isset($_POST['sessionToken']) ? $_POST['sessionToken'] : '';
$userId = isset($_POST['userId']) ? $_POST['userId'] : '';

// Validate session
if (!validateSession($sessionToken, $userId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid session']);
    exit;
}

try {
    // Get MongoDB connection
    $mongoClient = getMongoDBConnection();
    if (!$mongoClient) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Query MongoDB for user profile
    $filter = ['userId' => $userId];
    $options = ['limit' => 1];
    
    $query = new MongoDB\Driver\Query($filter, $options);
    $namespace = MONGO_DB . '.profiles';
    $cursor = $mongoClient->executeQuery($namespace, $query);
    
    $profile = null;
    foreach ($cursor as $document) {
        $profile = [
            'firstName' => isset($document->firstName) ? $document->firstName : '',
            'lastName' => isset($document->lastName) ? $document->lastName : '',
            'age' => isset($document->age) ? $document->age : '',
            'dob' => isset($document->dob) ? $document->dob : '',
            'contact' => isset($document->contact) ? $document->contact : '',
            'address' => isset($document->address) ? $document->address : '',
            'city' => isset($document->city) ? $document->city : '',
            'state' => isset($document->state) ? $document->state : '',
            'country' => isset($document->country) ? $document->country : ''
        ];
        break;
    }
    
    echo json_encode([
        'success' => true,
        'profile' => $profile
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error retrieving profile']);
}
?>
