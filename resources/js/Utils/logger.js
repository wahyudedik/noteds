export async function logEvent(event, payload = {}) {
  try {
    await fetch('/api/logs', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ event, payload }),
    });
  } catch {}
}
