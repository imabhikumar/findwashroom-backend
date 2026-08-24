# FindWashroom Backend Documentation

Complete documentation for the FindWashroom backend Laravel API application.

## Quick Navigation

### 📚 Documentation Files

- **[README.md](../README.md)** - Project overview and quick start
- **[SETUP.md](SETUP.md)** - Detailed setup and installation guide ⭐ START HERE
- **[AI_CONTEXT.md](AI_CONTEXT.md)** - AI/Assistant context guide for code generation
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - System architecture and design patterns
- **[API_DOCUMENT.md](API_DOCUMENT.md)** - Complete API endpoint documentation
- **[PROJECT_STATUS.md](PROJECT_STATUS.md)** - Current project status and progress
- **[DECISIONS.md](DECISIONS.md)** - Technical decisions and rationale
- **[SESSION_NOTES.md](SESSION_NOTES.md)** - Development session notes

### 🗂️ Directory Structure

- **api-collection/** - Postman API collections and tests
- **modules/** - Feature/module documentation

## Getting Started

### First Time Setup?
1. Read **[SETUP.md](SETUP.md)** for complete installation instructions
2. Review **[ARCHITECTURE.md](ARCHITECTURE.md)** to understand the codebase
3. Check **[API_DOCUMENT.md](API_DOCUMENT.md)** to understand available endpoints

### Working with the API?
- **[API_DOCUMENT.md](API_DOCUMENT.md)** - Endpoint specifications and examples
- **[../postman/](../postman/)** - Postman collections for testing

### Generating New Code?
- **[AI_CONTEXT.md](AI_CONTEXT.md)** - Full context for AI assistants

### Understanding Design Decisions?
- **[DECISIONS.md](DECISIONS.md)** - Why we chose certain technologies/patterns
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - How components interact

## Project Overview

**FindWashroom** is a property/washroom booking and management platform with:

- 🔐 Multi-role authentication (Admin, Owner, Customer, Cleaner)
- 🏠 Property management and booking system
- 💳 Integrated Razorpay payments
- ⭐ Review and rating system
- 🚨 Complaint handling
- 👔 Admin dashboard with activity logging
- 💰 Wallet and transaction management
- 🔍 Trust/reputation scoring

## Key Information at a Glance

| Aspect | Details |
|--------|---------|
| **Framework** | Laravel 13 |
| **PHP Version** | 8.3+ |
| **Database** | MySQL (SQLite for dev) |
| **Authentication** | Sanctum (Bearer tokens) |
| **API Versioning** | /api/v1/* |
| **Base URL** | http://localhost:8000/api |
| **Testing** | PHPUnit |
| **Documentation** | Scribe + OpenAPI |

## Development Workflow

### Running the App
```bash
# One-command startup (recommended)
composer run dev

# Or manually in separate terminals
php artisan serve
php artisan queue:listen
php artisan pail
npm run dev
```

### Code Quality
```bash
# Format code
./vendor/bin/pint

# Run tests
php artisan test

# Static analysis
./vendor/bin/phpstan analyze
```

### API Documentation
```bash
# Generate interactive docs
php artisan scribe:generate

# View at: http://localhost:8000/api/documentation
```

## Common Tasks

### Adding a New Endpoint
1. Create Model (if needed): `php artisan make:model ModelName -m`
2. Create Controller: `php artisan make:controller Api/ResourceController`
3. Create FormRequest: `php artisan make:request ResourceRequest`
4. Create Service: `app/Services/ResourceService.php`
5. Create Repository: `app/Repositories/ResourceRepository.php`
6. Add route in `routes/api.php`

### Running Tests
```bash
# All tests
php artisan test

# Specific test
php artisan test tests/Feature/BookingTest.php

# Watch mode
php artisan test --watch
```

### Database Maintenance
```bash
# Create migration
php artisan make:migration migration_name

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Seed data
php artisan db:seed
```

## Troubleshooting

### Common Issues
- **Port already in use**: Use `php artisan serve --port=8001`
- **Database connection error**: Check `.env` credentials
- **Autoload errors**: Run `composer dump-autoload`
- **Permission denied**: Fix storage permissions with `chmod -R 777 storage/`

See **[SETUP.md](SETUP.md)** for more solutions.

## Architecture Highlights

### Layered Architecture
```
Routes (api.php)
    ↓
Controllers (Api/*)
    ↓
Services (Services/*)
    ↓
Repositories (Repositories/*)
    ↓
Models (Models/*)
    ↓
Database
```

### Key Design Patterns
- **Service-Repository Pattern** - Clear separation of concerns
- **API Response Wrapper** - Consistent response format across all endpoints
- **Form Requests** - Centralized validation
- **Traits** - Reusable functionality (HasUUID, AuditLoggable, etc.)
- **Middleware** - Cross-cutting concerns (authentication, logging)

## Standard Response Format

All API responses follow this format:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {},
  "meta": {}
}
```

## Contributing Guidelines

### Code Style
- Follow PSR-12 coding standards
- Use PHP Pint for formatting: `./vendor/bin/pint`
- Always add type hints and return types
- Write tests for new features

### Before Committing
```bash
./vendor/bin/pint app/
php artisan test
./vendor/bin/phpstan analyze
```

## Need Help?

- 📖 Check the relevant documentation file above
- 🔍 Search in existing code for similar implementations
- 🧪 Look at test files for usage examples
- 💬 Review session notes in SESSION_NOTES.md

---

**Last Updated**: 2026-08-24  
**Project Status**: In Development  
**Current Version**: v1
