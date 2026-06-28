## Overview

This Laravel 11 canteen e-commerce application uses **standard Laravel error handling mechanisms** without custom exception classes or a dedicated error handling layer. Error handling is implemented through framework conventions: validation errors, HTTP aborts, try-catch blocks, and model query exceptions.

## Error Handling Approaches

### 1. Validation Errors (Primary Pattern)

The application relies heavily on Laravel's built-in validation system:

- **Request validation**: Controllers use `$request->validate([...])` which automatically throws `ValidationException` on failure
- **Error display**: Validation errors are returned via `withErrors()` and displayed in Blade templates
- **Localized messages**: Validation error messages are defined in `lang/en/validation.php` and `lang/id/validation.php` with Indonesian translations
- **Custom attributes**: The Indonesian validation file defines human-readable attribute names (`email` → `Alamat Email`, `password` → `Kata Sandi`)

Example pattern:
```php
$request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users',
]);
```

### 2. Try-Catch Blocks for External Services

External API calls (Duitku payment gateway, OpenStreetMap geocoding, OSRM routing) are wrapped in try-catch blocks:

- **AdminController**: Catches `\Exception` when calling Duitku payment API, returns JSON error response
- **HomeController**: Catches `\Throwable` for geocoding and routing service calls, gracefully degrades by returning null/error state
- **routes/web.php**: Temporary seed route catches `\Exception` during database seeding

Pattern observed:
```php
try {
    $response = Http::post($url, $params);
    // process response
} catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()]);
}
```

### 3. HTTP Aborts for Authorization

The `AdminMiddleware` uses `abort(403)` to deny unauthorized access:

```php
if (!Auth::check() || !Auth::user()->is_admin) {
    abort(403, 'Akses Ditolak. Anda bukan Admin!');
}
```

Similarly, invoice access control uses `abort(403)` when users attempt to view other users' invoices.

### 4. Model Query Exceptions

Controllers use Eloquent's `findOrFail()` and `firstOrFail()` methods which throw `ModelNotFoundException` when records don't exist:

- Used extensively in `AdminController` for menu, user, and order lookups
- Used in `HomeController` for cart item operations and order confirmation
- These exceptions are handled by Laravel's default exception handler (returns 404 responses)

### 5. Configuration Validation

Private helper methods check for required configuration before proceeding:

```php
private function duitkuConfigError(): ?string
{
    if (blank(config('duitku.merchant_code')) || blank(config('duitku.api_key'))) {
        return 'Konfigurasi Duitku belum lengkap...';
    }
    return null;
}
```

This pattern returns early with descriptive error messages when payment gateway credentials are missing.

### 6. JavaScript Error Handling

Frontend code in Blade templates includes try-catch blocks for AJAX operations:

- Cart delivery preview handles geocoding failures
- Payment flow catches network errors
- Errors are displayed to users via alert dialogs

## Architecture Observations

### No Custom Exception Classes

The application does not define any custom exception types. All error handling uses:
- PHP's base `\Exception` class
- PHP's `\Throwable` interface for broader catch coverage
- Laravel's built-in exceptions (`ValidationException`, `ModelNotFoundException`, `HttpException`)

### No Dedicated Exception Handler

The `bootstrap/app.php` file has an empty exception configuration closure:
```php
->withExceptions(function (Exceptions $exceptions) {
    //
})
```

This means the application relies entirely on Laravel's default exception handling behavior.

### No Error Views Directory

There is no `resources/views/errors/` directory, meaning the application uses Laravel's default error pages for HTTP status codes (404, 403, 500, etc.).

### Logging Configuration

The application uses Laravel's Monolog-based logging with:
- Default channel: `stack` (configured via `LOG_CHANNEL` env variable)
- Single log file: `storage/logs/laravel.log`
- Configurable log level via `LOG_LEVEL` environment variable
- Daily rotation available as alternative channel

## Developer Conventions

### When to Use Each Pattern

1. **Input validation**: Always use `$request->validate()` — never manually check input
2. **External API calls**: Wrap in try-catch with `\Exception` or `\Throwable`, return graceful error responses
3. **Authorization failures**: Use `abort(403, 'message')` in middleware or controllers
4. **Missing resources**: Use `findOrFail()` / `firstOrFail()` — let Laravel handle 404 responses
5. **Business logic errors**: Return JSON error responses with `success: false` and descriptive messages
6. **Configuration checks**: Validate required config early, return descriptive setup instructions

### Response Format Consistency

JSON error responses follow a consistent pattern:
```php
return response()->json(['success' => false, 'message' => 'Error description']);
```

Success responses include additional data:
```php
return response()->json(['success' => true, 'payment_url' => $url]);
```

### Limitations

- No structured error codes (only human-readable messages)
- No error tracking/integration (Sentry, Bugsnag, etc.)
- No custom exception hierarchy for domain-specific errors
- Exception details are exposed directly to clients in some cases (`$e->getMessage()`)
- No retry logic beyond OSRM routing (which uses `retry(1, 300)`)