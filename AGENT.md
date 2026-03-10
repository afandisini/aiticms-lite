# AGENT.md - AitiCMS Lite

## Motto
AitiCMS Lite - simple CMS baseline, secure by default.

## Product Positioning
Pisahkan identitas produk secara tegas:

1. `AitiCore-Flex` adalah framework/fondasi.
2. `Aiticms-Lite` adalah modul/aplikasi yang berjalan di atas `AitiCore-Flex`.

Konsekuensi kerja:

- jangan samakan branding framework dan branding CMS
- jangan ubah `AitiCore-Flex` menjadi seolah-olah produk CMS
- saat menulis docs, UI, meta, footer, atau release notes, bedakan mana milik framework dan mana milik modul
- repo ini harus diposisikan sebagai repo modul `Aiticms-Lite`, bukan repo framework

## Purpose
Repo ini adalah varian ringan dari `AitiCore-CMS` untuk kebutuhan CMS yang lebih kecil, lebih mudah dipangkas, dan lebih aman dijadikan baseline baru tanpa membawa seluruh fitur lama.

## Golden Rules
1. Secure by default: escape output aktif, CSRF aktif untuk web, DB binding only.
2. Thin controllers: business logic ada di `app/Services`.
3. Scope lite: hanya pertahankan fitur yang memang masuk kebutuhan `aiticms-lite`.
4. No runtime schema mutation: perubahan struktur database hanya lewat migrasi.
5. Public CLI surface tetap lewat `php aiti ...`.
6. Breaking change wajib dicatat di changelog dan docs.

## Structure Contract
Jaga folder inti tetap stabil:
`app/`, `routes/`, `bootstrap/`, `public/`, `storage/`, `system/`, `tests/`, root `aiti`.

## Repository Split Contract
Target distribusi publik:

1. pengguna ambil repo `AitiCore-Flex` terlebih dahulu sebagai framework
2. pengguna ambil repo `Aiticms-Lite` sebagai modul/aplikasi

Aturan agen:

- pertahankan narasi 2 repo terpisah di dokumentasi
- jika ada instruksi setup, tulis urutan framework dulu lalu modul
- jika ada branding teks, gunakan `Framework: AitiCore-Flex` dan `CMS: Aiticms-Lite`
- hindari wording yang membuat `Aiticms-Lite` terlihat sebagai framework utama
- bila ada perubahan pada integrasi framework, dokumentasikan batas antara kode framework dan kode modul

## Lite Direction
Saat memangkas fitur dari source awal:

- hapus fitur per modul, jangan setengah aktif
- rapikan route, controller, service, view, migrasi, dan docs dalam satu jalur
- jangan sisakan menu, route, atau query ke tabel yang sudah tidak dipakai
- utamakan alur inti CMS lebih dulu sebelum fitur tambahan

## Security Checklist
- XSS: escaped output by default.
- CSRF: aktif untuk route web.
- SQL Injection: prepared statement/query binding only.
- Session: HttpOnly true, SameSite Lax default, Secure when HTTPS.
- Uploads: whitelist MIME/ext, random filename, simpan aman.
- Error handling: hide stack trace in production, log ke `storage/logs`.

## CLI Contract
Minimal yang harus tetap hidup:

- `php aiti --version`
- `php aiti list`
- `php aiti serve`
- `php aiti route:list`
- `php aiti key:generate`

## Working Rules
- Gunakan `database/migrations` untuk semua perubahan schema/data baseline.
- Jika update `system/`, ikuti workflow submodule/core yang terdokumentasi.
- Jangan commit file eksperimen, dump lokal, atau kredensial.
- Sebelum commit pertama repo ini, bersihkan semua fitur yang memang tidak ingin ikut sejarah baru.
- Saat memperbarui docs/setup, pastikan alur distribusi 2 repo tetap terlihat agar pengguna sadar framework dan modul diambil terpisah.

## Definition of Done
- App jalan.
- Fitur lite yang dipilih berjalan end-to-end.
- Tidak ada route mati yang mengarah ke modul terhapus.
- Tests pass atau gap testing didokumentasikan jelas.
- Docs ter-update.
- Identitas `AitiCore-Flex` dan `Aiticms-Lite` tetap terpisah jelas di UI dan dokumentasi.
