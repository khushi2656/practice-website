# 🚀 Cloud Deployment Checklist

Use this checklist to track your progress deploying to the cloud.

## ☁️ MongoDB Atlas Setup
- [ ] Created account at https://www.mongodb.com/cloud/atlas/register
- [ ] Created FREE M0 cluster (512MB)
- [ ] Created database user (username + password)
- [ ] Whitelisted IP: 0.0.0.0/0 (allow from anywhere)
- [ ] Copied connection string
- [ ] Created database: `user_profiles_db`
- [ ] Created collection: `profiles`
- [ ] **Connection String**: ___________________________________________

## ⚡ Redis Cloud Setup
- [ ] Created account at https://redis.com/try-free/
- [ ] Created FREE database (30MB)
- [ ] Copied connection details
- [ ] **Endpoint**: ___________________________________________________
- [ ] **Port**: _______
- [ ] **Password**: ___________________________________________________

## 📝 Local Configuration
- [ ] Opened `php/config.php`
- [ ] Updated `MONGO_URI` with MongoDB Atlas connection string (line ~35)
- [ ] Updated `REDIS_HOST` with Redis Cloud endpoint (line ~50)
- [ ] Updated `REDIS_PORT` with Redis Cloud port (line ~51)
- [ ] Updated `REDIS_PASSWORD` with Redis Cloud password (line ~52)
- [ ] Saved the file

## 🏠 Local Testing (Optional)
- [ ] Installed XAMPP
- [ ] Copied project to `C:\xampp\htdocs\practice-website\`
- [ ] Started Apache in XAMPP
- [ ] Created MySQL database using `database_setup.sql`
- [ ] Tested: http://localhost/practice-website/environment_checker.html
- [ ] Verified: Shows "Running on: LOCALHOST"
- [ ] Verified: All 3 databases show as connected

## 🚂 Railway Setup
- [ ] Created account at https://railway.app/
- [ ] Logged in with GitHub
- [ ] Created new project
- [ ] Connected GitHub repository: `khushi2656/practice-website`
- [ ] Added MySQL database service
- [ ] **Railway MySQL Host**: _________________________________________
- [ ] **Railway MySQL Port**: _______
- [ ] **Railway MySQL Password**: _____________________________________

## ⚙️ Railway Environment Variables
Go to your web service → Variables tab and add:

- [ ] `DB_HOST` = [Railway MySQL host]
- [ ] `DB_PORT` = [Railway MySQL port]
- [ ] `DB_USER` = root
- [ ] `DB_PASS` = [Railway MySQL password]
- [ ] `DB_NAME` = railway
- [ ] `MONGO_URI` = [Your MongoDB Atlas connection string]
- [ ] `MONGO_DB` = user_profiles_db
- [ ] `REDIS_HOST` = [Your Redis Cloud endpoint]
- [ ] `REDIS_PORT` = [Your Redis Cloud port]
- [ ] `REDIS_PASSWORD` = [Your Redis Cloud password]

## 🗄️ Railway MySQL Database Setup
- [ ] Clicked on MySQL service in Railway
- [ ] Went to Connect tab
- [ ] Copied MySQL command
- [ ] Connected using MySQL client or phpMyAdmin
- [ ] Created database: `CREATE DATABASE railway;`
- [ ] Used database: `USE railway;`
- [ ] Ran SQL from `database_setup.sql` file

## 🌍 Railway Domain
- [ ] Went to web service → Settings
- [ ] Clicked "Generate Domain"
- [ ] **Live URL**: ___________________________________________________

## ✅ Online Testing
- [ ] Opened: [Your URL]/environment_checker.html
- [ ] Verified: Shows "Running on: ONLINE"
- [ ] Verified: All 3 databases connected
- [ ] Tested: [Your URL]/interactive_api_test.html
- [ ] Tested complete flow:
  - [ ] Register new user
  - [ ] Login with credentials
  - [ ] View profile
  - [ ] Update profile
  - [ ] Logout
- [ ] Verified data in databases:
  - [ ] Railway MySQL: users table has new user
  - [ ] MongoDB Atlas: profiles collection has profile data
  - [ ] Redis Cloud: session keys exist (check with Redis CLI or Insight)

## 🔄 GitHub Auto-Deployment
- [ ] Made a test change in code
- [ ] Committed: `git add .`
- [ ] Committed: `git commit -m "test deployment"`
- [ ] Pushed: `git push origin main`
- [ ] Watched Railway auto-deploy (check Deployments tab)
- [ ] Verified change appeared on live site

## 🎉 Congratulations!

If all boxes are checked, your application is now running both:
- 🏠 **Locally**: http://localhost/practice-website/
- 🌍 **Online**: [Your Railway URL]

Both environments share the same MongoDB Atlas and Redis Cloud databases!

---

## 📌 Important URLs to Save

| Service | URL | Username | Password |
|---------|-----|----------|----------|
| **Railway App** | _________________ | GitHub | - |
| **MongoDB Atlas** | https://cloud.mongodb.com | __________ | __________ |
| **Redis Cloud** | https://app.redislabs.com | __________ | __________ |
| **GitHub Repo** | https://github.com/khushi2656/practice-website | khushi2656 | - |

---

## 🆘 Troubleshooting

### Deployment fails on Railway
- Check Deployments → View Logs
- Verify all environment variables are set correctly
- Make sure PHP extensions (mongodb, redis) are in nixpacks.toml

### Database connections fail
- MongoDB: Check IP whitelist (0.0.0.0/0), verify credentials
- Redis: Check endpoint, port, and password are correct
- MySQL: Verify Railway MySQL is running, credentials are correct

### Changes not appearing online
- Check if push was successful: `git log`
- View Railway deployment status
- Check Railway logs for errors
- Try manual redeploy in Railway dashboard

### Environment shows wrong
- Clear browser cache
- Check which URL you're accessing
- Verify config.php environment detection logic

---

**Need Help?**
- 📖 Read: [CLOUD_DEPLOYMENT_GUIDE.md](CLOUD_DEPLOYMENT_GUIDE.md)
- 📋 Quick Reference: [CLOUD_QUICK_START.txt](CLOUD_QUICK_START.txt)
- 💬 Check Railway logs for detailed error messages
