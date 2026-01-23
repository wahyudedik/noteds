<template>
  <div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Blocked Users</h3>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Kelola daftar pengguna yang Anda blokir.</p>
    <div v-if="loading" class="text-sm text-gray-500">Loading...</div>
    <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
      <li v-for="u in blocked" :key="u.id" class="py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-full bg-indigo-500 text-white flex items-center justify-center">{{ (u.business_name || u.name).charAt(0).toUpperCase() }}</div>
          <div class="text-sm text-gray-900 dark:text-gray-100">{{ u.business_name || u.name }}</div>
        </div>
        <button @click="unblock(u.id)" class="px-3 py-1.5 bg-red-600 text-white rounded text-xs">Unblock</button>
      </li>
    </ul>
    <div v-if="blocked.length === 0 && !loading" class="text-sm text-gray-500">Tidak ada pengguna yang diblokir.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const blocked = ref([]);
const loading = ref(false);
const load = async () => {
  loading.value = true;
  try {
    const res = await fetch(route('user.blocked.list'), { credentials: 'include', headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    blocked.value = data.blocked || [];
  } finally {
    loading.value = false;
  }
};
const unblock = async (id) => {
  await fetch(route('user.unblock', id), { method: 'DELETE', credentials: 'include', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '' } });
  blocked.value = blocked.value.filter(x => x.id !== id);
};
onMounted(load);
</script>
