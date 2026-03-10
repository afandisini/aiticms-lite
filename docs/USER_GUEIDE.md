# User Guide Aiticms-Lite

Panduan ini ditujukan untuk user non-teknis yang memakai CMS `Aiticms-Lite`.

Catatan identitas produk:

- CMS/aplikasi: `Aiticms-Lite`
- Framework dasar: `AitiCore-Flex`

## Akses CMS

- URL login CMS: `http://127.0.0.1:8000/cms/login`
- Login memakai email atau username dari tabel `users`
- Role yang dipakai untuk akses CMS mengikuti konfigurasi aplikasi

Kredensial admin demo hasil baseline migration:

- Username: `admin`
- Email: `admin@mail.com`
- Password: `Admin123!`

Catatan:

- akun ini ditujukan untuk setup lokal atau demo awal
- setelah berhasil login, password sebaiknya langsung diganti

## Modul Utama CMS

Menu utama yang tersedia di versi lite:

- `Dashboard`
- `View Sites`
- `Artikel`
- `Posting`
- `Halaman`
- `Tags`
- `Komentar`
- `File Manager`
- `Tema`
- `Plugin`
- `Pengaturan Menu`
- `Setel Slider`
- `Informasi Pengaturan`
- `Access`

Panduan ini hanya membahas modul yang memang aktif pada `Aiticms-Lite`.

## Dashboard

Halaman dashboard dipakai untuk melihat ringkasan cepat:

- jumlah artikel
- jumlah halaman
- jumlah tags
- jumlah users

Dashboard juga menampilkan panel data terbaru agar admin cepat masuk ke modul yang dibutuhkan.

## View Sites

Menu `View Sites` dipakai untuk melihat ringkasan informasi website aktif, seperti:

- nama website
- URL default
- kontak utama
- shortcut ke halaman frontend

Halaman ini berguna untuk verifikasi konfigurasi situs secara cepat.

## Artikel

Menu `Artikel` dipakai untuk mengelola konten utama blog atau portal.

Langkah dasar menambah artikel:

1. buka menu `Artikel`
2. klik `Tambah Artikel`
3. isi judul, slug, kategori, tags, dan konten
4. pilih gambar bila diperlukan
5. simpan artikel

Untuk artikel yang sudah ada, admin dapat:

- mengubah isi artikel
- mengganti status publish
- menghapus artikel

## Posting

Menu `Posting` dipakai untuk memantau daftar artikel dan mengubah status publikasi secara cepat.

Gunakan modul ini bila ingin fokus ke workflow publish atau draft tanpa membuka form edit penuh.

## Halaman

Menu `Halaman` dipakai untuk membuat halaman statis, misalnya:

- Tentang Kami
- Kontak
- Layanan
- Kebijakan Privasi

Langkah dasar:

1. buka menu `Halaman`
2. klik tambah halaman
3. isi judul, slug, dan konten
4. pilih `Gambar Utama` bila halaman memerlukan hero/cover
5. simpan
6. verifikasi hasil di frontend

## Tags

Menu `Tags` dipakai untuk mengelola tag artikel.

Fungsi utamanya:

- menambah tag baru
- mengubah nama atau slug tag
- menghapus tag yang tidak dipakai

Tag dipakai untuk membantu pengelompokan dan pencarian artikel.

## Komentar

Menu `Komentar` dipakai untuk pengaturan komentar global.

Admin dapat:

- mengaktifkan komentar
- menonaktifkan komentar
- menyesuaikan HTML/embed komentar jika fitur ini dipakai

## File Manager

`File Manager` dipakai untuk mengelola file yang dipakai di CMS, seperti:

- upload gambar
- membuat album
- mencari file
- menghapus file
- memilih file dari form lain

File Manager juga dipakai sebagai media picker pada form:

- artikel
- halaman
- slider
- pengaturan sistem

Selain picker manual, upload gambar dari editor `TinyMCE` juga masuk ke File Manager. Artinya gambar yang di-paste, drag-drop, atau dipilih lewat dialog image editor akan tersimpan pada storage media yang sama.

## Tema

Menu `Tema` dipakai untuk mengelola tema frontend.

Di versi saat ini admin dapat:

- melihat daftar tema terpasang
- upload paket tema berbentuk ZIP
- mengaktifkan tema yang tersedia

Catatan penting:

- tema bawaan memakai slug `aiti-themes`
- tema aktif disimpan pada tabel `information.active_theme`
- paket tema wajib memiliki `manifest.json`
- screenshot tema sebaiknya dideklarasikan lewat field `screenshot` dan memakai format `.webp`
- file executable seperti PHP tidak diizinkan dalam paket upload
- contoh paket upload tersedia di `docs/samples/aurora-flow-theme.zip`

Untuk konsep teknisnya, lihat `docs/THEME_PLUGIN_CONCEPT.md`.

## Plugin

Menu `Plugin` pada versi ini masih berfungsi sebagai dokumentasi arah produk.

Halaman tersebut menjelaskan:

- jenis plugin yang aman untuk dikembangkan
- batas keamanan sistem plugin
- rekomendasi prioritas plugin internal

Belum ada fitur install atau eksekusi plugin pihak ketiga dari panel admin.

## Pengaturan Menu

Modul ini dipakai untuk mengelola struktur navigasi frontend.

Admin dapat:

- menambah menu utama
- menambah submenu
- mengubah urutan menu
- mengubah nama dan link
- menghapus menu yang tidak dipakai

## Setel Slider

Modul `Setel Slider` dipakai untuk mengelola slider halaman depan.

Admin dapat:

- menambah slider
- memilih gambar dari File Manager
- mengatur judul dan link
- mengubah urutan slider
- menghapus slider lama

Konten slider juga memakai editor `TinyMCE`, sehingga gambar tambahan di dalam editor mengikuti upload media yang sama.

## Informasi Pengaturan

Menu ini dipakai untuk mengubah identitas website dan pengaturan frontend, misalnya:

- nama website
- URL default
- meta description
- meta keyword
- kontak
- alamat
- sosial media
- logo
- favicon
- meta image
- footer

Lakukan review hasil di frontend setelah menyimpan perubahan.

## Access

Menu `Access` dipakai untuk mengelola user CMS.

Admin dapat:

- melihat daftar user
- mengubah role
- mengubah status aktif
- mencari user berdasarkan nama, username, email, atau role

Perubahan role akan memengaruhi akses login dan hak pakai modul CMS.

## Tips Operasional

- gunakan slug yang rapi dan mudah dibaca
- pastikan artikel berstatus publish jika harus tampil di frontend
- review tampilan frontend setelah update settings, menu, atau slider
- gunakan File Manager agar aset lebih tertata
- gunakan `Gambar Utama` pada artikel atau halaman agar hero frontend konsisten
- bila menyisipkan gambar dari editor, tunggu proses upload selesai sebelum menyimpan form
- hindari menghapus user aktif tanpa review role dan dampaknya

## Batasan Versi Lite

Versi `Aiticms-Lite` sengaja lebih ramping.

Artinya, panduan ini tidak lagi memasukkan modul yang sudah tidak dipakai, seperti:

- products
- transaction
- reports
- client
- login log

Jika modul tersebut dibutuhkan kembali, dokumentasinya harus ditambahkan ulang sesuai implementasi terbaru, bukan memakai panduan lama.
