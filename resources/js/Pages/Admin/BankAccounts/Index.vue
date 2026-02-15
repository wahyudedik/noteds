<template>
  <Head title="Bank Accounts" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Bank Accounts Management
        </h2>
        <button 
          @click="openModal()" 
          class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
        >
          Add Bank Account
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div v-if="accounts.data.length === 0" class="text-center py-8 text-gray-500">
              No bank accounts found. Add one to start accepting payments.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                  <tr>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bank Name</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Account Number</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Account Holder</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="account in accounts.data" :key="account.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ account.bank_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap font-mono">{{ account.account_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ account.account_holder }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                      >
                        {{ account.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                      <button @click="openModal(account)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                      <button @click="deleteAccount(account)" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="accounts.links && accounts.links.length > 3" class="mt-4">
              <!-- Simple pagination implementation or use Laravel's pagination links -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showModal" @close="closeModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          {{ isEditing ? 'Edit Bank Account' : 'Add New Bank Account' }}
        </h2>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Name</label>
            <input v-model="form.bank_name" type="text" placeholder="e.g. BCA" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <div v-if="form.errors.bank_name" class="text-red-500 text-xs mt-1">{{ form.errors.bank_name }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Number</label>
            <input v-model="form.account_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <div v-if="form.errors.account_number" class="text-red-500 text-xs mt-1">{{ form.errors.account_number }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Holder Name</label>
            <input v-model="form.account_holder" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <div v-if="form.errors.account_holder" class="text-red-500 text-xs mt-1">{{ form.errors.account_holder }}</div>
          </div>

          <div class="flex items-center">
            <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Active</label>
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
              {{ isEditing ? 'Update' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  accounts: Object
})

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = useForm({
  bank_name: '',
  account_number: '',
  account_holder: '',
  is_active: true,
  logo_url: ''
})

function openModal(account = null) {
  if (account) {
    isEditing.value = true
    editingId.value = account.id
    form.bank_name = account.bank_name
    form.account_number = account.account_number
    form.account_holder = account.account_holder
    form.is_active = !!account.is_active
    form.logo_url = account.logo_url
  } else {
    isEditing.value = false
    editingId.value = null
    form.reset()
    form.is_active = true
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.reset()
  form.clearErrors()
}

function submitForm() {
  if (isEditing.value) {
    form.put(route('admin.bank-accounts.update', editingId.value), {
      onSuccess: () => closeModal()
    })
  } else {
    form.post(route('admin.bank-accounts.store'), {
      onSuccess: () => closeModal()
    })
  }
}

function deleteAccount(account) {
  if (confirm('Are you sure you want to delete this bank account?')) {
    router.delete(route('admin.bank-accounts.destroy', account.id))
  }
}
</script>
