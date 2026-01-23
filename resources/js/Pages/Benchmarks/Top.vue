<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';

const props = defineProps({ rows: Array });
const charts = ref([]);

const mountCharts = () => {
  const datasetsByMetric = {};
  (props.rows || []).forEach(r => {
    const key = r.metric;
    datasetsByMetric[key] = datasetsByMetric[key] || [];
    datasetsByMetric[key].push(r);
  });
  const configs = [
    { id: 'timeChart', label: 'Query Time (ms)', value: r => r.query_time_ms },
    { id: 'memoryChart', label: 'Memory Usage (KB)', value: r => r.memory_diff_kb },
    { id: 'countChart', label: 'Items Returned', value: r => r.count },
    { id: 'throughputChart', label: 'Throughput (items/sec)', value: r => (r.count / (r.query_time_ms/1000)) },
  ];
  configs.forEach(cfg => {
    const ctx = document.getElementById(cfg.id);
    if (!ctx) return;
    const data = {
      labels: (props.rows || []).map(r => `${r.period}/${r.metric}`),
      datasets: [
        {
          label: cfg.label,
          data: (props.rows || []).map(r => cfg.value(r)),
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99,102,241,0.2)',
        }
      ]
    };
    charts.value.push(new Chart(ctx, { type: 'line', data }));
  });
};

onMounted(mountCharts);
</script>

<template>
  <Head title="Benchmarks - Top Query" />
  <div class="max-w-5xl mx-auto p-4">
    <h1 class="text-xl font-semibold mb-4">Top Query Benchmarks</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h2 class="text-sm font-semibold mb-2">Query Time</h2>
        <canvas id="timeChart" height="180"></canvas>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h2 class="text-sm font-semibold mb-2">Memory Usage</h2>
        <canvas id="memoryChart" height="180"></canvas>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h2 class="text-sm font-semibold mb-2">Items Returned</h2>
        <canvas id="countChart" height="180"></canvas>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h2 class="text-sm font-semibold mb-2">Throughput</h2>
        <canvas id="throughputChart" height="180"></canvas>
      </div>
    </div>
  </div>
</template>
