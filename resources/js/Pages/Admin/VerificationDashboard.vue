<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  types: { type: Array, default: () => [] },
  pending: { type: Array, default: () => [] },
  approved: { type: Array, default: () => [] },
  rejected: { type: Array, default: () => [] },
});

const approve = async (id) => {
  await axios.post(route('admin.verification.approve', id), {});
  window.location.reload();
};
const reject = async (id) => {
  const note = prompt('Alasan penolakan');
  if (!note) return;
  await axios.post(route('admin.verification.reject', id), { note });
  window.location.reload();
};
</script>

<template>
  <Head title="Verification Admin" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Verification</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-6xl space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
          <div class="text-sm font-semibold mb-3">Pending Requests</div>
          <div class="space-y-2">
            <div v-for="r in pending" :key="r.id" class="p-3 border rounded">
              <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">{{ r.type?.name }} • {{ r.user?.name }}</div>
              <div class="text-xs text-gray-600 dark:text-gray-300">Submitted: {{ r.submitted_at }}</div>
              <div v-if="r.recommendation" class="mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                <div class="text-xs font-semibold">Recommendation: <span :class="r.recommendation.recommendation ? 'text-green-600' : 'text-red-600'">{{ r.recommendation.recommendation ? 'Approve' : 'Review' }}</span></div>
                <div class="mt-1 text-xs">
                  <div v-for="c in r.recommendation.checks" :key="c.key" class="flex items-center justify-between">
                    <span>{{ c.key }}</span>
                    <span :class="c.pass ? 'text-green-600' : 'text-red-600'">{{ c.value }} / {{ c.threshold }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2 mt-2">
                <button @click="approve(r.id)" class="px-2 py-1 text-xs bg-green-600 text-white rounded">Approve</button>
                <button @click="reject(r.id)" class="px-2 py-1 text-xs bg-red-600 text-white rounded">Reject</button>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Approved</div>
            <div class="space-y-2">
              <div v-for="r in approved" :key="r.id" class="p-3 border rounded">
                <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">{{ r.type?.name }} • {{ r.user?.name }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-300">Reviewed: {{ r.reviewed_at }}</div>
              </div>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Rejected</div>
            <div class="space-y-2">
              <div v-for="r in rejected" :key="r.id" class="p-3 border rounded">
                <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">{{ r.type?.name }} • {{ r.user?.name }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-300">Reviewed: {{ r.reviewed_at }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-300">Note: {{ r.review_note }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
