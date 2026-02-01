<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
  watchlist: {
    type: Object,
    default: () => ({ data: [], links: [], meta: {} }),
  },
});
import Echo from '@/Utils/echo.js';
import { onMounted, ref } from 'vue';
const livePrices = ref({});
onMounted(() => {
  if (!Echo) return;
  const codes = (props.watchlist?.data || []).map(i => i.stock?.code).filter(Boolean);
  codes.forEach(code => {
    Echo.channel(`stock.${code}.prices`).listen('.price.updated', (payload) => {
      livePrices.value[code] = payload.close;
    });
  });
});

const formatPrice = (price) => {
  if (!price) return '-';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};
</script>

<template>
  <Head title="Stock Market Dashboard" />

  <AuthenticatedLayout>
    <div class="px-4 sm:px-6 py-4 sm:py-6">
      <div class="mx-auto max-w-7xl">
        <h1 class="text-3xl font-bold mb-6">Stock Market Dashboard</h1>

    <!-- Market Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-medium text-gray-500">IDX Composite</h3>
        <p class="text-2xl font-bold">-</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-medium text-gray-500">Top Gainers</h3>
        <p class="text-2xl font-bold text-green-600">-</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-medium text-gray-500">Top Losers</h3>
        <p class="text-2xl font-bold text-red-600">-</p>
      </div>
    </div>

    <!-- Watchlist -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">My Watchlist</h2>
      <div v-if="(watchlist?.data?.length || 0) === 0" class="text-gray-500">
        No stocks in watchlist. <Link :href="route('stocks.index')" class="text-blue-600">Browse stocks</Link>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in watchlist.data" :key="item.id">
              <td class="px-4 py-3 whitespace-nowrap font-medium">{{ item.stock?.code }}</td>
              <td class="px-4 py-3 whitespace-nowrap">{{ item.stock?.name }}</td>
              <td class="px-4 py-3 whitespace-nowrap">{{ formatPrice(livePrices[item.stock?.code] ?? item.stock?.prices?.[0]?.close) }}</td>
              <td class="px-4 py-3 whitespace-nowrap">-</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <Link :href="route('stocks.show', item.stock?.id)" class="text-blue-600 hover:underline">View</Link>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t flex items-center justify-between">
          <div class="text-sm text-gray-500">
            Halaman {{ watchlist?.meta?.current_page }} dari {{ watchlist?.meta?.last_page }}
          </div>
          <div class="space-x-2">
            <Link
              v-if="watchlist?.links?.prev"
              :href="watchlist.links.prev"
              class="px-3 py-1 bg-white border rounded text-sm"
            >Sebelumnya</Link>
            <Link
              v-if="watchlist?.links?.next"
              :href="watchlist.links.next"
              class="px-3 py-1 bg-white border rounded text-sm"
            >Berikutnya</Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Predictions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Recent Predictions</h2>
      <p class="text-gray-500">Predictions will appear here</p>
    </div>

    <!-- Active Signals -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Active Signals</h2>
      <p class="text-gray-500">Signals will appear here</p>
    </div>

    <!-- Quick Screening -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold mb-4">Quick Screening</h2>
      <Link :href="route('stocks.screening')" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Go to Advanced Screening
      </Link>
    </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

