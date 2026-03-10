<?php
/** @var array<int, array<string, mixed>> $rows */
/** @var array<int, array<string, mixed>> $roles */
/** @var string $search */
/** @var array<string, mixed>|null $editUser */
?>
<header class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Access</h1>
    <p class="text-secondary mb-0">Pengaturan role dan status aktif user.</p>
  </div>
</header>

<div class="card shadow-sm mb-3">
  <div class="card-body py-3">
    <form class="row g-2" method="get" action="/cms/system/access">
      <div class="col-12 col-md-10">
        <input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="Search : nama / username / email / role">
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
      </div>
    </form>
  </div>
</div>

<?php if (is_array($editUser)): ?>
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h2 class="h6 mb-3">Edit Access User: <?= e((string) ($editUser['name'] ?? $editUser['username'] ?? '-')) ?></h2>
      <form method="post" action="/cms/system/access/update/<?= e((string) ($editUser['id'] ?? 0)) ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Role</label>
            <?php $selectedRole = (int) ($editUser['roles'] ?? 0); ?>
            <select class="form-select" name="roles" required>
              <option value="">- pilih role -</option>
              <?php foreach ($roles as $role): ?>
                <?php $rid = (int) ($role['id'] ?? 0); ?>
                <option value="<?= e((string) $rid) ?>" <?= $selectedRole === $rid ? 'selected' : '' ?>>
                  <?= e((string) ($role['name_role'] ?? ('Role #' . $rid))) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Status User</label>
            <?php $active = (int) ($editUser['active'] ?? 0); ?>
            <select class="form-select" name="active" required>
              <option value="1" <?= $active === 1 ? 'selected' : '' ?>>Aktif</option>
              <option value="0" <?= $active !== 1 ? 'selected' : '' ?>>Nonaktif</option>
            </select>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-floppy me-1"></i>Simpan Access</button>
          <a class="btn btn-outline-secondary btn-sm" href="/cms/system/access"><i class="bi bi-x-circle me-1"></i>Batal</a>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 js-datatable">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Updated</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows === []): ?>
            <tr>
              <td colspan="8" class="text-center text-secondary py-4">Data user kosong.</td>
            </tr>
          <?php endif; ?>
          <?php foreach ($rows as $idx => $row): ?>
            <?php
              $status = ((int) ($row['active'] ?? 0)) === 1 ? 'Aktif' : 'Nonaktif';
              $badge = ((int) ($row['active'] ?? 0)) === 1 ? 'text-bg-success' : 'text-bg-secondary';
            ?>
            <tr>
              <td><?= e((string) ($idx + 1)) ?></td>
              <td><?= e((string) ($row['name'] ?? '-')) ?></td>
              <td><?= e((string) ($row['username'] ?? '-')) ?></td>
              <td><?= e((string) ($row['email'] ?? '-')) ?></td>
              <td><?= e((string) ($row['name_role'] ?? '-')) ?></td>
              <td><span class="badge <?= e($badge) ?>"><?= e($status) ?></span></td>
              <td><?= e((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? '-'))) ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="/cms/system/access?edit=<?= e((string) ($row['id'] ?? 0)) ?>"><i class="bi bi-sliders me-1"></i>Atur</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
