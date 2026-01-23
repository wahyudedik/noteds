# Contoh Payload Notifikasi (Database)

Semua notifikasi menggunakan field `data.type` sebagai penanda tipe. Berikut contoh payload untuk konsistensi backend:

## Orders
- Pesanan Baru (`new_order`)
```json
{ "type": "new_order", "order_id": 123, "title": "Pesanan Baru", "message": "Order #ORD-20260124-ABCD dibuat", "order_number": "ORD-20260124-ABCD" }
```
- Order Dibuat (`order_created`)
```json
{ "type": "order_created", "order_id": 123, "title": "Order Dibuat", "message": "Order #ORD-20260124-ABCD berhasil dibuat" }
```
- Order Dibatalkan (`order_cancelled`)
```json
{ "type": "order_cancelled", "order_id": 123, "title": "Order Dibatalkan", "message": "Order #ORD-20260124-ABCD dibatalkan", "reason": "payment_timeout" }
```
- Status Order Diupdate (`order_status_update`)
```json
{ "type": "order_status_update", "order_id": 123, "title": "Status Order Diperbarui", "message": "Order #ORD-20260124-ABCD → completed", "status": "completed" }
```
- Pembayaran Gagal (`payment_failed`)
```json
{ "type": "payment_failed", "order_id": 123, "title": "Pembayaran Gagal", "message": "Transaksi gagal, silakan coba lagi", "failure_reason": "deny" }
```

## Withdrawals
- Status Penarikan (`withdrawal_status`)
```json
{ "type": "withdrawal_status", "withdrawal_id": 99, "title": "Status Penarikan", "message": "Penarikan Rp 250.000 → approved", "status": "approved", "user_type": "seller" }
```
- Permintaan Penarikan (`withdrawal_request`)
```json
{ "type": "withdrawal_request", "withdrawal_id": 99, "title": "Permintaan Penarikan", "message": "User meminta penarikan Rp 250.000", "user_type": "clipper" }
```

## Products
- Produk Disetujui (`product_approved`)
```json
{ "type": "product_approved", "product_id": 45, "title": "Produk Disetujui", "message": "Produk Anda telah disetujui" }
```
- Produk Ditolak (`product_rejected`)
```json
{ "type": "product_rejected", "product_id": 45, "title": "Produk Ditolak", "message": "Produk ditolak: deskripsi tidak lengkap", "reason": "incomplete_description" }
```
- Produk Dibuat (`product_created`)
```json
{ "type": "product_created", "product_id": 45, "title": "Produk Baru", "message": "Produk baru dibuat oleh seller X" }
```

## Campaigns & Clips
- Kampanye Baru (`new_campaign`)
```json
{ "type": "new_campaign", "campaign_id": 77, "title": "Kampanye Baru", "message": "Kampanye tersedia untuk clippers" }
```
- Kampanye Dibuat (`campaign_created`)
```json
{ "type": "campaign_created", "campaign_id": 77, "title": "Kampanye Dibuat", "message": "Kampanye berhasil dibuat" }
```
- Kampanye Ditangguhkan (`campaign_suspended`)
```json
{ "type": "campaign_suspended", "campaign_id": 77, "title": "Kampanye Ditangguhkan", "message": "Ditangguhkan: pelanggaran kebijakan", "reason": "policy_violation" }
```
- Clip Disetujui (`clip_approved`)
```json
{ "type": "clip_approved", "clip_id": 555, "title": "Clip Disetujui", "message": "Clip Anda telah disetujui" }
```
- Clip Ditolak (`clip_rejected`)
```json
{ "type": "clip_rejected", "clip_id": 555, "title": "Clip Ditolak", "message": "Clip ditolak: kualitas rendah", "reason": "low_quality" }
```
- View Terverifikasi (`view_validated`)
```json
{ "type": "view_validated", "clip_id": 555, "title": "View Terverifikasi", "message": "View Anda diverifikasi" }
```
- Kecurangan Terdeteksi (`fraud_detected`)
```json
{ "type": "fraud_detected", "clip_id": 555, "title": "Kecurangan Terdeteksi", "message": "Anomali terdeteksi pada clip", "stability_score": 0.32, "reason": "traffic_spike" }
```

## Brand
- Brand Disetujui (`brand_approved`)
```json
{ "type": "brand_approved", "title": "Brand Disetujui", "message": "Pendaftaran brand Anda disetujui" }
```
- Brand Ditolak (`brand_rejected`)
```json
{ "type": "brand_rejected", "title": "Brand Ditolak", "message": "Pendaftaran brand ditolak", "reason": "documents_missing" }
```

## Support
- Tiket Baru (`new_support_ticket`)
```json
{ "type": "new_support_ticket", "ticket_id": 12, "title": "Tiket Dukungan Baru", "message": "User membuat tiket baru" }
```
- Balasan Tiket (`support_ticket_response`)
```json
{ "type": "support_ticket_response", "ticket_id": 12, "title": "Balasan Tiket", "message": "Ada balasan pada tiket Anda", "is_admin_response": true }
```
- Status Tiket Diperbarui (`support_ticket_status_update`)
```json
{ "type": "support_ticket_status_update", "ticket_id": 12, "title": "Status Tiket Diperbarui", "message": "Tiket → resolved", "old_status": "open", "new_status": "resolved" }
```

## Sosial
- Komentar Baru (`new_comment`)
```json
{ "type": "new_comment", "post_id": 88, "title": "Komentar Baru", "message": "Pengguna X mengomentari postingan Anda" }
```
- Pengikut Baru (`new_follow`)
```json
{ "type": "new_follow", "follower_id": 11, "title": "Pengikut Baru", "message": "Pengguna X mulai mengikuti Anda" }
```

## Wallet/Top Up
- Top Up Berhasil (`topup_success`)
```json
{ "type": "topup_success", "top_up_id": 999, "title": "Top Up Berhasil", "message": "Wallet bertambah Rp 100.000", "amount": 100000 }
```
