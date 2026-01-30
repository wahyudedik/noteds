<template>
  <AuthenticatedLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Editor Template</h1>
      <div class="grid grid-cols-3 gap-4">
        <div class="border rounded p-2">
          <div class="font-semibold mb-2">Komponen</div>
          <div draggable="true" class="border rounded p-2 mb-2 cursor-move" @dragstart="drag('text')">Teks</div>
          <div draggable="true" class="border rounded p-2 mb-2 cursor-move" @dragstart="drag('image')">Gambar</div>
          <div draggable="true" class="border rounded p-2 mb-2 cursor-move" @dragstart="drag('button')">Tombol</div>
          <div draggable="true" class="border rounded p-2 mb-2 cursor-move" @dragstart="drag('divider')">Pembatas</div>
          <div draggable="true" class="border rounded p-2 mb-2 cursor-move" @dragstart="drag('hero')">Hero</div>
          <div draggable="true" class="border rounded p-2 mb-2 cursor-move" @dragstart="drag('banner')">Banner</div>
          <div draggable="true" class="border rounded p-2 mb-2 cursor-move" @dragstart="drag('columns')">Kolom</div>
        </div>
        <div class="border rounded p-2" @dragover.prevent @drop="drop">
          <div class="font-semibold mb-2">Kanvas</div>
          <div v-for="(el, idx) in canvas" :key="idx" class="border rounded p-2 mb-2 bg-white">
            <input v-if="el.type === 'text'" v-model="el.content" class="border rounded px-2 py-1 w-full" placeholder="Teks" />
            <input v-else-if="el.type === 'image'" v-model="el.src" class="border rounded px-2 py-1 w-full" placeholder="URL Gambar" />
            <div v-else-if="el.type === 'button'" class="space-y-1">
              <input v-model="el.label" class="border rounded px-2 py-1 w-full" placeholder="Label Tombol" />
              <input v-model="el.href" class="border rounded px-2 py-1 w-full" placeholder="Link" />
            </div>
            <div v-else-if="el.type === 'divider'" class="text-xs">Pembatas</div>
            <div v-else-if="el.type === 'hero'" class="space-y-1">
              <input v-model="el.title" class="border rounded px-2 py-1 w-full" placeholder="Judul" />
              <input v-model="el.subtitle" class="border rounded px-2 py-1 w-full" placeholder="Subjudul" />
              <input v-model="el.background" class="border rounded px-2 py-1 w-full" placeholder="Background hex/#2563eb" />
            </div>
            <div v-else-if="el.type === 'banner'" class="space-y-1">
              <input v-model="el.text" class="border rounded px-2 py-1 w-full" placeholder="Teks banner" />
              <input v-model="el.color" class="border rounded px-2 py-1 w-full" placeholder="Warna teks" />
              <input v-model="el.bg" class="border rounded px-2 py-1 w-full" placeholder="Warna latar" />
            </div>
            <div v-else-if="el.type === 'columns'" class="space-y-1">
              <textarea v-model="el.columns" class="border rounded px-2 py-1 w-full" placeholder='JSON kolom [{"title":"...","text":"..."}]'></textarea>
            </div>
            <button class="mt-2 px-2 py-1 bg-red-600 text-white rounded text-xs" @click="remove(idx)">Hapus</button>
          </div>
        </div>
        <div class="border rounded p-2">
          <div class="font-semibold mb-2">Pratinjau</div>
          <div class="flex gap-2 items-center mb-2">
            <label class="text-sm">Klien</label>
            <select v-model="client" class="border rounded px-2 py-1">
              <option value="default">Default</option>
              <option value="brandA">Brand A</option>
              <option value="brandB">Brand B</option>
            </select>
          </div>
          <div class="border rounded p-2">
            <div v-html="renderHtml()" class="email-preview"></div>
          </div>
          <div class="flex gap-2 mt-2">
            <input v-model="tplName" class="border rounded px-2 py-1 w-64" placeholder="Nama template" />
            <select v-model="categoryId" class="border rounded px-2 py-1">
              <option :value="null">Tanpa Kategori</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <input v-model="categoryMeta" class="border rounded px-2 py-1 w-64" placeholder='Metadata JSON {"tag":"promo"}' />
            <button class="px-3 py-2 bg-green-600 text-white rounded" @click="save">Simpan</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
  <ToastContainer />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ToastContainer from '@/Components/Common/ToastContainer.vue';

const dragging = ref(null);
const canvas = ref([]);
const tplName = ref('');
const categories = ref([]);
const categoryId = ref(null);
const categoryMeta = ref('{}');
const clients = ref([]);
const selectedClientId = ref(null);

const drag = (type) => { dragging.value = type; };
const drop = () => {
  const type = dragging.value;
  if (!type) return;
  if (type === 'text') canvas.value.push({ type, content: 'Teks' });
  else if (type === 'image') canvas.value.push({ type, src: '' });
  else if (type === 'button') canvas.value.push({ type, label: 'Klik', href: '#' });
  else if (type === 'divider') canvas.value.push({ type });
  else if (type === 'hero') canvas.value.push({ type, title: 'Judul', subtitle: 'Subjudul', background: '#2563eb' });
  else if (type === 'banner') canvas.value.push({ type, text: 'Promo', color: '#ffffff', bg: '#111827' });
  else if (type === 'columns') canvas.value.push({ type, columns: '[]' });
  dragging.value = null;
};
const remove = (idx) => {
  canvas.value.splice(idx, 1);
};
const client = ref('default');
const serverBranding = ref(null);
const brandingPresets = {
  default: { color: '#111827', accent: '#2563eb', font: 'Arial, sans-serif' },
};
const loadData = async () => {
  const [catRes, cliRes] = await Promise.all([
    fetch(route('admin.newsletter.categories.index'), { credentials: 'include', headers: { 'Accept': 'application/json' } }),
    fetch(route('admin.newsletter.clients.index'), { credentials: 'include', headers: { 'Accept': 'application/json' } }),
  ]);
  if (catRes.ok) { const d = await catRes.json(); categories.value = d.categories || []; }
  if (cliRes.ok) { const d = await cliRes.json(); clients.value = d.clients || []; }
};
onMounted(loadData);
const renderHtml = () => {
  let html = '';
  let b = brandingPresets[client.value] || brandingPresets.default;
  if (selectedClientId.value) {
    const c = clients.value.find(x => String(x.id) === String(selectedClientId.value));
    if (c && c.branding) {
      try { serverBranding.value = JSON.parse(c.branding); } catch { serverBranding.value = null; }
      if (serverBranding.value) b = { color: serverBranding.value.color || b.color, accent: serverBranding.value.accent || b.accent, font: serverBranding.value.font || b.font };
    }
  }
  html += `<div style="font-family:${b.font};color:${b.color}">`;
  for (const el of canvas.value) {
    if (el.type === 'text') html += `<p style="font-size:14px;line-height:1.6">${el.content}</p>`;
    else if (el.type === 'image') html += `<img src="${el.src}" style="max-width:100%;height:auto" />`;
    else if (el.type === 'button') html += `<p><a href="${el.href}" style="display:inline-block;background:${b.accent};color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none">${el.label}</a></p>`;
    else if (el.type === 'divider') html += `<hr />`;
    else if (el.type === 'hero') html += `<div style="padding:24px;background:${el.background}"><h1 style="color:#fff">${el.title}</h1><p style="color:#fff">${el.subtitle}</p></div>`;
    else if (el.type === 'banner') html += `<div style="padding:12px;background:${el.bg};color:${el.color};text-align:center">${el.text}</div>`;
    else if (el.type === 'columns') {
      try {
        const cols = JSON.parse(el.columns || '[]');
        html += `<table width="100%"><tr>`;
        cols.forEach(c => {
          html += `<td style="vertical-align:top;padding:8px"><h4>${c.title || ''}</h4><p>${c.text || ''}</p></td>`;
        });
        html += `</tr></table>`;
      } catch {}
    }
  }
  html += `</div>`;
  return html;
};
const save = async () => {
  const res = await fetch(route('admin.newsletter.templates.store'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ name: tplName.value, html: renderHtml(), category_id: categoryId.value, metadata: safeJson(categoryMeta.value) }),
  });
  if (res.ok) window.__toast?.add({ title: 'Template', message: 'Disimpan', type: 'success' });
};
const safeJson = (s) => {
  try { return JSON.parse(s || '{}'); } catch { return {}; }
};
</script>

<style>
.email-preview { max-width: 480px; }
@media (min-width:768px) { .email-preview { max-width: 640px; } }
@media (min-width:1024px) { .email-preview { max-width: 768px; } }
</style>
