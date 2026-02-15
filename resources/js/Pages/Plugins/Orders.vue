<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Pembelian Saya
      </h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
          <div v-if="orders.data.length === 0" class="text-center py-8 text-gray-500">
            Belum ada pembelian.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead>
                <tr>
                  <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Tanggal
                  </th>
                  <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Produk
                  </th>
                  <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Harga
                  </th>
                  <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="order in orders.data" :key="order.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ new Date(order.created_at).toLocaleString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ order.plugin?.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">
                    Rp {{ Number(order.price_paid).toLocaleString('id-ID') }}
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
                    <a 
                      v-if="order.payment_status === 'paid'"
                      :href="route('marketplace.download', order.plugin.id)"
                      class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                    >
                      Download
                    </a>
                    <a v-if="adminWhatsapp"
                       :href="`https://wa.me/${adminWhatsapp}`"
                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 ml-2"
                       target="_blank"
                       rel="noopener"
                    >Chat Admin</a>
                    <span v-else class="text-xs text-gray-500">
                      Menunggu verifikasi
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
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
  </AuthenticatedLayout>
  </template>
  
  <script setup>
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { Link } from '@inertiajs/vue3'
  
  const props = defineProps({
    orders: Object,
    adminWhatsapp: {
      type: String,
      default: ''
    }
  })
  </script>
