<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  period: {
    type: String,
    default: null,
  },
});

const items = ref([]);
const loading = ref(false);
const period = ref(props.period || 'week');
const isAuth = !!window?.Ziggy?.props?.auth?.user;

const fetchData = async () => {
  loading.value = true;
  const res = await axios.get(route('recommendations.trending'), { params: { limit: 8, period: period.value } });
  items.value = res.data.data || [];
  loading.value = false;
};

onMounted(async () => {
  try {
    const savedLocal = localStorage.getItem('trending_period');
    if (!props.period && savedLocal) {
      period.value = savedLocal;
    }
    if (isAuth && !props.period) {
      const pref = await axios.get(route('user.preferences.trending.get'));
      if (pref?.data?.period) {
        period.value = pref.data.period;
        localStorage.setItem('trending_period', period.value);
      }
    }
  } catch {}
  fetchData();
});
watch(() => props.period, (val) => {
  if (val) {
    period.value = val;
    fetchData();
  }
});
const setPeriod = async (p) => {
  period.value = p;
  localStorage.setItem('trending_period', p);
  if (isAuth) {
    try { await axios.post(route('user.preferences.trending.save'), { period: p }); } catch {}
    try { await axios.post(route('analytics.events.store'), { type: 'trending_period_change', payload: { previous: null, new: p } }); } catch {}
  }
  fetchData();
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
    <div class="flex items-center justify-between mb-2">
      <div class="text-sm font-semibold">Trending</div>
      <div v-if="!props.period" class="flex gap-1">
        <button
          @click="setPeriod('today')"
          :class="['px-2 py-1 text-xs rounded', period === 'today' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']"
        >Today</button>
        <button
          @click="setPeriod('week')"
          :class="['px-2 py-1 text-xs rounded', period === 'week' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']"
        >7d</button>
        <button
          @click="setPeriod('month')"
          :class="['px-2 py-1 text-xs rounded', period === 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']"
        >30d</button>
      </div>
    </div>
    <div v-if="loading" class="text-xs text-gray-600 dark:text-gray-300">Loading...</div>
    <div v-else class="space-y-2">
      <div
        v-for="i in items"
        :key="i.id"
        class="p-3 rounded-md bg-gray-50 hover:bg-gray-100 transition dark:bg-gray-700/40 dark:hover:bg-gray-700"
      >
        <div class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">{{ i.title }}</div>
        <div class="text-xs text-gray-600 dark:text-gray-300 mt-1 leading-snug line-clamp-2">{{ i.excerpt }}</div>
      </div>
    </div>
  </div>
</template>
