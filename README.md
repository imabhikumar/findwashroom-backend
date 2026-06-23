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
