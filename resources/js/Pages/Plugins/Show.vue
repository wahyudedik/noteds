<template>
  <Head :title="plugin.name" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          {{ plugin.name }}
        </h2>
        <span class="text-sm bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full text-gray-600 dark:text-gray-300">
          v{{ plugin.version }}
        </span>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          
          <!-- Left Column: Details -->
          <div class="md:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div v-if="plugin.thumbnail_url" class="w-full h-64 bg-gray-200 dark:bg-gray-700">
                <img :src="plugin.thumbnail_url" class="w-full h-full object-cover" alt="Thumbnail">
              </div>
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Description</h3>
                <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300">
                  {{ plugin.description || 'No description available.' }}
                </div>
                
                <div v-if="plugin.demo_url" class="mt-6">
                  <a :href="plugin.demo_url" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    View Live Demo
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Purchase/Download -->
          <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
              <div class="text-center mb-6">
                <span v-if="plugin.is_paid" class="text-3xl font-bold text-gray-900 dark:text-white">
                  Rp {{ Number(plugin.price).toLocaleString('id-ID') }}
                </span>
                <span v-else class="text-3xl font-bold text-green-600 dark:text-green-400">
                  Free
                </span>
              </div>

              <div v-if="plugin.is_paid">
                <button 
                  @click="showBuyModal = true"
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Buy Now
                </button>
                <p class="mt-2 text-xs text-center text-gray-500">
                  Secure payment via Bank Transfer
                </p>
              </div>
              <div v-else>
                <a 
                  :href="route('marketplace.download', plugin.id)" 
                  class="w-full inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  Download
                </a>
                <p class="mt-2 text-xs text-center text-gray-500">
                  Free products are available for direct download.
                </p>
              </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
              <h4 class="font-medium text-gray-900 dark:text-white mb-2">Plugin Info</h4>
              <dl class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                <div class="flex justify-between">
                  <dt>Version</dt>
                  <dd>{{ plugin.version }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt>Type</dt>
                  <dd class="capitalize">{{ plugin.type }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt>Author</dt>
                  <dd>{{ plugin.author || 'Unknown' }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt>Updated</dt>
                  <dd>{{ new Date(plugin.updated_at).toLocaleDateString() }}</dd>
                </div>
              </dl>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Buy Modal -->
    <Modal :show="showBuyModal" @close="showBuyModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Purchase {{ plugin.name }}
        </h2>
        
        <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-md mb-6">
          <p class="text-sm text-blue-800 dark:text-blue-200 font-medium mb-2">Transfer Instructions:</p>
          <p class="text-sm text-blue-700 dark:text-blue-300">
            Please transfer <strong>Rp {{ Number(plugin.price).toLocaleString('id-ID') }}</strong> to one of the bank accounts below. Upload the proof of transfer to complete your order.
          </p>
          <ul class="mt-3 text-sm text-blue-800 dark:text-blue-200 list-none space-y-2">
            <li v-for="bank in bankAccounts" :key="bank.id" class="flex items-center space-x-2">
              <span class="font-bold">{{ bank.bank_name }}:</span>
              <span>{{ bank.account_number }}</span>
              <span class="text-xs">({{ bank.account_holder }})</span>
            </li>
          </ul>
        </div>

        <form @submit.prevent="submitOrder" class="space-y-4">
          <!-- Buyer Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Your Name</label>
            <input v-model="form.buyer_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp Number</label>
            <input v-model="form.buyer_whatsapp" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>

          <!-- Bank Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Destination</label>
            <select v-model="form.bank_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option v-for="bank in bankAccounts" :key="bank.id" :value="bank.id">
                {{ bank.bank_name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proof of Transfer</label>
            <input 
              type="file" 
              @input="form.proof_file = $event.target.files[0]" 
              accept="image/*,application/pdf"
              class="mt-1 block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-md file:border-0
                file:text-sm file:font-semibold
                file:bg-indigo-50 file:text-indigo-700
                hover:file:bg-indigo-100
              "
            >
            <div v-if="form.errors.proof_file" class="text-red-500 text-xs mt-1">{{ form.errors.proof_file }}</div>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button 
              type="button" 
              class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50" 
              @click="showBuyModal = false"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              :disabled="form.processing"
            >
              Submit Order
            </button>
          </div>
        </form>
      </div>
    </Modal>
    <div class="mt-4 text-center" v-if="adminWhatsapp">
      <a :href="`https://wa.me/${adminWhatsapp}`" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:underline">
        Chat Admin via WhatsApp
      </a>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  plugin: Object,
  bankAccounts: Array,
  adminWhatsapp: {
    type: String,
    default: ''
  }
})

const showBuyModal = ref(false)
const form = useForm({
  proof_file: null,
  buyer_name: '',
  buyer_whatsapp: '',
  bank_id: null,
})

function submitOrder() {
  form.post(route('plugins.buy', props.plugin.id), {
    onSuccess: () => {
      showBuyModal.value = false
      form.reset()
      // You might want to redirect to orders page or show success message
    }
  })
}
</script>
