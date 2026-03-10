# AitiCMS Lite

`AitiCMS Lite` adalah modul CMS ringan yang berjalan di atas framework `AitiCore-Flex`.

![AitiCMS Lite Preview](https://aiti-solutions.com/storage/filemanager/1/34/aiticms-lite-20260310005138-9b6f709d.webp)

Posisi produk:

- `AitiCore-Flex` = framework/fondasi
- `Aiticms-Lite` = modul CMS/aplikasi

Keduanya diposisikan sebagai 2 repo terpisah agar identitas framework dan modul tetap jelas.

## Tujuan Repo

Target `aiticms-lite`:

- menjadi baseline CMS yang lebih ramping
- tetap memakai fondasi aman dari AitiCore
- hanya menyimpan modul yang benar-benar dibutuhkan
- lebih mudah dirawat dan dirilis sebagai produk terpisah

## Arsitektur Distribusi

Konsep distribusi yang harus dijaga:

1. Pengguna mengambil repo `AitiCore-Flex` terlebih dahulu.
2. Pengguna mengambil repo `Aiticms-Lite` setelah itu.
3. `AitiCore-Flex` tetap tercatat sebagai framework dasar.
4. `Aiticms-Lite` tetap tercatat sebagai modul yang dipasang di atas framework.

Tujuan pemisahan ini:

- menjaga batas framework vs modul
- memudahkan release terpisah
- memudahkan pengguna memahami dependensi dasar sebelum memakai modul CMS

## Prinsip Teknis

- output view harus tetap escaped by default
- CSRF tetap aktif untuk web form
- controller tetap tipis
- business logic tetap di `app/Services`
- perubahan database hanya lewat migrasi
- semua route lama yang tidak dipakai harus dihapus tuntas

## Paket Tema ZIP

Upload tema ZIP di CMS sekarang hanya menerima paket frontend aman dengan aturan berikut:

- wajib lolos auth dan verifikasi CSRF server-side
- file upload harus benar-benar ZIP yang valid, ukuran maksimal `10 MB`
- isi arsip divalidasi penuh sebelum ekstraksi
- maksimal `200` entry dan total ukuran hasil ekstraksi maksimal `25 MB`
- `manifest.json` wajib ada tepat satu, di root paket atau di dalam satu wrapper folder teratas
- slug tema hanya boleh `[a-z0-9_-]` dan folder final selalu memakai slug aman
- paket tidak boleh memuat path traversal, symlink, dotfile, file server-side, file executable, atau file build/dependency seperti `.htaccess`, `.env`, `composer.json`, `package.json`
- ekstensi yang diizinkan hanya: `css`, `js`, `png`, `jpg`, `jpeg`, `gif`, `webp`, `svg`, `ico`, `html`, `txt`, `md`, dan `manifest.json`
- template tema frontend yang didukung saat ini adalah file `HTML` aman, misalnya `templates/home.html`, `templates/article.html`, `templates/page.html`, `templates/tag.html`
- tema boleh memakai directive seperti `{header}`, `{footer}`, `{article}`, dan `{sort_article limit=5}`; jika template full HTML tidak memakai directive sama sekali, sistem akan menyisipkan view default sebelum `</body>`
- meta `<meta name="generator" content="Aiticms-Lite">` dan `<meta name="framework" content="AitiCore Flex">` selalu diinjeksikan otomatis ke `<head>`

Contoh struktur ZIP yang valid:

```text
my-theme.zip
├─ manifest.json
├─ README.md
└─ assets/
   ├─ theme.css
   ├─ theme.js
   └─ screenshot.webp
```

## Struktur Project

```text
app/
bootstrap/
core/
database/
docs/
public/
routes/
scripts/
storage/
system/
tests/
aiti
```

## Setup Lokal

Windows PowerShell:

```powershell
git clone <repo-AitiCore-Flex>
git clone <repo-Aiticms-Lite>
cd aiticms-lite
Copy-Item .env.example .env
composer install
php aiti key:generate
php aiti serve
```

Lalu buka:

```text
http://127.0.0.1:8000
```

## Akun Demo Login

Setelah menjalankan `php aiti migrate`, akun admin demo default yang bisa dipakai adalah:

- URL login: `http://127.0.0.1:8000/cms/login`
- Username: `admin`
- Email: `admin@mail.com`
- Password: `Admin123!`

Catatan:

- akun ini hanya untuk baseline development/demo
- segera ganti password setelah login pertama, terutama jika project dipakai di server publik

## Database

Migrasi ada di:

```text
database/migrations
```

Jalankan:

```powershell
php aiti migrate
```

Baseline saat ini:

- repo hanya menyimpan `1` baseline migration: `database/migrations/2026_03_10_000001_baseline.sql`
- backup database penuh disimpan di `database/backups/aiticoresms-lite_2026-03-10_full_backup.sql`
- tabel histori migrasi lokal sudah di-reset agar hanya mengenali baseline tersebut
- baseline sudah diuji ke database kosong dan bisa dijalankan memakai `php aiti migrate`

Catatan operasional:

- gunakan file di `database/backups/` untuk restore manual penuh bila dibutuhkan
- gunakan file di `database/migrations/` untuk bootstrap database baru lewat command migrate
- bila schema berubah lagi ke depan, tambahkan migrasi baru di atas baseline ini, jangan edit histori lama

## Aturan Branding

Saat menulis UI, footer, meta, atau dokumentasi:

- tampilkan `Aiticms-Lite` sebagai nama CMS/modul
- tampilkan `AitiCore-Flex` sebagai nama framework
- jangan campur keduanya menjadi satu identitas produk tunggal
- jika perlu attribution, gunakan pola seperti:

```text
CMS: Aiticms-Lite
Framework: AitiCore-Flex
```

## Workflow Awal yang Disarankan

1. Pangkas fitur yang tidak akan masuk `aiticms-lite`.
2. Rapikan route, controller, service, view, dan migrasi yang terdampak.
3. Perbarui `README.md`, `AGENT.md`, dan docs lain.
4. Jalankan smoke test dan test otomatis yang relevan.
5. Baru lakukan commit pertama.

## Media dan Editor

Integrasi media aktif saat ini:

- `File Manager` menjadi sumber file terpusat untuk aset CMS
- form `Artikel`, `Halaman`, `Slider`, dan `Pengaturan Sistem` memakai picker yang terhubung ke `File Manager`
- upload gambar dari `TinyMCE` diarahkan ke endpoint `File Manager`, sehingga drag-drop, paste image, dan dialog image memakai storage yang sama
- `Halaman` sekarang mendukung `Gambar Utama` untuk hero/cover frontend

## Dokumentasi Saat Ini

Dokumen yang masih perlu direview agar sesuai versi lite:

- `docs/CORE_SUBMODULE_WORKFLOW.md`
- `docs/THEME_PLUGIN_CONCEPT.md`
- `docs/USER_GUEIDE.md`

Dokumen tersebut perlu dijaga tetap sinkron dengan posisi `AitiCore-Flex` sebagai framework dan `Aiticms-Lite` sebagai modul CMS.

## Commit Pertama

Contoh alur commit awal repo ini:

```powershell
git add .
git commit -m "Initial commit"
git push -u origin main
```
