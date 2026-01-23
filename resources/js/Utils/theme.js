export const getStoredTheme = () => {
  try { return localStorage.getItem('theme'); } catch { return null; }
};
export const storeTheme = (t) => {
  try { localStorage.setItem('theme', t); } catch {}
};
export const applyTheme = (t) => {
  const root = document.documentElement;
  if (t === 'dark') root.classList.add('dark');
  else root.classList.remove('dark');
};
export const systemPrefersDark = () => {
  return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
};
export const initTheme = () => {
  const stored = getStoredTheme();
  let theme = stored ? stored : (systemPrefersDark() ? 'dark' : 'light');
  if (stored === 'system') theme = systemPrefersDark() ? 'dark' : 'light';
  applyTheme(theme);
  try {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      const current = getStoredTheme();
      if (!current || current === 'system') applyTheme(e.matches ? 'dark' : 'light');
    });
  } catch {}
};
export const toggleTheme = () => {
  const root = document.documentElement;
  const isDark = root.classList.contains('dark');
  const next = isDark ? 'light' : 'dark';
  applyTheme(next);
  storeTheme(next);
  return next;
};
export const setTheme = (mode) => {
  if (mode === 'system') {
    storeTheme('system');
    applyTheme(systemPrefersDark() ? 'dark' : 'light');
    return 'system';
  }
  if (mode === 'dark' || mode === 'light') {
    storeTheme(mode);
    applyTheme(mode);
    return mode;
  }
  return getStoredTheme() || (systemPrefersDark() ? 'dark' : 'light');
};
