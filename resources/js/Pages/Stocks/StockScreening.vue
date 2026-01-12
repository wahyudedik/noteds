<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  savedScreenings: {
    type: Array,
    default: () => [],
  },
});

const filters = reactive({
  category: '',
  price_min: null,
  price_max: null,
  rsi_min: null,
  rsi_max: null,
  signal_type: '',
  risk_level: [],
});

const results = ref([]);
const loading = ref(false);

const screenStocks = async () => {
  loading.value = true;
  try {
    const response = await axios.post(route('stocks.screen'), filters);
    results.value = response.data.data || [];
  } catch (error) {
    console.error('Screening error:', error);
    results.value = [];
  } finally {
    loading.value = false;
  }
};

const saveScreening = async () => {
  if (results.value.length === 0) {
    alert('No results to save. Please run a screening first.');
    return;
  }
  
  const name = prompt('Enter a name for this screening:');
  if (!name || !name.trim()) {
    return;
  }
  
  try {
    const response = await axios.post(route('stocks.screenings.save'), {
      name: name.trim(),
      filters: filters,
    });
    
    if (response.data.success) {
      alert('Screening saved successfully!');
      // Optionally reload saved screenings or navigate
      window.location.reload();
    } else {
      alert('Failed to save screening: ' + (response.data.message || 'Unknown error'));
    }
  } catch (error) {
    console.error('Save screening error:', error);
    alert('Failed to save screening. Please try again.');
  }
};

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
  <Head title="Stock Screening" />

  <AuthenticatedLayout>
    <div class="px-4 sm:px-6 py-4 sm:py-6">
      <div class="mx-auto max-w-7xl">
        <h1 class="text-3xl font-bold mb-6">Stock Screening</h1>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
          <!-- Filters Sidebar -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-4 sticky top-4">
              <h2 class="text-lg font-bold mb-4">Filters</h2>
              
              <form @submit.prevent="screenStocks" class="space-y-4">
                <!-- Category -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                  <select v-model="filters.category" class="w-full border-gray-300 rounded-md">
                    <option value="">All</option>
                    <option value="LQ45">LQ45</option>
                    <option value="IDX30">IDX30</option>
                    <option value="IDX80">IDX80</option>
                    <option value="Kompas100">Kompas100</option>
                  </select>
                </div>

                <!-- Price Range -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                  <div class="flex gap-2">
                    <input
                      v-model.number="filters.price_min"
                      type="number"
                      placeholder="Min"
                      class="w-full border-gray-300 rounded-md"
                    />
                    <input
                      v-model.number="filters.price_max"
                      type="number"
                      placeholder="Max"
                      class="w-full border-gray-300 rounded-md"
                    />
                  </div>
                </div>

                <!-- RSI Range -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">RSI Range</label>
                  <div class="flex gap-2">
                    <input
                      v-model.number="filters.rsi_min"
                      type="number"
                      placeholder="Min"
                      min="0"
                      max="100"
                      class="w-full border-gray-300 rounded-md"
                    />
                    <input
                      v-model.number="filters.rsi_max"
                      type="number"
                      placeholder="Max"
                      min="0"
                      max="100"
                      class="w-full border-gray-300 rounded-md"
                    />
                  </div>
                </div>

                <!-- Signal Type -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Signal Type</label>
                  <select v-model="filters.signal_type" class="w-full border-gray-300 rounded-md">
                    <option value="">All</option>
                    <option value="buy">Buy</option>
                    <option value="sell">Sell</option>
                    <option value="hold">Hold</option>
                  </select>
                </div>

                <!-- Risk Level -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Risk Level</label>
                  <div class="space-y-1">
                    <label class="flex items-center">
                      <input type="checkbox" v-model="filters.risk_level" value="low" class="mr-2" />
                      Low
                    </label>
                    <label class="flex items-center">
                      <input type="checkbox" v-model="filters.risk_level" value="medium" class="mr-2" />
                      Medium
                    </label>
                    <label class="flex items-center">
                      <input type="checkbox" v-model="filters.risk_level" value="high" class="mr-2" />
                      High
                    </label>
                  </div>
                </div>

                <button
                  type="submit"
                  :disabled="loading"
                  class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
                >
                  {{ loading ? 'Screening...' : 'Screen Stocks' }}
                </button>
              </form>
            </div>
          </div>

          <!-- Results -->
          <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow p-6">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">Results ({{ results.length }})</h2>
                <button
                  v-if="results.length > 0"
                  @click="saveScreening"
                  class="text-blue-600 hover:underline"
                >
                  Save Screening
                </button>
              </div>

              <div v-if="loading" class="text-center py-8">
                <p class="text-gray-500">Loading...</p>
              </div>

              <div v-else-if="results.length === 0" class="text-center py-8">
                <p class="text-gray-500">No stocks found. Try adjusting your filters.</p>
              </div>

              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">RSI</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Signal</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="stock in results" :key="stock.id">
                      <td class="px-4 py-3 whitespace-nowrap font-medium">{{ stock.code }}</td>
                      <td class="px-4 py-3 whitespace-nowrap">{{ stock.name }}</td>
                      <td class="px-4 py-3 whitespace-nowrap">{{ formatPrice(stock.current_price) }}</td>
                      <td class="px-4 py-3 whitespace-nowrap">{{ stock.rsi || '-' }}</td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <span
                          v-if="stock.signal"
                          :class="{
                            'bg-green-100 text-green-800': stock.signal.type === 'buy',
                            'bg-red-100 text-red-800': stock.signal.type === 'sell',
                            'bg-gray-100 text-gray-800': stock.signal.type === 'hold',
                          }"
                          class="px-2 py-1 rounded text-xs font-medium"
                        >
                          {{ stock.signal.type.toUpperCase() }}
                        </span>
                        <span v-else>-</span>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <Link :href="route('stocks.show', stock.id)" class="text-blue-600 hover:underline">
                          View
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

