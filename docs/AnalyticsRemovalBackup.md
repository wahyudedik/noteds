# Analytics Page Backup

## UserDashboard.vue (before cleanup)
```vue
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
```

## Dashboard.vue (before cleanup)
```vue
<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import Chart from 'chart.js/auto';
// ... rest omitted for brevity
</script>
```
