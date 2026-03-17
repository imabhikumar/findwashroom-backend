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
