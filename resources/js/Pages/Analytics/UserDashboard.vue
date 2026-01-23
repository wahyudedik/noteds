<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Line, Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title, Tooltip as ChartTooltip, Legend, LineElement, BarElement, PointElement, LinearScale, CategoryScale,
} from 'chart.js';
ChartJS.register(Title, ChartTooltip, Legend, LineElement, BarElement, PointElement, LinearScale, CategoryScale);

const from = ref(new Date(new Date().setDate(new Date().getDate() - 30)).toISOString());
const to = ref(new Date().toISOString());
const metric = ref('engagement');
const loading = ref(false);
const data = ref({ by_date: {}, engagement_rate: {}, follower_growth: [], best: [] });

const fetchData = async () => {
  loading.value = true;
  const res = await axios.get(route('analytics.overview'), { params: { from: from.value, to: to.value, metric: metric.value } });
  data.value = res.data.data;
  loading.value = false;
};

const labels = computed(() => Object.keys(data.value.by_date));
const viewsSeries = computed(() => labels.value.map(d => data.value.by_date[d]?.views || 0));
const engRateSeries = computed(() => labels.value.map(d => data.value.engagement_rate[d] || 0));
const followerLabels = computed(() => data.value.follower_growth.map(r => r.d));
const followerSeries = computed(() => data.value.follower_growth.map(r => r.total));

const viewsChart = computed(() => ({
  labels: labels.value,
  datasets: [{ label: 'Views', data: viewsSeries.value, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.2)', tension: 0.25, fill: true }],
}));
const engRateChart = computed(() => ({
  labels: labels.value,
  datasets: [{ label: 'Engagement Rate (%)', data: engRateSeries.value, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.2)', tension: 0.25, fill: true }],
}));
const followerChart = computed(() => ({
  labels: followerLabels.value,
  datasets: [{ label: 'Followers', data: followerSeries.value, borderColor: '#db2777', backgroundColor: 'rgba(219,39,119,0.2)', tension: 0.25, fill: true }],
}));

const exportCsv = () => {
  const url = route('analytics.export', { from: from.value, to: to.value, format: 'csv' });
  window.location.href = url;
};

onMounted(fetchData);
</script>

<template>
  <Head title="Analytics" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Analytics</h2>
        <div class="flex items-center gap-2">
          <button @click="exportCsv" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md">Export CSV</button>
        </div>
      </div>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
              <label class="text-sm">From</label>
              <input type="datetime-local" v-model="from" class="mt-1 w-full border rounded px-2 py-1" />
            </div>
            <div>
              <label class="text-sm">To</label>
              <input type="datetime-local" v-model="to" class="mt-1 w-full border rounded px-2 py-1" />
            </div>
            <div>
              <label class="text-sm">Best Metric</label>
              <select v-model="metric" class="mt-1 w-full border rounded px-2 py-1">
                <option value="engagement">Engagement</option>
                <option value="views">Views</option>
              </select>
            </div>
            <div class="flex items-end">
              <button @click="fetchData" class="px-3 py-1.5 bg-blue-600 text-white rounded">Apply</button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Views</div>
            <Line :data="viewsChart" :options="{responsive: true, plugins:{tooltip:{enabled:true}}}" />
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Engagement Rate</div>
            <Line :data="engRateChart" :options="{responsive: true, plugins:{tooltip:{enabled:true}}}" />
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Follower Growth</div>
            <Line :data="followerChart" :options="{responsive: true, plugins:{tooltip:{enabled:true}}}" />
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <div class="text-sm font-semibold mb-3">Best Performing Content</div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div v-for="item in data.best" :key="item.post_id" class="p-3 border rounded">
              <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">Post #{{ item.post_id }}</div>
              <div class="text-sm">Views: <span class="font-semibold">{{ item.views }}</span></div>
              <div class="text-sm">Engagement: <span class="font-semibold">{{ item.engagement }}</span></div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <div class="text-sm font-semibold mb-3">Competitor Comparison</div>
          <div class="text-xs text-gray-600 dark:text-gray-300 mb-2">Tambahkan kompetitor di halaman pengaturan untuk perbandingan.</div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <!-- Placeholder untuk grafik kompetitor, dapat diisi saat data tersedia -->
            <div class="p-3 border rounded text-xs text-gray-600 dark:text-gray-300">No competitor data</div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <div class="text-sm font-semibold mb-2">Audience Insights</div>
          <div class="text-xs text-gray-600 dark:text-gray-300">Demografi dan lokasi belum tersedia di data saat ini.</div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
