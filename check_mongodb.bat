@echo off
echo ========================================
echo MongoDB Installation Verification
echo ========================================
echo.

echo Checking MongoDB Server...
where mongod >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] MongoDB Server found
    mongod --version
) else (
    echo [FAILED] MongoDB Server not found in PATH
    echo Please install MongoDB from: https://www.mongodb.com/try/download/community
)

echo.
echo Checking MongoDB Shell...
where mongosh >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] MongoDB Shell found
    mongosh --version
) else (
    where mongo >nul 2>&1
    if %errorlevel% equ 0 (
        echo [OK] MongoDB Shell (legacy) found
        mongo --version
    ) else (
        echo [FAILED] MongoDB Shell not found
    )
)

echo.
echo Checking MongoDB Service...
sc query MongoDB >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] MongoDB service exists
    sc query MongoDB | findstr STATE
) else (
    sc query "MongoDB Server" >nul 2>&1
    if %errorlevel% equ 0 (
        echo [OK] MongoDB Server service exists
        sc query "MongoDB Server" | findstr STATE
    ) else (
        echo [FAILED] MongoDB service not found
        echo Run: net start MongoDB
    )
)

echo.
echo ========================================
pause
