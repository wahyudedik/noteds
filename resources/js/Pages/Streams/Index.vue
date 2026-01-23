<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  streams: Object,
});
</script>

<template>
  <Head title="Live Streams" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Live Streams</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl">
        <div v-if="streams?.data?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="s in streams.data" :key="s.id" class="border rounded bg-white dark:bg-gray-800 p-3">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
              {{ (s.status || 'scheduled').toUpperCase() }}
            </div>
            <div class="text-sm font-semibold mb-2">{{ s.title }}</div>
            <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2">{{ s.description }}</p>
            <div class="mt-3 flex items-center gap-2">
              <Link :href="route('streams.show', s.id)" class="text-xs text-indigo-600">Lihat</Link>
              <span v-if="s.started_at" class="text-[10px] text-gray-400">Started {{ new Date(s.started_at).toLocaleString() }}</span>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-16">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada live stream</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat stream baru atau jadwalkan untuk tampil di sini.</p>
          <div class="mt-6">
            <Link :href="route('streams.index')" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm">Refresh</Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
  </template>
