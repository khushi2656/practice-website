<?php
require_once 'config.php';

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Get MySQL connection
$conn = getMySQLConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Get user details using prepared statement
    $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // Generate session token
    $sessionToken = generateSessionToken();
    
    // Store session in Redis
    $redis = getRedisConnection();
    if ($redis) {
        $redis->setex('session:' . $sessionToken, 3600, $user['id']); // Session expires in 1 hour
    } else {
        echo json_encode(['success' => false, 'message' => 'Session creation failed']);
        exit;
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'sessionToken' => $sessionToken,
        'userId' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email']
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during login']);
}
?>
