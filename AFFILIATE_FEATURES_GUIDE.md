# 📚 Affiliate Dashboard - Landing Page & Materials Guide

> Panduan lengkap cara menggunakan Landing Page dan Promotional Materials di affiliate dashboard.

---

## 1. 🌐 Landing Page Feature

### Apa itu Landing Page?
Landing page adalah halaman khusus yang akan ditampilkan ketika orang mengklik link affiliate Anda. Anda bisa customize halaman ini dengan HTML content sendiri untuk meningkatkan konversi.

### Lokasi & Cara Akses

**Di Dashboard Affiliate:**
```
http://noteds.test/affiliate
        ↓
    Lihat setiap affiliate link
        ↓
    Klik tombol UNGU: 🌐 Edit Landing Page
        ↓
    Modal akan terbuka dengan form landing page
```

### Fitur Landing Page

**Form Input:**
1. **Landing Page Slug** - URL custom untuk landing page
   - Format: `http://noteds.test/a/{slug}`
   - Contoh: `my-awesome-notes` → `http://noteds.test/a/my-awesome-notes`
   - Digunakan: Untuk akses direct ke landing page Anda

2. **Landing Page Content** - HTML content editor
   - Tipe: Rich HTML Editor dengan preview live
   - Support: Full HTML, CSS inline, JavaScript
   - Max: Unlimited (simpan sesuai kebutuhan)
   - Tips: Gunakan HTML profesional untuk better conversion

3. **Live Preview Panel**
   - Real-time preview saat Anda ketik HTML
   - Lihat bagaimana landing page akan terlihat
   - Update otomatis setiap kali ada perubahan

### Cara Menggunakan

**Step 1: Buka Modal Landing Page**
```
Lihat affiliate link Anda di dashboard
↓
Klik tombol ungu: 🌐 Edit Landing Page
```

**Step 2: Edit Slug (URL Custom)**
```
Masukkan slug yang memorable, contoh:
- "special-offer-2024"
- "my-digital-notes"
- "best-deal"
```

**Step 3: Input HTML Content**
```
Contoh HTML sederhana:
<h1>Special Offer - 50% OFF!</h1>
<p>Get access to premium notes at half price.</p>
<button onclick="window.location.href='http://noteds.test'">
  Claim Your Discount Now
</button>

Atau gunakan HTML template yang lebih kompleks dengan CSS & styling
```

**Step 4: Live Preview**
```
Lihat panel preview di sebelah kanan
Verifikasi tampilan landing page Anda
Pastikan semuanya terlihat bagus
```

**Step 5: Save**
```
Klik tombol "Save" untuk menyimpan
Landing page Anda akan live secara otomatis
```

### Akses Landing Page

**Setelah Saved, Landing Page Accessible di:**
```
URL: http://noteds.test/a/{slug-anda}

Contoh:
- http://noteds.test/a/special-offer-2024
- http://noteds.test/a/my-digital-notes
- http://noteds.test/a/best-deal
```

### Use Cases

1. **Promosi Khusus** - Buat landing page untuk promosi terbatas
2. **Product Showcase** - Showcase produk dengan deskripsi panjang
3. **Sales Funnel** - Gunakan sebagai first-touch page sebelum redirect ke marketplace
4. **Lead Capture** - Collect email sebelum mereka beli
5. **SEO Optimization** - Customize untuk ranking di search engines

---

## 2. 📦 Promotional Materials Feature

### Apa itu Promotional Materials?
Promotional materials adalah aset marketing siap pakai yang memudahkan orang untuk share link affiliate Anda. Bisa berupa banner, text ads, atau HTML code siap embed.

### Lokasi & Cara Akses

**Di Dashboard Affiliate:**
```
http://noteds.test/affiliate
        ↓
    Lihat setiap affiliate link
        ↓
    Klik tombol ORANGE: 📦 Promotional Materials
        ↓
    Modal akan terbuka dengan materials manager
```

### Tipe-Tipe Materials

#### 1. **Banner Images** (Recommended untuk visual impact)
**Kegunaan:**
- Social media posts
- Blog sidebars
- Website headers
- Email signatures

**Specification:**
- Ukuran: Pilih dari preset (728x90, 300x250, 468x60) atau custom
- Format: JPG, PNG
- Max Size: 2MB

**Contoh Use Case:**
```
Banner 728x90 (Leaderboard):
Gunakan di blog atau website header untuk promote link Anda

Banner 300x250 (Medium Rectangle):
Gunakan di sidebar atau email untuk higher conversion
```

#### 2. **Link Code** (Best untuk embedding)
**Kegunaan:**
- Embed di website/blog
- HTML email campaigns
- Forum signatures
- Document footers

**Format:**
```html
<a href="https://noteds.test/affiliate-link" class="affiliate-button">
  Click here to get premium notes
</a>
```

**Fitur:**
- Generate otomatis dari affiliate link Anda
- Copy & paste langsung ke code
- Pre-formatted dengan styling

#### 3. **Text Ads** (Compact & flexible)
**Kegunaan:**
- Forum signatures
- Email closing
- Document text references
- Social media captions

**Format:**
```
"Get premium digital notes - join thousands of satisfied customers!"
[Click here](affiliate-link)
```

### Cara Menggunakan

**Step 1: Buka Modal Materials**
```
Lihat affiliate link Anda di dashboard
↓
Klik tombol orange: 📦 Promotional Materials
```

**Step 2: Create New Material**

**Option A - Upload Banner:**
```
1. Masukkan "Material Name" (contoh: "50% Off Banner")
2. Pilih "Material Type" → "Banner Image"
3. Pilih "Size" → Contoh: 300x250
4. Upload file gambar
5. Klik Create
```

**Option B - Add Link Code:**
```
1. Masukkan "Material Name" (contoh: "Text Link")
2. Pilih "Material Type" → "Link Code"
3. Copy-paste atau biarkan kosong (auto-generated)
4. Klik Create
```

**Option C - Add Text Ad:**
```
1. Masukkan "Material Name" (contoh: "Promo Text")
2. Pilih "Material Type" → "Text Ad"
3. Input HTML atau plain text ad
4. Klik Create
```

**Step 3: View Existing Materials**
```
Panel kanan menampilkan semua materials
- Material name & type
- Status (Active/Inactive)
- Delete button
- Copy HTML button (untuk link codes)
```

**Step 4: Use Materials**

**Copy HTML Code:**
```
1. Temukan material yang ingin digunakan
2. Lihat panel preview
3. Klik tombol "Copy HTML"
4. Paste ke website/blog/email
```

**Download Banner:**
```
1. Upload banner Anda
2. Material akan tersimpan
3. Download atau embed langsung
```

---

## 3. 📊 How They Work Together

### Workflow Ideal

```
1. Create Affiliate Link
   ↓
2. Setup Landing Page
   - Input custom slug
   - Design profesional HTML
   ↓
3. Create Promotional Materials
   - Upload banners untuk social
   - Create text ads untuk forum
   - Generate link codes untuk website
   ↓
4. Share Materials
   - Gunakan banners di social media (point ke landing page)
   - Embed link code di website (point ke affiliate link)
   - Copy text ad ke forum signatures
   ↓
5. Track Performance
   - Check conversion rate di dashboard
   - Monitor clicks dari promotional materials
   - See which materials perform best
```

### Best Practices

**Landing Page:**
- ✅ Keep it simple & fast-loading
- ✅ Include clear call-to-action button
- ✅ Optimize for mobile
- ✅ Add social proof (testimonials, statistics)
- ❌ Don't make it too long
- ❌ Avoid redirects that confuse users

**Promotional Materials:**
- ✅ Create multiple variations (A/B testing)
- ✅ Use high-quality images
- ✅ Keep text short & compelling
- ✅ Include benefit-focused messaging
- ❌ Don't use low-res or pixelated images
- ❌ Avoid false/misleading statements

**Combined Strategy:**
```
✅ GOOD:
Banner (social) → Click → Landing Page → Call-to-action → Affiliate Link

✅ BETTER:
Multiple Banners + Text Ads + Email Code → Landing Page → Affiliate Link

✅ BEST:
Personalized Landing Page + Converted Materials + Email Sequence → Affiliate Link
```

---

## 4. 🎯 Complete Example

### Setup Example

**Affiliate Link:** `Evan's Premium Notes`
- Full URL: `https://noteds.test/?ref=12NDQL`

**Landing Page:**
```
Slug: "evan-premium-notes"
URL: https://noteds.test/a/evan-premium-notes

Content:
<div style="text-align: center; padding: 40px;">
  <h1>✨ Evan's Premium Digital Notes</h1>
  <p>Quality notes covering business, tech, and productivity</p>
  <p><strong>Limited Time: 30% Discount!</strong></p>
  <a href="https://noteds.test/?ref=12NDQL" 
     style="background: #6366f1; color: white; padding: 15px 30px; 
     border-radius: 5px; text-decoration: none; display: inline-block;">
    Access Premium Notes Now
  </a>
</div>
```

**Materials Created:**
1. **Banner 300x250** - "Premium Notes Ad"
   - Uploaded professional image
   - Ready to share on social
   
2. **Link Code** - "Website Embed"
   - Pre-formatted HTML
   - Ready to paste on blog

3. **Text Ad** - "Forum Signature"
   - Short compelling text
   - Links to landing page

### How to Promote

**Social Media:**
```
Post banner image with text:
"Check out my curated premium notes - click link in bio!"
→ Links to: https://noteds.test/a/evan-premium-notes
```

**Blog/Website:**
```
Embed link code in blog sidebar:
<a href="https://noteds.test/?ref=12NDQL">
  Explore Evan's Premium Notes Collection
</a>
```

**Forum:**
```
Forum Signature:
"Sharing quality digital notes - [Click here](landing-page) to check them out!"
```

**Email:**
```
Email Footer:
Want to discover quality digital notes? Check out my collection here:
[Evan's Premium Notes](affiliate-link)
```

---

## 5. 📈 Tracking & Optimization

### View Performance

**In Affiliate Dashboard:**
- **Clicks:** Track total clicks on your affiliate link
- **Conversions:** Count of actual purchases
- **Conversion Rate:** Clicks → Conversions percentage
- **Commissions:** Earned money

### What to Track

```
Performance Metrics:
- Which materials get most clicks?
- Which landing page gets highest conversion?
- What's your conversion rate?
- Which promotional channel works best?
```

### A/B Test Materials

```
Version A: Professional banner → Track clicks
Version B: Humorous banner → Track clicks
          ↓
        Compare performance
          ↓
        Use winning version more
```

---

## 6. ❓ FAQ

**Q: Berapa banyak materials yang bisa saya buat?**
A: Unlimited! Buat sebanyak yang Anda butuhkan untuk different channels.

**Q: Bisa di-edit setelah di-create?**
A: Untuk sekarang materials adalah permanent setelah create. Delete dan re-create jika perlu edit.

**Q: Landing page bisa di-hide dari public?**
A: Ya, cukup ganti slug atau delete landing page content.

**Q: Bagaimana analytics untuk materials?**
A: Setiap click dicatat di affiliate dashboard. Lihat total clicks per link.

**Q: Bisa gunakan affiliate link langsung tanpa landing page?**
A: Ya! Lansung share link tanpa landing page juga boleh.

**Q: Mana yang lebih penting, landing page atau materials?**
A: Keduanya penting! Landing page = destination, Materials = tools untuk drive traffic.

---

## 7. 🚀 Quick Start Checklist

- [ ] **Create affiliate link** di dashboard
- [ ] **Design landing page** dengan HTML custom
- [ ] **Save landing page** dan test URL nya
- [ ] **Create 3 promotional materials:**
  - [ ] 1 Banner image (untuk social)
  - [ ] 1 Link code (untuk website)
  - [ ] 1 Text ad (untuk forum/email)
- [ ] **Start promoting** di multiple channels
- [ ] **Monitor performance** di affiliate dashboard
- [ ] **Optimize** berdasarkan data (A/B test)

---

## 📞 Summary

| Feature | Purpose | Access | Output |
|---------|---------|--------|--------|
| **Landing Page** | Custom promo page | Edit button (purple) | Landing URL: `/a/{slug}` |
| **Promotional Materials** | Marketing assets | Materials button (orange) | Banners, Link codes, Text ads |
| **Both Together** | Complete affiliate solution | Full featured system | Higher conversion rates |

---

**Status:** ✅ Fully Functional & Ready to Use  
**Last Updated:** December 9, 2025
