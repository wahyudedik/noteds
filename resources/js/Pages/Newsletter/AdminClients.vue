<template>
  <AuthenticatedLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Klien Newsletter</h1>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <div class="flex gap-2 mb-2">
            <button class="px-2 py-1 bg-gray-800 text-white rounded text-sm" @click="load">Reload</button>
          </div>
          <div class="border rounded max-h-80 overflow-auto">
            <table class="w-full text-sm">
              <thead><tr><th class="text-left p-1">Nama</th><th class="text-left p-1">Branding</th><th class="text-left p-1">Aksi</th></tr></thead>
              <tbody>
                <tr v-for="c in clients" :key="c.id">
                  <td class="p-1">{{ c.name }}</td>
                  <td class="p-1"><span class="text-xs">{{ c.branding }}</span></td>
                  <td class="p-1"><button class="px-2 py-1 bg-blue-600 text-white rounded text-xs" @click="edit(c)">Edit</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div>
          <h2 class="text-lg font-semibold mb-2">Form</h2>
          <div class="space-y-2">
            <input v-model="form.name" class="border rounded px-2 py-1 w-64" placeholder="Nama klien" />
            <textarea v-model="brandingText" class="border rounded px-2 py-1 w-full h-32" placeholder='Branding JSON {"font":"Arial","color":"#111827","accent":"#2563eb"}'></textarea>
            <textarea v-model="variablesText" class="border rounded px-2 py-1 w-full h-24" placeholder='Variables JSON {"footer":"..."}'></textarea>
            <div class="flex gap-2">
              <button class="px-3 py-2 bg-green-600 text-white rounded" @click="save">Simpan Klien</button>
              <button class="px-3 py-2 bg-gray-600 text-white rounded" @click="reset">Reset</button>
            </div>
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

const clients = ref([]);
const form = ref({ id: null, name: '' });
const brandingText = ref('');
const variablesText = ref('');

const load = async () => {
  const res = await fetch(route('admin.newsletter.clients.index'), { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    clients.value = data.clients || [];
  }
};
const edit = (c) => {
  form.value.id = c.id;
  form.value.name = c.name;
  brandingText.value = c.branding || '';
  variablesText.value = c.variables || '';
};
const reset = () => { form.value = { id: null, name: '' }; brandingText.value = ''; variablesText.value = ''; };
const safeJson = (t) => { try { return JSON.parse(t || '{}'); } catch { return {}; } };
const save = async () => {
  const res = await fetch(route('admin.newsletter.clients.save'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ id: form.value.id, name: form.value.name, branding: safeJson(brandingText.value), variables: safeJson(variablesText.value) }),
  });
  if (res.ok) {
    window.__toast?.add({ title: 'Klien', message: 'Disimpan', type: 'success' });
    load();
    reset();
  }
};
onMounted(load);
</script>
