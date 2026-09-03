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
APP_KEY=                # hasil php artisan key:generate --show
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<project>.vercel.app

# database eksternal
DB_CONNECTION=pgsql      # atau mysql
DB_HOST=...
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

# storage (opsional tapi disarankan)
FILESYSTEM_DISK=local    # local TIDAK persisten di Vercel!
```

Setelah env diatur, jalankan migrasi sekali (via terminal lokal atau menu Vercel → halaman `api/migrate.php` sekali lalu hapus — atau gunakan CI).

## Alur request di Vercel

```
Browser ──> vercel.json routes
              ├─ /css, /js, /build, /storage  → @vercel/static (file publik)
              └─ (semua URL lain)             → api/index.php (vercel-php)
                                                   └─ Laravel handleRequest()
```

File yang disertakan:

| File | Fungsi |
|------|--------|
| `vercel.json` | Build static + fungsi PHP, routing asset vs Laravel |
| `api/index.php` | Entry serverless: boot Laravel (`handleRequest`) lalu `send()` |
| `composer.lock` | Sudah ter-commit → install deterministik |

## Batasan (jujur)

1. **Filesystem tidak permanen** — SQLite, gambar upload, dan log akan hilang antar request/instance. Solusi: database eksternal + storage objek.
2. **Cold start** — boot Laravel per instance lambat (±1–3 dtk) untuk request pertama.
3. **Upload via base64** (Content Studio) menghasilkan body besar → perhatikan limit Vercel (4.5 MB function payload).
4. **PHP bukan warga kelas satu di Vercel** — memakai builder komunitas `vercel-php`, risiko maintenance di luar kendali Vercel.
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
