# Rekomendasi Fitur: Iklan Catatan Unggulan (Featured Notes)

## 📋 Konsep Fitur

Fitur **Iklan Catatan Unggulan** memungkinkan seller untuk mempromosikan note mereka dengan bayar per iklan untuk mendapatkan visibility lebih tinggi di platform.

## 🎯 Tujuan

- Meningkatkan revenue platform melalui iklan
- Memberikan opsi kepada seller untuk meningkatkan visibility note mereka
- Menampilkan note unggulan di tempat strategis (landing page, marketplace, popup)

## 📍 Lokasi Tampilan Iklan

### 1. **Landing Page (Homepage)**
- **Hero Banner Section**: Note unggulan di bagian atas landing page
- **Featured Notes Carousel**: Slider/carousel menampilkan 3-5 note unggulan
- **Featured Section**: Grid dedicated untuk featured notes

### 2. **Marketplace Page**
- **Top Banner**: Banner promosi di atas search/filter
- **Featured Grid**: Section khusus "Featured Notes" di atas daftar note biasa 
- **Sidebar Widget**: Featured notes di sidebar (optional)

### 3. **Popup Modal**
- **Welcome Popup**: Popup untuk new users menampilkan featured notes
- **Exit Intent Popup**: Popup saat user ingin keluar halaman
- **Interstitial**: Popup di tengah browsing (tidak terlalu mengganggu)

## ⚙️ Fitur Teknis

### Database Schema

```php
// Migration: create_featured_notes_table.php
Schema::create('featured_notes', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
    $table->foreignUuid('user_id')->constrained()->onDelete('cascade'); // Seller
    $table->enum('location', ['landing_hero', 'landing_carousel', 'marketplace_banner', 'marketplace_grid', 'popup_welcome', 'popup_exit', 'popup_interstitial'])->default('marketplace_grid');
    $table->date('start_date');
    $table->date('end_date');
    $table->integer('duration_days'); // Berapa hari iklan ditampilkan
    $table->decimal('price', 12, 2); // Total harga iklan
    $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
    $table->integer('clicks')->default(0); // Tracking clicks
    $table->integer('impressions')->default(0); // Tracking views
    $table->text('admin_notes')->nullable();
    $table->timestamps();
    
    $table->index(['status', 'location', 'start_date', 'end_date']);
});
```

### Model: FeaturedNote

```php
class FeaturedNote extends Model
{
    protected $fillable = [
        'note_id',
        'user_id',
        'location',
        'start_date',
        'end_date',
        'duration_days',
        'price',
        'status',
        'clicks',
        'impressions',
        'admin_notes',
    ];
    
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'clicks' => 'integer',
            'impressions' => 'integer',
        ];
    }
    
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }
}
```

## 💰 Pricing Model (Rekomendasi)

### Pricing per Lokasi & Durasi

| Lokasi | Durasi | Harga (Rekomendasi) |
|--------|--------|---------------------|
| **Landing Hero** | 7 hari | Rp 150.000 |
| **Landing Hero** | 14 hari | Rp 280.000 |
| **Landing Hero** | 30 hari | Rp 500.000 |
| **Landing Carousel** | 7 hari | Rp 100.000 |
| **Landing Carousel** | 14 hari | Rp 180.000 |
| **Landing Carousel** | 30 hari | Rp 350.000 |
| **Marketplace Banner** | 7 hari | Rp 75.000 |
| **Marketplace Banner** | 14 hari | Rp 140.000 |
| **Marketplace Banner** | 30 hari | Rp 250.000 |
| **Marketplace Grid** | 7 hari | Rp 50.000 |
| **Marketplace Grid** | 14 hari | Rp 90.000 |
| **Marketplace Grid** | 30 hari | Rp 150.000 |
| **Popup Welcome** | 7 hari | Rp 100.000 |
| **Popup Exit Intent** | 7 hari | Rp 80.000 |
| **Popup Interstitial** | 7 hari | Rp 60.000 |

**Note:** Pricing bisa di-setting di admin settings, tidak hardcode.

## 🔄 Flow Sistem

### 1. **Seller Request Featured**
- Seller memilih note yang ingin di-featured
- Pilih lokasi iklan (landing hero, marketplace banner, dll)
- Pilih durasi (7, 14, atau 30 hari)
- Sistem kalkulasi harga otomatis
- Bayar dari wallet (deduct langsung)
- Status: `pending` (menunggu admin approval)

### 2. **Admin Approval**
- Admin review request
- Bisa approve/reject dengan catatan
- Jika approve: status jadi `active`, set `start_date` dan `end_date`
- Jika reject: refund ke wallet seller

### 3. **Auto Display**
- Sistem query featured notes aktif berdasarkan:
  - `status = 'active'`
  - `start_date <= now()`
  - `end_date >= now()`
  - `location` sesuai halaman
- Random atau priority order (bisa di-setting)

### 4. **Auto Expire**
- Scheduled command: `php artisan featured:expire`
- Check setiap hari untuk expired featured notes
- Update status jadi `expired`
- Bisa extend dengan payment baru

### 5. **Analytics Tracking**
- Track `impressions` (setiap kali featured note ditampilkan)
- Track `clicks` (setiap kali user klik featured note)
- Dashboard untuk seller melihat performance iklan

## 📊 Admin Features

### Admin Dashboard
- List semua featured notes requests
- Filter by status, location, date range
- Approve/Reject dengan notes
- Set pricing per location & duration
- View analytics (total revenue, active ads, dll)

### Admin Settings
- Pricing per location & duration (configurable)
- Max featured notes per location (e.g., max 3 di landing carousel)
- Auto-approve option (optional, untuk trusted sellers)
- Featured notes expiry reminder (email to seller)

## 🎨 UI/UX Recommendations

### Landing Page
```html
<!-- Hero Banner Section -->
<section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-6">✨ Featured Notes</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($featuredNotes as $featured)
                <div class="bg-white rounded-lg p-6 shadow-xl">
                    <!-- Note card -->
                </div>
            @endforeach
        </div>
    </div>
</section>
```

### Marketplace Page
```html
<!-- Featured Banner -->
@if($featuredBanner)
    <div class="mb-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <span class="inline-block px-3 py-1 bg-white rounded-full text-xs font-semibold text-orange-600 mb-2">⭐ FEATURED</span>
                <h3 class="text-2xl font-bold text-white">{{ $featuredBanner->note->title }}</h3>
                <p class="text-white/90 mt-2">{{ Str::limit($featuredBanner->note->summary, 100) }}</p>
            </div>
            <a href="{{ route('marketplace.show', $featuredBanner->note) }}" 
               class="ml-6 px-6 py-3 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50 transition">
                View Note →
            </a>
        </div>
    </div>
@endif
```

### Popup Modal
```html
<!-- Welcome Popup dengan Featured Notes -->
<div x-data="{ show: true }" x-show="show" class="fixed inset-0 z-50">
    <div class="bg-black/50 fixed inset-0" @click="show = false"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
            <h3 class="text-2xl font-bold mb-4">🎉 Welcome to Noteds!</h3>
            <p class="text-gray-600 mb-6">Check out these featured notes:</p>
            <div class="grid grid-cols-2 gap-4">
                @foreach($featuredPopupNotes as $featured)
                    <!-- Note card -->
                @endforeach
            </div>
            <button @click="show = false" class="mt-6 w-full px-4 py-2 bg-gray-100 rounded-lg">Close</button>
        </div>
    </div>
</div>
```

## 🔐 Business Rules

1. **Satu note hanya bisa featured di 1 lokasi pada waktu yang sama**
   - Mencegah spam/over-promotion
   - User experience lebih baik

2. **Max featured notes per location**
   - Landing Hero: Max 1
   - Landing Carousel: Max 5
   - Marketplace Banner: Max 1
   - Marketplace Grid: Max 6
   - Popup: Max 3

3. **Durasi minimum: 7 hari**
   - Durasi maksimum: 30 hari
   - Bisa extend dengan payment baru

4. **Approval Required**
   - Semua featured requests perlu admin approval
   - Admin bisa reject dengan alasan
   - Auto-approve option untuk premium sellers (optional)

5. **Refund Policy**
   - Jika reject: Full refund
   - Jika cancel sebelum start: Full refund
   - Jika cancel setelah start: Pro-rated refund (optional)

## 📈 Analytics & Reporting

### Seller Dashboard
- Active featured notes
- Total impressions & clicks
- CTR (Click-Through Rate)
- Revenue generated dari featured note
- ROI (Return on Investment)

### Admin Dashboard
- Total featured notes revenue
- Active featured notes count
- Popular locations
- Average CTR per location
- Top performing featured notes

## 🚀 Implementation Status

### Phase 1 (MVP) ✅ **COMPLETE**
1. ✅ Database migration & model
2. ✅ Featured notes request form (seller)
3. ✅ Admin approval system
4. ✅ Display di marketplace grid
5. ✅ Auto-expire command
6. ✅ Marketplace banner display
7. ✅ Analytics tracking (impressions, clicks)

### Phase 2 ✅ **COMPLETE** (4/4 complete)
1. ✅ Landing page featured section (landing hero & carousel) - **COMPLETE**
2. ✅ Marketplace banner - **COMPLETE**
3. ✅ Analytics tracking (impressions, clicks) - **COMPLETE**
4. ✅ Seller dashboard analytics - **COMPLETE**

### Phase 3 ✅ **MOSTLY COMPLETE** (3/4 complete)
1. ✅ Popup modals (welcome, exit intent, interstitial) - **COMPLETE**
2. ⚠️ Advanced analytics & reporting - **PENDING** (optional enhancement)
3. ✅ Auto-approve untuk premium sellers - **COMPLETE**
4. ⚠️ A/B testing untuk optimal placement - **PENDING** (optional enhancement)

## 💡 Additional Recommendations

1. **Featured Notes Badge**: Visual badge "⭐ FEATURED" di note card
2. **Priority Sorting**: Featured notes muncul di atas di marketplace
3. **Scheduled Ads**: Seller bisa schedule iklan untuk masa depan
4. **Bulk Discount**: Discount jika beli multiple locations
5. **Performance Guarantee**: Jika CTR < threshold, refund sebagian (optional) 

## 📝 Notes

- Pricing bisa di-setting di admin, tidak hardcode
- Durasi bisa fleksibel (tidak harus 7/14/30, bisa custom)
- Location bisa ditambah/dikurangi sesuai kebutuhan
- Popup frequency bisa di-limit (max 1x per user per day)

