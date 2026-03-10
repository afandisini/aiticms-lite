<main class="front-user-page">
  <div class="container front-user-shell">
    <header class="front-user-header">
      <p class="front-user-eyebrow">Area Pengguna</p>
      <h1 class="front-user-title">Lainnya</h1>
      <p class="front-user-subtitle">Halaman ini merangkum fitur user frontend yang sudah tersedia dan yang masih butuh infrastruktur tambahan.</p>
    </header>

    <div class="row g-4">
      <div class="col-12 col-lg-6">
        <div class="front-user-card bg-white h-100">
          <div class="front-user-card-head">
            <h2>Reset Password / Lupa Sandi</h2>
            <p>Belum aktif</p>
          </div>
          <p class="mb-0">Flow lupa sandi belum bisa dinyalakan karena repo saat ini belum punya token reset, email sender, dan halaman verifikasi reset password.</p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="front-user-card bg-white h-100">
          <div class="front-user-card-head">
            <h2>Verifikasi Email User</h2>
            <p>Belum aktif</p>
          </div>
          <p class="mb-0">Verifikasi akun via link email untuk user baru atau user belum aktif juga masih menunggu infrastruktur token, mail queue/sender, dan endpoint callback verifikasi.</p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="front-user-card bg-white h-100">
          <div class="front-user-card-head">
            <h2>Pembayaran Frontend</h2>
            <p>Rekomendasi implementasi</p>
          </div>
          <p class="mb-0">Pilihan yang lebih aman dan paling selaras dengan struktur database saat ini adalah Midtrans, karena tabel transaksi sudah menyimpan `midtrans_id` dan `snap_token`. QRIS tetap bisa dipakai lewat Midtrans agar validasi dan callback pembayaran tidak dibangun manual dari nol.</p>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="front-user-card bg-white h-100">
          <div class="front-user-card-head">
            <h2>Status User Area Saat Ini</h2>
            <p>Sudah aktif</p>
          </div>
          <p class="mb-0">User login frontend sekarang sudah bisa melihat profil aktif, mengubah profil, melihat histori transaksi, membuka detail transaksi, mengakses file premium, dan logout langsung dari panel user di frontpage.</p>
        </div>
      </div>
    </div>
  </div>
</main>
