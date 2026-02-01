<template>
  <Head title="Available Plugins" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Available Plugins</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Browse installed and active plugins.</p>

        <div v-if="plugins.data.length === 0" class="text-gray-500 text-sm">No active plugins found.</div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="p in plugins.data" :key="p.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-2">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ p.name }}</h2>
              <span class="text-xs text-gray-500">v{{ p.version }}</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ p.description || 'No description' }}</p>
            <div class="text-xs text-gray-500 dark:text-gray-400">Type: {{ p.type }} · Slug: {{ p.slug }}</div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
const props = defineProps({
  plugins: Object
})
</script>
