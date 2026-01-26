<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
      <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Accessibility</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Sesuaikan tampilan dan perilaku aplikasi untuk kenyamanan Anda.
      </p>
    </div>

    <!-- Main Preferences -->
    <div class="space-y-6">
      <!-- High Contrast Mode -->
      <div class="flex items-center justify-between">
        <div class="flex-1 pr-4">
          <label class="text-sm font-medium text-gray-900 dark:text-gray-100">High Contrast Mode</label>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Meningkatkan perbedaan warna untuk keterbacaan yang lebih baik.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
          <input type="checkbox" class="sr-only peer" :checked="hc" @change="toggleHc" aria-label="High contrast mode" />
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 dark:after:border-gray-600 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
        </label>
      </div>

      <!-- Keyboard Navigation Hints -->
      <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-800 pt-6">
        <div class="flex-1 pr-4">
          <label class="text-sm font-medium text-gray-900 dark:text-gray-100">Keyboard Navigation Hints</label>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tampilkan indikator fokus visual dan petunjuk pintasan keyboard.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
          <input type="checkbox" class="sr-only peer" :checked="keyboardHints" @change="toggleHints" aria-label="Keyboard navigation hints" />
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 dark:after:border-gray-600 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
        </label>
      </div>

      <!-- Font Size -->
      <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
        <div class="flex items-center justify-between mb-2">
          <label for="fontScale" class="text-sm font-medium text-gray-900 dark:text-gray-100">Font Size Scaling</label>
          <span class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-300">{{ fontScale }}%</span>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Sesuaikan ukuran teks aplikasi (100% - 200%).</p>
        <div class="flex items-center gap-4">
           <span class="text-xs text-gray-500">A</span>
           <input id="fontScale" type="range" min="100" max="200" step="5" :value="fontScale" @input="setScale($event.target.value)" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-indigo-600" aria-label="Font size percentage" />
           <span class="text-lg text-gray-900 dark:text-gray-100 font-bold">A</span>
        </div>
      </div>
      
      <!-- Motion Settings -->
      <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
        <div class="flex items-center justify-between mb-4">
           <div>
             <label class="text-sm font-medium text-gray-900 dark:text-gray-100">Global Motion</label>
             <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mengurangi intensitas animasi di seluruh aplikasi.</p>
           </div>
           <select v-model="reduceMotion" @change="applyReduceMotionLevel" class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              <option value="off">Off</option>
              <option value="system">System</option>
              <option value="light">Light</option>
              <option value="medium">Medium</option>
              <option value="full">Full</option>
           </select>
        </div>

        <!-- Granular Controls -->
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 space-y-4">
          <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Granular Animation Controls</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Feed -->
            <div class="flex items-center justify-between">
              <label class="text-sm text-gray-700 dark:text-gray-300">Feed Items</label>
              <select v-model="feedLevel" @change="applyFeedLevel" class="block w-28 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white h-8 py-1">
                <option value="off">Default</option>
                <option value="light">Light</option>
                <option value="medium">Medium</option>
                <option value="full">Full</option>
              </select>
            </div>
            <!-- Modal -->
            <div class="flex items-center justify-between">
              <label class="text-sm text-gray-700 dark:text-gray-300">Modals</label>
              <select v-model="modalLevel" @change="applyModalLevel" class="block w-28 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white h-8 py-1">
                <option value="off">Default</option>
                <option value="light">Light</option>
                <option value="medium">Medium</option>
                <option value="full">Full</option>
              </select>
            </div>
            <!-- Tooltip -->
            <div class="flex items-center justify-between">
              <label class="text-sm text-gray-700 dark:text-gray-300">Tooltips</label>
              <select v-model="tooltipLevel" @change="applyTooltipLevel" class="block w-28 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white h-8 py-1">
                <option value="off">Default</option>
                <option value="light">Light</option>
                <option value="medium">Medium</option>
                <option value="full">Full</option>
              </select>
            </div>
            <!-- Chart -->
            <div class="flex items-center justify-between">
              <label class="text-sm text-gray-700 dark:text-gray-300">Charts</label>
              <select v-model="chartLevel" @change="applyChartLevel" class="block w-28 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white h-8 py-1">
                <option value="off">Default</option>
                <option value="light">Light</option>
                <option value="medium">Medium</option>
                <option value="full">Full</option>
              </select>
            </div>
          </div>
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
