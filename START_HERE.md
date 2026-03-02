# 🚀 STEP-BY-STEP SETUP GUIDE

Follow these steps **in order** to get your User Registration & Login System working.

---

## ✅ STEP 1: Install XAMPP (Apache + PHP + MySQL)

### What to do:
1. **Download XAMPP**:
   - Go to: https://www.apachefriends.org/download.html
   - Download latest version (PHP 8.x recommended)

2. **Install XAMPP**:
   - Run the installer
   - Install to: `C:\xampp` (default location)
   - Select components: **Apache**, **MySQL**, **PHP**, **phpMyAdmin**
   - Click Install and wait

3. **Start Services**:
   - Open **XAMPP Control Panel**
   - Click **Start** next to **Apache**
   - Click **Start** next to **MySQL**
   - Both should show green "Running" status

4. **Verify Installation**:
   - Open browser
   - Go to: http://localhost
   - You should see XAMPP dashboard

✅ **Done? Apache and MySQL showing green in XAMPP Control Panel?**

---

## ✅ STEP 2: Setup MySQL Database

### What to do:
1. **Open phpMyAdmin**:
   - Go to: http://localhost/phpmyadmin
   - Click on "SQL" tab at the top

2. **Create Database**:
   - Copy the contents from your file: `database_setup.sql`
   - Paste into the SQL window
   - Click "Go" button

3. **Verify Database Created**:
   - Look at the left sidebar
   - You should see: `user_auth_db`
   - Click on it
   - You should see: `users` table

✅ **Done? Can you see user_auth_db database with users table?**

---

## ✅ STEP 3: Copy Project to XAMPP

### What to do:
1. **Copy your project folder**:
   ```
   FROM: C:\Users\hp\OneDrive\Desktop\practice website
   TO:   C:\xampp\htdocs\practice-website
   ```

2. **Using File Explorer**:
   - Open: `C:\Users\hp\OneDrive\Desktop\`
   - Right-click on "practice website" folder
   - Click "Copy"
   - Navigate to: `C:\xampp\htdocs\`
   - Right-click and "Paste"
   - Rename the folder from "practice website" to "practice-website" (remove space)

3. **OR Using PowerShell**:
   ```powershell
   Copy-Item -Recurse "C:\Users\hp\OneDrive\Desktop\practice website" "C:\xampp\htdocs\practice-website"
   ```

4. **Verify Files Copied**:
   - Check: `C:\xampp\htdocs\practice-website\`
   - Should contain: index.html, signup.html, login.html, css folder, js folder, php folder, etc.

✅ **Done? Files are in C:\xampp\htdocs\practice-website\ ?**

---

## ✅ STEP 4: Test Basic Setup (Without MongoDB/Redis)

### What to do:
1. **Open your application**:
   - Go to: http://localhost/practice-website/

2. **You should see**:
   - Welcome page with buttons
   - Register, Login, API Test, DB Test buttons

3. **Try Registration** (will work with just MySQL):
   - Click "Register"
   - Fill in the form:
     - Username: testuser
     - Email: test@example.com
     - Password: password123
     - Confirm Password: password123
   - Click "Sign Up"
   - You might see errors about MongoDB/Redis - **That's OK for now!**

4. **Check if user was saved in MySQL**:
   - Go to: http://localhost/phpmyadmin
   - Click on `user_auth_db` database
   - Click on `users` table
   - Click "Browse"
   - You should see your test user

✅ **Done? Can you see your test user in the database?**

---

## ✅ STEP 5: Install MongoDB (Choose ONE option)

### OPTION A: Install MongoDB Locally (If you want everything on your computer)

1. **Download MongoDB**:
   - Go to: https://www.mongodb.com/try/download/community
   - Version: 7.0.x (Current)
   - Platform: Windows
   - Package: msi
   - Click "Download"

2. **Install MongoDB**:
   - Run the downloaded .msi file
   - Choose "Complete" installation
   - **IMPORTANT**: Check ✅ "Install MongoDB as a Service"
   - Service Name: MongoDB
   - Check ✅ "Install MongoDB Compass" (optional but recommended)
   - Complete installation

3. **Verify MongoDB is Running**:
   - Open new PowerShell window
   - Run: `net start MongoDB`
   - Should say "service is already running" or start successfully

4. **Test MongoDB** (optional):
   - Run: `mongosh` (if installed)
   - You should see MongoDB shell
   - Type: `exit` to quit

### OPTION B: Use MongoDB Atlas (Cloud - FREE, Easier!)

1. **Sign up for MongoDB Atlas**:
   - Go to: https://www.mongodb.com/cloud/atlas/register
   - Create free account

2. **Create FREE Cluster**:
   - Click "Build a Database"
   - Choose "FREE" tier (M0 Sandbox)
   - Choose region (any closest to you)
   - Click "Create"
   - Wait 3-5 minutes for cluster to be created

3. **Create Database User**:
   - Click "Database Access" (left menu)
   - Click "Add New Database User"
   - Username: `admin`
   - Password: Choose a strong password (save it!)
   - User Privileges: "Read and write to any database"
   - Click "Add User"

4. **Allow Access**:
   - Click "Network Access" (left menu)
   - Click "Add IP Address"
   - Click "Allow Access from Anywhere" (for development)
   - IP: `0.0.0.0/0`
   - Click "Confirm"

5. **Get Connection String**:
   - Click "Database" (left menu)
   - Click "Connect" on your cluster
   - Choose "Connect your application"
   - Copy the connection string (looks like):
   ```
   mongodb+srv://admin:<password>@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority
   ```
   - **Save this string!** Replace `<password>` with your actual password

6. **Update Your Config File**:
   - Open: `C:\xampp\htdocs\practice-website\php\config.php`
   - Find the MongoDB section
   - Replace with (using your connection string):
   ```php
   // For MongoDB Atlas (Cloud)
   define('MONGO_CONNECTION_STRING', 'mongodb+srv://admin:YOUR_PASSWORD@cluster0.xxxxx.mongodb.net/');
   define('MONGO_DB', 'user_profiles_db');
   
   function getMongoDBConnection() {
       try {
           $mongoClient = new MongoDB\Driver\Manager(MONGO_CONNECTION_STRING . MONGO_DB);
           return $mongoClient;
       } catch (Exception $e) {
           error_log("MongoDB Connection failed: " . $e->getMessage());
           return null;
       }
   }
   ```

✅ **Done? MongoDB installed locally OR Atlas cluster created?**

---

## ✅ STEP 6: Install Redis (Choose ONE option)

### OPTION A: Install Redis Locally

1. **Download Redis for Windows**:
   - Go to: https://github.com/microsoftarchive/redis/releases
   - Download: `Redis-x64-3.0.504.msi`

2. **Install Redis**:
   - Run the installer
   - Check ✅ "Add to PATH"
   - Check ✅ "Add firewall exception"
   - Complete installation

3. **Start Redis**:
   - Open PowerShell
   - Run: `redis-server`
   - Should show Redis ASCII art and "Ready to accept connections"
   - **Keep this window open**

4. **Test Redis** (open new PowerShell):
   - Run: `redis-cli ping`
   - Should return: `PONG`

### OPTION B: Use Redis Cloud (FREE)

1. **Sign up**:
   - Go to: https://redis.com/try-free/
   - Create free account

2. **Create FREE Database**:
   - Click "Create Database"
   - Choose free tier
   - Get connection details (host, port, password)

3. **Update config.php**:
   - Use the cloud host, port, and password provided

### OPTION C: Skip Redis for Now (Session won't work)
- You can skip Redis initially
- Login will work, but session management won't persist
- Come back to this later

✅ **Done? Redis running (showing "Ready to accept connections")?**

---

## ✅ STEP 7: Install PHP Extensions

### What to do:

1. **Check your PHP version**:
   - Open PowerShell
   - Run: `C:\xampp\php\php.exe -v`
   - Note the version (e.g., PHP 8.2.12)

2. **Download MongoDB PHP Extension**:
   - Go to: https://pecl.php.net/package/mongodb
   - Click on "DLL" link
   - Find your PHP version and download:
     - Example: `php_mongodb-1.17.2-8.2-ts-x64.zip` (for PHP 8.2)
     - **ts** = Thread Safe (for Apache)
     - **x64** = 64-bit
   - Extract the zip file
   - Copy `php_mongodb.dll` to: `C:\xampp\php\ext\`

3. **Download Redis PHP Extension**:
   - Go to: https://pecl.php.net/package/redis
   - Click on "DLL" link
   - Find your PHP version and download
   - Extract and copy `php_redis.dll` to: `C:\xampp\php\ext\`

4. **Edit php.ini**:
   - Open: `C:\xampp\php\php.ini` (with Notepad)
   - Search for: `extension=mysqli`
   - Below it, add these lines:
   ```ini
   extension=mongodb
   extension=redis
   ```
   - Save the file

5. **Restart Apache**:
   - Open XAMPP Control Panel
   - Click "Stop" next to Apache
   - Wait 2 seconds
   - Click "Start" next to Apache

6. **Verify Extensions Loaded**:
   - Open PowerShell
   - Run: `C:\xampp\php\php.exe -m | findstr mongodb`
   - Should output: `mongodb`
   - Run: `C:\xampp\php\php.exe -m | findstr redis`
   - Should output: `redis`

✅ **Done? Extensions showing up in the list?**

---

## ✅ STEP 8: Test All Connections

### What to do:

1. **Test via Web Interface**:
   - Go to: http://localhost/practice-website/api_test.html
   - Wait for tests to run automatically
   - Check results:
     - ✅ MySQL: Should show "Connected" (green)
     - ✅ MongoDB: Should show "Connected" (green)
     - ✅ Redis: Should show "Connected" (green)
     - ✅ PHP Extensions: Should show all loaded

2. **If anything shows RED (Failed)**:
   - Click on that section to see error details
   - Go back to the relevant step above
   - Check troubleshooting section below

✅ **Done? All 3 databases showing GREEN (Connected)?**

---

## ✅ STEP 9: Test Complete Application Flow

### What to do:

1. **Register a New User**:
   - Go to: http://localhost/practice-website/
   - Click "Register"
   - Fill in the form:
     - Username: johndoe
     - Email: john@example.com
     - Password: password123
     - Confirm Password: password123
   - Click "Sign Up"
   - Should show: "Registration successful! Please login."

2. **Login**:
   - You'll be redirected to login page
   - Enter:
     - Email: john@example.com
     - Password: password123
   - Click "Login"
   - Should redirect to profile page

3. **Update Profile**:
   - Fill in profile details:
     - First Name: John
     - Last Name: Doe
     - Age: 25
     - Date of Birth: 1999-01-15
     - Contact: +1234567890
     - Address: 123 Main St
     - City: New York
     - State: NY
     - Country: USA
   - Click "Update Profile"
   - Should show: "Profile updated successfully"

4. **Logout**:
   - Click "Logout" button
   - Should redirect to login page

5. **Login Again**:
   - Login with same credentials
   - Profile data should still be there!

✅ **Done? Complete flow working (Register → Login → Profile → Logout)?**

---

## 🎉 CONGRATULATIONS!

If you completed all steps, your application is fully functional with:
- ✅ MySQL storing user credentials
- ✅ MongoDB storing user profiles
- ✅ Redis managing sessions
- ✅ Complete registration and login flow

---

## ❌ TROUBLESHOOTING

### Problem: "Can't access http://localhost"
**Solution**:
- Check XAMPP Control Panel → Apache is green/running?
- Try: http://127.0.0.1 instead
- Check firewall isn't blocking port 80

### Problem: "Database connection failed"
**Solution**:
- Check XAMPP Control Panel → MySQL is green/running?
- Verify database exists: http://localhost/phpmyadmin
- Check config.php has correct credentials

### Problem: "MongoDB extension not loaded"
**Solution**:
1. Verify dll exists: `C:\xampp\php\ext\php_mongodb.dll`
2. Check php.ini has: `extension=mongodb`
3. Restart Apache in XAMPP
4. Test: `C:\xampp\php\php.exe -m | findstr mongodb`

### Problem: "Redis connection failed"
**Solution**:
1. Check Redis is running: `redis-cli ping` (should return PONG)
2. If not running: `redis-server` (keep window open)
3. Verify php_redis.dll is in ext folder
4. Check php.ini has: `extension=redis`
5. Restart Apache

### Problem: "Page shows PHP code instead of running"
**Solution**:
- Apache is running in XAMPP?
- Files are in: `C:\xampp\htdocs\practice-website\` (NOT on Desktop)
- Access via: http://localhost/practice-website/ (NOT file://)

### Problem: "Registration works but login fails"
**Solution**:
- MongoDB and Redis must be installed and running
- Check api_test.html - all 3 databases green?
- Look at browser console (F12) for errors

### Problem: "Session doesn't persist"
**Solution**:
- Redis must be running
- Check: `redis-cli` then `KEYS session:*`
- Should show session keys
- Check browser localStorage (F12 → Application → Local Storage)

---

## 📞 QUICK CHECKLIST

Before asking for help, verify:
- [ ] XAMPP Control Panel: Apache is green/running
- [ ] XAMPP Control Panel: MySQL is green/running
- [ ] MongoDB service running OR Atlas cluster created
- [ ] Redis server running OR using alternative
- [ ] Files are in: `C:\xampp\htdocs\practice-website\`
- [ ] Database exists: http://localhost/phpmyadmin shows user_auth_db
- [ ] PHP extensions loaded: api_test.html shows all green
- [ ] Can access: http://localhost/practice-website/

---

## 🚀 QUICK START (Minimum to Test)

**If you want to test ASAP with minimal setup**:

1. ✅ Install XAMPP → Start Apache & MySQL
2. ✅ Create database (run database_setup.sql)
3. ✅ Copy files to C:\xampp\htdocs\practice-website\
4. ✅ Go to: http://localhost/practice-website/
5. ⚠️ Registration will work (MySQL only)
6. ⚠️ Login won't work without MongoDB & Redis

**Then add MongoDB & Redis later for full functionality!**

---

## 📖 WHAT TO READ NEXT

- `README.md` - Full project documentation
- `SETUP_GUIDE.md` - Detailed installation guide
- `API_DATABASE_GUIDE.md` - How APIs connect to databases
- `MONGODB_SETUP_GUIDE.md` - MongoDB specific setup
- `ARCHITECTURE.md` - System architecture details

---

**Good luck! 🎉**
