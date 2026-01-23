# Live Streaming

## Overview
- Live video streaming dengan provider: `custom_hls`, `youtube`, `twitch`
- Live chat realtime menggunakan Laravel Echo pada channel `livestream.{id}`
- Analytics dasar: views, chat_count, duration

## Backend
- Tables: `live_streams`, `live_chat_messages`, `stream_analytics`
- Controllers:
  - `LiveStreamController`: index/show/store/start/end
  - `StreamChatController`: POST `/api/streams/{liveStream}/chat`
- Events: `LiveChatMessageCreated` menyiarkan pesan ke channel `livestream.{id}`
- Routes:
  - GET `/streams`, GET `/streams/{liveStream}`
  - POST `/streams` (auth), `/streams/{liveStream}/start`, `/streams/{liveStream}/end`
  - POST `/api/streams/{liveStream}/chat` (auth)

## Frontend
- Pages:
  - `Streams/Index.vue`: daftar stream (live/scheduled/ended)
  - `Streams/Show.vue`: player (iframe YouTube atau `<video>` untuk HLS) + live chat
- Echo:
  - Subscribe: `window.Echo.channel('livestream.{id}').listen('LiveChatMessageCreated', cb)`

## Scheduling
- Isi `scheduled_at` saat membuat stream untuk penjadwalan (opsional)
- Atur `provider`, `ingest_url`, `stream_key`, `playback_url` sesuai layanan yang digunakan

## Security
- Channel livechat hanya untuk pengguna yang terautentik
- Validasi input chat dan kontrol panjang pesan

## Extensibility
- Tambah provider lain dengan field playback_url/ingest yang sesuai
- Perluas analytics untuk metrik tambahan (peak concurrents, retention)
