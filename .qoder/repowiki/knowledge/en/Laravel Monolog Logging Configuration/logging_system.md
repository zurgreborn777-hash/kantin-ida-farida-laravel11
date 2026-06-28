## Overview
The application uses Laravel's built-in logging system, which is powered by the **Monolog** library. Logging is configured centrally in `config/logging.php` and controlled via environment variables defined in `.env` (or `.env.example`).

## Key Components
- **Framework**: Laravel 11 with Monolog.
- **Configuration File**: `config/logging.php` defines all log channels, drivers, and handlers.
- **Default Channel**: The default channel is set to `stack`, which aggregates other channels. In the provided `.env.example`, this stack is configured to use the `single` channel.
- **Log Storage**: Logs are written to `storage/logs/laravel.log` when using the `single` or `daily` drivers.

## Log Channels & Drivers
The configuration supports several channels:
- **stack**: Aggregates multiple channels (configured via `LOG_STACK`).
- **single**: Writes all logs to a single file (`storage/logs/laravel.log`).
- **daily**: Rotates log files daily (retention configured via `LOG_DAILY_DAYS`, default 14).
- **slack**: Sends critical logs to a Slack webhook.
- **stderr/syslog/errorlog**: Standard output and system logging integrations.
- **null**: Discards all logs (useful for testing or deprecation suppression).

## Log Levels
- The default log level is controlled by the `LOG_LEVEL` environment variable.
- In `.env.example`, `LOG_LEVEL` is set to `error`, meaning only errors and more severe messages are logged in production-like environments.
- Deprecation warnings are handled separately via `LOG_DEPRECATIONS_CHANNEL` (set to `null` by default to ignore them).

## Usage Patterns
- No explicit usage of the `Log` facade or `logger()` helper was found in the application code (Controllers, Models, etc.).
- This suggests that logging is currently passive, relying on Laravel's internal error handling and exception reporting rather than custom business logic logging.

## Developer Conventions
- **Environment Control**: Always use `env('LOG_CHANNEL')` and `env('LOG_LEVEL')` to adjust logging behavior without changing code.
- **Structured Logging**: The `single` and `daily` channels have `replace_placeholders` enabled, allowing for basic context interpolation in log messages.
- **Production Safety**: The default `.env.example` sets `LOG_LEVEL=error` to prevent verbose debug logs from filling up disk space in production.