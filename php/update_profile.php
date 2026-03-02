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

// Get profile data
$firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
$lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
$age = isset($_POST['age']) ? trim($_POST['age']) : '';
$dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
$contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$state = isset($_POST['state']) ? trim($_POST['state']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';

try {
    // Get MongoDB connection
    $mongoClient = getMongoDBConnection();
    if (!$mongoClient) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Prepare profile document
    $profileDoc = [
        'userId' => $userId,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'age' => $age,
        'dob' => $dob,
        'contact' => $contact,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'country' => $country,
        'updatedAt' => new MongoDB\BSON\UTCDateTime()
    ];
    
    // Check if profile exists
    $filter = ['userId' => $userId];
    $options = ['limit' => 1];
    $query = new MongoDB\Driver\Query($filter, $options);
    $namespace = MONGO_DB . '.profiles';
    $cursor = $mongoClient->executeQuery($namespace, $query);
    
    $profileExists = false;
    foreach ($cursor as $document) {
        $profileExists = true;
        break;
    }
    
    // Update or insert profile
    if ($profileExists) {
        // Update existing profile
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update($filter, ['$set' => $profileDoc], ['multi' => false, 'upsert' => false]);
        $result = $mongoClient->executeBulkWrite($namespace, $bulk);
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        // Insert new profile
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($profileDoc);
        $result = $mongoClient->executeBulkWrite($namespace, $bulk);
        
        echo json_encode(['success' => true, 'message' => 'Profile created successfully']);
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating profile']);
}
?>
