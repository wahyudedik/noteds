<template>
  <Head title="Plugin Management" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Plugin Management</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-4">
        <div class="flex items-center gap-3">
          <input type="file" @change="onFile" accept=".zip" class="text-sm" />
          <button class="px-3 py-1 rounded border bg-blue-600 text-white disabled:opacity-50" :disabled="!archive" @click="upload">Upload</button>
          <button class="px-3 py-1 rounded border bg-gray-700 text-white disabled:opacity-50" :disabled="!uploadedPath" @click="install">Install</button>
        </div>
        <div v-if="errorMsg" class="bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200 border border-red-200 dark:border-red-800 rounded p-3 text-sm">
          {{ errorMsg }}
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
              <thead class="bg-gray-50 dark:bg-gray-900/30">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Name</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Slug</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Version</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Type</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Enabled</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="p in plugins.data" :key="p.id">
                  <td class="px-4 py-3">{{ p.name }}</td>
                  <td class="px-4 py-3">{{ p.slug }}</td>
                  <td class="px-4 py-3">{{ p.version }}</td>
                  <td class="px-4 py-3">{{ p.type }}</td>
                  <td class="px-4 py-3">
                    <span :class="p.enabled ? 'text-green-600' : 'text-gray-500'">{{ p.enabled ? 'Active' : 'Inactive' }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <button class="px-2 py-1 text-xs rounded border mr-2" @click="$inertia.get(route('admin.plugins.show', p.id))">Detail</button>
                    <button v-if="!p.enabled" class="px-2 py-1 text-xs rounded border bg-green-600 text-white mr-2" @click="activate(p.id)">Activate</button>
                    <button v-else class="px-2 py-1 text-xs rounded border bg-yellow-500 text-white mr-2" @click="deactivate(p.id)">Deactivate</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  plugins: Object
})

const archive = ref(null)
const uploadedPath = ref('')
const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || ''
const errorMsg = ref('')

function onFile(e) {
  archive.value = e.target.files?.[0] || null
}

async function upload() {
  if (!archive.value) return
  const form = new FormData()
  form.append('archive', archive.value)
  const res = await fetch(route('admin.plugins.upload'), {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
    },
    body: form
  })
  if (res.ok) {
    const json = await res.json()
    uploadedPath.value = json.path || ''
    errorMsg.value = ''
  } else {
    errorMsg.value = 'Upload gagal. Pastikan file ZIP valid.'
    try { 
      const t = await res.text()
      console.error('Upload error:', t)
    } catch {}
    window.__toast?.add({ title: 'Plugins', message: 'Upload gagal', type: 'error' })
  }
}

async function install() {
  if (!uploadedPath.value) return
  const res = await fetch(route('admin.plugins.install'), {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
    },
    body: JSON.stringify({ archive_path: uploadedPath.value })
  })
  if (res.ok) {
    errorMsg.value = ''
    router.get(route('admin.plugins.index'))
  } else {
    errorMsg.value = 'Install gagal. Periksa plugin.json di arsip dan dependencies.'
    try { 
      const t = await res.text()
      console.error('Install error:', t)
    } catch {}
    window.__toast?.add({ title: 'Plugins', message: 'Install gagal', type: 'error' })
  }
}

async function activate(id) {
  await fetch(route('admin.plugins.activate', id), {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
    }
  })
  router.get(route('admin.plugins.index'))
}

async function deactivate(id) {
  await fetch(route('admin.plugins.deactivate', id), {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
    }
  })
  router.get(route('admin.plugins.index'))
}
</script>
