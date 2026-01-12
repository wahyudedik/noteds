<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const loading = ref(false);

const form = useForm({
  risk_profile: '',
  investment_amount: null,
  investment_horizon: null,
});

const generatePortfolio = () => {
  loading.value = true;
  form.post(route('portfolio.generate.store'), {
    preserveScroll: true,
    onFinish: () => {
      loading.value = false;
    },
  });
};
</script>

<template>
  <Head title="Generate Portfolio Recommendation" />

  <AuthenticatedLayout>
    <div class="px-4 sm:px-6 py-4 sm:py-6">
      <div class="mx-auto max-w-2xl">
        <div class="mb-6">
          <Link :href="route('portfolio.index')" class="text-blue-600 hover:underline mb-4 inline-block">
            ← Back to Recommendations
          </Link>
          <h1 class="text-3xl font-bold">Generate Portfolio Recommendation</h1>
          <p class="text-gray-500 mt-2">Create a personalized portfolio recommendation based on your risk profile and investment goals.</p>
        </div>

    <form @submit.prevent="generatePortfolio" class="bg-white rounded-lg shadow p-6 space-y-6">
      <!-- Risk Profile -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Risk Profile <span class="text-red-500">*</span>
        </label>
        <div class="space-y-2">
          <label class="flex items-center p-4 border rounded cursor-pointer hover:bg-gray-50" :class="{ 'border-blue-500 bg-blue-50': form.risk_profile === 'conservative' }">
            <input
              type="radio"
              v-model="form.risk_profile"
              value="conservative"
              class="mr-3"
              required
            />
            <div>
              <p class="font-medium">Conservative</p>
              <p class="text-sm text-gray-500">Low risk, stable returns. Suitable for risk-averse investors.</p>
            </div>
          </label>
          <label class="flex items-center p-4 border rounded cursor-pointer hover:bg-gray-50" :class="{ 'border-blue-500 bg-blue-50': form.risk_profile === 'moderate' }">
            <input
              type="radio"
              v-model="form.risk_profile"
              value="moderate"
              class="mr-3"
              required
            />
            <div>
              <p class="font-medium">Moderate</p>
              <p class="text-sm text-gray-500">Balanced risk and return. Suitable for most investors.</p>
            </div>
          </label>
          <label class="flex items-center p-4 border rounded cursor-pointer hover:bg-gray-50" :class="{ 'border-blue-500 bg-blue-50': form.risk_profile === 'aggressive' }">
            <input
              type="radio"
              v-model="form.risk_profile"
              value="aggressive"
              class="mr-3"
              required
            />
            <div>
              <p class="font-medium">Aggressive</p>
              <p class="text-sm text-gray-500">High risk, high potential returns. Suitable for experienced investors.</p>
            </div>
          </label>
        </div>
        <div v-if="errors.risk_profile" class="mt-1 text-sm text-red-600">{{ errors.risk_profile }}</div>
      </div>

      <!-- Investment Amount -->
      <div>
        <label for="investment_amount" class="block text-sm font-medium text-gray-700 mb-2">
          Investment Amount (IDR) <span class="text-red-500">*</span>
        </label>
        <input
          id="investment_amount"
          v-model.number="form.investment_amount"
          type="number"
          min="1000000"
          step="100000"
          placeholder="1000000"
          class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
          required
        />
        <p class="mt-1 text-sm text-gray-500">Minimum: Rp 1,000,000</p>
        <div v-if="errors.investment_amount" class="mt-1 text-sm text-red-600">{{ errors.investment_amount }}</div>
      </div>

      <!-- Investment Horizon -->
      <div>
        <label for="investment_horizon" class="block text-sm font-medium text-gray-700 mb-2">
          Investment Horizon (Days) <span class="text-red-500">*</span>
        </label>
        <input
          id="investment_horizon"
          v-model.number="form.investment_horizon"
          type="number"
          min="30"
          max="3650"
          placeholder="365"
          class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
          required
        />
        <p class="mt-1 text-sm text-gray-500">Range: 30 - 3650 days (approximately 1 month - 10 years)</p>
        <div v-if="errors.investment_horizon" class="mt-1 text-sm text-red-600">{{ errors.investment_horizon }}</div>
      </div>

      <!-- Error Message -->
      <div v-if="errors.message" class="bg-red-50 border border-red-200 rounded p-4">
        <p class="text-sm text-red-600">{{ errors.message }}</p>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end space-x-4">
        <Link :href="route('portfolio.index')" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
          Cancel
        </Link>
        <button
          type="submit"
          :disabled="loading"
          class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Generating...' : 'Generate Recommendation' }}
        </button>
      </div>
    </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

