import { importKeyFromBase64, exportKeyToBase64, encryptBlob, decryptArrayBuffer } from '@/Utils/encryption';

describe('Encryption utils', () => {
  test('import/export base64 key', async () => {
    const raw = crypto.getRandomValues(new Uint8Array(32));
    const key = await crypto.subtle.importKey('raw', raw, 'AES-GCM', true, ['encrypt','decrypt']);
    const b64 = await exportKeyToBase64(key);
    const key2 = await importKeyFromBase64(b64);
    expect(key2).toBeDefined();
  });

  test('encrypt/decrypt blob', async () => {
    const raw = crypto.getRandomValues(new Uint8Array(32));
    const key = await crypto.subtle.importKey('raw', raw, 'AES-GCM', true, ['encrypt','decrypt']);
    const content = new Blob([new Uint8Array([1,2,3,4,5])], { type: 'application/octet-stream' });
    const enc = await encryptBlob(content, key);
    const buf = await enc.arrayBuffer();
    const dec = await decryptArrayBuffer(buf, key);
    const arr = new Uint8Array(dec);
    expect(arr[0]).toBe(1);
    expect(arr[4]).toBe(5);
  });
});
