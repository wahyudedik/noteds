<template>
  <MessagingLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Subscribers</h1>
      <div class="flex gap-2 mb-2">
        <input v-model="q" type="text" class="border rounded px-2 py-1" placeholder="Cari email" @input="load" />
        <button class="px-2 py-1 bg-gray-800 text-white rounded text-sm" @click="load">Reload</button>
      </div>
      <div class="border rounded max-h-72 overflow-auto">
        <table class="w-full text-sm">
          <thead><tr><th class="text-left p-1">Email</th><th class="text-left p-1">Nama</th><th class="text-left p-1">Status</th><th class="text-left p-1">Subscribed</th></tr></thead>
          <tbody>
            <tr v-for="s in subs" :key="s.id">
              <td class="p-1">{{ s.email }}</td>
              <td class="p-1">{{ s.name }}</td>
              <td class="p-1">{{ s.status }}</td>
              <td class="p-1">{{ s.subscribed_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <h2 class="text-lg font-semibold mt-4 mb-2">Template</h2>
      <div class="space-y-2">
        <input v-model="tplName" class="border rounded px-2 py-1 w-64" placeholder="Nama template" />
        <textarea v-model="tplHtml" class="border rounded px-2 py-1 w-full h-40" placeholder="HTML dengan {{nama}}"></textarea>
        <button class="px-3 py-2 bg-green-600 text-white rounded" @click="saveTemplate">Simpan Template</button>
      </div>
      <h2 class="text-lg font-semibold mt-4 mb-2">Campaign</h2>
      <div class="space-y-2">
        <input v-model="campName" class="border rounded px-2 py-1 w-64" placeholder="Nama campaign" />
        <input v-model="templateId" class="border rounded px-2 py-1 w-32" type="number" placeholder="Template ID" />
        <button class="px-3 py-2 bg-blue-600 text-white rounded" @click="createCampaign">Buat Campaign</button>
        <div v-if="campaignId" class="flex gap-2 items-center">
          <span>Campaign ID: {{ campaignId }}</span>
          <button class="px-2 py-1 bg-indigo-600 text-white rounded text-sm" @click="sendCampaign">Kirim</button>
        </div>
      </div>
    </div>
  </MessagingLayout>
  <ToastContainer />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';
import ToastContainer from '@/Components/Common/ToastContainer.vue';

const subs = ref([]);
const q = ref('');
const tplName = ref('');
const tplHtml = ref('');
const campName = ref('');
const templateId = ref(null);
const campaignId = ref(null);

const load = async () => {
  const url = route('admin.newsletter.subscribers.index') + (q.value ? ('?q=' + encodeURIComponent(q.value)) : '');
  const res = await fetch(url, { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    subs.value = data.subscribers || [];
  }
};

const saveTemplate = async () => {
  const res = await fetch(route('admin.newsletter.templates.store'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ name: tplName.value, html: tplHtml.value }),
  });
  if (res.ok) {
    const data = await res.json();
    templateId.value = data.id;
    window.__toast?.add({ title: 'Template', message: 'Disimpan', type: 'success' });
  }
};

const createCampaign = async () => {
  const res = await fetch(route('admin.newsletter.campaigns.store'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ name: campName.value, template_id: templateId.value }),
  });
  if (res.ok) {
    const data = await res.json();
    campaignId.value = data.id;
    window.__toast?.add({ title: 'Campaign', message: 'Dibuat', type: 'success' });
  }
};

const sendCampaign = async () => {
  const res = await fetch(route('admin.newsletter.campaigns.send', { campaign: campaignId.value }), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Accept': 'application/json' },
  });
  if (res.ok) window.__toast?.add({ title: 'Campaign', message: 'Diantrikan', type: 'success' });
};

onMounted(load);
</script>
