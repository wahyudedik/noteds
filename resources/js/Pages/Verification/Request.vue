<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  types: { type: Array, default: () => [] },
  requests: { type: Array, default: () => [] },
});

const form = ref({ type_id: '', form: {}, documents: [] });
const submitting = ref(false);

const submit = async () => {
  submitting.value = true;
  await axios.post(route('verification.submit'), form.value);
  submitting.value = false;
  window.location.reload();
};
</script>

<template>
  <Head title="Verification" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">User Verification</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-3xl space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="text-sm">Verification Type</label>
              <select v-model="form.type_id" class="mt-1 w-full border rounded px-2 py-1">
                <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
            </div>
            <div>
              <label class="text-sm">Notes</label>
              <input type="text" v-model="form.form.notes" class="mt-1 w-full border rounded px-2 py-1" placeholder="Alasan & referensi" />
            </div>
          </div>
          <div class="mt-3">
            <label class="text-sm">Documents</label>
            <input type="text" v-model="form.documents[0]" class="mt-1 w-full border rounded px-2 py-1" placeholder="File path or URL (optional)" />
          </div>
          <div class="flex items-center justify-end mt-4">
            <button @click="submit" :disabled="submitting || !form.type_id" class="px-3 py-1.5 bg-blue-600 text-white rounded">Submit</button>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <div class="text-sm font-semibold mb-2">My Requests</div>
          <div class="space-y-2">
            <div v-for="r in requests" :key="r.id" class="p-3 border rounded">
              <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">{{ r.type?.name }}</div>
              <div class="text-sm">Status: <span class="font-semibold capitalize">{{ r.status }}</span></div>
              <div class="text-xs text-gray-600 dark:text-gray-300">Submitted: {{ r.submitted_at }}</div>
              <div v-if="r.reviewed_at" class="text-xs text-gray-600 dark:text-gray-300">Reviewed: {{ r.reviewed_at }}</div>
              <div v-if="r.review_note" class="text-xs text-gray-600 dark:text-gray-300">Note: {{ r.review_note }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
