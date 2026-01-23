# SFU Integration

## Overview
- Provider configurable via env: [config/sfu.php](file:///d:/PROJECT/LARAVEL/noteds/config/sfu.php)
- Server routes: config, token, record start/stop [routes/web.php](file:///d:/PROJECT/LARAVEL/noteds/routes/web.php#L423-L437)
- Controller: [SfuController](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/SfuController.php)

## Token
- Replace placeholder with provider SDK:
  - Twilio: AccessToken + VideoGrant
  - Agora/Daily: follow provider docs
- Do not hardcode secrets; use env variables

## Recording
- Enable with SFU_RECORDING_ENABLED=true
- Use provider APIs to start/stop recording, persist files to secure storage

## Error Handling
- Return meaningful errors; add retry/fallback logic on client

## Scaling
- Ensure TURN is configured for connectivity
- Monitor metrics and alerts via monitoring config
