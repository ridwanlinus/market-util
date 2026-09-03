# 🧭 Kesimpulan Workflow & Aplikasi Pendukung — Freebuff Marketing Suite

Dokumen ini menjelaskan **alur kerja (workflow)** aplikasi dari hulu ke hilir, serta **aplikasi/layanan eksternal apa saja** yang menunjang operasionalnya.

---

## 1. Konsep Aplikasi dalam Satu Kalimat

> Satu dashboard ala iOS untuk **memproduksi konten** (single/carousel 4:5), **mendapatkan persetujuan**, lalu **memantau performa** Meta, Google Ads, dan Google Analytics — sehingga tim marketing bisa presentasi hasil tanpa pindah aplikasi.

---

## 2. Workflow Utama (End-to-End)

```
┌───────────────────────────  ALUR HARIAN TIM MARKETING  ───────────────────────────┐
│                                                                                    │
│  ┌────────────┐   ┌──────────────────┐   ┌─────────────────┐   ┌───────────────┐  │
│  │ 1. Setup   │ → │ 2. Produksi      │ → │ 3. Approval     │ → │ 4. Publish    │  │
│  │  (sekali)  │   │  (Content Studio)│   │  (Super Admin)  │   │ (di platform) │  │
│  └────────────┘   └──────────────────┘   └─────────────────┘   └───────────────┘  │
│         │                     │                  │                    │           │
│         ▼                     ▼                  ▼                    ▼           │
│  Daftarkan company      Pilih template      Setujui / tolak     Post konten di  │
│  Tambah halaman Meta    Susun slide 4:5     + catatan revisi    Instagram/FB    │
│  Tambah campaign Ads    Edit layer/teks/    (kembali ke #2      /Ads Manager    │
│  Tambah properti GA4    gambar, CTA         bila ditolak)       (manual atau    │
│  Isi API key (ops.)     Simpan draft                              via API)      │
│         │               Kirim approval                                              │
│         ▼                                                                         │
│  ┌──────────────────────────────┐    ┌──────────────────────────────┐             │
│  │ 5. Kumpulkan Data Insight   │ →  │ 6. Ukur & Analisis           │             │
│  │  Meta: sinkron API / CSV    │    │  ER per konten (Tool 2)      │             │
│  │  Ads : CSV dari UI Google   │    │  Tren Meta / Ads / GA        │             │
│  │  GA  : CSV dari GA4         │    │  (Tool 3-5)                  │             │
│  └──────────────────────────────┘    └──────────────┬───────────────┘             │
│                                                      ▼                            │
│  ┌─────────────────────────────────────────────────────────────────────┐         │
│  │ 7. Lapor & Presentasi (Dashboard company) + 8. Optimasi → ulang #2 │         │
│  └─────────────────────────────────────────────────────────────────────┘         │
└────────────────────────────────────────────────────────────────────────────────────┘
```

### Detail tiap tahap

| # | Tahap | Pelaku | Aksi di Freebuff | Output |
|---|-------|--------|------------------|--------|
| 1 | Setup | Company | Register akun → otomatis terbentuk *company*; tambah halaman Meta, campaign Google Ads, properti GA4; isi API key (opsional) | Entitas data + koneksi |
| 2 | Produksi | Tim kreatif (Company) | Content Studio: template → desain slide → caption/jadwal → **Simpan Draft** | File PNG 1080×1350 + desain JSON |
| 3 | Approval | Super Admin | Antrian approval: **Setujui** / **Tolak + catatan** | Status konten: approved/rejected |
| 4 | Publish | Tim marketing | Konten approved → post ke Instagram/Facebook/TikTok (di luar aplikasi, manual saat ini) | Konten live di platform |
| 5 | Data insight | Company | Meta: tombol **Sinkron API** (Graph API) atau **Import CSV**; Ads & GA: **Import CSV** / input manual | Data harian di dashboard |
| 6 | Ukur ER | Company | Kalkulator ER: input interaksi+followers **atau** pilih post Meta → tersimpan, terlihat tren-nya | Nilai ER + grade + riwayat |
| 7 | Presentasi | Company | Dashboard gabungan semua channel (siap layar lebar) | KPI untuk rapat/klien |
| 8 | Optimasi | Semua | Lihat konten ER tinggi/rendah → keputusan konten & budget iklan berikutnya | Siklus berulang |

**Peran & batasan:**
- **Company user**: melihat & mengelola data milik perusahaannya sendiri saja (scoping per `company_id`).
- **Super Admin**: *hanya approval* + melihat ringkasan seluruh perusahaan — tanpa akses edit data marketing.

---

## 3. Aplikasi & Layanan Terkait yang Menunjang

### 3.1. Platform sumber data (WAJIB ada di luar aplikasi)

Data performa asli tidak dibuat oleh Freebuff — platform ini pemilik datanya:

| Platform | Dipakai untuk | Cara terhubung ke Freebuff |
|----------|---------------|----------------------------|
| **Meta Business Manager** + **Meta Business Suite** | Halaman Facebook/Instagram, izin akses | Ambil **Access Token** (izin `pages_show_list`, `pages_read_engagement`) → simpan di Settings atau per-halaman; tombol *Sinkron API* menarik insight harian |
| **Google Ads** | Kampanye iklan | Export CSV (UI Google Ads → *Download*) → **Import CSV**; atau kredensial OAuth (developer token, client id/secret, customer id) untuk integrasi penuh |
| **Google Analytics 4** | Traffic website | Export CSV (GA4 → Reports → *Share/Export*) → **Import CSV**; atau OAuth + property ID |
| **Google Cloud Console** | Membuat OAuth client untuk API Google (opsional) | Buat kredensial → isi di Settings |

> Catatan penting: Freebuff bisa berjalan **100% tanpa API** (manual + CSV). API hanya mempercepat input.

### 3.2. Aplikasi penunjang operasional (opsional / roadmap)

| Aplikasi | Fungsi menunjang | Status |
|----------|------------------|--------|
| **OpenAI API** | AI copywriting & AI image di Content Studio | Opsional — key sudah disiapkan di Settings; generator belum diaktifkan |
| **Mailtrap / Amazon SES / Mailgun** | SMTP untuk notifikasi email (laporan mingguan, status approval) | Roadmap |
| **Telegram / Slack bot** | Notifikasi real-time: "ada konten menunggu approval", hasil ER tertinggi | Roadmap |
| **Google Sheets / Excel** | Menyiapkan/merapikan data sebelum Import CSV | Dipakai tim saat ini |
| **Cron / scheduler server** | `php artisan schedule:run` tiap menit → otomasi sync Meta & kirim laporan | Roadmap (perintah sudah siap di README) |

### 3.3. Infrastruktur & deployment

| Layanan | Peran | Keterangan |
|---------|-------|------------|
| **GitHub (ridwanlinus/market-util)** | Versi kode & sumber deploy | Repo sudah disiapkan (git init + remote), tinggal commit & push |
| **Hosting** — VPS (Laravel Forge), **Railway**, **Render**, atau shared hosting | Menjalankan Laravel | Arahkan document root ke `public/`; butuh PHP 8.3+ & Composer |
| **Database** — SQLite (lokal) → **MySQL/PostgreSQL** (produksi) | Penyimpanan | Cukup ubah variabel `DB_*` di `.env` |
| **Storage** — lokal → **S3 / DO Spaces** (produksi) | File gambar konten & avatar | Ubah `FILESYSTEM_DISK`; file lama perlu dimigrasi |
| **Redis** (opsional) | Cache & queue bila otomasi berat | `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis` |
| **Domain + SSL (Let's Encrypt)** | Akses publik yang aman | `APP_URL` harus sesuai domain |

### 3.4. Yang TIDAK perlu lagi (digantikan Freebuff)

- Canva / tool desain 4:5 terpisah → cukup **Content Studio** (bila butuh template lebih banyak, tinggal ditambah).
- Spreadsheet manual untuk hitung ER → **kalkulator ER** + ring gauge otomatis.
- Dashboard manual dari screenshot ads → **dashboard terpusat** per platform + gabungan.

---

## 4. Format Data yang Diterima (ringkas)

| Tool | Kolom CSV yang dipahami |
|------|--------------------------|
| Meta Insights | `date, impressions, reach, engagement, likes, comments, shares, saves, clicks, spend` |
| Google Ads | `date, impressions, clicks, cost, conversions, conversion_value` |
| Google Analytics | `date, users, new_users, sessions, pageviews, avg_session_duration, bounce_rate` |

`date` bisa format apa pun yang dikenali PHP (mis. `2026-09-01`, `2026/09/01`). Baris dengan tanggal sama akan di-**update** (bukan duplikat).

---

## 5. Checklist Go-Live

1. [ ] `git commit` + `git push` ke `ridwanlinus/market-util`
2. [ ] Deploy ke hosting, set `.env` produksi (`APP_ENV=production`, `APP_DEBUG=false`, DB MySQL)
3. [ ] `php artisan migrate --force` + `php artisan storage:link`
4. [ ] Buat akun Super Admin & akun tiap perusahaan tim
5. [ ] Isi Access Token Meta di Settings → klik **Sinkron API** pertama kali (tarik 30 hari data)
6. [ ] Tambah campaign Google Ads + import CSV (atau export berkala mingguan)
7. [ ] Tambah properti GA4 + import CSV
8. [ ] Sosialisasi alur approval ke tim: kreatif submit → super approve → publish
9. [ ] (Opsional) Hubungkan SMTP & scheduler untuk laporan otomatis

---

*Dokumen ini menyertai aplikasi Freebuff Marketing Suite. Alur dapat disesuaikan dengan SOP tim — struktur data (status konten, halaman, campaign, properti) sudah dirancang mengikuti alur di atas.*
