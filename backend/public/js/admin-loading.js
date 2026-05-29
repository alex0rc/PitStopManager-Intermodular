(function () {
  const GLOBAL_SCRIPTS = [
    'bootstrap.bundle',
    'admin-loading.js',
    'admin-mobile.js',
    'admin-map-picker.js',
    'admin-locations.js',
    'leaflet@1.9.4',
  ];
  const pageRoot = document.getElementById('admin-page');
  const contentLoading = document.getElementById('admin-content-loading');
  const legacyOverlay = document.getElementById('admin-loading');
  const spaMode = Boolean(pageRoot && contentLoading);

  let activeController = null;

  function showLoading() {
    if (spaMode) {
      contentLoading.hidden = false;
      contentLoading.classList.add('is-active');
      contentLoading.setAttribute('aria-busy', 'true');
      pageRoot.classList.add('is-loading');
      return;
    }
    if (!legacyOverlay) return;
    legacyOverlay.hidden = false;
    legacyOverlay.classList.add('is-active');
    legacyOverlay.setAttribute('aria-busy', 'true');
    document.body.classList.add('admin-is-loading');
  }

  function hideLoading() {
    if (spaMode) {
      contentLoading.classList.remove('is-active');
      contentLoading.hidden = true;
      contentLoading.setAttribute('aria-busy', 'false');
      pageRoot.classList.remove('is-loading');
      return;
    }
    if (!legacyOverlay) return;
    legacyOverlay.classList.remove('is-active');
    legacyOverlay.hidden = true;
    legacyOverlay.setAttribute('aria-busy', 'false');
    document.body.classList.remove('admin-is-loading');
  }

  function isAdminNavigation(anchor) {
    if (anchor.dataset.fullLoad !== undefined || anchor.dataset.noSpa !== undefined) {
      return false;
    }
    const href = anchor.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return false;
    if (anchor.target === '_blank' || anchor.hasAttribute('download')) return false;
    try {
      const url = new URL(href, window.location.origin);
      if (url.origin !== window.location.origin) return false;
      return url.pathname.startsWith('/admin');
    } catch {
      return false;
    }
  }

  function syncSidebarActive(doc) {
    const newByHref = new Map();
    doc.querySelectorAll('.admin-nav a[href]').forEach(function (link) {
      const href = link.getAttribute('href');
      if (href) newByHref.set(href, link);
    });

    document.querySelectorAll('.admin-nav a[href]').forEach(function (current) {
      const href = current.getAttribute('href');
      const newLink = href ? newByHref.get(href) : null;
      if (newLink) {
        current.className = newLink.className;
      }
    });
  }

  function syncMobileTitle(doc) {
    const titleEl = document.querySelector('.admin-mobile-title');
    const h1 = doc.querySelector('.admin-topbar h1');
    if (titleEl && h1) {
      titleEl.textContent = h1.textContent.trim();
    }
  }

  function runPageScripts(doc) {
    doc.body.querySelectorAll('script').forEach(function (oldScript) {
      const src = oldScript.getAttribute('src') || '';
      if (GLOBAL_SCRIPTS.some(function (marker) { return src.includes(marker); })) {
        return;
      }
      if (src && document.querySelector('script[src="' + src + '"]')) {
        return;
      }

      const script = document.createElement('script');
      if (src) {
        script.src = src;
      } else {
        script.textContent = oldScript.textContent;
      }
      document.body.appendChild(script);
      script.remove();
    });
  }

  function scrollContentTop() {
    const main = document.getElementById('admin-main');
    if (main) {
      main.scrollTop = 0;
    }
    window.scrollTo(0, 0);
  }

  async function navigate(url, push) {
    if (activeController) {
      activeController.abort();
    }
    activeController = new AbortController();
    const signal = activeController.signal;

    showLoading();

    try {
      const response = await fetch(url, {
        signal: signal,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'text/html',
        },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        window.location.href = url;
        return;
      }

      const html = await response.text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const nextPage = doc.getElementById('admin-page');

      if (!nextPage) {
        window.location.href = url;
        return;
      }

      pageRoot.innerHTML = nextPage.innerHTML;
      document.title = doc.title || document.title;
      syncSidebarActive(doc);
      syncMobileTitle(doc);
      runPageScripts(doc);
      scrollContentTop();

      if (push !== false) {
        history.pushState({ adminSpa: true, url: url }, '', url);
      }

      document.dispatchEvent(new CustomEvent('admin:page-loaded', { detail: { url: url } }));
    } catch (err) {
      if (err.name === 'AbortError') return;
      window.location.href = url;
    } finally {
      if (!signal.aborted) {
        hideLoading();
        activeController = null;
      }
    }
  }

  if (spaMode) {
    document.addEventListener('click', function (e) {
      const anchor = e.target.closest('a');
      if (!anchor || !isAdminNavigation(anchor)) return;
      if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

      const url = new URL(anchor.href, window.location.origin).href;
      if (url === window.location.href) return;

      e.preventDefault();
      navigate(url, true);
    });

    window.addEventListener('popstate', function (e) {
      if (e.state && e.state.adminSpa && e.state.url) {
        navigate(e.state.url, false);
      } else {
        window.location.reload();
      }
    });

    history.replaceState({ adminSpa: true, url: window.location.href }, '', window.location.href);
  } else {
    document.addEventListener('click', function (e) {
      const anchor = e.target.closest('a');
      if (!anchor || !isAdminNavigation(anchor)) return;
      if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      showLoading();
    });
  }

  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.noLoading !== undefined) return;
    if (spaMode && form.method.toLowerCase() === 'get') {
      return;
    }
    showLoading();
  });

  window.addEventListener('pageshow', hideLoading);
  document.addEventListener('DOMContentLoaded', hideLoading);
  hideLoading();
})();
