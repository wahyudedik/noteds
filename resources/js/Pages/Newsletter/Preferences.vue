<template>
  <MessagingLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Preferensi Email</h1>
      <div class="space-y-2">
        <label class="block text-sm">Email</label>
        <input v-model="email" class="border rounded px-2 py-1 w-64" placeholder="Email" />
        <label class="block text-sm">Frekuensi</label>
        <select v-model="frequency" class="border rounded px-2 py-1 w-64">
          <option value="high">Sering</option>
          <option value="medium">Sedang</option>
          <option value="low">Jarang</option>
          <option value="none">Berhenti</option>
        </select>
        <button class="px-3 py-2 bg-blue-600 text-white rounded" @click="save">Simpan</button>
      </div>
    </div>
  </MessagingLayout>
</template>

<script setup>
import { ref } from 'vue';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';

const email = ref('');
const frequency = ref('medium');

const save = async () => {
  await fetch('/api/newsletter/preferences', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ email: email.value, frequency: frequency.value }),
  });
};
</script>
