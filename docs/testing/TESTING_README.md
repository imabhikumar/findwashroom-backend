# P0 Testing

## Run

```text
php artisan test
php artisan test tests/Feature/P0ApiBusinessTest.php
php artisan test --filter=P0ApiBusinessTest
```

PHPUnit 12 is configured through Laravel. `phpunit.xml` sets `APP_ENV=testing`, SQLite `:memory:`, synchronous queues, array cache, and array mail. `RefreshDatabase` isolates each feature test.

The suite uses the repository's existing `UserFactory` and real Eloquent models. OTP and external payment-provider success flows are not called; gateway-dependent payment coverage is documented in `P0_TEST_GAPS.md`. Uploaded cleaning proof and complaint evidence use Laravel fake image files.

## Structure

- `tests/Feature/P0ApiBusinessTest.php`: executable P0 API/business coverage
- `tests/Feature/`: existing feature/regression tests
- `docs/testing/P0_TEST_MATRIX.md`: requirement-to-test mapping
- `docs/testing/P0_TEST_GAPS.md`: confirmed implementation/documentation gaps