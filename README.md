# User Registration & Login System

A complete user authentication and profile management system built with HTML, CSS, JavaScript (jQuery), PHP, MySQL, MongoDB, and Redis.

## Features

- **User Registration**: Signup with username, email, and password
- **User Login**: Secure login with session management
- **Profile Management**: Update profile details including age, DOB, contact, address, etc.
- **Session Management**: Using Redis for backend and localStorage for frontend
- **Responsive Design**: Bootstrap-based responsive UI
- **AJAX**: No page reloads - smooth user experience

## Tech Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, jQuery, AJAX
- **Backend**: PHP
- **Databases**: 
  - MySQL (User authentication data)
  - MongoDB (User profile data)
  - Redis (Session management)

## Folder Structure

```
practice-website/
├── index.html              # Landing page
├── signup.html             # Registration page
├── login.html              # Login page
├── profile.html            # User profile page
├── css/
│   └── styles.css          # Custom styles
├── js/
│   ├── signup.js           # Signup logic with jQuery AJAX
│   ├── login.js            # Login logic with jQuery AJAX
│   └── profile.js          # Profile management logic
├── php/
│   ├── config.php          # Database configurations
│   ├── signup.php          # Registration API
│   ├── login.php           # Login API
│   ├── get_profile.php     # Get profile data API
│   ├── update_profile.php  # Update profile API
│   └── logout.php          # Logout API
├── database_setup.sql      # MySQL database setup
└── README.md               # This file
```

## Prerequisites

Before running this application, ensure you have the following installed:

1. **XAMPP/WAMP/LAMP** (or similar) with:
   - Apache Web Server
   - PHP 7.4 or higher
   - MySQL 5.7 or higher

2. **MongoDB** (version 4.0 or higher)
   - Download from: https://www.mongodb.com/try/download/community

3. **Redis** (version 5.0 or higher)
   - Windows: Download from https://github.com/microsoftarchive/redis/releases
   - Or use Redis Docker container

4. **PHP Extensions** (enable in php.ini):
   - php_mysqli
   - php_mongodb
   - php_redis

## Installation Steps

### 1. Setup MySQL Database

1. Start Apache and MySQL from XAMPP/WAMP control panel
2. Open phpMyAdmin or MySQL command line
3. Import the database:
   ```sql
   mysql -u root -p < database_setup.sql
   ```
   Or run the SQL commands from `database_setup.sql` manually in phpMyAdmin

### 2. Setup MongoDB

1. Install MongoDB and start the service:
   ```bash
   # Windows
   net start MongoDB
   
   # Linux/Mac
   sudo systemctl start mongod
   ```

2. MongoDB will automatically create the `user_profiles_db` database when data is first inserted

### 3. Setup Redis

1. Install and start Redis:
   ```bash
   # Windows (if installed as service)
   redis-server
   
   # Linux/Mac
   sudo systemctl start redis
   # or
   redis-server
   ```

2. Verify Redis is running:
   ```bash
   redis-cli ping
   # Should return: PONG
   ```

### 4. Install PHP Extensions

#### For XAMPP:

1. Open `php.ini` file (usually in `C:\xampp\php\php.ini`)

2. Uncomment/enable these extensions:
   ```ini
   extension=mysqli
   extension=mongodb
   extension=redis
   ```

3. If MongoDB extension is not available:
   - Download from: https://pecl.php.net/package/mongodb
   - Place DLL in `C:\xampp\php\ext\`
   - Add `extension=mongodb` to php.ini

4. If Redis extension is not available:
   - Download from: https://pecl.php.net/package/redis
   - Place DLL in `C:\xampp\php\ext\`
   - Add `extension=redis` to php.ini

5. Restart Apache server

### 5. Configure Database Connection

1. Open `php/config.php`
2. Update the database credentials if needed:
   ```php
   // MySQL
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Your MySQL password
   define('DB_NAME', 'user_auth_db');
   
   // MongoDB
   define('MONGO_HOST', 'localhost');
   define('MONGO_PORT', 27017);
   
   // Redis
   define('REDIS_HOST', '127.0.0.1');
   define('REDIS_PORT', 6379);
   ```

### 6. Deploy the Application

1. Copy the entire project folder to your web server root:
   - XAMPP: `C:\xampp\htdocs\practice-website\`
   - WAMP: `C:\wamp64\www\practice-website\`

2. Access the application in your browser:
   ```
   http://localhost/practice-website/
   ```

## Usage

### Registration Flow

1. Navigate to `http://localhost/practice-website/`
2. Click "Register" button
3. Fill in the registration form:
   - Username
   - Email
   - Password
   - Confirm Password
4. Click "Sign Up"
5. Upon successful registration, you'll be redirected to the login page

### Login Flow

1. Enter your registered email and password
2. Click "Login"
3. Upon successful login, you'll be redirected to your profile page
4. Session token is stored in browser's localStorage

### Profile Management

1. After login, you'll see the profile page
2. Fill in additional details:
   - First Name, Last Name
   - Age, Date of Birth
   - Contact Number
   - Address, City, State, Country
3. Click "Update Profile" to save changes
4. Profile data is stored in MongoDB

### Logout

1. Click the "Logout" button in the navigation bar
2. Your session will be cleared from both Redis and localStorage
3. You'll be redirected to the login page

## Security Features

- **Password Hashing**: Using PHP's `password_hash()` with bcrypt
- **Prepared Statements**: All MySQL queries use prepared statements to prevent SQL injection
- **Session Management**: Secure session tokens stored in Redis with 1-hour expiration
- **Session Validation**: Every API request validates the session token
- **XSS Prevention**: Input sanitization and output encoding

## API Endpoints

All API endpoints return JSON responses:

### POST /php/signup.php
Register a new user
```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123"
}
```

### POST /php/login.php
Authenticate user
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### POST /php/get_profile.php
Get user profile data
```json
{
  "sessionToken": "...",
  "userId": "1"
}
```

### POST /php/update_profile.php
Update user profile
```json
{
  "sessionToken": "...",
  "userId": "1",
  "firstName": "John",
  "lastName": "Doe",
  "age": "25",
  "dob": "1999-01-01",
  "contact": "+1234567890",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "country": "USA"
}
```

### POST /php/logout.php
Logout user
```json
{
  "sessionToken": "...",
  "userId": "1"
}
```

## Troubleshooting

### MongoDB Connection Issues

1. Ensure MongoDB service is running
2. Check if MongoDB PHP extension is installed:
   ```bash
   php -m | grep mongodb
   ```
3. If not found, install the extension:
   ```bash
   pecl install mongodb
   ```

### Redis Connection Issues

1. Verify Redis is running:
   ```bash
   redis-cli ping
   ```
2. Check if Redis PHP extension is installed:
   ```bash
   php -m | grep redis
   ```

### MySQL Connection Issues

1. Check MySQL service is running
2. Verify credentials in `php/config.php`
3. Ensure user has proper permissions

### CORS Issues

If you encounter CORS errors:
1. The headers are already set in `php/config.php`
2. Ensure you're accessing via `http://localhost` not `file://`

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Notes

- Session expires after 1 hour of inactivity
- Session token is automatically extended on each validated request
- Passwords are hashed using bcrypt algorithm
- All database operations use prepared statements for security
- Profile data in MongoDB can be extended with additional fields as needed

## License

This project is created for educational purposes.

## Support

For issues or questions, please check:
1. All services (Apache, MySQL, MongoDB, Redis) are running
2. PHP extensions are properly loaded
3. Database credentials are correct
4. Browser console for any JavaScript errors

---

**Important**: This is a development setup. For production use, additional security measures should be implemented including:
- HTTPS/SSL certificates
- Environment variables for sensitive data
- Rate limiting
- CSRF protection
- More robust error handling
- Input validation on both client and server side
- Database connection pooling
- Proper logging mechanisms
