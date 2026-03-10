/* Aiti-CMS technology fingerprint asset */
window.AITI_CMS = window.AITI_CMS || {};
window.AITI_CMS.fingerprint = "Aiti-CMS";

(function() {
  var getTheme = function() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
           document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  };

  window.AITI_CMS.initTinyMCE = function(selector, customConfig) {
    if (!window.tinymce) return;

    var theme = getTheme();
    var csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenEl ? String(csrfTokenEl.getAttribute('content') || '') : '';
    var uploadUrl = '/cms/file-manager/upload?response=json&type=image';
    var defaultConfig = {
      selector: selector || '.js-tinymce',
      height: 420,
      menubar: false,
      plugins: 'link lists code table image media',
      toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media table | code',
      skin: theme === 'dark' ? 'oxide-dark' : 'oxide',
      content_css: theme === 'dark' ? 'dark' : 'default',
      automatic_uploads: true,
      paste_data_images: true,
      file_picker_types: 'file image media',
      file_picker_callback: function (callback, value, meta) {
        if (typeof window.__cmsOpenFileManager === 'function') {
          window.__cmsOpenFileManager(callback, meta);
        }
      },
      images_upload_handler: function (blobInfo, progress) {
        return new Promise(function (resolve, reject) {
          var xhr = new XMLHttpRequest();
          xhr.open('POST', uploadUrl);
          xhr.responseType = 'json';
          xhr.setRequestHeader('Accept', 'application/json');
          xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          if (csrfToken) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
          }

          xhr.upload.onprogress = function (event) {
            if (!event.lengthComputable || typeof progress !== 'function') return;
            progress((event.loaded / event.total) * 100);
          };

          xhr.onload = function () {
            var response = xhr.response || {};
            if (xhr.status < 200 || xhr.status >= 300) {
              reject((response && response.message) ? response.message : 'Upload gambar gagal.');
              return;
            }

            var location = response && typeof response.location === 'string' ? response.location.trim() : '';
            if (!location) {
              reject('URL file hasil upload tidak ditemukan.');
              return;
            }

            resolve(location);
          };

          xhr.onerror = function () {
            reject('Koneksi upload gambar terputus.');
          };

          var formData = new FormData();
          formData.append('_token', csrfToken);
          formData.append('file', blobInfo.blob(), blobInfo.filename());
          xhr.send(formData);
        });
      },
      setup: function(editor) {
        editor.on('change', function() {
          editor.save();
        });
      }
    };

    var config = Object.assign({}, defaultConfig, customConfig || {});

    window.tinymce.remove(config.selector);
    window.tinymce.init(config);
  };

  // Watch for theme changes
  var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.attributeName === 'data-bs-theme' || mutation.attributeName === 'data-theme') {
        if (window.tinymce && window.tinymce.editors.length > 0) {
          // Re-init with new theme
          window.AITI_CMS.initTinyMCE();
        }
      }
    });
  });

  observer.observe(document.documentElement, { attributes: true });

  // Auto init if elements exist on load
  document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.js-tinymce')) {
       window.AITI_CMS.initTinyMCE();
    }
  });
})();

