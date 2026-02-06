# Sentry Error Tracking Setup

## Installation

1. Install Sentry Laravel SDK:
```bash
composer require sentry/sentry-laravel
```

2. Publish Sentry configuration:
```bash
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

3. Add to `.env`:
```env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn@sentry.io/project-id
SENTRY_RELEASE=your-app-version
SENTRY_TRACES_SAMPLE_RATE=0.1
``` 

## Configuration

The Sentry configuration file is located at `config/sentry.php`.

## Usage

Sentry will automatically capture:
- All exceptions and errors
- Database connection errors

## Monitoring

Monitor these critical errors in Sentry:
- Failed auto transfers (after all retries)
- View tracking API down > 1 hour
- Database connection errors
- Payment gateway errors
- Security breaches
