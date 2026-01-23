# Video Calls (WebRTC)

## Fitur
- One-on-one & grup (mesh awal, rekomendasi SFU untuk >6)
- Screen sharing, mute/unmute, toggle video
- Signaling menggunakan Laravel Echo (private channel conversation.{id})

## Backend
- Events: call.signal [CallSignalSent](file:///d:/PROJECT/LARAVEL/noteds/app/Events/CallSignalSent.php)
- Endpoint:
  - POST /api/messaging/conversations/{conversation}/calls/start
  - POST /api/messaging/conversations/{conversation}/calls/{session}/join
  - POST /api/messaging/conversations/{conversation}/calls/{session}/leave
  - POST /api/messaging/conversations/{conversation}/calls/signal
- Schema: call_sessions, call_participants
- Auth: Sanctum/session; rate limiting aktif

## Frontend
- Komponen: CallPanel.vue, integrasi di Messaging/Conversation.vue
- ICE servers configurable; gunakan STUN default, tambahkan TURN untuk jaringan NAT ketat

## QoS
- Adaptasi resolusi/framerate sesuai bandwidth
- Fallback audio-only bila jaringan buruk

## Keamanan
- Media path P2P via WebRTC; gunakan HTTPS & secure origins
- Validasi akses peserta percakapan sebelum signaling

## Pengujian
- Manual cross-browser: Chrome, Firefox, Safari, Edge
- Skala: uji grup kecil; untuk 15 peserta gunakan SFU (mis. Twilio/Agora/Daily)

## Rekaman (Opsional)
- Client-side MediaRecorder; server-side memerlukan SFU/provider
