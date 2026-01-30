<script setup>
import { ref } from 'vue';

const toasts = ref([]);
const config = ref({ position: 'top-right', defaultDuration: 4000 });
let counter = 0;

const addToast = (toast) => {
  const id = ++counter;
  const duration = toast.duration ?? config.value.defaultDuration;
  const item = { id, type: toast.type || 'info', title: toast.title || '', message: toast.message || '', duration };
  toasts.value.push(item);
  item.timer = setTimeout(() => removeToast(id), duration);
};

const removeToast = (id) => {
  toasts.value = toasts.value.filter(t => {
    if (t.id === id && t.timer) clearTimeout(t.timer);
    return t.id !== id;
  });
};

if (!window.__toast) {
  window.__toast = { add: addToast, remove: removeToast, config: (c) => Object.assign(config.value, c || {}) };
}
</script>

<template>
  <div class="fixed z-50 space-y-2"
       :class="config.position === 'top-right' ? 'top-4 right-4' : config.position === 'top-left' ? 'top-4 left-4' : config.position === 'bottom-right' ? 'bottom-4 right-4' : 'bottom-4 left-4'">
    <div v-for="t in toasts" :key="t.id" class="transition transform ease-in-out duration-200 flex items-start gap-3 px-3 py-2 rounded shadow text-white"
         :class="t.type === 'error' ? 'bg-red-600' : t.type === 'success' ? 'bg-green-600' : 'bg-gray-800'" role="alert" aria-live="polite">
      <div class="flex-1">
        <div class="font-semibold">{{ t.title }}</div>
        <div class="text-sm opacity-90">{{ t.message }}</div>
      </div>
      <button type="button" class="text-white/80 hover:text-white" @click="removeToast(t.id)">×</button>
    </div>
  </div>
</template>
