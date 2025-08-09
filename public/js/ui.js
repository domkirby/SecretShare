// Shared UI helpers: tooltips + toasts
(function initTooltips(){
  try {
    const triggers = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...triggers].forEach(el => new bootstrap.Tooltip(el));
  } catch(e) {
    // bootstrap not loaded yet; that's fine on pages that don't include it
  }
})();

if (!window.showToast) {
  window.showToast = function(message, opts = {}) {
    let container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
      document.body.appendChild(container);
    }
    const el = document.createElement('div');
    el.className = 'toast ss align-items-center border-0';
    el.setAttribute('role', 'status');
    el.setAttribute('aria-live', 'polite');
    el.innerHTML = '<div class="d-flex"><div class="toast-body">'
      + (message ?? '')
      + '</div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
    container.appendChild(el);
    try {
      const t = new bootstrap.Toast(el, { delay: opts.delay ?? 2000, autohide: opts.autohide ?? true });
      t.show();
      el.addEventListener('hidden.bs.toast', () => el.remove());
    } catch(e) {
      // If Bootstrap JS isn't present, fall back to console
      console.log('Toast:', message);
    }
  }
}

// Theme management (light | dark | system)
(function initTheme() {
  const THEME_KEY = 'ss-theme';
  const media = window.matchMedia('(prefers-color-scheme: dark)');

  function systemPrefersDark() { return media.matches; }

  function applyTheme(theme) {
    let effective = theme;
    if (theme === 'system') {
      effective = systemPrefersDark() ? 'dark' : 'light';
    }
    if (effective === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      document.documentElement.style.colorScheme = 'dark';
    } else {
      document.documentElement.setAttribute('data-theme', 'light');
      document.documentElement.style.colorScheme = 'light';
    }
  }

  function getStoredTheme() {
    return localStorage.getItem(THEME_KEY) || 'system';
  }
  function setStoredTheme(val) {
    localStorage.setItem(THEME_KEY, val);
  }

  // Initialize
  const current = getStoredTheme();
  applyTheme(current);

  // Back-compat: select control if present
  function syncSelect(val) {
    const sel = document.getElementById('themeSelect');
    if (sel) sel.value = val;
  }

  // New: button group support
  function syncButtons(val) {
    const buttons = document.querySelectorAll('[data-theme-choice]');
    if (!buttons.length) return;
    buttons.forEach(btn => {
      // Normalize to outline-primary
      btn.classList.remove('btn-outline-secondary');
      btn.classList.add('btn-outline-primary');
      const isActive = btn.dataset.themeChoice === val;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-pressed', String(isActive));
    });
  }

  syncSelect(current);
  syncButtons(current);

  // Listen for changes in system preference when using 'system'
  media.addEventListener?.('change', () => {
    if (getStoredTheme() === 'system') applyTheme('system');
  });

  // Bindings
  document.addEventListener('DOMContentLoaded', () => {
    // Select handler (if exists)
    const sel = document.getElementById('themeSelect');
    if (sel) {
      sel.addEventListener('change', (e) => {
        const val = e.target.value; // 'light' | 'dark' | 'system'
        setStoredTheme(val);
        applyTheme(val);
        syncButtons(val);
        if (window.showToast) showToast(`Theme: ${val}`);
      });
    }

    // Button group handler
    const btns = document.querySelectorAll('[data-theme-choice]');
    btns.forEach(btn => {
      // Normalize classes at bind time too
      btn.classList.remove('btn-outline-secondary');
      btn.classList.add('btn-outline-primary');
      btn.addEventListener('click', () => {
        const val = btn.dataset.themeChoice;
        setStoredTheme(val);
        applyTheme(val);
        syncSelect(val);
        syncButtons(val);
        if (window.showToast) showToast(`Theme: ${val}`);
      });
    });

    // Optional cycle toggle
    const toggleBtn = document.getElementById('themeToggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        const order = ['system','light','dark'];
        const cur = getStoredTheme();
        const next = order[(order.indexOf(cur) + 1) % order.length];
        setStoredTheme(next);
        applyTheme(next);
        syncSelect(next);
        syncButtons(next);
        if (window.showToast) showToast(`Theme: ${next}`);
      });
    }
  });
})();
