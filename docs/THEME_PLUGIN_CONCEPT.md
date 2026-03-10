# Konsep Tema dan Plugin Aiticms-Lite

Dokumen ini menjelaskan arah desain modul `Tema` dan `Plugin` pada `Aiticms-Lite`.

Fokus utamanya:

- menjaga arsitektur tetap ringan
- menjaga batas aman agar admin tidak bisa mengunggah kode eksekusi arbitrer
- memberi jalur ekspansi yang realistis untuk versi berikutnya

## Posisi Fitur

Di versi saat ini:

- `Tema` sudah memiliki implementasi dasar
- `Plugin` masih berupa konsep produk dan batas arsitektur aman

Artinya:

- sistem tema sudah bisa dipakai untuk upload paket ZIP dan aktivasi tema
- sistem plugin belum boleh dieksekusi sebagai paket kode bebas

## Konsep Tema

Tema diposisikan sebagai lapisan presentasi frontend.

Tema seharusnya hanya mengubah:

- tampilan
- aset visual
- layout HTML
- metadata presentasi

Tema tidak seharusnya mengubah:

- logika autentikasi
- query bisnis inti
- permission
- migrasi database
- eksekusi PHP dari paket upload

## Implementasi Tema Saat Ini

Implementasi saat ini berada di:

- [app/Controllers/Cms/ThemeController.php](/d:/Flyenv/www/aiticms-lite/app/Controllers/Cms/ThemeController.php)
- [app/Services/Cms/ThemeService.php](/d:/Flyenv/www/aiticms-lite/app/Services/Cms/ThemeService.php)
- [app/Views/cms/appearance/themes/index.php](/d:/Flyenv/www/aiticms-lite/app/Views/cms/appearance/themes/index.php)

Perilaku yang sudah tersedia:

- tema bawaan internal memakai slug `aiti-themes`
- admin bisa upload file `.zip`
- paket tema wajib memiliki `manifest.json`
- status tema aktif utama disimpan pada `information.active_theme`
- `storage/themes/theme-state.json` dipakai sebagai fallback kompatibilitas lama
- tema hasil upload disimpan di `storage/themes/{slug}`
- asset tema aktif dimuat lewat route aman `/theme-assets/{slug}?path=...`

## Aturan Paket Tema

Paket tema harus diperlakukan sebagai paket aset, bukan paket aplikasi.

Struktur minimal yang disarankan:

```text
my-theme.zip
|- manifest.json
|- assets/
|  |- css/
|  |- js/
|  `- img/
`- views/
   `- optional-static-snippets.html
```

Contoh `manifest.json`:

```json
{
  "slug": "my-theme",
  "name": "My Theme",
  "version": "1.0.0",
  "description": "Tema frontend tambahan untuk Aiticms-Lite.",
  "screenshot": "assets/screenshot.webp"
}
```

Field minimal yang perlu dijaga:

- `slug`
- `name`
- `version`
- `description`

Field tambahan yang direkomendasikan:

- `screenshot` dengan path file preview `.webp`

Contoh paket siap upload tersedia di:

- [docs/samples/aurora-flow-theme.zip](/d:/Flyenv/www/aiticms-lite/docs/samples/aurora-flow-theme.zip)

## Batas Keamanan Tema

Validasi yang sekarang sudah diterapkan:

- hanya menerima file ZIP
- maksimal ukuran 10 MB
- MIME ZIP diverifikasi
- `manifest.json` wajib ada dan valid
- slug dinormalisasi
- path traversal seperti `../` ditolak
- file executable seperti PHP tidak diizinkan

Whitelist file saat ini:

- `json`
- `css`
- `js`
- `png`
- `jpg`
- `jpeg`
- `gif`
- `webp`
- `svg`
- `txt`
- `md`
- `html`

Keputusan ini bagus karena menjaga fitur tema tetap berada di level presentasi.

## Keterbatasan Tema Saat Ini

Sistem yang sekarang masih tergolong tahap awal.

Batasnya:

- belum ada preview tema sebelum aktivasi
- belum ada uninstall dari panel admin
- belum ada validasi struktur folder yang lebih ketat
- belum ada fallback render berbasis layout per tema
- belum ada versi kontrak tema, misalnya `engine_version`

Saran perbaikan bertahap:

1. Tambahkan field `engine_version` di manifest.
2. Tambahkan screenshot preview seperti `assets/screenshot.webp`.
3. Tambahkan fitur hapus tema nonaktif.
4. Tambahkan resolver view agar frontend bisa membaca tema aktif secara nyata.
5. Tambahkan checksum paket untuk distribusi resmi.

## Konsep Plugin

Plugin tidak boleh diposisikan seperti WordPress-style plugin bebas upload PHP.

Untuk `Aiticms-Lite`, plugin yang aman lebih cocok diposisikan sebagai:

- modul capability
- konfigurasi terstruktur
- integrasi provider yang sudah di-whitelist
- hook ke titik ekstensi yang sudah disediakan core

Dengan kata lain, plugin harus berjalan lewat kontrak sistem, bukan lewat file yang langsung dieksekusi.

## Arah Arsitektur Plugin Yang Disarankan

Model yang disarankan:

1. Core menyediakan daftar hook resmi.
2. Plugin mendeklarasikan capability lewat manifest.
3. Konfigurasi plugin disimpan sebagai data tervalidasi.
4. Eksekusi plugin hanya boleh melalui registry internal.
5. Paket pihak ketiga hanya boleh dipasang jika format dan signature-nya valid.

Contoh capability yang realistis:

- `content.filter`
- `meta.extend`
- `head.inject`
- `footer.inject`
- `comment.moderation`
- `analytics.provider`
- `cache.policy`

## Jenis Plugin Yang Layak Dibuat Dulu

Urutan yang paling aman dan bernilai:

1. `SEO Helper`
   Menambah metadata, schema, sitemap enrichment, dan audit konten.
2. `Analytics Connector`
   Integrasi GA4, Plausible, atau Matomo lewat provider terdaftar.
3. `Comment Shield`
   Menambah blocklist, cooldown, dan antrean moderasi komentar.
4. `Media Optimizer`
   Resize, kompresi, dan konversi gambar berbasis pipeline file yang aman.
5. `Cache Booster`
   Menyimpan cache halaman atau blok tertentu dengan invalidasi terkontrol.
6. `Form Builder Ringan`
   Form berbasis schema JSON, bukan kode PHP per form.

Alasan urutan ini bagus:

- manfaat bisnisnya cepat terasa
- risiko keamanannya lebih rendah
- implementasinya tidak memaksa runtime menjalankan kode asing

## Batas Aman Sistem Plugin

Aturan yang sebaiknya dianggap non-negotiable:

- jangan izinkan upload plugin PHP executable langsung dari panel admin
- jangan pakai `eval`, `include`, atau autoload dinamis dari folder upload
- semua hook harus eksplisit dan terdokumentasi
- semua input konfigurasi plugin harus tervalidasi
- semua output plugin tetap melewati sanitasi dan escaping yang sesuai konteks
- plugin tidak boleh menulis file di luar direktori yang diizinkan
- plugin tidak boleh membuat tabel atau migrasi tanpa workflow developer

## Bentuk Manifest Plugin Yang Disarankan

Jika nanti plugin mulai diimplementasikan, format awal yang aman bisa seperti ini:

```json
{
  "slug": "seo-helper",
  "name": "SEO Helper",
  "version": "1.0.0",
  "description": "Enhancement SEO untuk artikel dan halaman.",
  "capabilities": [
    "meta.extend",
    "content.filter"
  ],
  "config_schema": {
    "enable_schema": "boolean",
    "default_og_type": "string"
  }
}
```

Manifest seperti ini bagus karena:

- mudah divalidasi
- mudah dibaca admin dan developer
- belum membuka jalur eksekusi kode bebas

## Roadmap Implementasi Plugin

Tahap yang disarankan:

1. Tambahkan `PluginManifestValidator`.
2. Tambahkan `PluginRegistry` untuk daftar plugin terpasang.
3. Tambahkan storage state plugin aktif/nonaktif.
4. Tambahkan hook system sederhana di level service atau render pipeline.
5. Implementasikan satu plugin resmi internal sebagai acuan.
6. Baru setelah itu evaluasi dukungan paket pihak ketiga.

Jangan mulai dari marketplace plugin. Mulai dari plugin resmi internal lebih aman untuk mematangkan kontrak sistem.

## Rekomendasi Produk

Rekomendasi yang paling bagus untuk fase sekarang:

- lanjutkan sistem tema sebagai paket aset aman
- jadikan plugin sebagai sistem capability, bukan upload kode
- rilis 1 sampai 2 plugin resmi internal dulu
- dokumentasikan kontrak hook sebelum membuka ekstensi pihak ketiga

Dengan arah ini, `Aiticms-Lite` tetap ringan, aman, dan cukup fleksibel untuk tumbuh tanpa meniru model CMS yang rawan RCE.
