# Complete MongoDB Setup Guide for Your Project

## Current Status
❌ PHP not found in PATH
❌ MongoDB not installed
❌ XAMPP not found in standard location

---

## 🎯 COMPLETE SETUP GUIDE

### Step 1: Install XAMPP (PHP + MySQL + Apache)

1. **Download XAMPP**:
   - Visit: https://www.apachefriends.org/download.html
   - Choose latest version with PHP 8.x
   - Download for Windows

2. **Install XAMPP**:
   - Run installer
   - Install to: `C:\xampp`
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   - Click Install

3. **Start Services**:
   - Open XAMPP Control Panel
   - Start Apache
   - Start MySQL

---

### Step 2: Install MongoDB

#### Option A: Local Installation

1. **Download MongoDB**:
   ```
   https://www.mongodb.com/try/download/community
   ```
   - Version: 7.0.x
   - Platform: Windows x64
   - Package: MSI

2. **Install MongoDB**:
   - Run the .msi installer
   - Choose "Complete" installation
   - ✅ Check "Install MongoDB as a Service"
   - Service name: MongoDB
   - ✅ Check "Install MongoDB Compass" (optional GUI)
   - Complete installation

3. **Verify Installation**:
   Open new PowerShell:
   ```powershell
   mongod --version
   ```

4. **Start MongoDB**:
   ```powershell
   net start MongoDB
   ```

#### Option B: MongoDB Atlas (Cloud - FREE)

1. Sign up: https://www.mongodb.com/cloud/atlas/register
2. Create FREE M0 cluster (512MB)
3. Create database user
4. Whitelist IP: 0.0.0.0/0 (for development)
5. Get connection string:
   ```
   mongodb+srv://username:password@cluster.mongodb.net/user_profiles_db
   ```

---

### Step 3: Install PHP MongoDB Extension

1. **Find your PHP version**:
   ```powershell
   C:\xampp\php\php.exe -v
   ```

2. **Download MongoDB PHP Driver**:
   - Go to: https://pecl.php.net/package/mongodb
   - Click "DLL" link
   - Download matching your PHP version:
     - Thread Safe (TS) for Apache
     - x64 for 64-bit Windows
   - Example: `php_mongodb-1.17.0-8.2-ts-x64.zip`

3. **Install the Extension**:
   - Extract `php_mongodb.dll` from zip
   - Copy to: `C:\xampp\php\ext\`
   - Edit: `C:\xampp\php\php.ini`
   - Add line: `extension=mongodb`
   - Save file
   - Restart Apache in XAMPP Control Panel

4. **Verify Extension**:
   ```powershell
   C:\xampp\php\php.exe -m | findstr mongodb
   ```
   Should output: `mongodb`

---

### Step 4: Install Redis

#### Option A: Redis for Windows

1. **Download Redis**:
   ```
   https://github.com/microsoftarchive/redis/releases
   ```
   - Download: `Redis-x64-3.0.504.msi`

2. **Install Redis**:
   - Run installer
   - ✅ Check "Add to PATH"
   - ✅ Check "Add firewall exception"

3. **Start Redis**:
   ```powershell
   redis-server
   ```

#### Option B: Redis via Docker

```powershell
docker run -d -p 6379:6379 redis
```

#### Option C: Use Redis Cloud (FREE)

1. Sign up: https://redis.com/try-free/
2. Create FREE database
3. Get connection details
4. Update config.php with host and credentials

---

### Step 5: Install PHP Redis Extension

1. **Download PHP Redis Extension**:
   - Go to: https://pecl.php.net/package/redis
   - Click "DLL"
   - Download matching your PHP version

2. **Install**:
   - Extract `php_redis.dll`
   - Copy to: `C:\xampp\php\ext\`
   - Edit `C:\xampp\php\php.ini`
   - Add line: `extension=redis`
   - Restart Apache

---

### Step 6: Setup Your Databases

#### MySQL Setup:

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import"
3. Choose file: `database_setup.sql`
4. Click "Go"

#### MongoDB Setup (Local):

MongoDB automatically creates databases when you first insert data.
No manual setup needed!

#### MongoDB Setup (Atlas):

1. In your cluster, click "Collections"
2. Create database: `user_profiles_db`
3. Create collection: `profiles`

---

### Step 7: Configure Your Application

Edit: `php/config.php`

**For Local MongoDB**:
```php
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', 27017);
define('MONGO_DB', 'user_profiles_db');
```

**For MongoDB Atlas**:
```php
define('MONGO_CONNECTION_STRING', 'mongodb+srv://username:password@cluster.mongodb.net/');
define('MONGO_DB', 'user_profiles_db');
```

---

### Step 8: Test Your Setup

1. **Copy project to XAMPP**:
   ```powershell
   Copy-Item -Recurse "c:\Users\hp\OneDrive\Desktop\practice website" "C:\xampp\htdocs\practice-website"
   ```

2. **Access test page**:
   ```
   http://localhost/practice-website/test_connections.html
   ```

3. **All should show green ✓**:
   - ✓ MySQL Connected
   - ✓ MongoDB Connected
   - ✓ Redis Connected
   - ✓ PHP Extensions Loaded

---

## 🔍 Troubleshooting

### MongoDB won't start?

**Check service**:
```powershell
Get-Service MongoDB
```

**Start manually**:
```powershell
net start MongoDB
```

**Or run manually**:
```powershell
mongod --dbpath "C:\data\db"
```

### PHP extensions not loading?

1. Check php.ini location:
   ```powershell
   C:\xampp\php\php.exe --ini
   ```

2. Verify extensions exist:
   ```powershell
   dir C:\xampp\php\ext\php_mongodb.dll
   dir C:\xampp\php\ext\php_redis.dll
   ```

3. Check php.ini contains:
   ```ini
   extension=mysqli
   extension=mongodb
   extension=redis
   ```

4. Restart Apache

### Redis connection refused?

**Start Redis**:
```powershell
redis-server
```

**Test connection**:
```powershell
redis-cli ping
```
Should return: `PONG`

---

## 📋 Quick Installation Checklist

- [ ] XAMPP installed (Apache + PHP + MySQL)
- [ ] Apache & MySQL running in XAMPP
- [ ] MongoDB installed and running
- [ ] Redis installed and running
- [ ] PHP MongoDB extension installed
- [ ] PHP Redis extension installed
- [ ] Extensions enabled in php.ini
- [ ] Apache restarted after php.ini changes
- [ ] MySQL database created (database_setup.sql)
- [ ] Test connections page shows all green

---

## 🌐 Download Links Summary

| Software | Link |
|----------|------|
| XAMPP | https://www.apachefriends.org/download.html |
| MongoDB | https://www.mongodb.com/try/download/community |
| MongoDB Atlas | https://www.mongodb.com/cloud/atlas/register |
| Redis Windows | https://github.com/microsoftarchive/redis/releases |
| PHP MongoDB Driver | https://pecl.php.net/package/mongodb |
| PHP Redis Driver | https://pecl.php.net/package/redis |

---

## 🆘 Need Help?

1. Run diagnostic: `.\check_mongodb.bat`
2. Check Apache error log: `C:\xampp\apache\logs\error.log`
3. Check PHP info: Create file `info.php` with `<?php phpinfo(); ?>`
4. Visit: http://localhost/info.php

---

**After completing all steps, your application will be fully functional!**
