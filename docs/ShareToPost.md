# Share to Post - Flow dan Implementasi

## Masalah
Berbagi link `GET /home?product_id=...&utm_*` hanya menampilkan feed, tidak membuka editor post otomatis.

## Penyebab
- Halaman `Home` tidak memproses parameter query untuk membuka `PostComposer`.
- Tidak ada props yang meneruskan data produk ke komposer untuk prefill.

## Solusi
- `PostController@index`: deteksi `product_id` + UTM, bangun `shareDraft` (title, content, link_url, preview) dan kirim ke Inertia.
- `Home.vue`: meneruskan `shareDraft` ke `PostComposer`.
- `PostComposer.vue`: auto-open komposer dan prefill form berdasarkan `shareDraft`, termasuk generate link preview.

## Rute dan Permission
- Flow memerlukan user login (Home feed).
- Parameter UTM dipertahankan di `link_url`.

## Uji
- `tests/Feature/ShareToPostTest.php`: memverifikasi props `shareDraft` dikirim saat `product_id` valid.

## Dampak
Pengguna yang membuka link share di Home akan langsung melihat editor post terbuka dengan konten relevan dan preview link produk siap dipublikasikan.
