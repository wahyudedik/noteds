# Content Recommendations

## Endpoints
- GET `/api/recommendations/feed?limit=30` — feed personal
- GET `/api/recommendations/posts/{post}/related?limit=10` — related posts
- GET `/api/recommendations/users/similar?limit=10` — similar users
- GET `/api/recommendations/trending?limit=20` — trending content

## Scoring
- Feed: kombinasi overlap tags (hashtags), engagement rate 7 hari, recency boost
- Related: overlap tags dan engagement
- Similar users: overlap tags dan follower count
- Trending: skor views dan engagement 7 hari

## Interest Vector
- Dibentuk dari konten pengguna dan interaksi (vote, comment, bookmark) → hashtag counts
- Disimpan sementara dengan cache 10 menit per pengguna

## Performance
- Cache: feed dan trending disimpan sementara untuk mempercepat respon
- Limitkan kandidat dan gunakan query sederhana berbasis indeks

## Privacy
- Menghindari rekomendasi dari pengguna yang di-follow untuk memunculkan penemuan konten baru
