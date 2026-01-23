<template>
  <MessagingLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Berlangganan Newsletter</h1>
      <form class="space-y-2" @submit.prevent="submit">
        <input v-model="name" type="text" class="border rounded px-3 py-2 w-64" placeholder="Nama" />
        <input v-model="email" type="email" class="border rounded px-3 py-2 w-64" placeholder="Email" />
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Subscribe</button>
      </form>
      <div class="mt-2 text-sm text-gray-600" v-if="message">{{ message }}</div>
    </div>
  </MessagingLayout>
  <ToastContainer />
  </template>

<script setup>
import { ref } from 'vue';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';
import ToastContainer from '@/Components/Common/ToastContainer.vue';

const name = ref('');
const email = ref('');
const message = ref('');

const submit = async () => {
  const res = await fetch(route('newsletter.subscribe'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ name: name.value, email: email.value }),
  });
  if (res.ok) {
    message.value = 'Terima kasih! Silakan cek email Anda untuk konfirmasi.';
    window.__toast?.add({ title: 'Subscribed', message: 'Konfirmasi dikirim', type: 'success' });
  } else {
    window.__toast?.add({ title: 'Gagal', message: 'Email tidak valid atau tersuppress', type: 'error' });
  }
};
</script>
