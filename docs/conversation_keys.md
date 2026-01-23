# Conversation Keys

## Storage
- Table: conversation_keys
- Fields: conversation_id, version, algorithm, encrypted_key, rotated_at
- Encryption at rest uses Laravel Crypt for wrapping base64 key material

## Access Control
- Endpoint: GET /api/secure/conversations/{conversation}/key
- Auth: session/Sanctum, participant-only
- Rate limit: 10/minute
- Response: { version, algorithm, key_b64_encrypted }
- Audit: conversation_key_access_logs records fetch/rotate with IP & UA

## Rotation
- Endpoint: POST /api/secure/conversations/{conversation}/key/rotate (creator only)
- Version increments; rotated_at set
- Clients should cache key by version; older media may require previous keys

## Client Usage
- Fetch key on conversation open, decrypt on client if wrapping is used
- Set window.__conversationKey to enable E2E encrypt/decrypt in UI

## Monitoring
- Use logs and access_logs table to monitor usage
- Add alerts on excessive fetches via rate limit metrics
