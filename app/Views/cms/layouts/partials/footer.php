<?php

use App\Support\Branding;
?>
<footer class="cms-footer px-3 px-lg-4 py-3">
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
    <small class="text-secondary mb-0"><?= e(Branding::adminFooterLeft()) ?></small>
    <small class="text-secondary mb-0"><?= e(Branding::adminFooterRight()) ?></small>
  </div>
</footer>
