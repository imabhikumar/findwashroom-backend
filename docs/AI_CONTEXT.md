# FindWashroom Backend - AI Context Guide

## Project Overview
FindWashroom Backend is a Laravel-based RESTful API for a property/washroom booking and management platform. It uses Sanctum for authentication, supports multiple user roles (admin, owner, customer, cleaner), and handles payments via Razorpay.

## Tech Stack
- **Framework**: Laravel 13
- **Database**: MySQL (config available for SQLite in dev)
- **Authentication**: Laravel Sanctum (token-based)
- **Authorization**: Role-based with middleware
- **Payments**: Razorpay integration
- **API Documentation**: Scribe + OpenAPI/Swagger
- **Frontend Build**: Vite + Tailwind CSS
- **Testing**: PHPUnit

## Architecture & Patterns

### Code Organization
- **Controllers**: `app/Http/Controllers/Api/` - API endpoints organized by domain
- **Models**: `app/Models/` - Eloquent models with relationships
- **Services**: `app/Services/` - Business logic layer
- **Repositories**: `app/Repositories/` - Data access abstraction
- **Requests**: `app/Http/Requests/` - Form validation classes
- **Traits**: `app/Traits/` - Reusable functionality (HasUUID, AuditLoggable, ApiResponse)
- **Middleware**: `app/Http/Middleware/` - Request processing
- **Tests**: `tests/Feature/` and `tests/Unit/` - Test suites

### Design Patterns
- **Service-Repository Pattern**: Controllers → Services → Repositories → Models
- **API Response Wrapper**: All responses wrapped in standard JSON format with success/message/data/meta fields
- **Form Requests**: Centralized validation using Laravel Form Requests
- **Soft Deletes**: Enabled on major entities (User, Property, Booking, etc.)
- **UUID as Route Key**: Models use UUID instead of ID for API routes

## Response Format
```json
{
  "success": true/false,
  "message": "Human-readable message",
  "data": {} | [],
  "meta": {} // Optional pagination, counts, etc.
}
```

## Naming Conventions
- **Controllers**: `*Controller` (singular resource: `UserController`, `BookingController`)
- **Models**: PascalCase singular (`User`, `Booking`, `Property`)
- **Tables**: snake_case plural (`users`, `bookings`, `properties`)
- **Methods**: camelCase actions (`index`, `store`, `show`, `update`, `destroy`)
- **Routes**: kebab-case (`/v1/customer/set-password`, `/v1/properties/{id}/service-units`)
- **Variables**: camelCase (`$userId`, `$propertyId`)

## Authentication & Authorization
- **Token-based**: Sanctum generates bearer tokens (no JWT required)
- **Guards**: 'sanctum' guard used with middleware
- **Roles**: admin, owner, customer, cleaner (stored in `role` column)
- **Middleware**: `auth:sanctum` protects routes; `role:admin` restricts to roles
- **OTP Auth**: Mobile-first authentication via OTP, followed by optional password/PIN setup

## Database Design
- **UUID Primary Keys**: All tables have UUID columns for public APIs
- **Soft Deletes**: Core entities preserve data
- **Timestamps**: created_at/updated_at on all models
- **Audit Logging**: AuditLog model tracks entity changes
- **Relationships**:
  - User has many Properties (as owner), Bookings, Reviews, Complaints
  - Property has many Bookings, ServiceUnits, Products
  - Booking has one Payment, relates to Review/Complaint

## Key Entities & Relationships
- **User**: Unified user model with role differentiation
- **Property**: Washroom/bathroom properties owned by owners
- **Booking**: Time-slot reservations by customers
- **Payment**: Razorpay payment records
- **Review**: Customer ratings for properties
- **Complaint**: Issue reports
- **CleaningJob**: Assigned cleaning tasks
- **ServiceUnit**: Amenities/features at a property
- **Product**: Inventory items for purchase
- **Wallet**: User balance for transactions
- **TrustEvent**: Events affecting user trust score
- **AuditLog**: Change tracking

## API Versioning
- **Current Version**: v1 (routes prefixed with `/api/v1/`)
- **Pattern**: `/api/v1/{resource}/{action}`
- **Public Endpoints**: Property browsing, OTP auth
- **Protected Endpoints**: Require `Authorization: Bearer <token>` header

## Coding Standards
- **Language**: PHP 8.3+
- **Formatting**: PSR-12 (use PHP Pint)
- **Type Hints**: Strict type declarations on all methods
- **Return Types**: Always specify return types
- **Immutability**: Use `readonly` on constructor properties
- **Validation**: Use Form Request classes, not manual validation
- **Errors**: Throw exceptions; let middleware handle responses

## Configuration Files
- `.env`: Environment variables (DB connection, mail, Razorpay credentials)
- `config/app.php`: Application name, timezone, locale
- `config/database.php`: Database connections
- `config/sanctum.php`: Token expiration, stateful domains
- `config/services.php`: Third-party service credentials (Razorpay)

## Common Commands
```bash
# Setup
composer install
php artisan key:generate
php artisan migrate

# Development
npm install
npm run dev
php artisan serve
php artisan queue:listen

# Testing
php artisan test
php artisan test --filter=TestName

# Code Quality
./vendor/bin/pint  # Format code
./vendor/bin/phpstan analyze  # Static analysis

# API Documentation
php artisan scribe:generate
```

## Known Limitations & TODO Items
- Role/permission middleware partially implemented
- Activity logging not fully integrated
- API rate limiting pending
- Email templates pending
- Search optimization pending
- Token refresh mechanism pending

## Before Generating Code:
1. Check domain model relationships in `app/Models/`
2. Review existing service patterns in `app/Services/`
3. Use FormRequest validation
4. Return wrapped responses using ApiResponse trait
5. Add audit logging for sensitive operations
6. Follow naming conventions exactly