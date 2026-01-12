<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
  watchlist: {
    type: Array,
    default: () => [],
  },
});

const formatPrice = (price) => {
  if (!price) return '-';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const removeFromWatchlist = (watchlistId) => {
  if (confirm('Remove from watchlist?')) {
    router.delete(route('stocks.watchlist.destroy', watchlistId));
  }
};
</script>

<template>
  <Head title="My Watchlist" />

  <AuthenticatedLayout>
    <div class="px-4 sm:px-6 py-4 sm:py-6">
      <div class="mx-auto max-w-7xl">
        <h1 class="text-3xl font-bold mb-6">My Watchlist</h1>

    <div v-if="watchlist.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
      <p class="text-gray-500 mb-4">Your watchlist is empty</p>
      <Link :href="route('stocks.index')" class="text-blue-600 hover:underline">
        Browse stocks to add to watchlist
      </Link>
    </div>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="item in watchlist" :key="item.id">
            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ item.stock?.code }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ item.stock?.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              {{ formatPrice(item.stock?.prices?.[0]?.close) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">-</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <Link :href="route('stocks.show', item.stock?.id)" class="text-blue-600 hover:underline mr-4">
                View
              </Link>
              <button
                @click="removeFromWatchlist(item.id)"
                class="text-red-600 hover:underline"
              >
                Remove
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

