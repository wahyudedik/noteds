<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  stock: Object,
  latestPrice: Object,
  latestIndicator: Object,
  latestSignals: Array,
  latestPredictions: Array,
});

const formatPrice = (price) => {
  if (!price) return '-';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);
};

const addToWatchlist = async () => {
  try {
    await axios.post(route('stocks.watchlist.store'), {
      stock_id: props.stock.id,
    });
    alert('Added to watchlist');
  } catch (error) {
    alert('Failed to add to watchlist');
  }
};
</script>

<template>
  <Head :title="stock ? `${stock.code} - ${stock.name}` : 'Stock Details'" />

  <AuthenticatedLayout>
    <div class="px-4 sm:px-6 py-4 sm:py-6" v-if="stock">
      <div class="mx-auto max-w-7xl">
        <div class="mb-6">
          <h1 class="text-3xl font-bold">{{ stock.code }} - {{ stock.name }}</h1>
          <p class="text-gray-600">{{ stock.sector }} / {{ stock.sub_sector }}</p>
        </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Current Price -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-bold mb-4">Current Price</h2>
          <div v-if="latestPrice" class="text-3xl font-bold">
            {{ formatPrice(latestPrice.close) }}
          </div>
          <div v-else class="text-gray-500">No price data available</div>
        </div>

        <!-- Chart Placeholder -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-bold mb-4">Price Chart</h2>
          <p class="text-gray-500">Chart will be displayed here</p>
        </div>

        <!-- Technical Indicators -->
        <div class="bg-white rounded-lg shadow p-6" v-if="latestIndicator">
          <h2 class="text-xl font-bold mb-4">Technical Indicators</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-sm text-gray-500">RSI:</span>
              <span class="ml-2 font-medium">{{ latestIndicator.rsi || '-' }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-500">MACD:</span>
              <span class="ml-2 font-medium">{{ latestIndicator.macd || '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Predictions -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-bold mb-4">ML Predictions</h2>
          <div v-if="latestPredictions.length === 0" class="text-gray-500">
            No predictions available
          </div>
          <div v-else class="space-y-2">
            <div v-for="prediction in latestPredictions" :key="prediction.id" class="border-b pb-2">
              <div class="flex justify-between">
                <span>{{ prediction.prediction_horizon }} day prediction</span>
                <span class="font-bold">{{ formatPrice(prediction.predicted_price) }}</span>
              </div>
              <div class="text-sm text-gray-500">
                Confidence: {{ (prediction.confidence_score * 100).toFixed(1) }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Signals -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-bold mb-4">Buy/Sell Signals</h2>
          <div v-if="latestSignals.length === 0" class="text-gray-500">
            No active signals
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="signal in latestSignals"
              :key="signal.id"
              class="border rounded p-3"
              :class="{
                'border-green-500 bg-green-50': signal.signal_type === 'buy',
                'border-red-500 bg-red-50': signal.signal_type === 'sell',
                'border-gray-500 bg-gray-50': signal.signal_type === 'hold',
              }"
            >
              <div class="flex justify-between items-center">
                <span class="font-bold uppercase">{{ signal.signal_type }}</span>
                <span class="text-sm">Strength: {{ (signal.signal_strength * 100).toFixed(0) }}%</span>
              </div>
              <p class="text-sm text-gray-600 mt-1">{{ signal.reason }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Add to Watchlist -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-bold mb-4">Actions</h3>
          <button
            @click="addToWatchlist"
            class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            Add to Watchlist
          </button>
        </div>

        <!-- Company Info -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-bold mb-4">Company Information</h3>
          <dl class="space-y-2 text-sm">
            <div>
              <dt class="text-gray-500">Sector:</dt>
              <dd>{{ stock.sector || '-' }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Category:</dt>
              <dd>{{ stock.category || '-' }}</dd>
            </div>
            <div>
              <dt class="text-gray-500">Market Cap:</dt>
              <dd>{{ formatPrice(stock.market_cap) }}</dd>
            </div>
          </dl>
        </div>
      </div>
      </div>
    </div>
    </div>
  </AuthenticatedLayout>
</template>

