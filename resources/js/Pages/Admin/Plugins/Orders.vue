<template>
  <Head title="Plugin Orders" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Orders Management
        </h2>
        <a 
          :href="route('admin.marketplace.orders.export')"
          class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
          target="_blank"
        >
          Export Excel
        </a>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div v-if="orders.data.length === 0" class="text-center py-8 text-gray-500">
              No orders found.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                  <tr>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Buyer</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Plugin</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bank</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="order in orders.data" :key="order.id">
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-mono">
                      #{{ order.id.substring(0, 8) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      {{ new Date(order.created_at).toLocaleDateString() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium">{{ order.buyer_name || order.user.name }}</div>
                      <div class="text-xs text-gray-500">{{ order.buyer_whatsapp }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      {{ order.plugin.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">
                      Rp {{ Number(order.price_paid).toLocaleString('id-ID') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      {{ order.bank_account ? order.bank_account.bank_name : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="{
                          'bg-green-100 text-green-800': order.payment_status === 'paid',
                          'bg-yellow-100 text-yellow-800': order.payment_status === 'pending',
                          'bg-red-100 text-red-800': order.payment_status === 'rejected'
                        }"
                      >
                        {{ order.payment_status.toUpperCase() }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button @click="openModal(order)" class="text-indigo-600 hover:text-indigo-900">Details</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="orders.links && orders.links.length > 3" class="mt-4 flex justify-center">
               <template v-for="(link, key) in orders.links" :key="key">
                <div
                  v-if="link.url === null"
                  class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                  v-html="link.label"
                />
                <Link
                  v-else
                  class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                  :class="{ 'bg-blue-700 text-white': link.active }"
                  :href="link.url"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Detail / Update Modal -->
    <Modal :show="showModal" @close="closeModal">
      <div class="p-6" v-if="selectedOrder">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Order #{{ selectedOrder.id.substring(0, 8) }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <h3 class="font-bold text-sm text-gray-700 dark:text-gray-300">Buyer Info</h3>
            <p class="text-sm">Name: {{ selectedOrder.buyer_name || selectedOrder.user.name }}</p>
            <p class="text-sm">Email: {{ selectedOrder.user.email }}</p>
            <p class="text-sm">WhatsApp: {{ selectedOrder.buyer_whatsapp }}</p>
          </div>
          <div>
            <h3 class="font-bold text-sm text-gray-700 dark:text-gray-300">Product Info</h3>
            <p class="text-sm">Plugin: {{ selectedOrder.plugin.name }}</p>
            <p class="text-sm">Price: Rp {{ Number(selectedOrder.price_paid).toLocaleString('id-ID') }}</p>
          </div>
        </div>

        <div class="mb-6">
          <h3 class="font-bold text-sm text-gray-700 dark:text-gray-300 mb-2">Proof of Transfer</h3>
          <div v-if="selectedOrder.proof_file" class="border p-2 rounded bg-gray-50 dark:bg-gray-700">
            <a :href="'/storage/' + selectedOrder.proof_file" target="_blank" class="text-blue-600 hover:underline flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              View Proof File
            </a>
          </div>
          <div v-else class="text-sm text-gray-500 italic">No proof uploaded</div>
        </div>

        <form @submit.prevent="updateStatus" class="space-y-4 border-t pt-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Status</label>
            <select v-model="form.payment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="pending">Pending</option>
              <option value="paid">Paid (Verify & Send Email)</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Note</label>
            <textarea v-model="form.admin_note" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button 
              type="button" 
              class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50" 
              @click="closeModal"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              :disabled="form.processing"
            >
              Update Status
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  orders: Object
})

const showModal = ref(false)
const selectedOrder = ref(null)

const form = useForm({
  payment_status: 'pending',
  admin_note: ''
})

function openModal(order) {
  selectedOrder.value = order
  form.payment_status = order.payment_status
  form.admin_note = order.admin_note
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  selectedOrder.value = null
  form.reset()
}

function updateStatus() {
  if (!selectedOrder.value) return
  
  form.put(route('admin.marketplace.orders.update', selectedOrder.value.id), {
    onSuccess: () => closeModal()
  })
}
</script>
