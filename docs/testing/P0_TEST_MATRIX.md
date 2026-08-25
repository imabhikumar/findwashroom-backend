# P0 Test Matrix

The matrix maps implemented routes and business flows to `tests/Feature/P0ApiBusinessTest.php`.

| Test ID | Requirement/reference | API/flow | Role | Preconditions | Test data/action | Expected HTTP | Expected business/database result |
|---|---|---|---|---|---|---|---|
| AUTH-001 | API_DOCUMENT authentication | Customer register and account | Public/customer | None | Register valid customer, call `me` | 201, 200 | Customer exists with customer role and usable token |
| AUTH-002 | API_DOCUMENT authentication | Password login and protected endpoint | Customer/none | Existing customer | Wrong password; unauthenticated `me` | 401 | No authenticated access |
| AUTH-003 | API_DOCUMENT logout | Customer logout | Customer | Active token | Logout | 200 | Current token is revoked |
| PROP-001 | API_DOCUMENT Properties | Public list/detail | Public | Active and inactive properties | List, detail, missing ID | 200, 404 | Active property is listed; missing property is rejected |
| BOOK-001 | API_DOCUMENT Bookings | Create/start/end booking | Customer | Active property | Create, start, end | 200 | Booking progresses pending -> active -> completed with timestamps and amount |
| RBAC-001 | RBAC rule in task; BookingService | Owner self-booking and ownership | Owner/customer | Owned property and customer booking | Owner books own property; other user starts booking | 400, 404 | Self-booking creates no row; other customer cannot operate booking |
| BOOK-002 | Booking validation/service | Invalid, duplicate, invalid transition | Customer | Active property and pending booking | Missing/invalid property, duplicate create, end pending | 422, 400 | Invalid operations do not create extra booking |
| UNIT-001 | OpenAPI ServiceUnit | Listing, availability, types, detail | Public | Service type and units | Query public endpoints and missing ID | 200, 404 | Available endpoint returns only available unit |
| REV-001 | API_DOCUMENT Reviews | Completed booking review | Customer | Completed own booking | Submit rating/comment twice | 200, 400 | Review exists and property aggregate is updated; duplicate rejected |
| CMP-001 | API_DOCUMENT Complaints | Completed booking complaint | Customer | Completed own booking within 24 hours | Missing description, submit evidence, repeat | 422, 200, 400 | Pending complaint exists; duplicate open complaint rejected |
| CLN-001 | API_DOCUMENT Cleaning Jobs | Create, accept, proof | Owner/cleaner | Owner property and cleaner | Create job, accept, upload image proof | 200 | Job becomes assigned then completed with cleaner |
| CLN-002 | CleaningJobService ownership | Non-owner job creation | Other owner | Property owned by another owner | Create job against foreign property | 404 | No cleaning job is created |
| PAY-001 | API_DOCUMENT Payments | Protected order validation | Customer/none | No gateway call | Unauthenticated and missing booking | 401, 422 | Gateway is not invoked; no payment state is changed |
| WAL-001 | Actual routes in `routes/api.php` | Wallet authentication boundary | None | None | Call wallet endpoints unauthenticated | 401 | Protected routes reject unauthenticated callers |