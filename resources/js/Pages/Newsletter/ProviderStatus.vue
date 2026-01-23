<template>
  <MessagingLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Status Provider</h1>
      <div class="border rounded p-2 mb-4">
        <table class="w-full text-sm">
          <thead><tr><th class="text-left p-1">Provider</th><th class="text-left p-1">Last Success</th><th class="text-left p-1">Failures</th><th class="text-left p-1">Last Error</th><th class="text-left p-1">Actions</th></tr></thead>
          <tbody>
            <tr v-for="s in status" :key="s.id">
              <td class="p-1">{{ s.provider }}</td>
              <td class="p-1">{{ s.last_success_at }}</td>
              <td class="p-1">{{ s.failures_count }}</td>
              <td class="p-1">{{ s.last_error }}</td>
              <td class="p-1">
                <button class="px-2 py-1 bg-blue-600 text-white rounded text-xs" @click="resync(s.provider)">Re-sync</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex gap-2 items-center mb-2">
        <label class="text-sm">Filter provider</label>
        <input v-model="filterProvider" class="border rounded px-2 py-1 w-64" placeholder="sendgrid/mailgun/ses" />
        <button class="px-2 py-1 bg-gray-800 text-white rounded text-sm" @click="loadLogs">Load Logs</button>
      </div>
      <div class="border rounded p-2">
        <table class="w-full text-sm">
          <thead><tr><th class="text-left p-1">Time</th><th class="text-left p-1">Provider</th><th class="text-left p-1">Valid</th><th class="text-left p-1">Signature</th><th class="text-left p-1">Error</th></tr></thead>
          <tbody>
            <tr v-for="l in logs" :key="l.id">
              <td class="p-1">{{ l.created_at }}</td>
              <td class="p-1">{{ l.provider }}</td>
              <td class="p-1">{{ l.valid ? 'Yes' : 'No' }}</td>
              <td class="p-1">{{ l.signature }}</td>
              <td class="p-1">{{ l.error }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </MessagingLayout>
  <ToastContainer />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';
import ToastContainer from '@/Components/Common/ToastContainer.vue';

const status = ref([]);
const logs = ref([]);
const filterProvider = ref('');

const loadStatus = async () => {
  const res = await fetch(route('admin.newsletter.providers.status'), { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    status.value = data.status || [];
  }
};
const resync = async (provider) => {
  await fetch(route('admin.newsletter.providers.resync'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ provider }),
  });
  window.__toast?.add({ title: 'Resync', message: provider, type: 'success' });
  loadStatus();
};
const loadLogs = async () => {
  const url = route('admin.newsletter.webhooks.logs') + (filterProvider.value ? ('?provider=' + encodeURIComponent(filterProvider.value)) : '');
  const res = await fetch(url, { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    logs.value = data.logs || [];
  }
};
onMounted(() => { loadStatus(); loadLogs(); });
</script>
