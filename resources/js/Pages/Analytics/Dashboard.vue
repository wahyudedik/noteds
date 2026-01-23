<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({ events: Array, summary: Object, role: String });
const byType = computed(() => {
  const map = {};
  (props.events || []).forEach(e => {
    map[e.type] = (map[e.type] || 0) + 1;
  });
  return map;
});
const dailyCounts = computed(() => {
  const map = {};
  (props.events || []).forEach(e => {
    const d = (new Date(e.created_at)).toLocaleDateString('en-CA');
    map[d] = (map[d] || 0) + 1;
  });
  return map;
});
const errorDaily = computed(() => {
  const map = {};
  const isError = (e) => {
    if (!e) return false;
    const t = String(e.type || '').toLowerCase();
    if (t.includes('error')) return true;
    const p = e.payload || {};
    return !!(p.error || p.status === 'error' || p.code === 'server_error' || p.code === 'too_many_requests');
  };
  (props.events || []).forEach(e => {
    if (!isError(e)) return;
    const d = (new Date(e.created_at)).toLocaleDateString('en-CA');
    map[d] = (map[d] || 0) + 1;
  });
  return map;
});
const timeframe = ref('7d');
const filterDaily = (data) => {
  const keys = Object.keys(data);
  const days = timeframe.value === '7d' ? 7 : timeframe.value === '30d' ? 30 : keys.length;
  const selected = keys.slice(-days);
  const vals = selected.map(k => data[k]);
  return { labels: selected, values: vals };
};

onMounted(() => {
  const ctx1 = document.getElementById('byTypeChart');
  new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: Object.keys(byType.value),
      datasets: [{ label: 'Events by Type', data: Object.values(byType.value), backgroundColor: '#22c55e' }]
    }
  });
  const ctx2 = document.getElementById('dailyChart');
  const d1 = filterDaily(dailyCounts.value);
  new Chart(ctx2, {
    type: 'line',
    data: {
      labels: d1.labels,
      datasets: [{ label: 'Daily Events', data: d1.values, borderColor: '#f59e0b' }]
    }
  });
  const ectx = document.getElementById('errorChart');
  const d2 = filterDaily(errorDaily.value);
  new Chart(ectx, {
    type: 'line',
    data: {
      labels: d2.labels,
      datasets: [{ label: 'Error Rate (events/day)', data: d2.values, borderColor: '#ef4444' }]
    }
  });
});
</script>

<template>
  <Head title="Analytics Dashboard" />
  <div class="max-w-5xl mx-auto p-4">
    <h1 class="text-xl font-semibold mb-4">Analytics Dashboard</h1>
    <div class="flex items-center gap-3 mb-4">
      <label class="text-sm text-gray-600 dark:text-gray-300">Timeframe</label>
      <select v-model="timeframe" class="px-2 py-1 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
        <option value="7d">Last 7 days</option>
        <option value="30d">Last 30 days</option>
        <option value="all">All</option>
      </select>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h2 class="text-sm font-semibold mb-2">Events by Type</h2>
        <canvas id="byTypeChart" height="200"></canvas>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h2 class="text-sm font-semibold mb-2">Daily Events</h2>
        <canvas id="dailyChart" height="200"></canvas>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4 md:col-span-2">
        <h2 class="text-sm font-semibold mb-2">Error Rate</h2>
        <canvas id="errorChart" height="200"></canvas>
      </div>
    </div>
  </div>
</template>
