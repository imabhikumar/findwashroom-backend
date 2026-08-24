# FindWashroom Backend API Documentation

This document reflects the currently verified API routes for the backend and is ready to use for development and testing.

Base URL:
- http://127.0.0.1:8000/api

Authentication:
- Most protected endpoints require a Sanctum bearer token.
- Send the token as:
  - Header: Authorization: Bearer <token>

Common response shape:
```json
{
  "success": true,
  "message": "OK",
  "data": {}
}
```

Error response shape:
```json
{
  "success": false,
  "message": "Forbidden.",
  "data": {}
}
```

## 1. Health Check

### GET /hello
Public endpoint.

Example:
```bash
curl http://127.0.0.1:8000/api/hello
```

## 2. Authentication

### POST /auth/send-otp
Public.
Request body:
```json
{
  "mobile": "9876543210"
}
```

### POST /auth/verify-otp
Public.
Request body:
```json
{
  "mobile": "9876543210",
  "otp": "123456"
}
```

### POST /customer/register
Public.
Request body:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "mobile": "9876543210",
  "password": "secret123",
  "pin": "1234"
}
```

### POST /customer/login/otp/request
Public.
Request body:
```json
{
  "channel": "sms",
  "identifier": "9876543210"
}
```

### POST /customer/login/otp/verify
Public.
Request body:
```json
{
  "channel": "sms",
  "identifier": "9876543210",
  "otp": "123456"
}
```

### POST /customer/login/password
Public.
Request body:
```json
{
  "identifier": "john@example.com",
  "password": "secret123"
}
```

### POST /customer/login/pin
Public.
Request body:
```json
{
  "identifier": "john@example.com",
  "pin": "1234"
}
```

## 3. Customer Account (Authenticated)

### GET /customer/me
Authenticated.

### POST /customer/logout
Authenticated.

### POST /customer/set-password
Authenticated.
Request body:
```json
{
  "password": "newsecret123"
}
```

### POST /customer/set-pin Tested By Abhi: not working
Authenticated.
Request body:
```json
{
  "pin": "5678"
}
```

## 4. Properties

### GET /properties
Public.
Returns the list of public properties.

### GET /properties/{id}
Public.
Returns the details of a specific property by ID.

### POST /owner/properties
Authenticated owner.
Creates a new property for the logged-in owner.

Request body:
```json
{
  "name": "Luxury Washroom",
  "address": "123 Main Street",
  "price_per_use": 50,
  "description": "Clean and modern washroom",
  "city": "Mumbai",
  "latitude": 19.0760,
  "longitude": 72.8777,
  "is_active": true
}
```

### GET /owner/properties
Authenticated owner.
Returns the properties owned by the authenticated user.

### PUT /owner/properties/{id}
Authenticated owner.
Updates an existing property owned by the authenticated user.

Example request body:
```json
{
  "name": "Updated Washroom",
  "address": "456 New Street",
  "price_per_use": 80,
  "is_active": false
}
```

## 5. Bookings

### POST /bookings
Authenticated.
Request body:
```json
{
  "property_id": 1
}
```

### GET /bookings
Authenticated.

### POST /bookings/{id}/start
Authenticated.

### POST /bookings/{id}/end
Authenticated.

## 6. Payments

### POST /payments/order
Authenticated.
Request body:
```json
{
  "booking_id": 1
}
```

### POST /payments/verify
Authenticated.
Request body:
```json
{
  "booking_id": 1,
  "order_id": "order_123",
  "payment_id": "pay_123",
  "signature": "signature_value"
}
```

## 7. Reviews and Complaints

### POST /reviews
Authenticated.
Request body:
```json
{
  "booking_id": 1,
  "rating": 5,
  "comment": "Excellent service"
}
```

### POST /complaints
Authenticated.
Request body:
```json
{
  "booking_id": 1,
  "description": "Issue with cleanliness"
}
```

## 8. Cleaning Jobs

### POST /owner/cleaning-jobs
Authenticated owner.
Request body:
```json
{
  "property_id": 1,
  "price_offer": 100
}
```

### GET /cleaner/cleaning-jobs
Authenticated cleaner.

### POST /cleaner/cleaning-jobs/{id}/accept
Authenticated cleaner.

### POST /cleaner/cleaning-jobs/{id}/proof
Authenticated cleaner.
Request body:
```json
{
  "proof": "binary-file"
}
```

## 9. Admin APIs

### POST /v1/admin/login/otp/request
Public admin login request.

### POST /v1/admin/login/otp/verify
Public admin login verify.

### POST /v1/admin/login/pin
Public admin pin login.

### GET /v1/admin/me
Authenticated admin.

### POST /v1/admin/logout
Authenticated admin.

### POST /v1/admin/set-pin
Authenticated admin.

### GET /v1/admin/dashboard
Authenticated admin.

### GET /v1/admin/activity
Authenticated admin.

### GET /v1/admin/activity/suspicious
Authenticated admin.

## 10. Wallet APIs

### GET /v1/wallet
Authenticated.

### GET /v1/wallet/transactions
Authenticated.

### POST /v1/wallet/payout
Authenticated.

### GET /v1/admin/wallets
Authenticated admin.

### PUT /v1/admin/wallets/{id}/status
Authenticated admin.

### POST /v1/admin/wallets/{id}/adjust
Authenticated admin.

## 11. Service Units and Products

### GET /v1/properties/{propertyId}/service-units
Public.

### GET /v1/properties/{propertyId}/service-units/available
Public.

### GET /v1/service-units/types
Public.

### GET /v1/service-units/{id}
Public.

### GET /v1/properties/{propertyId}/products
Public.

### GET /v1/properties/{propertyId}/products/available
Public.

### GET /v1/products/categories
Public.

### GET /v1/products/{id}
Public.

### POST /v1/partner/service-units
Authenticated partner.

### PUT /v1/partner/service-units/{id}
Authenticated partner.

### PUT /v1/partner/service-units/{id}/status/{status}
Authenticated partner.

### POST /v1/partner/products
Authenticated partner.

### PUT /v1/partner/products/{id}
Authenticated partner.

### POST /v1/partner/products/{id}/stock
Authenticated partner.

## 12. Trust APIs

### GET /v1/trust/score
Authenticated.

### GET /v1/trust/badges
Authenticated.

### GET /v1/trust/summary
Authenticated.

### GET /v1/trust/property/{propertyId}/badges
Authenticated.

## 13. Audit Logs

### GET /v1/audit-logs
Authenticated admin.

### GET /v1/audit-logs/{id}
Authenticated admin.

## Notes
- The routes above were verified through Laravel route listing after clearing stale route cache.
- A regression test was added at tests/Feature/ApiRouteRegistrationTest.php to keep the core routes covered.
