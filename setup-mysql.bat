@echo off
REM MySQL Setup Script for Joudah Store Migration
REM Run this after starting Laragon MySQL service

echo.
echo ========================================
echo   Joudah Store - MySQL Setup Script
echo ========================================
echo.

REM Try to connect to MySQL
echo Checking MySQL connection...
mysql -u root -e "SELECT 1" > nul 2>&1

if %errorlevel% neq 0 (
    echo.
    echo ❌ MySQL is not running!
    echo.
    echo Steps to fix:
    echo 1. Open Laragon in system tray
    echo 2. Click on MySQL or "Start All" to start the service
    echo 3. Wait 10 seconds for MySQL to start
    echo 4. Run this script again
    echo.
    pause
    exit /b 1
)

echo ✓ MySQL connection successful
echo.

echo Creating database joudah_store...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS joudah_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %errorlevel% neq 0 (
    echo ❌ Failed to create database
    pause
    exit /b 1
)

echo ✓ Database created successfully
echo.

echo Checking tables...
mysql -u root joudah_store -e "SHOW TABLES;" 

echo.
echo ✓ Setup complete! MySQL database is ready for migration.
echo.
pause
