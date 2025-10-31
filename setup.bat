@echo off
echo.
echo =============================================
echo  Science-Qur'an Integration - Setup Script
echo =============================================
echo.

:menu
echo.
echo Choose an option:
echo 1. Setup Database
echo 2. Install Node.js Dependencies
echo 3. Verify Environment
echo 4. Start Application (Node.js)
echo 5. Start Application (XAMPP)
echo 6. Exit
echo.

set /p choice="Enter your choice (1-6): "

if "%choice%"=="1" (
    goto setup_db
) else if "%choice%"=="2" (
    goto install_deps
) else if "%choice%"=="3" (
    goto verify_env
) else if "%choice%"=="4" (
    goto start_app
) else if "%choice%"=="5" (
    goto start_xampp
) else if "%choice%"=="6" (
    goto exit_script
) else (
    echo Invalid choice. Please try again.
    goto menu
)

:setup_db
echo.
echo Setting up database...
echo.
echo Please follow these steps:
echo 1. Make sure XAMPP is installed and MySQL service is running
echo 2. Open http://localhost/phpmyadmin in your browser
echo 3. Create a new database named "science_quran"
echo 4. Import the SQL file from database/science_quran.sql
echo 5. Import the additional features from database/additional_features.sql
echo.
echo Press any key to continue...
pause >nul
goto menu

:install_deps
echo.
echo Installing Node.js dependencies...
echo.
cd /d "%~dp0"
if exist "package.json" (
    npm install
) else (
    echo package.json not found in current directory
)
echo.
echo Press any key to continue...
pause >nul
goto menu

:verify_env
echo.
echo Verifying environment...
echo.
echo Current directory: %cd%
echo.
if exist ".env" (
    echo Environment variables file (.env) found
    type .env
) else (
    echo .env file not found
)
echo.
echo Checking if Node.js is available...
node --version >nul 2>&1
if %errorlevel% == 0 (
    echo Node.js is installed
) else (
    echo Node.js is NOT installed or not in PATH
)
echo.
echo Checking if npm is available...
npm --version >nul 2>&1
if %errorlevel% == 0 (
    echo npm is installed
) else (
    echo npm is NOT installed or not in PATH
)
echo.
echo Press any key to continue...
pause >nul
goto menu

:start_app
echo.
echo Starting Node.js application...
echo.
echo Make sure MySQL service is running before starting the app.
echo.
cd /d "%~dp0"
if exist "server\app.js" (
    echo Starting server on http://localhost:3000
    node server/app.js
) else (
    echo server/app.js not found
)
goto menu

:start_xampp
echo.
echo Starting with XAMPP...
echo.
echo 1. Open XAMPP Control Panel
echo 2. Start Apache and MySQL services
echo 3. Place this project folder in xampp/htdocs/
echo 4. Access the application via http://localhost/ScienceQuranIntegration/
echo.
echo Press any key to continue...
pause >nul
goto menu

:exit_script
echo.
echo Thank you for using Science-Qur'an Integration setup script!
echo.
pause