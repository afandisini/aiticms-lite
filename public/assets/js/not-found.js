(function () {
  function createParticles() {
    var particlesContainer = document.getElementById("particles");
    if (!particlesContainer) {
      return;
    }

    var particleCount = 20;
    for (var i = 0; i < particleCount; i += 1) {
      var particle = document.createElement("div");
      particle.className = "particle";
      particle.style.left = Math.random() * 100 + "%";
      particle.style.top = Math.random() * 100 + "%";
      particle.style.animationDelay = Math.random() * 15 + "s";
      particle.style.animationDuration = 15 + Math.random() * 10 + "s";
      particlesContainer.appendChild(particle);
    }
  }

  function showRequestedPath() {
    var requestedPathEl = document.getElementById("requestedPath");
    if (!requestedPathEl) {
      return;
    }

    var fromData = String(requestedPathEl.getAttribute("data-requested-path") || "").trim();
    var path = fromData !== "" ? fromData : (window.location.pathname || "/halaman-tidak-ditemukan");
    requestedPathEl.textContent = path;
  }

  function initParallax() {
    if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      return;
    }

    var orbs = document.querySelectorAll(".glow-orb");
    if (orbs.length < 1) {
      return;
    }

    document.addEventListener("mousemove", function (event) {
      var x = (event.clientX / window.innerWidth - 0.5) * 20;
      var y = (event.clientY / window.innerHeight - 0.5) * 20;

      orbs.forEach(function (orb, index) {
        var factor = (index + 1) * 0.5;
        if (orb.classList.contains("glow-orb-3")) {
          orb.style.transform =
            "translate(calc(-50% + " + (x * factor) + "px), calc(-50% + " + (y * factor) + "px))";
          return;
        }

        orb.style.transform = "translate(" + (x * factor) + "px, " + (y * factor) + "px)";
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    createParticles();
    showRequestedPath();
    initParallax();
  });
})();
