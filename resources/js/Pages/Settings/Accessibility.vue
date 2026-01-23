<template>
  <div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Accessibility</h3>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Pengaturan untuk meningkatkan aksesibilitas aplikasi.</p>
    <div class="space-y-4">
      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Reduce Motion</label>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Kurangi animasi yang tidak esensial.</p>
        <div class="flex items-center gap-2">
          <select v-model="reduceMotion" @change="applyReduceMotionLevel" class="border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100">
            <option value="off">Off</option>
            <option value="system">System</option>
            <option value="light">Ringan</option>
            <option value="medium">Sedang</option>
            <option value="full">Lengkap</option>
          </select>
          <span class="text-xs text-gray-500 dark:text-gray-400">Level: {{ reduceMotion }}</span>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Keyboard Navigation Hints</label>
          <p class="text-xs text-gray-500 dark:text-gray-400">Tampilkan indikator fokus dan petunjuk keyboard.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" class="sr-only peer" :checked="keyboardHints" @change="toggleHints" aria-label="Keyboard navigation hints" />
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 dark:after:border-gray-600 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
        </label>
      </div>
      <div class="flex items-center justify-between">
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">High Contrast Mode</label>
          <p class="text-xs text-gray-500 dark:text-gray-400">Meningkatkan kontras warna untuk keterbacaan.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" class="sr-only peer" :checked="hc" @change="toggleHc" aria-label="High contrast mode" />
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 dark:after:border-gray-600 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
        </label>
      </div>
      <div>
        <label for="fontScale" class="text-sm font-medium text-gray-700 dark:text-gray-300">Font Size</label>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Sesuaikan ukuran font hingga 200%.</p>
        <input id="fontScale" type="range" min="100" max="200" step="5" :value="fontScale" @input="setScale($event.target.value)" class="w-full" aria-valuemin="100" aria-valuemax="200" :aria-valuenow="fontScale" aria-label="Font size percentage" />
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Current: {{ fontScale }}%</div>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Feed Animations</label>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Kontrol granular untuk animasi feed.</p>
        <select v-model="feedLevel" @change="applyFeedLevel" class="border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100">
          <option value="off">Default</option>
          <option value="light">Ringan</option>
          <option value="medium">Sedang</option>
          <option value="full">Lengkap</option>
        </select>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Modal Animations</label>
          <select v-model="modalLevel" @change="applyModalLevel" class="mt-1 border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100">
            <option value="off">Default</option>
            <option value="light">Ringan</option>
            <option value="medium">Sedang</option>
            <option value="full">Lengkap</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tooltip Animations</label>
          <select v-model="tooltipLevel" @change="applyTooltipLevel" class="mt-1 border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100">
            <option value="off">Default</option>
            <option value="light">Ringan</option>
            <option value="medium">Sedang</option>
            <option value="full">Lengkap</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Chart Animations</label>
          <select v-model="chartLevel" @change="applyChartLevel" class="mt-1 border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100">
            <option value="off">Default</option>
            <option value="light">Ringan</option>
            <option value="medium">Sedang</option>
            <option value="full">Lengkap</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { applyHighContrast, applyFontScale, applyReduceMotion, getReduceMotion, setReduceMotion, applyKeyboardHints, getKeyboardHints, setKeyboardHints, applyComponentReduceMotion, getComponentReduceMotion, setComponentReduceMotion } from '@/Utils/accessibility';
const saveServer = async () => {
  try {
    await fetch(route('user.a11y.preferences.save'), {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ high_contrast: hc.value, font_scale: fontScale.value, reduce_motion: reduceMotion.value }),
    });
  } catch {}
};

const hc = ref(false);
const fontScale = ref(100);
const reduceMotion = ref('off');
const keyboardHints = ref(false);
const feedLevel = ref('off');
const modalLevel = ref('off');
const tooltipLevel = ref('off');
const chartLevel = ref('off');
const toggleHc = (e) => { hc.value = e.target.checked; applyHighContrast(hc.value); saveServer(); };
const setScale = (v) => { fontScale.value = Number(v); applyFontScale(fontScale.value); saveServer(); };
const applyReduceMotionLevel = () => { applyReduceMotion(reduceMotion.value); setReduceMotion(reduceMotion.value); saveServer(); };
const toggleHints = (e) => { keyboardHints.value = e.target.checked; applyKeyboardHints(keyboardHints.value); setKeyboardHints(keyboardHints.value); saveServer(); };
const applyFeedLevel = () => {
  applyComponentReduceMotion('feed', feedLevel.value);
  const prefs = getComponentReduceMotion();
  prefs.feed = feedLevel.value;
  setComponentReduceMotion(prefs);
  saveServer();
};
const applyModalLevel = () => {
  applyComponentReduceMotion('modal', modalLevel.value);
  const prefs = getComponentReduceMotion();
  prefs.modal = modalLevel.value;
  setComponentReduceMotion(prefs);
  saveServer();
};
const applyTooltipLevel = () => {
  applyComponentReduceMotion('tooltip', tooltipLevel.value);
  const prefs = getComponentReduceMotion();
  prefs.tooltip = tooltipLevel.value;
  setComponentReduceMotion(prefs);
  saveServer();
};
const applyChartLevel = () => {
  applyComponentReduceMotion('chart', chartLevel.value);
  const prefs = getComponentReduceMotion();
  prefs.chart = chartLevel.value;
  setComponentReduceMotion(prefs);
  saveServer();
};
onMounted(() => {
  hc.value = document.documentElement.classList.contains('hc');
  fontScale.value = parseInt(getComputedStyle(document.documentElement).fontSize) / 16 * 100;
  reduceMotion.value = getReduceMotion();
  keyboardHints.value = getKeyboardHints();
  const comp = getComponentReduceMotion();
  feedLevel.value = comp.feed || 'off';
  modalLevel.value = comp.modal || 'off';
  tooltipLevel.value = comp.tooltip || 'off';
  chartLevel.value = comp.chart || 'off';
  // fetch server prefs
  (async () => {
    try {
      const res = await fetch(route('user.a11y.preferences.get'), { credentials: 'include', headers: { 'Accept': 'application/json' } });
      if (res.ok) {
        const data = await res.json();
        const p = data.preferences || {};
        if (typeof p.high_contrast === 'boolean') { hc.value = p.high_contrast; applyHighContrast(hc.value); }
        if (typeof p.font_scale === 'number') { fontScale.value = p.font_scale; applyFontScale(fontScale.value); }
        if (typeof p.reduce_motion === 'string') { reduceMotion.value = p.reduce_motion; applyReduceMotion(reduceMotion.value); }
        if (typeof p.keyboard_navigation === 'boolean') { keyboardHints.value = p.keyboard_navigation; applyKeyboardHints(keyboardHints.value); }
        if (p.component_reduce_motion && typeof p.component_reduce_motion.feed === 'string') { feedLevel.value = p.component_reduce_motion.feed; applyComponentReduceMotion('feed', feedLevel.value); }
        if (p.component_reduce_motion && typeof p.component_reduce_motion.modal === 'string') { modalLevel.value = p.component_reduce_motion.modal; applyComponentReduceMotion('modal', modalLevel.value); }
        if (p.component_reduce_motion && typeof p.component_reduce_motion.tooltip === 'string') { tooltipLevel.value = p.component_reduce_motion.tooltip; applyComponentReduceMotion('tooltip', tooltipLevel.value); }
        if (p.component_reduce_motion && typeof p.component_reduce_motion.chart === 'string') { chartLevel.value = p.component_reduce_motion.chart; applyComponentReduceMotion('chart', chartLevel.value); }
      }
    } catch {}
  })();
});
</script>
