<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Marketplace Settings
      </h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-4xl space-y-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Admin Contact</h3>
          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin WhatsApp Number</label>
              <input 
                v-model="form.admin_whatsapp"
                type="text" 
                placeholder="e.g. 6281654932383" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              >
              <p class="mt-1 text-xs text-gray-500">Gunakan format internasional tanpa tanda + (contoh: 62812xxxxxxx).</p>
              <div v-if="form.errors.admin_whatsapp" class="text-red-500 text-xs mt-1">{{ form.errors.admin_whatsapp }}</div>
            </div>
            <div class="flex justify-end">
              <button 
                type="submit" 
                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                :disabled="form.processing"
              >
                Save Settings
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  admin_whatsapp: {
    type: String,
    default: ''
  }
})

const form = useForm({
  admin_whatsapp: props.admin_whatsapp || ''
})

function submit() {
  form.post(route('admin.marketplace.settings.update'), {
    preserveScroll: true
  })
}
</script>
