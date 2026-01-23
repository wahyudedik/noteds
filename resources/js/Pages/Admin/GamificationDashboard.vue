<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title, Tooltip as ChartTooltip, Legend, LineElement, PointElement, LinearScale, CategoryScale,
} from 'chart.js';

ChartJS.register(Title, ChartTooltip, Legend, LineElement, PointElement, LinearScale, CategoryScale);

const props = defineProps({
  configs: { type: Array, default: () => [] },
  trend: { type: Array, default: () => [] },
  leaderboard: { type: Object, default: () => ({ daily: [], weekly: [], monthly: [] }) },
});

const data = ref({
  labels: props.trend.map(t => t.date),
  datasets: [{
    label: 'Total Points',
    data: props.trend.map(t => t.total),
    borderColor: '#2563eb',
    backgroundColor: 'rgba(37, 99, 235, 0.2)',
    tension: 0.25,
    fill: true,
  }],
});
const options = ref({
  responsive: true,
  plugins: {
    legend: { display: true },
    title: { display: true, text: 'Tren Poin 30 Hari' },
  },
  scales: {
    x: { display: true },
    y: { display: true, beginAtZero: true },
  },
});

const updateConfig = async (cfg) => {
  await fetch(route('admin.gamification.configs.update', { key: cfg.key }), {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ points: cfg.points, enabled: cfg.enabled }),
  });
};
</script>

<template>
  <Head title="Gamification Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Gamification</h2>
        <a :href="route('admin.gamification.export')" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md">Export CSV</a>
      </div>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <Line :data="data" :options="options" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Leaderboard (Daily)</div>
            <ul class="space-y-1">
              <li v-for="row in leaderboard.daily" :key="row.user_id" class="flex items-center justify-between text-sm">
                <span>{{ row.name || row.user_id }}</span>
                <span class="font-semibold">{{ row.total }}</span>
              </li>
            </ul>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Leaderboard (Weekly)</div>
            <ul class="space-y-1">
              <li v-for="row in leaderboard.weekly" :key="row.user_id" class="flex items-center justify-between text-sm">
                <span>{{ row.name || row.user_id }}</span>
                <span class="font-semibold">{{ row.total }}</span>
              </li>
            </ul>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Leaderboard (Monthly)</div>
            <ul class="space-y-1">
              <li v-for="row in leaderboard.monthly" :key="row.user_id" class="flex items-center justify-between text-sm">
                <span>{{ row.name || row.user_id }}</span>
                <span class="font-semibold">{{ row.total }}</span>
              </li>
            </ul>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <div class="text-sm font-semibold mb-3">Konfigurasi Bobot Poin</div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div v-for="cfg in configs" :key="cfg.key" class="p-3 border rounded">
              <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">{{ cfg.key }}</div>
              <div class="flex items-center gap-2">
                <input type="number" v-model.number="cfg.points" min="0" class="px-2 py-1 border rounded w-20" />
                <label class="flex items-center gap-1 text-xs">
                  <input type="checkbox" v-model="cfg.enabled" />
                  Enabled
                </label>
                <button @click="updateConfig(cfg)" class="px-2 py-1 text-xs bg-blue-600 text-white rounded">Save</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
