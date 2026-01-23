export async function importKeyFromBase64(base64) {
  const raw = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
  return crypto.subtle.importKey('raw', raw, 'AES-GCM', true, ['encrypt','decrypt']);
}

export async function exportKeyToBase64(key) {
  const raw = await crypto.subtle.exportKey('raw', key);
  const bytes = String.fromCharCode(...new Uint8Array(raw));
  return btoa(bytes);
}

export async function encryptBlob(blob, key) {
  const iv = crypto.getRandomValues(new Uint8Array(12));
  const data = await blob.arrayBuffer();
  const ct = await crypto.subtle.encrypt({ name: 'AES-GCM', iv }, key, data);
  const merged = new Uint8Array(iv.length + ct.byteLength);
  merged.set(iv, 0);
  merged.set(new Uint8Array(ct), iv.length);
  return new Blob([merged], { type: 'application/octet-stream' });
}

export async function decryptArrayBuffer(buf, key) {
  const data = new Uint8Array(buf);
  const iv = data.slice(0, 12);
  const ct = data.slice(12);
  const pt = await crypto.subtle.decrypt({ name: 'AES-GCM', iv }, key, ct);
  return pt;
}
