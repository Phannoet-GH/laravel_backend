@echo off
echo ===================================================
echo   LuxeCart Laravel 13 API - Test Suite Runner
echo ===================================================
echo Running 16 Core API Endpoints Tests + 24 Scramble/Fuzz Tests...
echo.
php artisan test
echo.
echo ===================================================
pause
