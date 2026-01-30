<template>
  <AuthenticatedLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Analytics Kampanye</h1>
      <div class="flex gap-2 mb-2">
        <input v-model="campaignId" type="number" class="border rounded px-2 py-1 w-40" placeholder="Campaign ID" />
        <button class="px-2 py-1 bg-gray-800 text-white rounded text-sm" @click="load">Load</button>
        <button class="px-2 py-1 bg-green-700 text-white rounded text-sm" @click="exportCsv">Export CSV</button>
        <button class="px-2 py-1 bg-indigo-700 text-white rounded text-sm" @click="exportPdf">Export PDF</button>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div class="border rounded p-3">
          <div class="text-sm">Open Rate: {{ overview.open_rate }}%</div>
          <div class="text-sm">Click Rate: {{ overview.click_rate }}%</div>
          <div class="text-sm">Unsubscribe: {{ overview.unsubscribe_count }}</div>
        </div>
        <div class="border rounded p-3">
          <canvas ref="chartEl"></canvas>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Chart } from 'chart.js/auto';

const campaignId = ref(null);
const overview = ref({ open_rate: 0, click_rate: 0, unsubscribe_count: 0 });
const chartEl = ref(null);
let chart = null;

const load = async () => {
  const url = route('admin.newsletter.analytics.overview') + (campaignId.value ? ('?campaign_id=' + campaignId.value) : '');
  const res = await fetch(url, { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    overview.value = await res.json();
    const data = {
      labels: ['Open', 'Click', 'Unsubscribe'],
      datasets: [{ label: 'Kinerja', data: [overview.value.open_rate, overview.value.click_rate, overview.value.unsubscribe_count], backgroundColor: ['#10b981', '#2563eb', '#ef4444'] }]
    };
    if (!chart) chart = new Chart(chartEl.value.getContext('2d'), { type: 'bar', data });
    else { chart.data = data; chart.update(); }
  }
};
const exportCsv = () => {
  const url = route('admin.newsletter.analytics.export.csv') + (campaignId.value ? ('?campaign_id=' + campaignId.value) : '');
  window.open(url, '_blank');
};
const exportPdf = () => {
  const url = route('admin.newsletter.analytics.export.pdf') + (campaignId.value ? ('?campaign_id=' + campaignId.value) : '');
  window.open(url, '_blank');
};
</script>
