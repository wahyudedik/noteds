<script setup>
import { onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  title: { type: String, required: true },
  points: { type: Array, default: () => [] },
  labels: { type: Array, default: () => [] },
  data: { type: Array, default: () => [] },
  type: { type: String, default: 'line' },
  color: { type: String, default: '#4F46E5' },
});

const canvasEl = ref(null);
let chart = null;

const render = () => {
  const labels = (props.points && props.points.length)
    ? (props.points || []).map(p => new Date(p.t).toLocaleTimeString())
    : (props.labels || []);
  const data = (props.points && props.points.length)
    ? (props.points || []).map(p => p.v)
    : (props.data || []);
  if (chart) {
    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    chart.update();
    return;
  }
  chart = new Chart(canvasEl.value, {
    type: props.type || 'line',
    data: {
      labels,
      datasets: [{
        label: props.title,
        data,
        borderColor: props.color,
        backgroundColor: props.type === 'bar' ? props.color : 'rgba(79,70,229,0.2)',
        tension: 0.3,
        pointRadius: props.type === 'bar' ? 0 : 0,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
      },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { precision: 0 } }
      }
    }
  });
};

onMounted(render);
watch(() => props.points, render);
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
    <div class="text-sm font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ title }}</div>
    <div style="height:240px">
      <canvas ref="canvasEl"></canvas>
    </div>
  </div>
</template>
