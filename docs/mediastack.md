# MediaStack Integration Guide

## Environment Variables
- MEDIASTACK_API_KEY: API key (32–256 chars, alphanumeric and underscore)
- MEDIASTACK_API_ENDPOINT: API endpoint (default: https://api.mediastack.com/v1/news)
- MEDIASTACK_VERIFY_SSL: Verify SSL certificate (true/false)
- MEDIASTACK_SUPPORTS_MULTI_LANGUAGE: Allow multiple languages in one request (true/false)
- MEDIASTACK_REQUEST_DELAY_MS: Delay between sequential requests (ms)
- MEDIASTACK_RETRY_TIMES: Max retries for transient errors
- MEDIASTACK_RETRY_SLEEP_MS: Base backoff (ms), exponential per attempt
- MEDIASTACK_DEFAULT_CATEGORIES: Default categories (comma or array)

## Categories
- Allowed: business, technology, sports, health, science, entertainment, general, other
- Fallback: ["general","other"] when input invalid or empty

## Retry Strategy
- Max attempts: MEDIASTACK_RETRY_TIMES (default 3)
- Backoff: exponential (base MEDIASTACK_RETRY_SLEEP_MS)
- Rate limit: 429 honored via Retry-After header (seconds)
- Errors retried: 5xx and 429; 4xx (non-429) are not retried
- After retries exhausted: throws RuntimeException

## Multi-language Handling
- If MEDIASTACK_SUPPORTS_MULTI_LANGUAGE = true: use comma-separated languages in single request
- Otherwise: sequential requests per language with delay MEDIASTACK_REQUEST_DELAY_MS
- Usage tracking increments atomically with lock; timestamps recorded for monitoring

## Security
- API key validated before use; logs and skips fetch if invalid
- HTTPS endpoint with SSL verification recommended for production

## Examples
```
MEDIASTACK_API_KEY=YOUR_KEY_32CHARS_MIN
MEDIASTACK_VERIFY_SSL=true
MEDIASTACK_SUPPORTS_MULTI_LANGUAGE=false
MEDIASTACK_REQUEST_DELAY_MS=500
MEDIASTACK_RETRY_TIMES=3
MEDIASTACK_RETRY_SLEEP_MS=1000
MEDIASTACK_DEFAULT_CATEGORIES="business,technology,general"
```
