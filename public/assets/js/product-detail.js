(function () {
  var mainImage = document.getElementById('productMainImage');
  if (mainImage) {
    var thumbs = document.querySelectorAll('[data-product-thumb]');
    var previewTrigger = document.getElementById('productPreviewTrigger');
    var modalImage = document.getElementById('productImagePreviewModalImage');
    var modalEl = document.getElementById('productImagePreviewModal');
    var previewModal = modalEl && window.bootstrap && window.bootstrap.Modal
      ? window.bootstrap.Modal.getOrCreateInstance(modalEl)
      : null;

    var syncModalImage = function () {
      if (!modalImage) {
        return;
      }
      modalImage.setAttribute('src', String(mainImage.getAttribute('src') || ''));
      modalImage.setAttribute('alt', String(mainImage.getAttribute('alt') || 'Preview gambar produk'));
    };

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        var nextSrc = String(thumb.getAttribute('data-src') || '').trim();
        if (!nextSrc) {
          return;
        }

        mainImage.setAttribute('src', nextSrc);
        thumbs.forEach(function (item) {
          item.classList.remove('is-active');
        });
        thumb.classList.add('is-active');
        syncModalImage();
      });
    });

    if (previewTrigger) {
      previewTrigger.addEventListener('click', function () {
        syncModalImage();
        if (previewModal) {
          previewModal.show();
        }
      });
    }

    syncModalImage();
  }

  var faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(function (item) {
    var header = item.querySelector('.faq-header');
    if (!header) {
      return;
    }

    header.addEventListener('click', function () {
      var isOpen = item.classList.contains('open');
      faqItems.forEach(function (entry) {
        entry.classList.remove('open');
      });

      if (!isOpen) {
        item.classList.add('open');
      }
    });
  });
})();
