@echo off
echo ===================================================
echo   LuxeCart SE Shop - Laravel 13 REST API Backend
echo ===================================================
echo Server starting at: http://localhost:8000
echo Framework: Laravel 13 (PHP 8.3)
echo Database:  MySQL (se_shop_db)
echo.
echo [SCRAMBLE INTERACTIVE API TESTING UI]:
echo   - http://localhost:8000/docs/api
echo   - OpenAPI Schema: http://localhost:8000/docs/api.json
echo.
echo REST API Endpoints:
echo   - GET  http://localhost:8000/api/health
echo   - GET  http://localhost:8000/api/csrf-token
echo   - GET  http://localhost:8000/api/categories
echo   - GET  http://localhost:8000/api/products
echo   - POST http://localhost:8000/api/auth/login
echo   - POST http://localhost:8000/api/auth/register
echo   - GET  http://localhost:8000/api/orders
echo   - POST http://localhost:8000/api/orders
echo   - POST http://localhost:8000/api/coupons/validate
echo   - GET  http://localhost:8000/api/wishlists
echo ===================================================
php artisan serve --host=127.0.0.1 --port=8000
