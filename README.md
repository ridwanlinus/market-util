# 🎨 Freebuff Marketing Suite

Dashboard terpadu **Social Media Marketing & Digital Marketing** dengan UI/UX bergaya iOS — dibangun dengan **Laravel 13** (PHP 8.4) + SQLite/MySQL.

## ✨ Fitur Utama

| # | Tool | Fungsi |
|---|------|--------|
| 1 | **Content Studio** | Produksi konten **single image** & **carousel 4:5 (1080×1350)** langsung di browser — canvas editor dengan layer teks/gambar/CTA, template siap pakai, drag & drop, ekspor PNG, plus alur **approval Super Admin**. |
| 2 | **Engagement Rate Calculator** | Hitung `(total interaksi ÷ followers) × 100` secara real-time dengan ring gauge, grade industri (Excellent → Poor), perhitungan per konten Meta, dan riwayat tersimpan. |
| 3 | **Meta Ads Insights** | Dashboard insight Meta (impressions, reach, engagement, CTR, spend) — input manual, **import CSV**, atau **sinkronisasi Meta Graph API**. |
| 4 | **Google Ads Insights** | KPI kampanye (klik, CTR, CPC, biaya, konversi, **ROAS**) dengan visualisasi tren, donut biaya per kampanye, import CSV. |
| 5 | **Google Analytics** | Traffic website GA4 — users, sessions, pageviews, bounce rate, durasi, top pages, channel donut; import CSV. |

**Role & Dashboard:**
- 👤 **User Company** — dashboard lengkap siap presentasi: KPI ringkas semua channel + visualisasi + Content Studio.
- 🛡️ **Super Admin** — konsol approval saja: setujui/tolak konten dengan catatan + ringkasan seluruh perusahaan.

## 🚀 Setup Lokal

```bash
composer install
cp .env.example .env          # lalu atur APP_KEY dll.
php artisan key:generate
php artisan migrate --seed    # seed data demo + akun
php artisan storage:link      # agar gambar konten bisa diakses
php artisan serve
```

### Akun Demo

| Role | Email | Password |
|------|-------|----------|
| Company | `company@freebuff.test` | `password` |
| Super Admin | `super@freebuff.test` | `password` |

Seeder menghasilkan ~60 hari data demo: 8 konten, 2 halaman Meta, post Meta, 3 kampanye Google Ads, dan properti GA4.

## 🧭 Workflow & Ekosistem

Kesimpulan alur kerja (produksi → approval → publish → insight → presentasi) dan daftar aplikasi/layanan pendukung ada di **[docs/workflow-ecosystem.md](docs/workflow-ecosystem.md)**.

## 🧪 Test

```bash
php artisan test
```

Mencakup: kalkulasi & grade Engagement Rate, parser CSV import, proteksi role, dan alur approval (submit → approve/reject).

## 🗂️ Struktur Penting

```
app/Models/            # Content, MetaPage/Post/Insight, GoogleAds*, Ga*, Calculation
app/Http/Controllers/  # Dashboard, ContentStudio, Engagement, Meta, GoogleAds, Analytics, Settings, Super
app/Services/          # EngagementRateService, InsightStats, CsvImportService, MetaApiService
database/seeders/      # DatabaseSeeder (data demo lengkap + cover gradient via GD)
resources/views/       # Blade + Tailwind + Alpine + Chart.js (iOS design system)
public/css/app.css     # Design system iOS (kartu, sidebar frosted, toggle, ring gauge...)
public/js/editor.js    # Engine canvas Content Studio (1080×1350)
```

## 🔌 Koneksi API (opsional — di halaman Settings)

- **Meta Graph API**: `META_ACCESS_TOKEN` — izin `pages_show_list`, `pages_read_engagement`. Tombol *Sinkron API* di Meta Insights otomatis menarik data harian.
- **Google Ads / GA4 / OpenAI**: simpan kredensial di Settings (tersimpan terenkripsi). Data juga bisa **input manual** atau **import CSV** tanpa API.

Format CSV tiap tool tertera di modal *Import CSV* masing-masing.

## 📦 Deploy

### 1. Push ke GitHub

```bash
git init
git remote add origin git@github.com:ridwanlinus/market-util.git
git add .
git commit -m "Initial Freebuff Marketing Suite"
git branch -M main
git push -u origin main
```

### 2. Siapkan di server (VPS / shared hosting)

```bash
git clone git@github.com:ridwanlinus/market-util.git
cd market-util
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
# atur APP_ENV=production, APP_DEBUG=false, APP_URL, DB_* di .env
php artisan migrate --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

Arahkan document root ke folder `public/`. Buat job scheduler bila ingin otomasi berkala:

```bash
* * * * * php /path/project/artisan schedule:run >> /dev/null 2>&1
```

> UI memakai CDN (Tailwind, Chart.js, Alpine, Lucide) sehingga tidak butuh `npm run build`. Untuk production ketat, pindahkan Tailwind ke build lokal (Vite) bila diperlukan.

## 🛠️ Teknologi

Laravel 13 · PHP 8.4 · SQLite (default) / MySQL · Blade · Tailwind CSS · Alpine.js · Chart.js · Lucide Icons · GD (cover generator)
