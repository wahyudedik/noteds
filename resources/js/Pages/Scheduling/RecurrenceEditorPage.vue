<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import RecurrenceEditor from '@/Components/RecurrenceEditor.vue';
import { ref } from 'vue';

const type = ref('post');
const id = ref('');
</script>

<template>
  <Head title="Recurrence Editor" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Recurrence</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-3xl space-y-4">
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="text-sm">Type</label>
            <select v-model="type" class="mt-1 w-full border rounded px-2 py-1">
              <option value="post">Post</option>
              <option value="campaign">Campaign</option>
              <option value="product_release">Product Release</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="text-sm">Item ID</label>
            <input type="text" v-model="id" class="mt-1 w-full border rounded px-2 py-1" placeholder="Enter item ID" />
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <RecurrenceEditor :type="type" :id="id" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
