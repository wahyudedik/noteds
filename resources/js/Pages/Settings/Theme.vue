<template>
  <div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Appearance</h3>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Pilih mode tema untuk aplikasi.</p>
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <label for="themeMode" class="text-sm font-medium text-gray-700 dark:text-gray-300">Theme</label>
      <select id="themeMode" v-model="selected" @change="apply" class="border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100">
        <option value="system">System</option>
        <option value="light">Light</option>
        <option value="dark">Dark</option>
      </select>
      <span class="text-xs text-gray-500 dark:text-gray-400" aria-live="polite">Current: {{ selectedLabel }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getStoredTheme, setTheme, systemPrefersDark } from '@/Utils/theme';

const selected = ref('system');
const selectedLabel = computed(() => selected.value.charAt(0).toUpperCase() + selected.value.slice(1));

const init = () => {
  const stored = getStoredTheme();
  if (stored) selected.value = stored;
  else selected.value = systemPrefersDark() ? 'dark' : 'light';
};
const apply = () => {
  setTheme(selected.value);
};
onMounted(init);
</script>
