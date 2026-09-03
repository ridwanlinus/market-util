# ▲ Deploy ke Vercel — Freebuff Marketing Suite

> ⚠️ **Baca dulu bagian "Batasan" di bawah.** Vercel bersifat *serverless* — tidak cocok langsung untuk Laravel yang menyimpan file & database lokal. Versi saat ini **membutuhkan 2 penyesuaian** sebelum benar-benar layak produksi di Vercel.

## Prasyarat

1. Kode sudah di GitHub → **Import Project** di dashboard Vercel dari repo `ridwanlinus/market-util`.
2. Framework preset: pilih **Other** (Vercel mendeteksi `vercel.json` yang sudah disediakan).
3. Siapkan layanan eksternal (karena filesystem serverless **ephemeral / tidak permanen**):
   - **Database MySQL/Postgres**: mis. Neon, Supabase, PlanetScale, atau Railway. Isi `DB_CONNECTION=pgsql|mysql` + `DB_HOST/PORT/DATABASE/USERNAME/PASSWORD` di Environment Variables Vercel.
   - **Storage objek untuk gambar konten** (Content Studio menyimpan PNG hasil desain): S3/Cloudflare R2/Backblaze. Ubah `FILESYSTEM_DISK=s3` + tambah `AWS_ACCESS_KEY_ID`, dst., dan sesuaikan penyimpanan di kode bila perlu.

## Environment Variables (wajib diatur di Vercel)

```
APP_KEY=                # WAJIB — hasil php artisan key:generate --show
                        # tanpa ini, enkripsi cookie/session gagal (error "No application encryption key")
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<project>.vercel.app

# database eksternal — WAJIB agar data tersimpan (SQLite di Vercel bersifat ephemeral)
DB_CONNECTION=pgsql      # atau mysql
DB_HOST=...
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

# session harus persisten lintas instance → pakai database (bukan array/file)
SESSION_DRIVER=database

# storage (opsional tapi disarankan)
FILESYSTEM_DISK=local    # local TIDAK persisten di Vercel!
```

Setelah env diatur, jalankan migrasi sekali (via terminal lokal atau menu Vercel → halaman `api/migrate.php` sekali lalu hapus — atau gunakan CI).

## Alur request di Vercel

```
Browser ──> vercel.json routes
              ├─ /favicon, /build, /css, /js, /storage  → file static di public/ (rewrite)
              └─ (semua URL lain)                        → api/index.php (vercel-php function)
                                                              └─ Laravel handleRequest()
```

`vercel.json` memakai key **`functions`** (bukan `builds` yang legacy — memakai `builds` memunculkan warning "Build and Development Settings will not apply"). Blok `env` di dalamnya menyesuaikan Laravel agar jalan di filesystem function yang read-only:

- `VIEW_COMPILED_PATH=/tmp/views` — compiled Blade ditulis ke `/tmp` (writable)
- `LOG_CHANNEL=stderr` — log dikirim ke stderr (tertampil di Vercel Logs), bukan file
- `CACHE_DRIVER=array` — cache per-instance di memori, tanpa write file
- `APP_*_CACHE=/tmp/*.php` — aman bila nanti menjalankan `config:cache`

File yang disertakan:

| File | Fungsi |
|------|--------|
| `vercel.json` | Function PHP (`functions`) + routing asset static vs Laravel |
| `api/index.php` | Entry serverless: boot Laravel (`handleRequest`) lalu `send()` |
| `composer.lock` | Sudah ter-commit → install deterministik (dijalankan builder saat build) |

## Batasan (jujur)

1. **Filesystem tidak permanen** — SQLite, gambar upload, dan log akan hilang antar request/instance. Solusi: database eksternal + storage objek.
2. **Cold start** — boot Laravel per instance lambat (±1–3 dtk) untuk request pertama.
3. **Upload via base64** (Content Studio) menghasilkan body besar → perhatikan limit Vercel (4.5 MB function payload).
4. **PHP bukan warga kelas satu di Vercel** — memakai builder komunitas `vercel-php`, risiko maintenance di luar kendali Vercel.
5. **Runtime builder**: `vercel-php@0.8.0` (PHP 8.4, Node autodetect). **Jangan turunkan** ke `0.6.x` atau lebih lama — versi itu memakai `nodejs18.x` yang sudah discontinued dan build gagal dengan *"Runtime is using nodejs18.x, which is discontinued"*.
5. **Seeder demo** berisi data visual; jalankan sekali di DB eksternal, jangan tiap request.

## Alternatif yang lebih pas untuk Laravel (stateful)

| Host | Kenapa |
|------|--------|
| **Railway / Render / Fly.io** | Jalankan `php artisan serve`/Octane utuh + persistent disk → file upload & SQLite langsung jalan tanpa ubah kode |
| **VPS + Laravel Forge** | Kontrol penuh, paling stabil untuk produksi |

> Rekomendasi: jalankan versi Vercel ini sebagai **demo/preview**, dan pakai Railway/Render untuk pemakaian nyata dengan upload gambar.

## Langkah deploy Vercel

1. Push kode ke GitHub (lihat README → Deploy).
2. Vercel dashboard → *Add New Project* → import `market-util`.
3. Isi Environment Variables di atas.
4. Deploy → cek log bila error → pastikan migrasi DB sudah jalan.
