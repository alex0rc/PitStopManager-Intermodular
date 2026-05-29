(function () {
  const toggle = document.getElementById('adminSidebarToggle');
  const backdrop = document.getElementById('adminSidebarBackdrop');
  const body = document.body;

  if (!toggle || !backdrop) return;

  function openSidebar() {
    body.classList.add('admin-sidebar-open');
    backdrop.hidden = false;
    backdrop.setAttribute('aria-hidden', 'false');
    toggle.setAttribute('aria-expanded', 'true');
    toggle.setAttribute('aria-label', 'Cerrar menú');
  }

  function closeSidebar() {
    body.classList.remove('admin-sidebar-open');
    backdrop.hidden = true;
    backdrop.setAttribute('aria-hidden', 'true');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Abrir menú');
  }

  toggle.addEventListener('click', function () {
    if (body.classList.contains('admin-sidebar-open')) {
      closeSidebar();
    } else {
      openSidebar();
    }
  });

  backdrop.addEventListener('click', closeSidebar);

  document.querySelectorAll('.admin-sidebar a[href]').forEach(function (link) {
    link.addEventListener('click', function () {
      if (!window.matchMedia('(max-width: 991.98px)').matches) {
        return;
      }
      // --- Navegación móvil ---
      window.setTimeout(closeSidebar, 0);
    });
  });

  window.addEventListener('resize', function () {
    if (window.matchMedia('(min-width: 992px)').matches) {
      closeSidebar();
    }
  });
})();
