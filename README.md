# LuxeCart REST API Backend (Laravel 13 & PHP 8.3)

Enterprise-grade REST API backend built with **Laravel 13+ (v13.34.0)** and **PHP 8.3**, designed with complete backward compatibility for the Flutter mobile application (`se_shop_e_commerce_app`).

---

## 🚀 Key Features & Highlights

- **Framework**: Laravel 13.x running on PHP 8.3.31.
- **Contract Compatibility**: 100% contract and schema compatibility with the Flutter frontend. Identical JSON envelopes for success and error states.
- **Database Architecture**: MySQL 8.x (`se_shop_db` via Laragon on port 3306) with in-memory SQLite support for automated CI/CD testing.
- **Luxury Product Catalog**: 50 curated high-resolution designer products across 7 categories (Apparel, Footwear, Electronics, Accessories, Bags, Jewelry).
- **Authentication**: Laravel Sanctum API tokens with Bcrypt password hashing and OTP-based password recovery.
- **Security Shields**:
  - **XSS Defense**: Null-byte stripping, HTML tag removal, and character normalization via `Sanitizer` utility and `SanitizeInput` middleware.
  - **SQL Injection Defense**: Prepared statements with parameterized bindings across all Eloquent queries.
  - **Type Safety & Fuzz Resilience**: Type checking and boundary handling against malformed JSON, array injections, negative IDs, and buffer overflow attempts.
  - **Anti-CSRF Protection**: Token issuance endpoint (`GET /api/csrf-token`).

---

## 📁 Project Structure

```
laravel_backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AddressController.php     # User shipping addresses
│   │   │   ├── AuthController.php        # Register, login, OTP reset, profile
│   │   │   ├── CategoryController.php    # Category listings
│   │   │   ├── CouponController.php      # Voucher discount validation
│   │   │   ├── HealthController.php      # System health & anti-CSRF token
│   │   │   ├── OrderController.php       # Order creation & tracking
│   │   │   ├── ProductController.php     # Product catalog & review associations
│   │   │   ├── ReviewController.php      # Customer reviews & product rating updates
│   │   │   └── WishlistController.php    # Wishlists & favorites
│   │   ├── Middleware/
│   │   │   └── SanitizeInput.php         # Input sanitization & malformed JSON rejection
│   │   └── Traits/
│   │       └── ApiResponse.php           # Standardized JSON response envelope
│   ├── Models/
│   │   ├── Address.php
│   │   ├── Category.php
│   │   ├── Coupon.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── PasswordReset.php
│   │   ├── Product.php
│   │   ├── Review.php
│   │   ├── User.php
│   │   └── Wishlist.php
│   └── Security/
│       └── Sanitizer.php                 # Sanitization logic & string boundary enforcement
├── database/
│   ├── migrations/                       # 11 table migrations
│   └── seeders/
│       ├── CategorySeeder.php            # 7 Categories
│       ├── ProductSeeder.php             # 50 Luxury products with Unsplash assets
│       ├── CouponSeeder.php              # LUXE20, SAVE10, FREESHIP, VIP50
│       ├── UserSeeder.php                # Demo accounts & default wishlist/address
│       └── DatabaseSeeder.php
├── routes/
│   └── api.php                           # Complete REST endpoint definitions & fallback
├── tests/Feature/
│   ├── CoreEndpointsTest.php             # 16 Core system diagnostic tests
│   └── ScrambleFuzzTest.php              # 24 Scramble, mutation & fuzzing tests
├── run.bat                               # One-click server launcher (port 8000)
└── test_api.bat                          # One-click test runner
```

---

## 📡 API Endpoint Summary

All responses follow the unified envelope:

### Success Envelope (HTTP 200 / 201)
```json
{
  "success": true,
  "status": 200,
  "data": { ... },
  "meta": { ... } // Optional metadata
}
```

### Error Envelope (HTTP 4xx / 5xx)
```json
{
  "success": false,
  "status": 404,
  "error": {
    "message": "Resource or product not found.",
    "details": { ... } // Optional validation details
  }
}
```

### Route Index

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/health` | Service health status and security shields |
| `GET` | `/api/csrf-token` | Generate anti-CSRF token |
| `GET` | `/api/categories` | List all 7 luxury categories |
| `GET` | `/api/products` | Browse catalog with `category`, `search`, `in_stock`, `limit`, `offset` |
| `GET` | `/api/products/{id}` | Retrieve single product with full specs & customer reviews |
| `POST` | `/api/coupons/validate` | Validate discount coupon code (`LUXE20`, `SAVE10`, etc.) |
| `GET` | `/api/reviews?product_id={id}` | Get customer reviews for product |
| `POST` | `/api/reviews` | Submit product review (with clamped rating 1.0 - 5.0) |
| `POST` | `/api/auth/login` | Authenticate customer & generate Sanctum token |
| `POST` | `/api/auth/register` | Register new customer account |
| `POST` | `/api/auth/forgot-password` | Generate 6-digit OTP for password recovery |
| `POST` | `/api/auth/verify-otp` | Verify OTP code |
| `POST` | `/api/auth/reset-password` | Reset password using verified OTP |
| `POST` | `/api/auth/profile` | Update user name & phone with buffer overflow protection |
| `GET` | `/api/addresses?user_id={id}` | List shipping addresses |
| `POST` | `/api/addresses` | Create new shipping address |
| `DELETE` | `/api/addresses` | Delete shipping address |
| `POST` | `/api/addresses/default` | Set primary default shipping address |
| `GET` | `/api/wishlists?user_id={id}` | List user wishlists |
| `POST` | `/api/wishlists` | Create new wishlist |
| `POST` | `/api/wishlists/items` | Add product to wishlist |
| `DELETE` | `/api/wishlists/items` | Remove product from wishlist |
| `GET` | `/api/favorites?user_id={id}` | Get array of favorited product IDs |
| `POST` | `/api/favorites/toggle` | Toggle favorite state for product |
| `GET` | `/api/orders?user_id={id}` | List orders with line items |
| `POST` | `/api/orders` | Place order with validation & transaction rollback safety |
| `GET` | `/api/orders/track?tracking_number={no}` | Track order status by tracking or order number |

## 📖 Scramble Interactive API Testing & Documentation

[Dedoc Scramble](https://scramble.dedoc.co) is installed and configured for interactive API testing directly from your browser without needing Postman:

- **Interactive Testing UI**: `http://localhost:8000/docs/api`
  - Every endpoint has a **"Try It"** button allowing you to run live API requests with parameters, JSON bodies, and headers.
  - Automatically generates real-time OpenAPI 3.1 schema.
  - Includes Bearer Token Authentication support for protected endpoints.
- **Raw OpenAPI Schema**: `http://localhost:8000/docs/api.json`

---

## 🧪 Automated Testing Suite

To run the automated tests:

```bash
# Run all tests (44 tests, 135 assertions)
php artisan test

# Run only Core API Endpoints tests (16 tests)
php artisan test --filter=CoreEndpointsTest

# Run only Scramble & Fuzz Mutation tests (24 tests)
php artisan test --filter=ScrambleFuzzTest

# Run Scramble Documentation UI tests (2 tests)
php artisan test --filter=ScrambleDocsTest
```

---

## 🏃 Quick Start

1. **Install Dependencies** (already installed):
   ```bash
   composer install
   ```

2. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate:fresh --seed
   ```

3. **Start the Development Server**:
   ```bash
   run.bat
   # Or directly:
   php artisan serve --host=127.0.0.1 --port=8000
   ```
