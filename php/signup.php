<?php
require_once 'config.php';

// Get POST data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate input
if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Get MySQL connection
$conn = getMySQLConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Check if email already exists using prepared statement
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    $stmt->close();
    
    // Check if username already exists using prepared statement
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Username already taken']);
        exit;
    }
    $stmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user using prepared statement
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'message' => 'Registration successful! Please login.']);
    } else {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during registration']);
}
?>
