<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ plugin.name }} ({{ plugin.slug }})
      </h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Version {{ plugin.version }} · Type {{ plugin.type }}
        </p>

        <!-- Marketplace Settings Form -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Marketplace Settings</h3>
          <form @submit.prevent="updateMarketplace" class="space-y-4">
            <div class="flex items-center space-x-2">
              <input 
                id="is_paid" 
                v-model="form.is_paid" 
                type="checkbox" 
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
              >
              <label for="is_paid" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Paid Plugin?
              </label>
            </div>

            <div v-if="form.is_paid">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (IDR)</label>
              <input 
                v-model="form.price" 
                type="number" 
                step="0.01" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Demo URL</label>
              <input 
                v-model="form.demo_url" 
                type="url" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thumbnail URL</label>
              <input 
                v-model="form.thumbnail_url" 
                type="text" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
              <textarea 
                v-model="form.description" 
                rows="3" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              ></textarea>
            </div>

            <button 
              type="submit" 
              class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              :disabled="form.processing"
            >
              Save Changes
            </button>
          </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h2 class="font-semibold mb-2">Manifest</h2>
            <pre class="bg-gray-100 dark:bg-gray-900/30 p-3 rounded text-xs overflow-auto h-64">{{ JSON.stringify(plugin.manifest, null, 2) }}</pre>
          </div>
          <div>
            <h2 class="font-semibold mb-2">Permissions</h2>
            <pre class="bg-gray-100 dark:bg-gray-900/30 p-3 rounded text-xs overflow-auto h-64">{{ JSON.stringify(plugin.permissions, null, 2) }}</pre>
          </div>
        </div>

        <div class="mt-6">
          <h2 class="font-semibold mb-2">Versions</h2>
          <ul class="text-sm space-y-2">
            <li v-for="v in plugin.versions" :key="v.id" class="border p-3 rounded flex justify-between items-center dark:border-gray-700">
              <span>
                <span class="font-medium">{{ v.version }}</span> 
                <span class="text-gray-500 mx-2">·</span> 
                <span class="text-gray-500">{{ v.installed_at }}</span>
              </span>
              <button 
                v-if="v.version !== plugin.version"
                class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200 text-xs font-medium" 
                @click="rollback(v.version)"
              >
                Rollback
              </button>
              <span v-else class="text-xs text-green-600 font-medium bg-green-100 px-2 py-1 rounded">Current</span>
            </li>
          </ul>
        </div>
        
        <!-- Upload Downloadable File -->
        <div class="mt-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Upload Download File</h3>
            <p class="text-sm text-gray-500 mb-4">Unggah file produk untuk di-download oleh pembeli (zip/apk/exe/dmg, dll). Opsional: set versi secara manual.</p>
            
            <form @submit.prevent="uploadDownload" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
              <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product File</label>
                  <input 
                      type="file" 
                      @input="downloadForm.download_file = $event.target.files[0]"
                      accept=".zip,.apk,.exe,.dmg,.tar,.gz,.rar,application/zip,application/x-msdownload,application/x-apple-diskimage"
                      class="mt-1 block w-full text-sm text-gray-500
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-md file:border-0
                      file:text-sm file:font-semibold
                      file:bg-indigo-50 file:text-indigo-700
                      hover:file:bg-indigo-100"
                  >
                  <div v-if="downloadForm.errors.download_file" class="text-red-500 text-xs mt-1">{{ downloadForm.errors.download_file }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Version (optional)</label>
                <input 
                  v-model="downloadForm.version"
                  type="text"
                  placeholder="e.g. 1.2.0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                />
              </div>
              <div>
                <button 
                    type="submit" 
                    class="w-full px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                    :disabled="downloadForm.processing"
                >
                    Upload & Save
                </button>
              </div>
            </form>
            
            <div v-if="downloadForm.progress" class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-4">
              <div class="bg-green-600 h-2.5 rounded-full" :style="{ width: downloadForm.progress.percentage + '%' }"></div>
            </div>
        </div>

        <div class="mt-6">
          <h2 class="font-semibold mb-2">Recent Logs</h2>
          <ul class="text-sm space-y-1">
            <li v-for="l in plugin.logs" :key="l.id" class="border-b py-2 dark:border-gray-700 last:border-0">
              <span :class="levelClass(l.level)" class="font-bold mr-2 text-xs uppercase w-16 inline-block">{{ l.level }}</span>
              <span class="text-gray-800 dark:text-gray-200">{{ l.message }}</span>
              <span v-if="l.duration_ms" class="text-gray-500 text-xs ml-2">({{ l.duration_ms }} ms)</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  plugin: Object
})

const form = useForm({
  price: props.plugin.price || 0,
  demo_url: props.plugin.demo_url || '',
  thumbnail_url: props.plugin.thumbnail_url || '',
  is_paid: !!props.plugin.is_paid,
  description: props.plugin.description || '',
})

 const downloadForm = useForm({
   download_file: null,
   version: ''
 })

 function uploadDownload() {
   downloadForm.post(route('admin.plugins.upload-download', props.plugin.id), {
     preserveScroll: true,
     onSuccess: () => {
       downloadForm.reset()
     }
   })
 }

function updateMarketplace() {
  form.put(route('admin.plugins.update', props.plugin.id), {
    preserveScroll: true,
    onSuccess: () => {
      // alert('Updated successfully')
    }
  })
}

function rollback(version) {
  if (!confirm(`Are you sure you want to rollback to version ${version}?`)) return
  
  router.post(route('admin.plugins.rollback', props.plugin.id), {
    to_version: version
  })
}

function levelClass(level) {
  switch (level) {
    case 'error': return 'text-red-600 bg-red-100 px-1 rounded'
    case 'warning': return 'text-yellow-600 bg-yellow-100 px-1 rounded'
    case 'info': return 'text-blue-600 bg-blue-100 px-1 rounded'
    default: return 'text-gray-600 bg-gray-100 px-1 rounded'
  }
}
</script>

<style scoped>
/* Scoped styles removed in favor of Tailwind classes */
</style>
