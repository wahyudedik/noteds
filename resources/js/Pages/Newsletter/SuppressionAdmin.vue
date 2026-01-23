<template>
  <MessagingLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Suppression List</h1>
      <div class="flex gap-2 mb-2">
        <input v-model="email" class="border rounded px-2 py-1 w-64" placeholder="Tambah email" />
        <button class="px-2 py-1 bg-green-600 text-white rounded text-sm" @click="add">Tambah</button>
      </div>
      <div class="border rounded max-h-72 overflow-auto">
        <table class="w-full text-sm">
          <thead><tr><th class="text-left p-1">Email</th><th class="text-left p-1">Reason</th><th class="text-left p-1">Actions</th></tr></thead>
          <tbody>
            <tr v-for="s in rows" :key="s.id">
              <td class="p-1">{{ s.email }}</td>
              <td class="p-1">{{ s.reason }}</td>
              <td class="p-1"><button class="px-2 py-1 bg-red-600 text-white rounded text-xs" @click="remove(s.id)">Hapus</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </MessagingLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';

const email = ref('');
const rows = ref([]);

const load = async () => {
  const res = await fetch('/api/admin/newsletter/suppression', { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    rows.value = data.rows || [];
  }
};
const add = async () => {
  await fetch('/api/admin/newsletter/suppression', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ email: email.value }),
  });
  email.value = '';
  load();
};
const remove = async (id) => {
  await fetch('/api/admin/newsletter/suppression/' + id, {
    method: 'DELETE',
    credentials: 'include',
    headers: { 'Accept': 'application/json' },
  });
  load();
};
onMounted(load);
</script>
