# 🚀 Cloud Deployment Guide
### Run Your App on Localhost AND Online

This guide helps you deploy your application to work **both locally and online** using cloud databases.

---

## 📋 **What You'll Use**

| Service | Purpose | Cost |
|---------|---------|------|
| **MongoDB Atlas** | Cloud MongoDB database | FREE (512MB) |
| **Redis Cloud** | Cloud Redis cache | FREE (30MB) |
| **Railway** | Host your PHP application | FREE ($5 credit) |
| **Localhost** | Local testing with XAMPP | FREE |

---

## 🎯 **PART 1: MongoDB Atlas Setup** (Cloud MongoDB)

### Step 1: Create MongoDB Atlas Account
1. Go to: https://www.mongodb.com/cloud/atlas/register
2. Sign up with Google/GitHub or email
3. Choose **FREE M0 Cluster** (512MB)
4. Select provider: **AWS**
5. Region: Choose closest to you (e.g., Mumbai, Singapore)
6. Cluster Name: `user-profiles-cluster`
7. Click **Create Cluster** (takes 3-5 minutes)

### Step 2: Create Database User
1. Go to **Database Access** (left menu)
2. Click **Add New Database User**
3. Authentication: **Password**
4. Username: `admin`
5. Password: `Admin@123` (or create strong password)
6. Database User Privileges: **Read and write to any database**
7. Click **Add User**

### Step 3: Whitelist IP Address
1. Go to **Network Access** (left menu)
2. Click **Add IP Address**
3. Click **Allow Access from Anywhere** (0.0.0.0/0)
4. Click **Confirm**

⚠️ **For production, use specific IP addresses only!**

### Step 4: Get Connection String
1. Go to **Database** → Click **Connect** on your cluster
2. Choose **Connect your application**
3. Driver: **PHP**, Version: **1.13 or later**
4. Copy the connection string:
```
mongodb+srv://admin:<password>@user-profiles-cluster.xxxxx.mongodb.net/?retryWrites=true&w=majority
```
5. Replace `<password>` with your actual password: `Admin@123`
6. Save this - you'll need it!

### Step 5: Create Database and Collection
1. Click **Browse Collections**
2. Click **Add My Own Data**
3. Database name: `user_profiles_db`
4. Collection name: `profiles`
5. Click **Create**

✅ **MongoDB Atlas is ready!**

---

## 🎯 **PART 2: Redis Cloud Setup**

### Step 1: Create Redis Cloud Account
1. Go to: https://redis.com/try-free/
2. Click **Get Started Free**
3. Sign up with Google/GitHub or email
4. Verify your email

### Step 2: Create Redis Database
1. Click **Create database**
2. Plan: **Free** (30MB)
3. Cloud: **AWS**
4. Region: Choose closest to you
5. Database name: `user-sessions`
6. Click **Activate database**

### Step 3: Get Connection Details
1. After creation, click on your database
2. Copy these details:
   - **Endpoint**: `redis-12345.c123.us-east-1-1.ec2.cloud.redislabs.com:12345`
   - **Default user password**: `xxxxxxxxxxxxxxxx`

✅ **Redis Cloud is ready!**

---

## 🎯 **PART 3: Configure Your Application**

### Create Two Config Files

#### **Option A: Automatic Switch (Recommended)**

**File: `php/config.php`** (Update existing file)

```php
<?php
// Auto-detect environment
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']) 
           || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false;

// ============ MYSQL CONFIGURATION ============
if ($isLocal) {
    // Local XAMPP MySQL
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'user_auth_db');
} else {
    // Railway MySQL (you'll get this later)
    define('DB_HOST', 'containers-us-west-123.railway.app');
    define('DB_USER', 'root');
    define('DB_PASS', 'your-railway-mysql-password');
    define('DB_NAME', 'railway');
    define('DB_PORT', '6543');
}

// ============ MONGODB CONFIGURATION ============
if ($isLocal) {
    // Option 1: Local MongoDB (if installed)
    define('MONGO_HOST', 'localhost');
    define('MONGO_PORT', '27017');
    define('MONGO_DB', 'user_profiles_db');
    define('MONGO_URI', 'mongodb://localhost:27017');
    
    // Option 2: Use Atlas even locally (comment lines above, uncomment below)
    // define('MONGO_URI', 'mongodb+srv://admin:Admin@123@user-profiles-cluster.xxxxx.mongodb.net/?retryWrites=true&w=majority');
    // define('MONGO_DB', 'user_profiles_db');
} else {
    // MongoDB Atlas (Cloud)
    define('MONGO_URI', 'mongodb+srv://admin:Admin@123@user-profiles-cluster.xxxxx.mongodb.net/?retryWrites=true&w=majority');
    define('MONGO_DB', 'user_profiles_db');
}

// ============ REDIS CONFIGURATION ============
if ($isLocal) {
    // Option 1: Local Redis (if installed)
    define('REDIS_HOST', '127.0.0.1');
    define('REDIS_PORT', 6379);
    define('REDIS_PASSWORD', null);
    
    // Option 2: Use Redis Cloud even locally (comment lines above, uncomment below)
    // define('REDIS_HOST', 'redis-12345.c123.us-east-1-1.ec2.cloud.redislabs.com');
    // define('REDIS_PORT', 12345);
    // define('REDIS_PASSWORD', 'your-redis-cloud-password');
} else {
    // Redis Cloud
    define('REDIS_HOST', 'redis-12345.c123.us-east-1-1.ec2.cloud.redislabs.com');
    define('REDIS_PORT', 12345);
    define('REDIS_PASSWORD', 'your-redis-cloud-password');
}

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// ============ CONNECTION FUNCTIONS ============

function getMySQLConnection() {
    $port = defined('DB_PORT') ? DB_PORT : 3306;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

function getMongoDBConnection() {
    try {
        $manager = new MongoDB\Driver\Manager(MONGO_URI);
        
        // Test connection
        $command = new MongoDB\Driver\Command(['ping' => 1]);
        $manager->executeCommand('admin', $command);
        
        return $manager;
    } catch (Exception $e) {
        throw new Exception("MongoDB Connection failed: " . $e->getMessage());
    }
}

function getRedisConnection() {
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        
        if (REDIS_PASSWORD) {
            $redis->auth(REDIS_PASSWORD);
        }
        
        // Test connection
        $redis->ping();
        
        return $redis;
    } catch (Exception $e) {
        throw new Exception("Redis Connection failed: " . $e->getMessage());
    }
}

function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

function validateSession($token, $userId) {
    try {
        $redis = getRedisConnection();
        $storedUserId = $redis->get("session:$token");
        
        if ($storedUserId && $storedUserId == $userId) {
            // Extend session
            $redis->expire("session:$token", SESSION_TIMEOUT);
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        return false;
    }
}
?>
```

#### **Option B: Easy Switch (Manual)**

Create separate config files:

**File: `php/config.local.php`** (for localhost)
```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_auth_db');

define('MONGO_URI', 'mongodb://localhost:27017');
define('MONGO_DB', 'user_profiles_db');

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASSWORD', null);

define('SESSION_TIMEOUT', 3600);
// ... (add all connection functions)
?>
```

**File: `php/config.cloud.php`** (for online)
```php
<?php
define('DB_HOST', 'containers-us-west-123.railway.app');
define('DB_USER', 'root');
define('DB_PASS', 'your-railway-password');
define('DB_NAME', 'railway');
define('DB_PORT', '6543');

define('MONGO_URI', 'mongodb+srv://admin:Admin@123@user-profiles-cluster.xxxxx.mongodb.net/');
define('MONGO_DB', 'user_profiles_db');

define('REDIS_HOST', 'redis-12345.c123.us-east-1-1.ec2.cloud.redislabs.com');
define('REDIS_PORT', 12345);
define('REDIS_PASSWORD', 'your-redis-password');

define('SESSION_TIMEOUT', 3600);
// ... (add all connection functions)
?>
```

Then rename `config.php` → `config.local.php` or `config.cloud.php` based on where you're running.

---

## 🎯 **PART 4: Railway Deployment**

### Step 1: Prepare Your Project
1. Make sure all files are in your folder
2. Create `.htaccess` file (already exists)
3. Update `php/config.php` with cloud credentials

### Step 2: Create Railway Account
1. Go to: https://railway.app/
2. Click **Login with GitHub**
3. Authorize Railway

### Step 3: Create New Project
1. Click **New Project**
2. Choose **Deploy from GitHub repo**
3. Select your repository: `khushi2656/practice-website`
4. Railway will auto-detect it's a PHP project

### Step 4: Add MySQL Database
1. In your project dashboard, click **New**
2. Select **Database** → **MySQL**
3. Railway creates a MySQL instance
4. Copy connection details:
   - Host: `containers-us-west-123.railway.app`
   - Port: `6543`
   - User: `root`
   - Password: `xxxxxxxxxx`
   - Database: `railway`

### Step 5: Configure Environment Variables
1. Click on your **web service**
2. Go to **Variables** tab
3. Add these variables:
```
DB_HOST=containers-us-west-123.railway.app
DB_PORT=6543
DB_USER=root
DB_PASS=your-mysql-password
DB_NAME=railway

MONGO_URI=mongodb+srv://admin:Admin@123@user-profiles-cluster.xxxxx.mongodb.net/
MONGO_DB=user_profiles_db

REDIS_HOST=redis-12345.c123.us-east-1-1.ec2.cloud.redislabs.com
REDIS_PORT=12345
REDIS_PASSWORD=your-redis-password
```

### Step 6: Setup MySQL Database
1. Click on **MySQL** service
2. Go to **Connect** tab
3. Click **MySQL Command**
4. Copy the command and run in your local terminal
5. Paste your database_setup.sql content:
```sql
CREATE DATABASE IF NOT EXISTS railway;
USE railway;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Step 7: Deploy
1. Railway auto-deploys on every GitHub push
2. Wait for deployment to complete (2-5 minutes)
3. Click **Generate Domain** to get your URL
4. Your app is live at: `https://your-app.up.railway.app`

---

## 🎯 **PART 5: Testing**

### Test Locally (Localhost)
1. Start XAMPP (Apache + MySQL)
2. Open: `http://localhost/practice-website/`
3. Test: Register → Login → Profile → Logout
4. Check: Uses local MySQL OR cloud databases (based on config)

### Test Online (Railway)
1. Open: `https://your-app.up.railway.app/`
2. Test: Register → Login → Profile → Logout
3. Check: Uses cloud databases (Atlas + Redis Cloud)

### Verify Databases
**MongoDB Atlas:**
1. Go to Atlas dashboard → Browse Collections
2. Check `user_profiles_db.profiles` for profile data

**Redis Cloud:**
1. Use Redis Insight or CLI
2. Run `KEYS *` to see session tokens

**Railway MySQL:**
1. Go to Railway MySQL service → Data tab
2. Check `users` table for registered users

---

## 🔧 **Configuration Summary**

### Recommended Setup

| Environment | MySQL | MongoDB | Redis |
|-------------|-------|---------|-------|
| **Localhost** | Local (XAMPP) | Atlas (Cloud) | Cloud |
| **Online** | Railway | Atlas (Cloud) | Cloud |

**Why?**
- ✅ MongoDB Atlas: FREE, always available, no local install
- ✅ Redis Cloud: FREE, fast, no local install
- ✅ MySQL Local: Comes with XAMPP, fast for development
- ✅ MySQL Railway: Automatic backup, production-ready

### Environment Detection (Automatic)
Your `config.php` automatically detects:
- **Localhost**: Uses local MySQL + cloud Atlas + cloud Redis
- **Online**: Uses Railway MySQL + cloud Atlas + cloud Redis

---

## 📱 **Quick Commands**

### Push Changes to Railway
```bash
git add .
git commit -m "Update application"
git push origin main
```
Railway auto-deploys in 2-3 minutes!

### Check Railway Logs
1. Go to Railway dashboard
2. Click your service
3. Click **Deployments** → View logs

### Rollback Deployment
1. Go to **Deployments**
2. Click previous deployment
3. Click **Redeploy**

---

## 🛡️ **Security Checklist**

Before going live:
- [ ] Change all default passwords
- [ ] Update MongoDB IP whitelist to Railway's IP only
- [ ] Use environment variables for all credentials
- [ ] Enable HTTPS (Railway provides free SSL)
- [ ] Add CORS headers if needed
- [ ] Set proper session timeout
- [ ] Add rate limiting to APIs
- [ ] Validate all user inputs
- [ ] Use prepared statements (already done ✅)
- [ ] Hash passwords (already done ✅)

---

## 🎉 **You're Done!**

Your app now runs:
- 🏠 **Locally**: `http://localhost/practice-website/`
- 🌍 **Online**: `https://your-app.up.railway.app/`

Both use the SAME cloud databases for consistency!

### Need Help?
- MongoDB Atlas: https://docs.atlas.mongodb.com/
- Redis Cloud: https://docs.redis.com/latest/rc/
- Railway: https://docs.railway.app/

---

## 📌 **Quick Reference**

**MongoDB Atlas Connection:**
```
mongodb+srv://admin:Admin@123@cluster.xxxxx.mongodb.net/
```

**Redis Cloud Connection:**
```
Host: redis-xxxxx.cloud.redislabs.com
Port: xxxxx
Password: xxxxx
```

**Railway URL:**
```
https://your-app.up.railway.app
```

**Test Everything:**
```
http://localhost/practice-website/interactive_api_test.html
https://your-app.up.railway.app/interactive_api_test.html
```

Good luck! 🚀
