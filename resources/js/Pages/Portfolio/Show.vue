<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
  recommendation: {
    type: Object,
    default: null,
  },
  breakdown: {
    type: Array,
    default: () => [],
  },
  riskMetrics: {
    type: Object,
    default: () => ({}),
  },
});

const formatCurrency = (amount) => {
  if (!amount) return '-';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount);
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const formatMetric = (value) => {
  if (value === null || value === undefined) return '-';
  if (typeof value === 'number') {
    if (value < 1 && value > 0) {
      return (value * 100).toFixed(2) + '%';
    }
    return value.toFixed(2);
  }
  return value;
};
</script>

<template>
  <Head title="Portfolio Recommendation Details" />

  <AuthenticatedLayout>
    <div class="px-4 sm:px-6 py-4 sm:py-6">
      <div class="mx-auto max-w-7xl">
        <div class="mb-6">
          <Link :href="route('portfolio.index')" class="text-blue-600 hover:underline mb-4 inline-block">
            ← Back to Recommendations
          </Link>
          <h1 class="text-3xl font-bold">Portfolio Recommendation Details</h1>
        </div>

    <div v-if="recommendation" class="space-y-6">
      <!-- Header Info -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-2xl font-bold mb-2">Portfolio Allocation</h2>
            <p class="text-sm text-gray-500">
              Generated on {{ formatDate(recommendation.generated_at) }}
            </p>
          </div>
          <span
            :class="{
              'bg-green-100 text-green-800': recommendation.risk_profile === 'conservative',
              'bg-yellow-100 text-yellow-800': recommendation.risk_profile === 'moderate',
              'bg-red-100 text-red-800': recommendation.risk_profile === 'aggressive',
            }"
            class="px-3 py-1 rounded-full text-xs font-medium capitalize"
          >
            {{ recommendation.risk_profile }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <p class="text-sm text-gray-500">Investment Amount</p>
            <p class="text-lg font-semibold">{{ formatCurrency(recommendation.investment_amount) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Expected Return</p>
            <p class="text-lg font-semibold text-green-600">
              {{ recommendation.expected_return ? (recommendation.expected_return * 100).toFixed(2) + '%' : '-' }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Risk Level</p>
            <p class="text-lg font-semibold">
              {{ recommendation.expected_risk ? (recommendation.expected_risk * 100).toFixed(2) + '%' : '-' }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Sharpe Ratio</p>
            <p class="text-lg font-semibold">
              {{ recommendation.sharpe_ratio ? recommendation.sharpe_ratio.toFixed(2) : '-' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Allocation Breakdown -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold mb-4">Stock Allocation</h3>
        <div v-if="breakdown && breakdown.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Allocation %</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in breakdown" :key="index">
                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ item.stock_code || item.code || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ item.percentage ? (item.percentage * 100).toFixed(2) + '%' : '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(item.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-gray-500 text-center py-8">
          No allocation data available
        </div>
      </div>

      <!-- Risk Metrics -->
      <div v-if="riskMetrics" class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold mb-4">Risk Metrics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="(value, key) in riskMetrics" :key="key" class="border rounded p-4">
            <p class="text-sm text-gray-500 capitalize">{{ key.replace(/_/g, ' ') }}</p>
            <p class="text-lg font-semibold">{{ formatMetric(value) }}</p>
          </div>
        </div>
      </div>
      </div>
    </div>
    </div>
  </AuthenticatedLayout>
</template>

