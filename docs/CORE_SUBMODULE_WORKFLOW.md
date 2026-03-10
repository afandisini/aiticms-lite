# Core Workflow: AitiCore-Flex + Aiticms-Lite

Dokumen ini menjelaskan batas yang harus dijaga antara:

- `AitiCore-Flex` sebagai framework dasar
- `Aiticms-Lite` sebagai modul CMS/aplikasi

Tujuan utama workflow ini adalah menjaga 2 repo tetap terpisah, mudah dirilis, dan mudah dipahami oleh pengguna baru.

## Posisi Tiap Repo

1. `AitiCore-Flex`
   Repo framework yang menyediakan fondasi runtime, bootstrap, helper inti, routing, middleware, session, dan komponen dasar lain.

2. `Aiticms-Lite`
   Repo modul CMS yang berisi:
   - controller aplikasi
   - service bisnis
   - view CMS dan frontend
   - route aplikasi
   - migrasi database
   - asset dan dokumentasi modul

## Struktur Integrasi

Di workspace ini pemisahan logisnya adalah:

- kode modul ada di repo utama
- kode framework dapat disimpan terpisah dan disinkronkan ke layer runtime yang dipakai aplikasi

Folder yang harus dianggap sebagai area modul:

```text
app/
database/
docs/
public/
routes/
scripts/
tests/
```

Folder yang harus diperlakukan sebagai area framework/core:

```text
system/
core/
bootstrap/
aiti
```

Catatan:

- bila mengubah area framework, dokumentasikan alasannya dengan jelas
- bila perubahan hanya milik CMS, jangan dorong ke area framework

## Alur Pengguna Baru

Konsep distribusi publik yang diinginkan:

1. pengguna mengambil repo `AitiCore-Flex`
2. pengguna mengambil repo `Aiticms-Lite`
3. pengguna mengikuti panduan integrasi yang disediakan project

Tujuan alur ini:

- jejak adopsi framework tetap tercatat
- jejak adopsi modul tetap tercatat
- pengguna memahami bahwa CMS ini bukan framework mandiri

## Alur Developer

Saat bekerja pada repo `Aiticms-Lite`:

1. tentukan dulu apakah perubahan termasuk framework atau modul
2. jika perubahan hanya modul, kerjakan di repo modul
3. jika perubahan menyentuh framework, pisahkan catatan dan workflow-nya
4. setelah update framework masuk, sinkronkan ke runtime aplikasi dengan prosedur yang terdokumentasi

## Aturan Saat Update Framework

- jangan ubah branding `AitiCore-Flex` menjadi branding CMS
- jangan gabungkan release note framework dan modul menjadi satu produk
- semua perubahan yang memengaruhi kontrak framework harus dicatat di docs
- jika ada patch lokal pada layer framework, tandai apakah:
  - akan di-upstream ke repo framework, atau
  - hanya patch sementara untuk modul ini

## Aturan Saat Update Modul CMS

- gunakan branding `Aiticms-Lite` untuk UI CMS dan frontend modul
- gunakan `AitiCore-Flex` hanya saat menyebut framework
- hindari wording yang membuat `Aiticms-Lite` tampak sebagai framework
- dokumentasikan fitur yang sengaja dibuang dari versi lite

## Checklist Review

Sebelum merge atau release, cek:

- apakah batas framework vs modul masih jelas
- apakah docs masih menyebut alur 2 repo
- apakah branding admin/front masih memakai:
  - `CMS: Aiticms-Lite`
  - `Framework: AitiCore-Flex`
- apakah perubahan di area core memang diperlukan
- apakah perubahan modul tetap tidak mengotori area framework tanpa alasan

## Ringkasan Praktis

Gunakan aturan cepat berikut:

- ubah `app/`, `routes/`, `database/`, `public/` = biasanya perubahan modul
- ubah `system/`, `bootstrap/`, `aiti` = biasanya perubahan framework
- jika ragu, dokumentasikan dulu batasnya sebelum coding
