<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
const page = usePage();
const settings = page.props.settings;
const form = useForm({
  auto_play_enabled: settings?.auto_play_enabled ?? false,
});
const submit = () => {
  form.post(route('settings.playback'), {
    preserveScroll: true,
  });
};
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">Playback</h3>
    <label class="flex items-center gap-2">
      <input type="checkbox" v-model="form.auto_play_enabled" />
      Auto-play pesan suara baru
    </label>
    <p class="text-xs text-gray-500">Auto-play akan dibatasi pada kondisi tertentu (headphone, baterai rendah).</p>
    <button class="px-3 py-2 bg-indigo-600 text-white rounded text-sm" @click="submit">Simpan</button>
  </div>
</template>
