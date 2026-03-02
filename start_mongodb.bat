@echo off
REM Manual MongoDB Startup Script

echo Starting MongoDB manually...
echo.
echo Creating data directory if not exists...
if not exist "C:\data\db" mkdir "C:\data\db"

echo.
echo Starting MongoDB server...
echo Press Ctrl+C to stop MongoDB
echo.

REM Adjust the path if MongoDB is installed elsewhere
"C:\Program Files\MongoDB\Server\7.0\bin\mongod.exe" --dbpath "C:\data\db"

pause
