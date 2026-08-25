# P0 Test Gaps

These gaps are confirmed from the current repository and are intentionally not implemented in production code or tests.

| Test/Requirement | Requirement Source | Current API/Implementation | Status | Reason |
|---|---|---|---|---|
| REQ-DOC-001 | User task: latest BRD/FRD/PDL/SRS/RBAC/Database Schema | No files with these requirement names are present in the workspace | BLOCKED | The repository provides OpenAPI/API/status documentation, but not the named approved source documents |
| WAL-002 | User task: own wallet and ledger consistency | `WalletController::summary` and `transactions` use `Transaction`; no `App\\Models\\Transaction` exists, and the route does not scope to the authenticated user | BLOCKED | Auth boundary is testable, but authenticated wallet behavior cannot be safely executed or asserted against the current implementation |
| WAL-003 | User task: valid payout and ledger consistency | Actual route is `POST /api/v1/wallet/request-payout`; controller requires `user_id`; `Payout` model is absent while `payouts` schema requires `payout_method` | BLOCKED | The implemented route/controller contract is inconsistent with the documented own-wallet flow |
| PAY-002 | User task: valid payment order and verification | Payment service delegates to Razorpay and requires a completed, payable booking; no test gateway contract/configuration is available | BLOCKED | A real provider must not be called, and the current repository exposes no confirmed fake integration contract |
| RBAC-002 | User task: customer/cleaner/admin permission matrix | Only `role:admin` middleware is registered for admin routes; owner/cleaner routes have no role middleware and no Spatie package is installed | BLOCKED | The broader approved matrix cannot be verified without inventing authorization behavior |
| CLN-003 | User task: invalid proof submission | Proof endpoint uses image upload, but no additional proof rules beyond image/max validation are documented | BLOCKED | Only valid image upload is asserted; unsupported proof semantics are not invented |
| PROP-002 | User task: supported property filters/search | `PropertyController::index` accepts no filter/search parameters | BLOCKED | Advanced search/filtering is listed as pending in project status |
| UNIT-002 | OpenAPI ServiceUnit availability | `ServiceUnit::bookingServiceUnits()` references missing `App\\Models\\BookingServiceUnit` | BLOCKED | Service-unit serialization reaches the missing related model when calculating occupancy |