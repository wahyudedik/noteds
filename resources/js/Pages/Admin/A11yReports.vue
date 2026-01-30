<template>
  <AuthenticatedLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Accessibility Reports</h1>
      <div class="flex gap-2 items-end mb-3">
        <div>
          <label class="text-xs">Context</label>
          <input v-model="filters.context" class="border rounded px-2 py-1" placeholder="/path or component" />
        </div>
        <div>
          <label class="text-xs">From</label>
          <input v-model="filters.from" type="date" class="border rounded px-2 py-1" />
        </div>
        <div>
          <label class="text-xs">To</label>
          <input v-model="filters.to" type="date" class="border rounded px-2 py-1" />
        </div>
        <button class="px-2 py-1 bg-gray-800 text-white rounded text-sm" @click="load">Filter</button>
      </div>
      <div class="grid md:grid-cols-2 gap-4">
        <div class="border rounded p-2">
          <h2 class="text-sm font-semibold mb-2">Violations by Rule</h2>
          <table class="w-full text-sm">
            <thead><tr><th class="text-left p-1">Rule</th><th class="text-left p-1">Count</th></tr></thead>
            <tbody>
              <tr v-for="(c,rule) in summary.rules" :key="rule">
                <td class="p-1">{{ rule }}</td>
                <td class="p-1">{{ c }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="border rounded p-2">
          <h2 class="text-sm font-semibold mb-2">Trend (Last 14)</h2>
          <canvas ref="chartEl" height="140"></canvas>
        </div>
      </div>
      <div class="mt-4 border rounded p-2">
        <h2 class="text-sm font-semibold mb-2">Latest Reports</h2>
        <table class="w-full text-sm">
          <thead><tr><th class="text-left p-1">Time</th><th class="text-left p-1">Context</th><th class="text-left p-1">Violations</th></tr></thead>
          <tbody>
            <tr v-for="r in reports" :key="r.id">
              <td class="p-1">{{ r.created_at }}</td>
              <td class="p-1">{{ r.context }}</td>
              <td class="p-1">{{ r.violation_count }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Chart from 'chart.js/auto';

const reports = ref([]);
const summary = ref({ rules: {} });
const chartEl = ref(null);
const chart = ref(null);
const filters = ref({ context: '', from: '', to: '' });

const load = async () => {
  const params = new URLSearchParams();
  if (filters.value.context) params.set('context', filters.value.context);
  if (filters.value.from) params.set('from', filters.value.from);
  if (filters.value.to) params.set('to', filters.value.to);
  const res = await fetch(route('admin.a11y.reports.index') + '?' + params.toString(), { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    reports.value = data.reports || [];
    summary.value = data.summary || { rules: {} };
    drawChart(data.trend || []);
  }
};
const drawChart = (trend) => {
  const labels = trend.map(t => t.date);
  const values = trend.map(t => t.count);
  if (chart.value) chart.value.destroy();
  chart.value = new Chart(chartEl.value, {
    type: 'line',
    data: { labels, datasets: [{ label: 'Violations', data: values, borderColor: '#6366f1' }] },
    options: { responsive: true, plugins: { legend: { display: false } } },
  });
};
onMounted(load);
watch(filters, () => {}, { deep: true });
</script>
