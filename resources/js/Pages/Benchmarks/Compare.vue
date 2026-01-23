<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

const data = ref([]);

onMounted(async () => {
  try {
    const res = await axios.get('/storage/benchmarks/top_compare.json');
    data.value = res.data || [];
    const labels = data.value.map(r => r.key);
    const before = data.value.map(r => r.query_time_ms_before);
    const after = data.value.map(r => r.query_time_ms_after);
    const improvement = data.value.map(r => r.improvement_pct);
    new Chart(document.getElementById('compareChart'), {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'Before (ms)', data: before, backgroundColor: '#ef4444' },
          { label: 'After (ms)', data: after, backgroundColor: '#22c55e' }
        ]
      }
    });
    new Chart(document.getElementById('improvementChart'), {
      type: 'line',
      data: {
        labels,
        datasets: [{ label: 'Improvement (%)', data: improvement, borderColor: '#3b82f6' }]
      }
    });
  } catch {}
});
</script>

<template>
  <Head title="Benchmark Compare" />
  <div class="max-w-5xl mx-auto p-4">
    <h1 class="text-xl font-semibold mb-4">Benchmark Comparison (Before vs After)</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h2 class="text-sm font-semibold mb-2">Response Time</h2>
        <canvas id="compareChart" height="200"></canvas>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h2 class="text-sm font-semibold mb-2">Improvement Percentage</h2>
        <canvas id="improvementChart" height="200"></canvas>
      </div>
    </div>
  </div>
</template>
