<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
  providers: Array,
});

const form = useForm({
  name: '',
  type: 'custom_hls',
  config: {
    ingest_url: '',
    stream_key: '',
    playback_url: '',
    region: '',
    channel_arn: '',
    api_token: '',
  },
  active: true,
});

const submit = () => {
  form.post(route('admin.streaming.providers.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <Head title="Streaming Providers" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Streaming Providers</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 border rounded-lg p-4">
          <div class="text-sm font-semibold mb-4">Tambah Provider</div>
          <div class="space-y-3">
            <input v-model="form.name" class="w-full px-3 py-2 border rounded text-sm" placeholder="Nama" />
            <select v-model="form.type" class="w-full px-3 py-2 border rounded text-sm">
              <option value="custom_hls">Custom HLS</option>
              <option value="aws_ivs">AWS IVS</option>
              <option value="livepeer">Livepeer</option>
            </select>
            <div class="grid grid-cols-2 gap-3">
              <input v-model="form.config.ingest_url" class="px-3 py-2 border rounded text-sm" placeholder="Ingest URL" />
              <input v-model="form.config.stream_key" class="px-3 py-2 border rounded text-sm" placeholder="Stream Key" />
              <input v-model="form.config.playback_url" class="px-3 py-2 border rounded text-sm" placeholder="Playback URL" />
              <input v-model="form.config.region" class="px-3 py-2 border rounded text-sm" placeholder="Region" />
              <input v-model="form.config.channel_arn" class="px-3 py-2 border rounded text-sm" placeholder="Channel ARN" />
              <input v-model="form.config.api_token" class="px-3 py-2 border rounded text-sm" placeholder="API Token" />
            </div>
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" v-model="form.active" />
              Active
            </label>
            <button @click="submit" class="px-3 py-2 bg-indigo-600 text-white rounded text-sm">Simpan</button>
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 border rounded-lg p-4">
          <div class="text-sm font-semibold mb-4">Daftar Provider</div>
          <div class="space-y-3">
            <div v-for="p in props.providers" :key="p.id" class="border rounded p-3">
              <div class="text-sm font-semibold">{{ p.name }} ({{ p.type }})</div>
              <div class="text-xs text-gray-600 dark:text-gray-300">Active: {{ p.active ? 'Ya' : 'Tidak' }}</div>
              <div class="mt-2 text-xs break-all">Config: {{ JSON.stringify(p.config || {}) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
