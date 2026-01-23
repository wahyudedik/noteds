const get = (k, d) => {
  try { const v = localStorage.getItem(k); return v !== null ? JSON.parse(v) : d; } catch { return d; }
};
const set = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch {} };

export const applyHighContrast = (enabled) => {
  const root = document.documentElement;
  root.classList.toggle('hc', !!enabled);
  set('a11y.hc', !!enabled);
};
export const applyFontScale = (percent) => {
  const p = Math.max(100, Math.min(200, percent || 100));
  document.documentElement.style.fontSize = p + '%';
  set('a11y.fontScale', p);
};
export const getReduceMotion = () => {
  try { const v = localStorage.getItem('a11y.reduceMotion'); return v ? JSON.parse(v) : 'off'; } catch { return 'off'; }
};
export const setReduceMotion = (level) => { set('a11y.reduceMotion', level); };
export const applyReduceMotion = (level) => {
  const root = document.documentElement;
  root.classList.remove('rm-light','rm-medium','rm-full');
  if (level === 'light') root.classList.add('rm-light');
  else if (level === 'medium') root.classList.add('rm-medium');
  else if (level === 'full') root.classList.add('rm-full');
  else if (level === 'system') {
    const prefers = systemPrefersReducedMotion();
    if (prefers) root.classList.add('rm-medium');
    else root.classList.remove('rm-light','rm-medium','rm-full');
  }
};
export const systemPrefersReducedMotion = () => {
  return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};
export const getComponentReduceMotion = () => {
  try { const v = localStorage.getItem('a11y.componentReduceMotion'); return v ? JSON.parse(v) : {}; } catch { return {}; }
};
export const setComponentReduceMotion = (prefs) => { set('a11y.componentReduceMotion', prefs || {}); };
export const applyComponentReduceMotion = (component, level) => {
  const root = document.documentElement;
  const levels = ['light','medium','full'];
  levels.forEach(l => root.classList.remove(`rm-${component}-${l}`));
  if (levels.includes(level)) root.classList.add(`rm-${component}-${level}`);
};
export const initA11y = () => {
  const hc = get('a11y.hc', false);
  const fs = get('a11y.fontScale', 100);
  const rm = getReduceMotion();
  const kh = getKeyboardHints();
  applyHighContrast(hc);
  applyFontScale(fs);
  applyReduceMotion(rm);
  applyKeyboardHints(kh);
  const comp = getComponentReduceMotion();
  Object.entries(comp).forEach(([k,v]) => applyComponentReduceMotion(k, v));
  try {
    window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (e) => {
      const current = getReduceMotion();
      if (current === 'system') applyReduceMotion('system');
    });
  } catch {}
};
export const announce = (message) => {
  const el = document.getElementById('aria-live');
  if (el) { el.textContent = ''; setTimeout(() => { el.textContent = message; }, 50); }
};

export const getKeyboardHints = () => {
  try { const v = localStorage.getItem('a11y.keyboardHints'); return v ? JSON.parse(v) : false; } catch { return false; }
};
export const setKeyboardHints = (enabled) => { set('a11y.keyboardHints', !!enabled); };
export const applyKeyboardHints = (enabled) => {
  const root = document.documentElement;
  root.classList.toggle('kn', !!enabled);
};
