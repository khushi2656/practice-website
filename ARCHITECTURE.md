# Project Architecture & Flow Documentation

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT SIDE                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  signup.html │  │  login.html  │  │ profile.html │          │
│  │              │  │              │  │              │          │
│  │  Bootstrap   │  │  Bootstrap   │  │  Bootstrap   │          │
│  │  Forms       │  │  Forms       │  │  Forms       │          │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘          │
│         │                 │                  │                  │
│         ▼                 ▼                  ▼                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  signup.js   │  │  login.js    │  │  profile.js  │          │
│  │              │  │              │  │              │          │
│  │  jQuery AJAX │  │  jQuery AJAX │  │  jQuery AJAX │          │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘          │
│         │                 │                  │                  │
│         │                 │    localStorage  │                  │
│         │                 │    (sessionToken,│                  │
│         │                 │     userId, etc) │                  │
└─────────┼─────────────────┼──────────────────┼──────────────────┘
          │                 │                  │
          │ AJAX POST       │ AJAX POST        │ AJAX POST
          │                 │                  │
┌─────────┼─────────────────┼──────────────────┼──────────────────┐
│         ▼                 ▼                  ▼   SERVER SIDE    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  signup.php  │  │  login.php   │  │get_profile.php│         │
│  │              │  │              │  │update_profile │         │
│  │              │  │              │  │   .php        │         │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘          │
│         │                 │                  │                  │
│         │            ┌────┴─────┐           │                  │
│         │            │          │           │                  │
│         ▼            ▼          ▼           ▼                  │
│  ┌──────────────────────────────────────────────┐              │
│  │            config.php                        │              │
│  │   • Database Connections                     │              │
│  │   • Helper Functions                         │              │
│  │   • Session Validation                       │              │
│  └───┬──────────────┬──────────────┬───────────┘              │
│      │              │              │                           │
└──────┼──────────────┼──────────────┼───────────────────────────┘
       │              │              │
       ▼              ▼              ▼
┌──────────────┐ ┌──────────┐ ┌──────────────┐
│   MySQL      │ │  Redis   │ │  MongoDB     │
│              │ │          │ │              │
│ user_auth_db │ │ Sessions │ │user_profiles │
│              │ │          │ │    _db       │
│ ┌──────────┐ │ │ Key:Value│ │              │
│ │  users   │ │ │          │ │ ┌──────────┐ │
│ │  table   │ │ │ session: │ │ │ profiles │ │
│ │          │ │ │  token   │ │ │collection│ │
│ │ - id     │ │ │  userId  │ │ │          │ │
│ │ - username│ │ │          │ │ │ - userId │ │
│ │ - email  │ │ │ TTL: 1hr │ │ │ - firstName│
│ │ - password│ │ └──────────┘ │ │ - lastName│ │
│ │ - created│ │              │ │ - age    │ │
│ └──────────┘ │              │ │ - dob    │ │
│              │              │ │ - contact│ │
│   Prepared   │              │ │ - address│ │
│  Statements  │              │ │ - city   │ │
└──────────────┘              │ │ - state  │ │
                              │ │ - country│ │
                              │ └──────────┘ │
                              └──────────────┘
```

## Application Flow

### 1. Registration Flow (signup.html → signup.php → MySQL)

```
User fills form → signup.js validates → AJAX POST to signup.php
                                             ↓
                                    Validate inputs
                                             ↓
                                    Check email exists (Prepared Statement)
                                             ↓
                                    Check username exists (Prepared Statement)
                                             ↓
                                    Hash password (bcrypt)
                                             ↓
                                    Insert into MySQL users table (Prepared Statement)
                                             ↓
                                    Return success response
                                             ↓
                                    Redirect to login.html
```

### 2. Login Flow (login.html → login.php → MySQL + Redis)

```
User enters credentials → login.js validates → AJAX POST to login.php
                                                      ↓
                                              Validate inputs
                                                      ↓
                                              Query MySQL for user (Prepared Statement)
                                                      ↓
                                              Verify password (password_verify)
                                                      ↓
                                              Generate session token (random_bytes)
                                                      ↓
                                              Store in Redis: session:token → userId
                                              (TTL: 3600 seconds / 1 hour)
                                                      ↓
                                              Return: sessionToken, userId, username, email
                                                      ↓
                                              Store in localStorage
                                                      ↓
                                              Redirect to profile.html
```

### 3. Profile View Flow (profile.html → get_profile.php → Redis + MongoDB)

```
Page loads → profile.js checks localStorage
                    ↓
            Has sessionToken? → Yes → Continue
                    ↓ No
            Redirect to login.html
                    ↓
            AJAX POST to get_profile.php (sessionToken + userId)
                    ↓
            Validate session in Redis
                    ↓
            Query MongoDB profiles collection (userId)
                    ↓
            Return profile data (or empty if new user)
                    ↓
            Populate form fields with existing data
```

### 4. Profile Update Flow (profile.html → update_profile.php → Redis + MongoDB)

```
User updates form → Submit → profile.js → AJAX POST to update_profile.php
                                                 ↓
                                          Validate session in Redis
                                                 ↓
                                          Extend session TTL
                                                 ↓
                                          Prepare profile document
                                                 ↓
                                          Check if profile exists in MongoDB
                                                 ↓
                                    ┌────────────┴────────────┐
                                    ▼                         ▼
                              Exists: UPDATE            New: INSERT
                                    │                         │
                                    └────────────┬────────────┘
                                                 ▼
                                          Return success
                                                 ▼
                                          Show success message
```

### 5. Logout Flow (profile.html → logout.php → Redis)

```
User clicks Logout → profile.js → AJAX POST to logout.php
                                         ↓
                                  Delete session from Redis
                                         ↓
                                  Clear localStorage
                                         ↓
                                  Redirect to login.html
```

## Data Storage Strategy

### MySQL (user_auth_db.users)
**Purpose**: Store user authentication credentials
**Why**: ACID compliance, reliable for sensitive authentication data
**Data Stored**:
- User ID (Primary Key)
- Username (Unique)
- Email (Unique)
- Hashed Password
- Created Timestamp

**Security**:
- All queries use Prepared Statements (SQL injection prevention)
- Passwords hashed with bcrypt
- Indexed on email and username for fast lookups

### MongoDB (user_profiles_db.profiles)
**Purpose**: Store flexible user profile data
**Why**: Schema-less, easy to extend with new fields
**Data Stored**:
- User ID (reference to MySQL)
- Personal details (firstName, lastName, age, dob)
- Contact information (contact, address, city, state, country)
- Timestamps

**Benefits**:
- Easy to add new profile fields without schema changes
- Good for documents with varying structures
- Fast reads for profile data

### Redis (In-Memory Key-Value Store)
**Purpose**: Session management
**Why**: Extremely fast, built-in TTL support
**Data Stored**:
- Key: `session:{token}`
- Value: `userId`
- TTL: 3600 seconds (1 hour)

**Benefits**:
- Lightning-fast session validation
- Automatic expiration (TTL)
- Reduced database load
- Session persistence across page reloads

## Session Management Architecture

```
┌─────────────────────────────────────────────────────────┐
│                  Session Lifecycle                      │
└─────────────────────────────────────────────────────────┘

Login
  ↓
Generate Token (64 char hex string)
  ↓
Store in Redis: SET session:TOKEN userId EX 3600
  ↓
Send to Client: {sessionToken: "...", userId: "123"}
  ↓
Client stores in localStorage
  ↓
Every Request:
  ├→ Client sends: sessionToken + userId
  ├→ Server validates: GET session:TOKEN from Redis
  ├→ Check if value matches userId
  ├→ If valid: EXPIRE session:TOKEN 3600 (extend)
  └→ If invalid: Return "Invalid session"
  ↓
Logout:
  ├→ Client sends: sessionToken
  ├→ Server: DEL session:TOKEN from Redis
  └→ Client: Clear localStorage

Auto-expire after 1 hour of inactivity
```

## File Responsibilities

### Frontend Files

**index.html**
- Landing page with navigation to signup/login
- Bootstrap styled buttons
- Gradient background

**signup.html**
- Registration form (username, email, password, confirm password)
- Bootstrap form styling
- Client-side validation

**login.html**
- Login form (email, password)
- Bootstrap form styling
- Redirects to profile on success

**profile.html**
- Profile management interface
- Navbar with welcome message and logout
- Form for personal details
- Loads existing data on page load

**css/styles.css**
- Custom styling on top of Bootstrap
- Gradient backgrounds
- Button hover effects
- Responsive design adjustments

**js/signup.js**
- Form validation
- AJAX call to signup.php
- Error handling and display
- Redirect on success

**js/login.js**
- Session check (redirect if already logged in)
- Form validation
- AJAX call to login.php
- Store session data in localStorage
- Redirect to profile on success

**js/profile.js**
- Session validation (redirect if not logged in)
- Load profile data on page load
- Handle profile updates
- Logout functionality

### Backend Files

**php/config.php**
- Database connection functions (MySQL, MongoDB, Redis)
- Configuration constants
- Session validation function
- Token generation function
- CORS headers

**php/signup.php**
- Validate registration data
- Check for existing email/username (Prepared Statements)
- Hash password
- Insert new user (Prepared Statement)
- Return JSON response

**php/login.php**
- Validate credentials
- Query user (Prepared Statement)
- Verify password
- Generate session token
- Store session in Redis
- Return session data

**php/get_profile.php**
- Validate session
- Query MongoDB for profile
- Return profile data or empty object

**php/update_profile.php**
- Validate session
- Update or insert profile in MongoDB
- Return success/failure

**php/logout.php**
- Delete session from Redis
- Return success

### Configuration Files

**database_setup.sql**
- MySQL database schema
- Creates database and users table
- Sets up indexes

**.htaccess**
- Security headers
- Prevent directory browsing
- Deny access to sensitive files

**README.md**
- Complete project documentation
- Installation instructions
- API documentation
- Troubleshooting guide

**SETUP_GUIDE.md**
- Quick setup instructions
- Verification checklist
- Common issues and solutions
- Test scripts

## Security Features Implemented

### 1. Password Security
- ✅ Passwords hashed with bcrypt (PHP password_hash)
- ✅ Never stored in plain text
- ✅ Verified with password_verify function
- ✅ Minimum 6 characters enforced

### 2. SQL Injection Prevention
- ✅ All MySQL queries use Prepared Statements
- ✅ Parameters bound separately from SQL
- ✅ No direct string concatenation in queries

### 3. Session Security
- ✅ Cryptographically secure token generation (random_bytes)
- ✅ Tokens stored in Redis (not in MySQL)
- ✅ Automatic expiration (1 hour TTL)
- ✅ Session validation on every protected request
- ✅ Session extension on activity

### 4. Input Validation
- ✅ Client-side validation (JavaScript)
- ✅ Server-side validation (PHP)
- ✅ Email format validation
- ✅ Required field checks
- ✅ Data trimming

### 5. HTTP Security
- ✅ CORS headers configured
- ✅ Content-Type: application/json
- ✅ X-Content-Type-Options: nosniff
- ✅ X-Frame-Options: SAMEORIGIN
- ✅ X-XSS-Protection: enabled

### 6. Access Control
- ✅ Protected routes check session
- ✅ Redirect to login if not authenticated
- ✅ User can only access their own data

### 7. Error Handling
- ✅ Generic error messages (don't expose system details)
- ✅ Errors logged server-side
- ✅ User-friendly messages client-side

## Technologies Used

| Technology | Version | Purpose |
|------------|---------|---------|
| HTML5 | Latest | Page structure |
| CSS3 | Latest | Styling |
| Bootstrap | 5.3.0 | Responsive UI framework |
| JavaScript | ES6+ | Client-side logic |
| jQuery | 3.6.0 | DOM manipulation & AJAX |
| PHP | 7.4+ | Server-side logic |
| MySQL | 5.7+ | User authentication storage |
| MongoDB | 4.0+ | User profile storage |
| Redis | 5.0+ | Session management |

## API Request/Response Examples

### Signup Request
```json
POST /php/signup.php
Content-Type: application/x-www-form-urlencoded

username=johndoe&email=john@example.com&password=secret123
```

### Signup Response (Success)
```json
{
  "success": true,
  "message": "Registration successful! Please login."
}
```

### Signup Response (Error)
```json
{
  "success": false,
  "message": "Email already registered"
}
```

### Login Request
```json
POST /php/login.php

email=john@example.com&password=secret123
```

### Login Response (Success)
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

### Get Profile Request
```json
POST /php/get_profile.php

sessionToken=a1b2c3d4e5f6...&userId=1
```

### Get Profile Response
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

### Update Profile Request
```json
POST /php/update_profile.php

sessionToken=a1b2c3d4e5f6...&userId=1&firstName=John&lastName=Doe&age=25&...
```

### Update Profile Response
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

## Performance Considerations

### Frontend
- Minified CSS and JS (can be added)
- Bootstrap CDN for caching
- jQuery CDN for caching
- Lazy loading (can be added)

### Backend
- Redis for fast session lookup (O(1) complexity)
- MySQL indexes on email and username
- MongoDB indexes (can be added on userId)
- Prepared statements cached by MySQL

### Database
- MySQL connection pooling (can be configured)
- MongoDB connection reuse
- Redis connection persistence
- Proper indexing

## Scalability Notes

**Current Setup**: Single server (good for development/small scale)

**For Production Scale**:
1. Load balancer (distribute traffic)
2. Multiple PHP application servers
3. MySQL master-slave replication
4. MongoDB replica sets
5. Redis cluster or Redis Sentinel
6. CDN for static assets
7. Caching layer (Memcached/Varnish)
8. Database connection pooling
9. Queue system for async tasks (RabbitMQ/Redis Queue)

## Testing Checklist

### Unit Testing
- [ ] Signup with valid data
- [ ] Signup with duplicate email
- [ ] Signup with duplicate username
- [ ] Signup with weak password
- [ ] Login with correct credentials
- [ ] Login with wrong password
- [ ] Login with non-existent email
- [ ] Profile update with valid session
- [ ] Profile update with invalid session
- [ ] Logout functionality

### Integration Testing
- [ ] Complete flow: Signup → Login → Profile Update → Logout
- [ ] Session expiration after 1 hour
- [ ] Session extension on activity
- [ ] Multiple simultaneous sessions
- [ ] Profile data persistence

### Security Testing
- [ ] SQL injection attempts
- [ ] XSS attempts
- [ ] CSRF attempts (add tokens in production)
- [ ] Password brute force (add rate limiting)
- [ ] Session hijacking attempts

### Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers

### Responsive Testing
- [ ] Desktop (1920x1080)
- [ ] Laptop (1366x768)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

## Future Enhancements

### Features
- [ ] Email verification
- [ ] Password reset functionality
- [ ] Remember me option
- [ ] Two-factor authentication
- [ ] Profile picture upload
- [ ] Social media login (OAuth)
- [ ] Account deletion
- [ ] Activity log

### Security
- [ ] CSRF token implementation
- [ ] Rate limiting
- [ ] IP-based blocking
- [ ] Captcha on signup/login
- [ ] Password strength meter
- [ ] Account lockout after failed attempts
- [ ] Security questions

### Performance
- [ ] Caching layer
- [ ] Asset minification
- [ ] Image optimization
- [ ] Lazy loading
- [ ] Service workers (PWA)
- [ ] Database query optimization

### UX/UI
- [ ] Loading spinners
- [ ] Toast notifications
- [ ] Form field icons
- [ ] Password visibility toggle
- [ ] Auto-save draft
- [ ] Dark mode
- [ ] Animations

---

**Project Status**: ✅ Complete and Ready for Development Use

**Production Ready**: ⚠️ Additional security measures recommended

**Documentation**: ✅ Comprehensive

**Code Quality**: ✅ Follows best practices

**Maintainability**: ✅ Well-organized and commented
