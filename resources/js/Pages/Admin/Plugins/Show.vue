<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ plugin.name }} ({{ plugin.slug }})</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Version {{ plugin.version }} · Type {{ plugin.type }}</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <h2 class="font-semibold mb-2">Manifest</h2>
        <pre class="bg-gray-100 dark:bg-gray-900/30 p-3 rounded text-xs overflow-auto">{{ JSON.stringify(plugin.manifest, null, 2) }}</pre>
      </div>
      <div>
        <h2 class="font-semibold mb-2">Permissions</h2>
        <pre class="bg-gray-100 dark:bg-gray-900/30 p-3 rounded text-xs overflow-auto">{{ JSON.stringify(plugin.permissions, null, 2) }}</pre>
      </div>
    </div>

    <div class="mt-6">
      <h2 class="font-semibold mb-2">Versions</h2>
      <ul class="text-sm">
        <li v-for="v in plugin.versions" :key="v.id" class="border-t py-2 flex justify-between">
          <span>{{ v.version }} · {{ v.installed_at }}</span>
          <button class="btn btn-warning btn-sm" @click="rollback(v.version)">Rollback</button>
        </li>
      </ul>
    </div>

    <div class="mt-6">
      <h2 class="font-semibold mb-2">Recent Logs</h2>
      <ul class="text-sm">
        <li v-for="l in plugin.logs" :key="l.id" class="border-t py-2">
          <strong :class="levelClass(l.level)">{{ l.level.toUpperCase() }}</strong> · {{ l.message }}
          <span v-if="l.duration_ms" class="text-gray-600"> ({{ l.duration_ms }} ms)</span>
        </li>
      </ul>
    </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  plugin: Object
})

function rollback(version) {
  fetch(route('admin.plugins.rollback', props.plugin.id), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ to_version: version })
  }).then(() => {
    router.get(route('admin.plugins.show', props.plugin.id))
  })
}

function levelClass(level) {
  switch (level) {
    case 'error': return 'text-red-600'
    case 'warning': return 'text-yellow-600'
    case 'info': return 'text-blue-600'
    default: return 'text-gray-700'
  }
}
</script>

<style scoped>
.btn { @apply px-3 py-1 rounded border; }
.btn-warning { @apply bg-yellow-500 text-white; }
.btn-sm { @apply text-xs px-2 py-1; }
</style>
