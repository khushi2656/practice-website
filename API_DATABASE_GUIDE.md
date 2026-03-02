# API Database Connection Documentation

## 🔌 How APIs Connect to Databases

This document explains how each API endpoint connects to and interacts with the three databases: MySQL, MongoDB, and Redis.

---

## 📋 Table of Contents

1. [Database Architecture](#database-architecture)
2. [API Endpoints Overview](#api-endpoints-overview)
3. [Detailed API Documentation](#detailed-api-documentation)
4. [Testing APIs](#testing-apis)
5. [Code Examples](#code-examples)
6. [Troubleshooting](#troubleshooting)

---

## 🗄️ Database Architecture

### MySQL (user_auth_db)
- **Purpose**: Store user authentication credentials
- **Connection**: Via mysqli with prepared statements
- **Data**: username, email, hashed password

### MongoDB (user_profiles_db)
- **Purpose**: Store flexible user profile data
- **Connection**: Via MongoDB\Driver\Manager
- **Data**: Personal details (name, age, DOB, contact, address)

### Redis
- **Purpose**: Session management
- **Connection**: Via Redis PHP extension
- **Data**: Session tokens with 1-hour TTL

---

## 📡 API Endpoints Overview

| Endpoint | Method | Databases Used | Purpose |
|----------|--------|----------------|---------|
| `signup.php` | POST | MySQL | Register new user |
| `login.php` | POST | MySQL + Redis | Authenticate & create session |
| `get_profile.php` | POST | Redis + MongoDB | Retrieve user profile |
| `update_profile.php` | POST | Redis + MongoDB | Update user profile |
| `logout.php` | POST | Redis | Destroy session |
| `api_test.php` | GET | All 3 databases | Test connectivity |

---

## 📝 Detailed API Documentation

### 1. Signup API (`signup.php`)

**Endpoint**: `POST /php/signup.php`

**Database Connection**: MySQL

**How it works**:
```php
// 1. Get database connection
$conn = getMySQLConnection();

// 2. Check if email exists (Prepared Statement)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

// 3. Insert new user (Prepared Statement)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $username, $email, $hashedPassword);
$stmt->execute();
```

**Request**:
```javascript
$.ajax({
    url: 'php/signup.php',
    type: 'POST',
    data: {
        username: 'johndoe',
        email: 'john@example.com',
        password: 'password123'
    },
    success: function(response) {
        console.log(response);
    }
});
```

**Response**:
```json
{
    "success": true,
    "message": "Registration successful! Please login."
}
```

**Database Flow**:
```
Client Request
    ↓
signup.php
    ↓
getMySQLConnection() → MySQL Connection
    ↓
Check if email exists (SELECT with prepared statement)
    ↓
Hash password (bcrypt)
    ↓
Insert user (INSERT with prepared statement)
    ↓
Return JSON response
```

---

### 2. Login API (`login.php`)

**Endpoint**: `POST /php/login.php`

**Database Connections**: MySQL + Redis

**How it works**:
```php
// 1. Connect to MySQL
$conn = getMySQLConnection();

// 2. Get user (Prepared Statement)
$stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 3. Verify password
if (password_verify($password, $user['password'])) {
    
    // 4. Generate session token
    $sessionToken = generateSessionToken();
    
    // 5. Store in Redis
    $redis = getRedisConnection();
    $redis->setex('session:' . $sessionToken, 3600, $user['id']); // 1 hour TTL
    
    // 6. Return session data
    return [
        'success' => true,
        'sessionToken' => $sessionToken,
        'userId' => $user['id'],
        'username' => $user['username']
    ];
}
```

**Request**:
```javascript
$.ajax({
    url: 'php/login.php',
    type: 'POST',
    data: {
        email: 'john@example.com',
        password: 'password123'
    },
    success: function(response) {
        // Store session in localStorage
        localStorage.setItem('sessionToken', response.sessionToken);
        localStorage.setItem('userId', response.userId);
    }
});
```

**Response**:
```json
{
    "success": true,
    "message": "Login successful",
    "sessionToken": "a1b2c3d4e5f6...",
    "userId": "1",
    "username": "johndoe",
    "email": "john@example.com"
}
```

**Database Flow**:
```
Client Request
    ↓
login.php
    ↓
getMySQLConnection() → MySQL
    │
    ├─→ Query user by email (SELECT with prepared statement)
    └─→ Verify password (password_verify)
    ↓
Generate session token (random_bytes)
    ↓
getRedisConnection() → Redis
    │
    └─→ Store session: SET session:TOKEN userId EX 3600
    ↓
Return session data to client
```

---

### 3. Get Profile API (`get_profile.php`)

**Endpoint**: `POST /php/get_profile.php`

**Database Connections**: Redis + MongoDB

**How it works**:
```php
// 1. Validate session in Redis
if (!validateSession($sessionToken, $userId)) {
    return ['success' => false, 'message' => 'Invalid session'];
}

// 2. Connect to MongoDB
$mongoClient = getMongoDBConnection();

// 3. Query profile
$filter = ['userId' => $userId];
$query = new MongoDB\Driver\Query($filter, ['limit' => 1]);
$namespace = MONGO_DB . '.profiles';
$cursor = $mongoClient->executeQuery($namespace, $query);

// 4. Return profile data
foreach ($cursor as $document) {
    return [
        'success' => true,
        'profile' => [
            'firstName' => $document->firstName,
            'lastName' => $document->lastName,
            // ... other fields
        ]
    ];
}
```

**Request**:
```javascript
$.ajax({
    url: 'php/get_profile.php',
    type: 'POST',
    data: {
        sessionToken: localStorage.getItem('sessionToken'),
        userId: localStorage.getItem('userId')
    },
    success: function(response) {
        // Populate form with profile data
        $('#firstName').val(response.profile.firstName);
        $('#lastName').val(response.profile.lastName);
    }
});
```

**Response**:
```json
{
    "success": true,
    "profile": {
        "firstName": "John",
        "lastName": "Doe",
        "age": "25",
        "dob": "1999-01-15",
        "contact": "+1234567890",
        "address": "123 Main St",
        "city": "New York",
        "state": "NY",
        "country": "USA"
    }
}
```

**Database Flow**:
```
Client Request (with sessionToken + userId)
    ↓
get_profile.php
    ↓
getRedisConnection() → Redis
    │
    └─→ Validate session: GET session:TOKEN
    └─→ Extend TTL: EXPIRE session:TOKEN 3600
    ↓
getMongoDBConnection() → MongoDB
    │
    └─→ Query: db.profiles.findOne({userId: "1"})
    ↓
Return profile data to client
```

---

### 4. Update Profile API (`update_profile.php`)

**Endpoint**: `POST /php/update_profile.php`

**Database Connections**: Redis + MongoDB

**How it works**:
```php
// 1. Validate session in Redis
if (!validateSession($sessionToken, $userId)) {
    return ['success' => false, 'message' => 'Invalid session'];
}

// 2. Connect to MongoDB
$mongoClient = getMongoDBConnection();

// 3. Prepare profile document
$profileDoc = [
    'userId' => $userId,
    'firstName' => $firstName,
    'lastName' => $lastName,
    'age' => $age,
    // ... other fields
    'updatedAt' => new MongoDB\BSON\UTCDateTime()
];

// 4. Update or insert profile
$bulk = new MongoDB\Driver\BulkWrite;
$bulk->update(
    ['userId' => $userId],
    ['$set' => $profileDoc],
    ['multi' => false, 'upsert' => true]
);
$result = $mongoClient->executeBulkWrite($namespace, $bulk);
```

**Request**:
```javascript
$.ajax({
    url: 'php/update_profile.php',
    type: 'POST',
    data: {
        sessionToken: localStorage.getItem('sessionToken'),
        userId: localStorage.getItem('userId'),
        firstName: 'John',
        lastName: 'Doe',
        age: '25',
        // ... other fields
    },
    success: function(response) {
        alert(response.message);
    }
});
```

**Response**:
```json
{
    "success": true,
    "message": "Profile updated successfully"
}
```

**Database Flow**:
```
Client Request (with session + profile data)
    ↓
update_profile.php
    ↓
getRedisConnection() → Redis
    │
    └─→ Validate session: GET session:TOKEN
    ↓
getMongoDBConnection() → MongoDB
    │
    ├─→ Check if profile exists
    │
    ├─→ If exists: UPDATE (bulk update operation)
    │   db.profiles.update({userId: "1"}, {$set: {...}})
    │
    └─→ If not exists: INSERT (upsert)
        db.profiles.insert({userId: "1", ...})
    ↓
Return success message
```

---

### 5. Logout API (`logout.php`)

**Endpoint**: `POST /php/logout.php`

**Database Connection**: Redis

**How it works**:
```php
// 1. Connect to Redis
$redis = getRedisConnection();

// 2. Delete session token
$redis->del('session:' . $sessionToken);

// 3. Return success
return ['success' => true, 'message' => 'Logged out successfully'];
```

**Request**:
```javascript
$.ajax({
    url: 'php/logout.php',
    type: 'POST',
    data: {
        sessionToken: localStorage.getItem('sessionToken'),
        userId: localStorage.getItem('userId')
    },
    success: function(response) {
        // Clear local storage
        localStorage.clear();
        // Redirect to login
        window.location.href = 'login.html';
    }
});
```

**Database Flow**:
```
Client Request
    ↓
logout.php
    ↓
getRedisConnection() → Redis
    │
    └─→ Delete session: DEL session:TOKEN
    ↓
Clear localStorage on client
    ↓
Redirect to login page
```

---

### 6. API Test Endpoint (`api_test.php`)

**Endpoint**: `GET /php/api_test.php?action=all`

**Database Connections**: All (MySQL + MongoDB + Redis)

**Parameters**:
- `action`: `all`, `mysql`, `mongodb`, or `redis`

**How it works**:
```php
// Test all database connections
$response = [
    'success' => false,
    'results' => []
];

// Test MySQL
$conn = getMySQLConnection();
if ($conn) {
    $response['results']['mysql'] = [
        'connected' => true,
        'version' => /* query version */,
        'total_users' => /* count users */
    ];
}

// Test MongoDB
$mongoClient = getMongoDBConnection();
if ($mongoClient) {
    $response['results']['mongodb'] = [
        'connected' => true,
        'version' => /* get version */,
        'total_profiles' => /* count profiles */
    ];
}

// Test Redis
$redis = getRedisConnection();
if ($redis) {
    $response['results']['redis'] = [
        'connected' => true,
        'active_sessions' => /* count sessions */
    ];
}
```

**Usage**:
```bash
# Test all databases
curl http://localhost/practice-website/php/api_test.php?action=all

# Test MySQL only
curl http://localhost/practice-website/php/api_test.php?action=mysql

# Test MongoDB only
curl http://localhost/practice-website/php/api_test.php?action=mongodb

# Test Redis only
curl http://localhost/practice-website/php/api_test.php?action=redis
```

---

## 🧪 Testing APIs

### Option 1: Web Interface
Visit: `http://localhost/practice-website/api_test.html`

### Option 2: Browser Console
```javascript
// Test signup
$.post('php/signup.php', {
    username: 'testuser',
    email: 'test@example.com',
    password: 'password123'
}, function(response) {
    console.log(response);
});

// Test login
$.post('php/login.php', {
    email: 'test@example.com',
    password: 'password123'
}, function(response) {
    console.log(response);
    localStorage.setItem('sessionToken', response.sessionToken);
    localStorage.setItem('userId', response.userId);
});

// Test get profile
$.post('php/get_profile.php', {
    sessionToken: localStorage.getItem('sessionToken'),
    userId: localStorage.getItem('userId')
}, function(response) {
    console.log(response);
});
```

### Option 3: Command Line with curl
```bash
# Test signup
curl -X POST http://localhost/practice-website/php/signup.php \
  -d "username=testuser&email=test@example.com&password=password123"

# Test login
curl -X POST http://localhost/practice-website/php/login.php \
  -d "email=test@example.com&password=password123"

# Test API connections
curl http://localhost/practice-website/php/api_test.php?action=all
```

---

## 💻 Code Examples

### Example 1: Complete User Registration Flow

```javascript
// Frontend (JavaScript/jQuery)
function registerUser(username, email, password) {
    $.ajax({
        url: 'php/signup.php',
        type: 'POST',
        dataType: 'json',
        data: {
            username: username,
            email: email,
            password: password
        },
        success: function(response) {
            if (response.success) {
                alert('Registration successful!');
                window.location.href = 'login.html';
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Request failed: ' + error);
        }
    });
}
```

```php
// Backend (PHP - signup.php)
require_once 'config.php';

// Get POST data
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];

// Connect to MySQL
$conn = getMySQLConnection();

// Check if email exists (Prepared Statement)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

// Hash password and insert
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}
```

### Example 2: Session Validation with Redis

```php
// Function in config.php
function validateSession($sessionToken, $userId) {
    // Connect to Redis
    $redis = getRedisConnection();
    if (!$redis) {
        return false;
    }
    
    // Get stored userId for this session
    $storedUserId = $redis->get('session:' . $sessionToken);
    
    // Validate
    if ($storedUserId && $storedUserId == $userId) {
        // Extend session expiry (1 hour)
        $redis->expire('session:' . $sessionToken, 3600);
        return true;
    }
    
    return false;
}

// Usage in any protected API endpoint
if (!validateSession($_POST['sessionToken'], $_POST['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid session']);
    exit;
}
// Continue with authorized actions...
```

### Example 3: MongoDB Profile Operations

```php
// Update profile with upsert
$mongoClient = getMongoDBConnection();

$profileDoc = [
    'userId' => $userId,
    'firstName' => $firstName,
    'lastName' => $lastName,
    'updatedAt' => new MongoDB\BSON\UTCDateTime()
];

$bulk = new MongoDB\Driver\BulkWrite;
$bulk->update(
    ['userId' => $userId],           // Filter
    ['$set' => $profileDoc],         // Update
    ['multi' => false, 'upsert' => true]  // Options: upsert creates if not exists
);

$namespace = MONGO_DB . '.profiles';
$result = $mongoClient->executeBulkWrite($namespace, $bulk);

echo json_encode(['success' => true, 'message' => 'Profile updated']);
```

---

## 🔧 Troubleshooting

### MySQL Connection Issues

**Problem**: `Failed to connect to MySQL`
```
Solution:
1. Check XAMPP - MySQL running?
2. Verify credentials in php/config.php
3. Test: C:\xampp\mysql\bin\mysql.exe -u root -p
```

### MongoDB Connection Issues

**Problem**: `MongoDB extension not loaded`
```
Solution:
1. Check php.ini: extension=mongodb
2. Verify DLL exists: C:\xampp\php\ext\php_mongodb.dll
3. Restart Apache
4. Test: php -m | findstr mongodb
```

### Redis Connection Issues

**Problem**: `Failed to connect to Redis`
```
Solution:
1. Start Redis: redis-server
2. Test connection: redis-cli ping (should return PONG)
3. Check php.ini: extension=redis
4. Verify Redis is listening on port 6379
```

### API Returns Empty Response

**Problem**: Blank response from API
```
Solution:
1. Check Apache error log: C:\xampp\apache\logs\error.log
2. Enable PHP errors:
   - Add to top of PHP file: error_reporting(E_ALL);
   - Check php.ini: display_errors = On
3. Check browser console for CORS errors
```

### Session not persisting

**Problem**: User logged out immediately
```
Solution:
1. Verify Redis is running
2. Check if session is created:
   redis-cli
   > KEYS session:*
   > GET session:YOUR_TOKEN
3. Verify sessionToken in localStorage (F12 → Application tab)
```

---

## 📚 Additional Resources

- **MySQL Prepared Statements**: https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php
- **MongoDB PHP Driver**: https://www.php.net/manual/en/set.mongodb.php
- **Redis PHP Extension**: https://github.com/phpredis/phpredis

---

## 🎯 Summary

Your application uses a **3-tier database architecture**:

1. **MySQL**: Authentication (secure, ACID-compliant)
2. **MongoDB**: User profiles (flexible, schema-less)
3. **Redis**: Sessions (fast, auto-expiring)

All API endpoints connect through the `config.php` file which provides:
- `getMySQLConnection()` → MySQL database
- `getMongoDBConnection()` → MongoDB database
- `getRedisConnection()` → Redis cache
- `validateSession()` → Session validation

**Test your APIs**: Open `api_test.html` in your browser!
