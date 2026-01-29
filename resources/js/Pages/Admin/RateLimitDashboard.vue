<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import RateLimitChart from '@/Components/Admin/RateLimitChart.vue';

const range = ref('1h');
const loading = ref(false);
const series = ref({
  'search.suggestions': [],
  'streams.chat.store': [],
  'analytics.events.store': [],
});
const stats = ref({ total_blocked: 0, peak: null });
let timer = null;

const fetchData = async () => {
  loading.value = true;
  const url = route('admin.rate-limit.metrics', { range: range.value });
  const res = await window.axios.get(url);
  series.value = res.data.series || series.value;
  stats.value = res.data.stats || stats.value;
  loading.value = false;
};

onMounted(async () => {
  await fetchData();
  timer = setInterval(fetchData, 30000);
});

watch(range, async () => {
  await fetchData();
});
</script>

<template>
  <Head title="Rate Limit Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Rate Limit Dashboard
      </h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Rate Limit 429 per Menit</h2>
          <div class="flex gap-2">
            <button @click="range = '1h'" :class="['px-3 py-1 text-sm rounded', range==='1h' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']">Last 1h</button>
            <button @click="range = '24h'" :class="['px-3 py-1 text-sm rounded', range==='24h' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']">24h</button>
            <button @click="range = '7d'" :class="['px-3 py-1 text-sm rounded', range==='7d' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']">7d</button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <RateLimitChart title="search.suggestions" :points="series['search.suggestions']" />
          <RateLimitChart title="streams.chat.store" :points="series['streams.chat.store']" />
          <RateLimitChart title="analytics.events.store" :points="series['analytics.events.store']" />
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">Total blocked</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_blocked }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">Peak hits</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ stats.peak ? stats.peak.count : 0 }}
            </div>
            <div class="text-xs text-gray-600 dark:text-gray-400">
              {{ stats.peak ? new Date(stats.peak.minute_bucket).toLocaleString() : '-' }}
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">Status</div>
            <div class="text-sm text-gray-800 dark:text-gray-200">
              {{ loading ? 'Refreshing...' : 'Live (auto-refresh 30s)' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
