<template>
  <MessagingLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Dashboard Newsletter</h1>
      <div class="grid grid-cols-2 gap-4">
        <div class="border rounded p-3">
          <h2 class="text-lg font-semibold mb-2">Provider Status</h2>
          <table class="w-full text-sm">
            <thead><tr><th class="text-left p-1">Provider</th><th class="text-left p-1">Last Success</th><th class="text-left p-1">Failures</th></tr></thead>
            <tbody>
              <tr v-for="s in status" :key="s.id">
                <td class="p-1">{{ s.provider }}</td>
                <td class="p-1">{{ s.last_success_at }}</td>
                <td class="p-1">{{ s.failures_count }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="border rounded p-3">
          <h2 class="text-lg font-semibold mb-2">Analytics Overview</h2>
          <div class="flex gap-2 mb-2">
            <input v-model="campaignId" type="number" class="border rounded px-2 py-1 w-40" placeholder="Campaign ID" />
            <button class="px-2 py-1 bg-gray-800 text-white rounded text-sm" @click="loadOverview">Load</button>
          </div>
          <div class="text-sm">Open Rate: {{ overview.open_rate }}%</div>
          <div class="text-sm">Click Rate: {{ overview.click_rate }}%</div>
          <div class="text-sm">Unsubscribe: {{ overview.unsubscribe_count }}</div>
        </div>
      </div>
    </div>
  </MessagingLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';

const status = ref([]);
const overview = ref({ open_rate: 0, click_rate: 0, unsubscribe_count: 0 });
const campaignId = ref(null);

const loadStatus = async () => {
  const res = await fetch(route('admin.newsletter.providers.status'), { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    status.value = data.status || [];
  }
};
const loadOverview = async () => {
  const url = route('admin.newsletter.analytics.overview') + (campaignId.value ? ('?campaign_id=' + campaignId.value) : '');
  const res = await fetch(url, { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) overview.value = await res.json();
};
onMounted(() => { loadStatus(); loadOverview(); });
</script>
