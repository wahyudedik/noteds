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
          <div v-for="p in plugins.data" :key="p.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col h-full">
            <div v-if="p.thumbnail_url" class="mb-4">
              <img :src="p.thumbnail_url" alt="Plugin thumbnail" class="w-full h-32 object-cover rounded-md">
            </div>
            <div class="flex items-center justify-between mb-2">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white truncate" :title="p.name">{{ p.name }}</h2>
              <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">v{{ p.version }}</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-3 flex-grow">{{ p.description || 'No description' }}</p>
            
            <div class="mt-auto pt-4 border-t dark:border-gray-700 flex justify-between items-center">
              <div>
                <span v-if="p.is_paid" class="text-indigo-600 dark:text-indigo-400 font-bold">
                  Rp {{ Number(p.price).toLocaleString('id-ID') }}
                </span>
                <span v-else class="text-green-600 dark:text-green-400 font-bold">
                  Free
                </span>
              </div>
              <a :href="route('plugins.show', p.id)" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                View Details
              </a>
            </div>
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
