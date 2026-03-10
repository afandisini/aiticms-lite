<?php
/** @var string $title */
/** @var string $content */

use App\Support\Branding;

$cmsUser = isset($user) && is_array($user) ? $user : null;
$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$flashMessage = trim((string) ($message ?? ''));
$flashType = trim((string) ($messageType ?? ''));
$pickerModeLayout = ((string) ($pickerMode ?? '') === '1') || ((string) ($_GET['picker'] ?? '') === '1');
$cmsName = Branding::cmsName();
$frameworkName = Branding::frameworkName();
$adminPanelTitle = Branding::adminPanelTitle();
$cmsMeta = Branding::cmsMeta();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="generator" content="<?= e(Branding::generatorMeta()) ?>">
  <meta name="framework" content="<?= e(Branding::frameworkMeta()) ?>">
  <meta name="application-name" content="<?= e($cmsName) ?>">
  <meta name="cms" content="<?= e($cmsMeta) ?>">
  <title><?= e($title ?? $adminPanelTitle) ?></title>
  <script>
    (function () {
      var root = document.documentElement;
      var key = 'aiti_theme';
      var resolveTheme = function () {
        var stored = null;
        try {
          stored = localStorage.getItem(key);
        } catch (e) {}
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        return stored === 'dark' || stored === 'light' ? stored : (prefersDark ? 'dark' : 'light');
      };
      var applyTheme = function (theme) {
        var normalized = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', normalized);
        root.setAttribute('data-bs-theme', normalized);
      };
      applyTheme(resolveTheme());
    })();
  </script>
  <script>
    (function () {
      var root = document.documentElement;
      var key = 'sidebar:state';
      var state = 'closed';
      try {
        var saved = localStorage.getItem(key);
        if (saved === 'open' || saved === 'closed') {
          state = saved;
        }
      } catch (e) {}

      root.classList.add('sidebar-no-transitions');
      root.classList.remove('sidebar-open', 'sidebar-closed');
      root.classList.add(state === 'open' ? 'sidebar-open' : 'sidebar-closed');
    })();
  </script>
  <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/assets/css/cms.css">
  <link rel="stylesheet" href="/assets/vendor/datatables/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="/assets/aiti-cms/core.css">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body class="cms-body<?= $cmsUser === null ? ' cms-auth-page' : '' ?>">
  <?php if ($cmsUser !== null && !$pickerModeLayout): ?>
    <div class="cms-shell" id="cmsShell">
      <?= view('cms/layouts/partials/sidebar', ['uri' => $uri]) ?>
      <div class="cms-main">
        <?= view('cms/layouts/partials/navbar', ['cmsUser' => $cmsUser]) ?>
        <main class="container-fluid py-3 py-lg-4">
          <?php if ($flashMessage !== ''): ?>
            <div class="alert rounded-4 alert-dismissible fade show cms-floating-alert <?= $flashType === 'success' ? 'alert-success' : 'alert-danger' ?> shadow-sm" role="alert">
              <span><?= e($flashMessage) ?></span>
              <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <?= $content ?>
        </main>
        <?= view('cms/layouts/partials/footer') ?>
      </div>
      <button id="brandToggle" class="cms-brand-toggle" type="button" title="<?= e($adminPanelTitle) ?>"><?= e(Branding::adminBadge()) ?></button>
    </div>
  <?php elseif ($cmsUser !== null && $pickerModeLayout): ?>
    <main class="container-fluid py-3">
      <?php if ($flashMessage !== ''): ?>
        <div class="alert rounded-4 alert-dismissible fade show <?= $flashType === 'success' ? 'alert-success' : 'alert-danger' ?> shadow-sm" role="alert">
          <span><?= e($flashMessage) ?></span>
          <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <?= $content ?>
    </main>
  <?php else: ?>
    <button class="btn btn-outline-secondary rounded-pill btn-sm cms-theme-toggle-floating cms-theme-toggle-btn" type="button" data-theme-toggle aria-label="Toggle dark mode">
      <i class="bi bi-moon-stars-fill"></i>
      <i class="bi bi-sun-fill"></i>
    </button>
    <main class="container py-4 py-lg-5">
      <?= $content ?>
    </main>
  <?php endif; ?>

  <script src="/assets/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
  <script src="/assets/vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="/assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
  <script src="/assets/aiti-cms/core.js"></script>
  <script>
    (function () {
      var root = document.documentElement;
      var key = 'aiti_theme';
      var applyTheme = function (theme) {
        var norm = theme === 'dark' ? 'dark' : 'light';
        root.setAttribute('data-theme', norm);
        root.setAttribute('data-bs-theme', norm);
      };
      var currentTheme = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
      applyTheme(currentTheme);

      document.querySelectorAll('[data-theme-toggle]').forEach(function (toggleButton) {
        toggleButton.addEventListener('click', function () {
          currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
          applyTheme(currentTheme);
          try {
            localStorage.setItem(key, currentTheme);
          } catch (e) {}
        });
      });

      window.addEventListener('storage', function (event) {
        if (!event || event.key !== key) {
          return;
        }

        if (event.newValue === 'dark' || event.newValue === 'light') {
          currentTheme = event.newValue;
          applyTheme(currentTheme);
          return;
        }

        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        currentTheme = prefersDark ? 'dark' : 'light';
        applyTheme(currentTheme);
      });

      window.addEventListener('pageshow', function () {
        try {
          var storedTheme = localStorage.getItem(key);
          if (storedTheme === 'dark' || storedTheme === 'light') {
            currentTheme = storedTheme;
          }
        } catch (e) {}
        applyTheme(currentTheme);
      });

      window.__cmsFilePickerCallback = null;
      window.__cmsFilePickerModal = null;
      window.__cmsFilePickerFrame = null;
      window.__cmsFilePickerMeta = {};
      window.__cmsCloseFileManager = function () {
        if (!window.__cmsFilePickerModal) return;
        window.__cmsFilePickerModal.hide();
      };

      window.__cmsOpenFileManager = function (callback, meta) {
        if (typeof callback !== 'function') return;
        window.__cmsFilePickerCallback = callback;
        window.__cmsFilePickerMeta = meta && typeof meta === 'object' ? meta : {};

        var type = (meta && typeof meta.filetype === 'string' && meta.filetype) ? meta.filetype : 'file';
        var isMultiple = !!(meta && meta.multiple);
        var url = '/cms/file-manager?picker=1&type=' + encodeURIComponent(type);
        if (isMultiple) {
          url += '&multiple=1';
        }
        var modalEl = document.getElementById('filePickerModal');
        var frameEl = document.getElementById('filePickerFrame');
        if (!modalEl || !(frameEl instanceof HTMLIFrameElement) || !window.bootstrap || !window.bootstrap.Modal) {
          window.__cmsFilePickerCallback = null;
          window.__cmsFilePickerMeta = {};
          return;
        }

        frameEl.src = url;
        window.__cmsFilePickerFrame = frameEl;
        window.__cmsFilePickerModal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        window.__cmsFilePickerModal.show();
      };

      window.__cmsOnFilePicked = function (url, meta) {
        if (typeof window.__cmsFilePickerCallback !== 'function') return;
        window.__cmsFilePickerCallback(url, meta || {});
        if (window.__cmsFilePickerMeta && window.__cmsFilePickerMeta.multiple) {
          return;
        }
        window.__cmsFilePickerCallback = null;
        window.__cmsFilePickerMeta = {};
        if (window.__cmsFilePickerModal) {
          window.__cmsFilePickerModal.hide();
        }
      };

      document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('filePickerModal');
        if (!modalEl) return;
        modalEl.addEventListener('hidden.bs.modal', function () {
          if (window.__cmsFilePickerFrame instanceof HTMLIFrameElement) {
            window.__cmsFilePickerFrame.src = 'about:blank';
          }
          window.__cmsFilePickerCallback = null;
          window.__cmsFilePickerMeta = {};
        });
      });
    })();
  </script>
  <div class="modal fade" id="filePickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Pilih File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0" style="height: 75vh;">
          <iframe id="filePickerFrame" title="File Manager Picker" src="about:blank" style="width:100%;height:100%;border:0;"></iframe>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function () {
      var closeAlert = function (alertEl) {
        if (!alertEl || alertEl.classList.contains('d-none')) return;
        alertEl.classList.remove('show');
        window.setTimeout(function () {
          alertEl.classList.add('d-none');
        }, 180);
      };

      document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) return;
        if (!target.classList.contains('btn-close')) return;
        var alertEl = target.closest('.alert');
        closeAlert(alertEl);
      });

      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.alert.alert-dismissible').forEach(function (alertEl) {
          window.setTimeout(function () {
            closeAlert(alertEl);
          }, 3000);
        });
      });
    })();
  </script>
  <script>
    (function () {
      if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
      // Keep pager compact but always numeric: Sebelumnya 1 2 3 ... n Selanjutnya
      jQuery.fn.DataTable.ext.pager.numbers_length = 7;
      jQuery.fn.dataTable.ext.errMode = 'console';

      jQuery(function () {
        jQuery('.js-datatable').each(function () {
          var table = jQuery(this);
          if (table.hasClass('dataTable')) return;

          var headerColumns = table.find('thead th, thead td').length;
          var bodyRows = table.find('tbody tr');
          if (bodyRows.length === 1) {
            var onlyRowCells = bodyRows.eq(0).children('th, td');
            if (onlyRowCells.length === 1) {
              var colspan = parseInt(String(onlyRowCells.eq(0).attr('colspan') || '1'), 10);
              if (!Number.isNaN(colspan) && colspan === headerColumns) {
                table.find('tbody').empty();
              }
            }
          }

          var orderingAttr = String(table.data('ordering') ?? '').toLowerCase();
          var orderingEnabled = !(orderingAttr === 'false' || orderingAttr === '0' || orderingAttr === 'no');

          var options = {
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            pagingType: 'simple_numbers',
            ordering: orderingEnabled,
            order: []
          };

          jQuery.getJSON('/assets/vendor/datatables/i18n/id.json').done(function (lang) {
            options.language = lang;
            table.DataTable(options);
          }).fail(function () {
            table.DataTable(options);
          });
        });
      });
    })();
  </script>
  <script>
    (function () {
      var root = document.documentElement;
      var shell = document.getElementById('cmsShell');
      var toggle = document.getElementById('sidebarToggle');
      var brandToggle = document.getElementById('brandToggle');
      var mobileClose = document.getElementById('mobileSidebarClose');
      if (!shell || !toggle || !brandToggle || !mobileClose) return;

      var key = 'sidebar:state';
      var mobileTabletQuery = window.matchMedia('(max-width: 992px)');

      var applyState = function (state) {
        var normalized = state === 'open' ? 'open' : 'closed';
        root.classList.remove('sidebar-open', 'sidebar-closed');
        root.classList.add(normalized === 'open' ? 'sidebar-open' : 'sidebar-closed');
        try {
          localStorage.setItem(key, normalized);
        } catch (e) {}
      };

      var toggleState = function () {
        var nextState = root.classList.contains('sidebar-open') ? 'closed' : 'open';
        applyState(nextState);
      };

      var closeSidebar = function () {
        applyState('closed');
      };

      ['data-bs-toggle', 'data-bs-target', 'aria-controls'].forEach(function (attr) {
        toggle.removeAttribute(attr);
        brandToggle.removeAttribute(attr);
      });

      document.addEventListener('DOMContentLoaded', function () {
        root.classList.remove('sidebar-no-transitions');
      });

      toggle.addEventListener('click', function (event) {
        event.preventDefault();
        toggleState();
      });
      brandToggle.addEventListener('click', function (event) {
        event.preventDefault();
        toggleState();
      });
      mobileClose.addEventListener('click', function (event) {
        event.preventDefault();
        closeSidebar();
      });

      document.querySelectorAll('#cmsSidebar .nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
          if (mobileTabletQuery.matches) {
            closeSidebar();
          }
        });
      });
    })();
  </script>
  <script>
    (function () {
      var normalizeTag = function (value) {
        var cleaned = String(value || '').trim();
        if (!cleaned) return '';
        return cleaned.replace(/\s+/g, ' ');
      };

      var splitTags = function (value) {
        return String(value || '')
          .split(',')
          .map(normalizeTag)
          .filter(function (item, index, arr) {
            return item !== '' && arr.indexOf(item) === index;
          });
      };

      var mountTagsInput = function (input) {
        if (!(input instanceof HTMLInputElement)) return;
        if (input.dataset.tagsReady === '1') return;

        input.dataset.tagsReady = '1';
        input.type = 'hidden';

        var wrapper = document.createElement('div');
        wrapper.className = 'cms-tag-input';

        var editor = document.createElement('input');
        editor.type = 'text';
        editor.className = 'cms-tag-editor';
        editor.placeholder = input.getAttribute('placeholder') || 'ketik lalu koma';

        var tags = splitTags(input.value);

        var syncInputValue = function () {
          input.value = tags.join(', ');
        };

        var render = function () {
          wrapper.innerHTML = '';
          tags.forEach(function (tag, index) {
            var chip = document.createElement('span');
            chip.className = 'cms-tag-chip';
            chip.textContent = tag;

            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.setAttribute('aria-label', 'Hapus tag ' + tag);
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', function () {
              tags.splice(index, 1);
              syncInputValue();
              render();
            });

            chip.appendChild(closeBtn);
            wrapper.appendChild(chip);
          });

          wrapper.appendChild(editor);
        };

        var addTag = function (raw) {
          var tag = normalizeTag(raw);
          if (!tag) return;
          if (tags.indexOf(tag) !== -1) return;
          tags.push(tag);
          syncInputValue();
          render();
        };

        editor.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' || event.key === ',') {
            event.preventDefault();
            addTag(editor.value);
            editor.value = '';
            return;
          }

          if (event.key === 'Backspace' && editor.value === '' && tags.length > 0) {
            tags.pop();
            syncInputValue();
            render();
          }
        });

        editor.addEventListener('blur', function () {
          if (editor.value.trim() !== '') {
            addTag(editor.value);
            editor.value = '';
          }
        });

        wrapper.addEventListener('click', function () {
          editor.focus();
        });

        input.parentNode.insertBefore(wrapper, input.nextSibling);
        syncInputValue();
        render();
      };

      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.cms-main input[name="tags"]').forEach(mountTagsInput);
      });
    })();
  </script>
</body>
</html>
