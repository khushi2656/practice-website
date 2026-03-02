# Quick Setup Guide

## Step-by-Step Installation

### 1. Install Required Software

#### XAMPP (Includes Apache, PHP, MySQL)
- Download: https://www.apachefriends.org/
- Install and start Apache + MySQL from Control Panel

#### MongoDB
- Download: https://www.mongodb.com/try/download/community
- Install and ensure service is running
- Windows: `net start MongoDB`
- Linux/Mac: `sudo systemctl start mongod`

#### Redis
- Windows: https://github.com/microsoftarchive/redis/releases
- Or use Docker: `docker run -d -p 6379:6379 redis`
- Start Redis server: `redis-server`

### 2. Install PHP Extensions

#### Check Current Extensions
```bash
php -m
```

#### Install MongoDB Extension
```bash
# Using PECL
pecl install mongodb

# Or download DLL from https://pecl.php.net/package/mongodb
# Place in C:\xampp\php\ext\
# Add to php.ini: extension=mongodb
```

#### Install Redis Extension
```bash
# Using PECL
pecl install redis

# Or download DLL from https://pecl.php.net/package/redis
# Place in C:\xampp\php\ext\
# Add to php.ini: extension=redis
```

#### Edit php.ini
Location: `C:\xampp\php\php.ini`

Uncomment or add:
```ini
extension=mysqli
extension=mongodb
extension=redis
```

Restart Apache after changes!

### 3. Setup MySQL Database

#### Option A: Using phpMyAdmin
1. Go to http://localhost/phpmyadmin
2. Click "SQL" tab
3. Copy and paste contents of `database_setup.sql`
4. Click "Go"

#### Option B: Using Command Line
```bash
mysql -u root -p < database_setup.sql
```

### 4. Verify Services are Running

#### MySQL
```bash
# Check if MySQL is running
mysqladmin -u root -p status
```

#### MongoDB
```bash
# Check if MongoDB is running
mongo --eval "db.version()"
# or
mongosh --eval "db.version()"
```

#### Redis
```bash
# Check if Redis is running
redis-cli ping
# Should return: PONG
```

### 5. Configure the Application

Edit `php/config.php` if needed:
```php
// MySQL Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Add your MySQL password if set
define('DB_NAME', 'user_auth_db');

// MongoDB Configuration
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', 27017);

// Redis Configuration
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
```

### 6. Deploy Application

Copy project to web root:
- **XAMPP**: `C:\xampp\htdocs\practice-website\`
- **WAMP**: `C:\wamp64\www\practice-website\`
- **Linux**: `/var/www/html/practice-website/`

### 7. Access the Application

Open browser and navigate to:
```
http://localhost/practice-website/
```

### 8. Test the Flow

1. **Register**: Click "Register" → Fill form → Submit
2. **Login**: Use registered credentials → Login
3. **Profile**: Update profile information → Save
4. **Logout**: Click Logout button

## Verification Checklist

- [ ] Apache is running (Port 80)
- [ ] MySQL is running (Port 3306)
- [ ] MongoDB is running (Port 27017)
- [ ] Redis is running (Port 6379)
- [ ] PHP extensions loaded (mysqli, mongodb, redis)
- [ ] Database `user_auth_db` created
- [ ] Can access http://localhost/practice-website/
- [ ] No errors in browser console
- [ ] Registration works
- [ ] Login works
- [ ] Profile page loads
- [ ] Profile update works
- [ ] Logout works

## Common Issues & Solutions

### Issue: "Call to undefined function MongoDB\Driver\Manager()"
**Solution**: MongoDB extension not installed
```bash
pecl install mongodb
# Add to php.ini: extension=mongodb
# Restart Apache
```

### Issue: "Class 'Redis' not found"
**Solution**: Redis extension not installed
```bash
pecl install redis
# Add to php.ini: extension=redis
# Restart Apache
```

### Issue: "Connection refused" (MySQL)
**Solution**: Start MySQL service
```bash
# Windows (XAMPP Control Panel)
# Or command line
net start mysql
```

### Issue: "Connection refused" (MongoDB)
**Solution**: Start MongoDB service
```bash
# Windows
net start MongoDB

# Linux/Mac
sudo systemctl start mongod
```

### Issue: "Connection refused" (Redis)
**Solution**: Start Redis server
```bash
redis-server
```

### Issue: Pages showing PHP code instead of executing
**Solution**: Apache not configured for PHP
- Ensure Apache is running
- Check if PHP module is loaded in httpd.conf
- Access via http://localhost not file://

### Issue: AJAX requests failing
**Solution**: Check browser console for errors
1. Open Developer Tools (F12)
2. Go to Console tab
3. Check for error messages
4. Verify Network tab shows correct requests

### Issue: Session not persisting
**Solution**: Check Redis connection
```bash
redis-cli
> KEYS session:*
> GET session:TOKEN_HERE
```

## Testing Database Connections

### Test MySQL Connection
Create `php/test_mysql.php`:
```php
<?php
$conn = new mysqli('localhost', 'root', '', 'user_auth_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "MySQL Connected successfully";
?>
```

### Test MongoDB Connection
Create `php/test_mongodb.php`:
```php
<?php
try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $manager->executeCommand('admin', $command);
    echo "MongoDB Connected successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

### Test Redis Connection
Create `php/test_redis.php`:
```php
<?php
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    echo "Redis Connected: " . $redis->ping();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

## Security Notes for Production

If deploying to production, implement:

1. **HTTPS/SSL**: Use SSL certificates
2. **Strong Passwords**: Enforce password policies
3. **Environment Variables**: Never hardcode credentials
4. **Rate Limiting**: Prevent brute force attacks
5. **CSRF Tokens**: Add CSRF protection
6. **Input Validation**: Server-side validation for all inputs
7. **Error Handling**: Generic error messages (don't expose system info)
8. **Database Backups**: Regular automated backups
9. **Firewall Rules**: Restrict database access
10. **Monitoring**: Implement logging and monitoring

## Getting Help

If you encounter issues:

1. Check all services are running
2. Check PHP error log: `C:\xampp\apache\logs\error.log`
3. Check browser console for JavaScript errors
4. Verify database credentials in `php/config.php`
5. Ensure PHP extensions are loaded: `php -m`
6. Test database connections using test scripts above

## Useful Commands

```bash
# Check PHP version
php -v

# Check loaded PHP extensions
php -m

# Check specific extension
php -m | grep mongodb
php -m | grep redis

# Restart Apache (XAMPP)
# Use XAMPP Control Panel or:
net stop Apache2.4
net start Apache2.4

# MySQL command line
mysql -u root -p

# MongoDB shell
mongo
# or newer version:
mongosh

# Redis CLI
redis-cli

# Clear Redis cache
redis-cli FLUSHALL

# View PHP configuration
php -i
# or
php -r "phpinfo();"
```

## Project Structure Overview

```
practice-website/
├── index.html              → Landing/Welcome page
├── signup.html             → User registration page
├── login.html              → User login page
├── profile.html            → User profile management
├── css/
│   └── styles.css          → Custom styling
├── js/
│   ├── signup.js           → Registration logic (jQuery AJAX)
│   ├── login.js            → Login logic (jQuery AJAX)
│   └── profile.js          → Profile logic (jQuery AJAX)
├── php/
│   ├── config.php          → Database configs + helper functions
│   ├── signup.php          → Registration API endpoint
│   ├── login.php           → Login API endpoint
│   ├── get_profile.php     → Get profile API endpoint
│   ├── update_profile.php  → Update profile API endpoint
│   └── logout.php          → Logout API endpoint
├── database_setup.sql      → MySQL database schema
├── README.md               → Full documentation
├── SETUP_GUIDE.md          → This file
└── .htaccess               → Apache configuration

Databases:
- MySQL: user_auth_db (users table)
- MongoDB: user_profiles_db (profiles collection)
- Redis: Session storage (key-value pairs)
```

## Success!

If you've completed all steps and all tests pass, your application is ready to use!

Visit http://localhost/practice-website/ and start using the system.

---

**Happy Coding!** 🚀
