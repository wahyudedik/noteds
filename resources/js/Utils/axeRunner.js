export const runAxeIfDev = async () => {
  if (!import.meta.env.DEV) return;
  if (window.axe) {
    const results = await window.axe.run(document, { runOnly: { type: 'tag', values: ['wcag2aa'] } });
    report(results);
    return;
  }
  const s = document.createElement('script');
  s.src = 'https://unpkg.com/axe-core@4.7.0/axe.min.js';
  s.onload = async () => {
    const results = await window.axe.run(document, { runOnly: { type: 'tag', values: ['wcag2aa'] } });
    report(results);
  };
  document.head.appendChild(s);
};

const report = async (results) => {
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    await fetch(route('a11y.report'), {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ context: location.pathname, report: results }),
    });
  } catch {}
  // Also log to console for developer
  console.group('axe-core accessibility report');
  console.log(results);
  console.groupEnd();
};
