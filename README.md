# findwashroom-backend
	Laravel API
	Authentication
	Booking logic
	Payments
	Property management
	Reviews
	Complaints
	Cleaning jobs
Tech stack:
	Laravel
	MySQL
	Sanctum (API auth)
	Razorpay
Folder structure:
	findwashroom-backend
	│
	├── app
	│   ├── Models
	│   │   ├── User.php
	│   │   ├── Property.php
	│   │   ├── Booking.php
	│   │   ├── Review.php
	│   │   ├── Complaint.php
	│   │   ├── CleaningJob.php
	│   │   └── Payment.php
	│
	│   ├── Http
	│   │   ├── Controllers
	│   │   │   ├── AuthController.php
	│   │   │   ├── PropertyController.php
	│   │   │   ├── BookingController.php
	│   │   │   ├── PaymentController.php
	│   │   │   ├── ReviewController.php
	│   │   │   ├── ComplaintController.php
	│   │   │   └── CleaningJobController.php
	│   │
	│   │   ├── Middleware
	│   │   │   ├── AdminMiddleware.php
	│   │   │   ├── OwnerMiddleware.php
	│   │   │   └── CleanerMiddleware.php
	│
	│   ├── Services
	│   │   ├── PaymentService.php
	│   │   ├── BookingService.php
	│   │   └── NotificationService.php
	│
	│   └── Helpers
	│
	├── database
	│   ├── migrations
	│   └── seeders
	│
	├── routes
	│   ├── api.php
	│   └── web.php
	│
	├── config
	│
	├── storage
	│
	└── tests

<!-- Here is a complete reference of all API endpoints and their parameters:

http://127.0.0.1:8000/api/auth/send-otp

Public Endpoints (No Auth)
Auth (Legacy)
Method	Endpoint	Parameters
POST	/api/auth/send-otp	mobile (required, 10 digits)
POST	/api/auth/verify-otp	mobile (required, 10 digits), otp (required, 6 digits)
Customer Auth
Method	Endpoint	Parameters
POST	/api/customer/register	name (optional), email (optional), mobile (optional, 10 digits), password (optional, min 6), pin (optional, 4–8 digits) — at least email or mobile required
POST	/api/customer/login/otp/request	channel (required, sms|email), identifier (required — mobile for sms, email for email)
POST	/api/customer/login/otp/verify	channel (required, sms|email), identifier (required), otp (required, 6 digits)
POST	/api/customer/login/password	identifier (required), password (required)
POST	/api/customer/login/pin	identifier (required), pin (required, 4–8 digits)
Properties (Public Read)
Method	Endpoint	Parameters
GET	/api/properties	—
GET	/api/properties/{id}	id (URL param)
Admin Auth
Method	Endpoint	Parameters
POST	/api/v1/admin/login/otp/request	channel (required, sms|email), identifier (required)
POST	/api/v1/admin/login/otp/verify	channel (required, sms|email), identifier (required), otp (required, 6 digits)
POST	/api/v1/admin/login/pin	identifier (required), pin (required, 4–8 digits)
Protected Endpoints (Requires Authorization: Bearer <token>)
Customer Account
Method	Endpoint	Parameters
GET	/api/customer/me	—
POST	/api/customer/logout	—
POST	/api/customer/set-password	password (required, min 6)
POST	/api/customer/set-pin	pin (required, 4–8 digits)
Properties (Owner)
Method	Endpoint	Parameters
POST	/api/owner/properties	name (required), address (required), price_per_use (required, numeric), description (optional), city (optional), latitude (optional), longitude (optional), is_active (optional, boolean)
GET	/api/owner/properties	—
PUT	/api/owner/properties/{id}	Same as store but all optional (sometimes)
Bookings
Method	Endpoint	Parameters
POST	/api/bookings	property_id (required, integer, must exist)
GET	/api/bookings	—
POST	/api/bookings/{id}/start	id (URL param)
POST	/api/bookings/{id}/end	id (URL param)
Payments
Method	Endpoint	Parameters
POST	/api/payments/order	booking_id (required, integer, must exist)
POST	/api/payments/verify	booking_id (required, integer), payment_id (required, string)
Reviews
Method	Endpoint	Parameters
POST	/api/reviews	booking_id (required, integer), rating (required, 1–5), comment (optional)
Complaints
Method	Endpoint	Parameters
POST	/api/complaints	booking_id (required, integer), description (required), evidence (optional, image file, max 5MB)
Cleaning Jobs
Method	Endpoint	Parameters
POST	/api/owner/cleaning-jobs	property_id (required, integer), price_offer (required, numeric)
GET	/api/cleaner/cleaning-jobs	—
POST	/api/cleaner/cleaning-jobs/{id}/accept	id (URL param)
POST	/api/cleaner/cleaning-jobs/{id}/proof	proof (required, image file, max 5MB)
Admin (Protected)
Method	Endpoint	Parameters
GET	/api/v1/admin/me	—
POST	/api/v1/admin/logout	—
POST	/api/v1/admin/set-pin	pin (required, 4–8 digits)
GET	/api/v1/admin/dashboard	—
GET	/api/v1/admin/activity	—
GET	/api/v1/admin/activity/suspicious	— -->
for abhishek referece : 15 sept 26
<!-- Main API listing: http://127.0.0.1:8000/docs
OpenAPI spec: http://127.0.0.1:8000/docs.openapi
Postman collection: http://127.0.0.1:8000/docs.postman -->

# Role / Multi-Role Auth Fix

Fixes the blocker where no user could ever become an `owner` or `cleaner`
(every OTP login just created/found a `role=customer` user, and there was
no `switch-role` endpoint — so every `role:owner` / `role:cleaner` gated
route in the app was unreachable).

## What changed

- **New table `user_roles`** (`user_id`, `role`, `status`) — the set of
  roles an identity is allowed to switch into. `users.role` stays as the
  *active* role (what `RoleMiddleware` checks on every request) so no
  existing controller/middleware needed to change.
- **`HasRoles` trait** on `User`: auto-grants the user's initial role on
  creation, plus `hasRole()` / `grantRole()` helpers. This fires no
  matter which service creates the user (AuthService, CustomerAuthService,
  AdminUserController, etc.), so nothing else needed touching.
- **Migration backfill**: every existing user's current `role` is copied
  into `user_roles` so nobody already in the DB gets locked out.
- **New endpoints**, all under the existing `auth:sanctum` group (no new
  middleware, works for any logged-in identity):
  - `GET  /api/v1/auth/profile` — generic profile (API Contract Module 1;
    previously only `/customer/me` and `/admin/me` existed)
  - `GET  /api/v1/auth/roles` — roles this identity holds
  - `POST /api/v1/auth/roles` — self-register as `owner` or `cleaner`
    (granted immediately — per PDL-008, verification is encouraged, not
    mandatory)
  - `POST /api/v1/auth/switch-role` — switch the active role to one
    already held; 403 if not held yet

## Files

```
app/Models/User.php                              (modified: use HasRoles trait)
app/Models/UserRole.php                          (new)
app/Traits/HasRoles.php                          (new)
app/Http/Controllers/Api/RoleController.php      (new)
app/Http/Requests/Auth/RequestRoleRequest.php    (new)
app/Http/Requests/Auth/SwitchRoleRequest.php     (new)
routes/api.php                                   (modified: 5 new lines under the auth:sanctum group)
database/migrations/2026_09_17_000001_create_user_roles_table.php (new)
```

Copy these into your repo at the same paths, overwriting `User.php` and
`routes/api.php` (or re-apply the two small blocks by hand if you've
since edited those files further).

## Apply & test

```bash
php artisan migrate

# 1. Log in as usual (customer by default)
curl -X POST /api/v1/auth/send-otp -d mobile=9876543210
curl -X POST /api/v1/auth/verify-otp -d mobile=9876543210 -d otp=123456
# -> save the token

# 2. Request the owner role
curl -X POST /api/v1/auth/roles -H "Authorization: Bearer $TOKEN" -d role=owner

# 3. Switch into it
curl -X POST /api/v1/auth/switch-role -H "Authorization: Bearer $TOKEN" -d role=owner

# 4. Now the owner-gated routes work
curl -X POST /api/v1/owner/properties -H "Authorization: Bearer $TOKEN" -d ...
```

## Next up (phase 1, admin-UI alignment)

Not done yet, still queued from the gap analysis:
1. `/admin/customers`, `/admin/owners`, `/admin/cleaners` role-scoped
   aliases (admin UI calls these; backend only has generic `/admin/users`)
2. Cleaning job lifecycle: `reject`/`start`/`complete` for the cleaner
   side, counter-offer/assign for the owner side
3. `POST /complaints/{id}/reply`
4. KYC verify/block endpoints
5. `PUT /admin/bookings/{id}`, `GET /admin/reports/users`
