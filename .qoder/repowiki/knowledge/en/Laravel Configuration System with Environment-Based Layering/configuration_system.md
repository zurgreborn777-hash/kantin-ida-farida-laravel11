## Overview

This Laravel 11 application uses the standard Laravel configuration system, which employs a **two-layer approach**: environment variables (`.env` file) provide runtime-specific values, while PHP configuration files in `config/` define structure, defaults, and type casting.

## Architecture

### Configuration Loading Flow

1. **Bootstrap** (`bootstrap/app.php`) initializes the application using `Application::configure()`
2. Laravel automatically loads `.env` via the `vlucas/phpdotenv` library
3. All `config/*.php` files are loaded and their `env()` calls resolve to environment variable values with fallback defaults
4. Configuration is cached in production via `bootstrap/cache/config.php` (not committed)

### Two-Layer Pattern

Every config value follows this pattern:
```php
'value' => env('ENV_VAR_NAME', 'default_fallback')
```

- **Layer 1 (Environment)**: `.env` file holds secrets, credentials, and deployment-specific settings
- **Layer 2 (Config Files)**: PHP arrays in `config/` provide structure, type casting, and sensible defaults

## Key Configuration Files

### Application-Specific Configs

- **`config/canteen.php`** — Domain-specific settings for the canteen business logic:
  - `name`: Canteen display name
  - `latitude` / `longitude`: Geographic coordinates (cast to `float`)
  - `max_delivery_km`: Maximum delivery radius (cast to `float`)

- **`config/duitku.php`** — Payment gateway integration:
  - `merchant_code`, `api_key`: Credentials from `.env`
  - `env`: Sandbox vs production toggle
  - `callback_url`, `return_url`: Webhook and redirect URLs
  - Hardcoded API endpoints for sandbox and production

### Standard Laravel Configs

- **`config/app.php`** — Core app settings (name, debug mode, timezone, locale, encryption key)
- **`config/database.php`** — Database connections (MySQL default), Redis configuration
- **`config/services.php`** — Third-party service credentials (Postmark, AWS SES, Slack)
- **`config/auth.php`**, **`config/session.php`**, **`config/cache.php`**, **`config/filesystems.php`**, **`config/logging.php`**, **`config/mail.php`**, **`config/queue.php`** — Framework subsystem configs

### Environment Template

- **`.env.example`** — Documents all required environment variables with example values, serving as the canonical reference for deployment setup

## Conventions and Rules

### Adding New Configuration

1. **Create a dedicated config file** in `config/` for domain-specific settings (e.g., `config/canteen.php`)
2. **Use `env()` with fallback defaults** for every value that may vary between environments
3. **Apply explicit type casting** where needed (e.g., `(float) env('CANTEEN_LATITUDE', -6.168417)`)
4. **Add corresponding entries to `.env.example`** so new developers and deployments know what variables are required
5. **Never commit `.env`** — it is gitignored; only `.env.example` is tracked

### Secrets Management

- Sensitive values (API keys, database passwords) are **never hardcoded** in config files
- They are referenced exclusively via `env()` calls
- The `.env` file itself should not be shared; use `.env.example` as the template

### Environment-Specific Behavior

- `APP_ENV` controls environment detection (`local`, `production`, etc.)
- `APP_DEBUG` toggles detailed error pages
- `DUITKU_ENV` switches payment gateway between sandbox and production endpoints
- Locale is set to Indonesian (`id`) by default via `APP_LOCALE`

### Deployment Notes

- In production, run `php artisan config:cache` to compile all config into a single cached file for performance
- After caching, `env()` calls outside config files will return `null` — always use `config()` helper in application code
- The app is deployed on Railway (see `APP_URL` in `.env.example`), which injects environment variables at runtime

## Accessing Configuration in Code

```php
// Correct: use config() helper throughout application code
$name = config('canteen.name');
$apiKey = config('duitku.api_key');

// Incorrect: do NOT use env() outside of config files
$name = env('CANTEEN_NAME'); // Will break when config is cached
```
