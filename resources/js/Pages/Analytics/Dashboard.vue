<script setup>
import { Head } from '@inertiajs/vue3';
const props = defineProps({ events: Array, summary: Object, role: String });
</script>

<template>
  <Head title="Analytics Dashboard" />
  <div class="max-w-3xl mx-auto p-4">
    <h1 class="text-xl font-semibold mb-4">Analytics</h1>
    <div class="bg-white dark:bg-gray-800 rounded-lg border p-6">
      <p class="text-sm text-gray-700 dark:text-gray-300">
        Fitur visualisasi kompleks pada halaman Analytics telah dinonaktifkan karena tidak diperlukan.
        Data event tetap direkam untuk keperluan internal, namun tampilan chart, timeframe, dan ringkasan visual telah dihapus.
      </p>
      <div class="mt-4">
        <a :href="route('admin.rate-limit.dashboard')" class="inline-block px-3 py-2 bg-indigo-600 text-white rounded">
          Buka Rate Limit Dashboard
        </a>
      </div>
    </div>
  </div>
</template>
