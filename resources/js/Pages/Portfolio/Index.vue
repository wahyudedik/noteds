<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
  recommendations: {
    type: Object,
    default: () => ({ data: [], links: [] }),
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
</script>

<template>
  <Head title="Portfolio Recommendations" />

  <AuthenticatedLayout>
    <div class="px-4 sm:px-6 py-4 sm:py-6">
      <div class="mx-auto max-w-7xl">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold">Portfolio Recommendations</h1>
          <Link :href="route('portfolio.generate')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Generate New Recommendation
          </Link>
        </div>

        <div v-if="recommendations.data && recommendations.data.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
          <p class="text-gray-500 mb-4">You don't have any portfolio recommendations yet.</p>
          <Link :href="route('portfolio.generate')" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Generate Your First Recommendation
          </Link>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="recommendation in recommendations.data"
            :key="recommendation.id"
            class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition"
          >
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-xl font-bold mb-2">Portfolio Recommendation</h3>
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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
            </div>

            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Sharpe Ratio</p>
                <p class="text-lg font-semibold">
                  {{ recommendation.sharpe_ratio ? recommendation.sharpe_ratio.toFixed(2) : '-' }}
                </p>
              </div>
              <Link
                :href="route('portfolio.show', recommendation.id)"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
              >
                View Details
              </Link>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="recommendations.links && recommendations.links.length > 3" class="flex justify-center mt-6">
            <div class="flex space-x-2">
              <Link
                v-for="link in recommendations.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="{
                  'bg-blue-600 text-white': link.active,
                  'bg-white text-gray-700 hover:bg-gray-50': !link.active && link.url,
                  'bg-gray-100 text-gray-400 cursor-not-allowed': !link.url,
                }"
                class="px-4 py-2 rounded border"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

