@echo off
echo ========================================
echo Barangay Blotter Form - Installation
echo ========================================
echo.

echo [1/3] Installing Composer dependencies...
composer install
if %errorlevel% neq 0 (
    echo ERROR: Composer install failed
    echo Please ensure Composer is installed: https://getcomposer.org/
    pause
    exit /b 1
)
echo Composer dependencies installed successfully!
echo.

echo [2/3] Creating email configuration file...
if not exist "config\email.config.php" (
    copy "config\email.config.example.php" "config\email.config.php"
    echo Email config created! Please edit config\email.config.php with your SMTP settings.
) else (
    echo Email config already exists, skipping...
)
echo.

echo [3/3] Creating config directory if needed...
if not exist "config" mkdir config
echo.

echo ========================================
echo Installation Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Edit config\email.config.php with your SMTP credentials
echo 2. Run the database migrations in phpMyAdmin:
echo    - Database query\add_dob_fields.sql
echo    - Database query\add_ml_and_status_tables.sql
echo 3. Read SETUP_GUIDE.md for detailed instructions
echo.
echo Press any key to exit...
pause >nul
