<template>
  <AuthenticatedLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Style Guide: Theme Colors</h1>
      <p class="text-gray-600 dark:text-gray-400 mb-4">Palet warna untuk mode terang dan gelap termasuk kode HEX/RGB/HSL dan contoh penerapan.</p>
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <h2 class="text-lg font-semibold mb-2">Light Theme</h2>
          <ul class="text-sm space-y-2">
            <li><strong>Primary:</strong> #2563EB / rgb(37,99,235) / hsl(217,91%,60%)</li>
            <li><strong>Secondary:</strong> #111827 / rgb(17,24,39) / hsl(215,28%,10%)</li>
            <li><strong>Accent:</strong> #10B981 / rgb(16,185,129) / hsl(160,84%,39%)</li>
            <li>Kontras teks utama: {{ contrast('#111827', '#ffffff') }}:1</li>
          </ul>
          <div class="mt-3 space-x-2">
            <button class="px-3 py-2 bg-indigo-600 text-white rounded">Primary Button</button>
            <button class="px-3 py-2 bg-green-600 text-white rounded">Accent Button</button>
          </div>
          <div class="mt-3 p-3 border rounded bg-white text-gray-900">Card example</div>
          <div class="mt-3 p-3 border rounded">
            <div class="mb-2">Modal</div>
            <div class="p-4 bg-white rounded shadow">Content</div>
          </div>
          <div class="mt-3 p-3 border rounded">
            <div class="mb-2">Tooltip</div>
            <span class="inline-block px-2 py-1 rounded bg-gray-800 text-white">Tooltip</span>
          </div>
          <div class="mt-3 p-3 border rounded">
            <div class="mb-2">Table</div>
            <table class="w-full text-sm">
              <thead><tr><th class="text-left p-1">Col</th><th class="text-left p-1">Val</th></tr></thead>
              <tbody><tr><td class="p-1">A</td><td class="p-1">1</td></tr></tbody>
            </table>
          </div>
        </div>
        <div>
          <h2 class="text-lg font-semibold mb-2">Dark Theme</h2>
          <ul class="text-sm space-y-2">
            <li><strong>Primary:</strong> #60A5FA / rgb(96,165,250) / hsl(210,94%,68%)</li>
            <li><strong>Secondary:</strong> #1F2937 / rgb(31,41,55) / hsl(216,33%,14%)</li>
            <li><strong>Accent:</strong> #34D399 / rgb(52,211,153) / hsl(158,64%,52%)</li>
            <li>Kontras teks utama: {{ contrast('#ffffff', '#1F2937') }}:1</li>
          </ul>
          <div class="mt-3 space-x-2">
            <button class="px-3 py-2 bg-blue-500 text-white rounded">Primary Button</button>
            <button class="px-3 py-2 bg-emerald-500 text-white rounded">Accent Button</button>
          </div>
          <div class="mt-3 p-3 border border-gray-700 rounded bg-gray-800 text-gray-100">Card example</div>
          <div class="mt-3 p-3 border border-gray-700 rounded">
            <div class="mb-2 text-gray-100">Modal</div>
            <div class="p-4 bg-gray-800 rounded shadow">Content</div>
          </div>
          <div class="mt-3 p-3 border border-gray-700 rounded">
            <div class="mb-2 text-gray-100">Tooltip</div>
            <span class="inline-block px-2 py-1 rounded bg-gray-900 text-gray-100">Tooltip</span>
          </div>
          <div class="mt-3 p-3 border border-gray-700 rounded">
            <div class="mb-2 text-gray-100">Table</div>
            <table class="w-full text-sm text-gray-100">
              <thead><tr><th class="text-left p-1">Col</th><th class="text-left p-1">Val</th></tr></thead>
              <tbody><tr><td class="p-1">A</td><td class="p-1">1</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="mt-6">
        <h2 class="text-lg font-semibold mb-2">States</h2>
        <div class="flex flex-wrap gap-3 text-sm">
          <button class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded">Hover/Active</button>
          <button class="px-3 py-2 bg-gray-400 text-gray-700 rounded cursor-not-allowed" disabled>Disabled</button>
        </div>
      </div>
      <div class="mt-6">
        <h2 class="text-lg font-semibold mb-2">Aksesibilitas</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Kontras warna dipilih untuk memenuhi WCAG 2.1 AA pada teks utama dan tombol.</p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const luminance = (hex) => {
  const rgb = hex.replace('#','').match(/.{1,2}/g).map(x => parseInt(x,16)/255).map(v => v <= 0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055, 2.4));
  return 0.2126*rgb[0] + 0.7152*rgb[1] + 0.0722*rgb[2];
};
const contrast = (c1, c2) => {
  const L1 = luminance(c1);
  const L2 = luminance(c2);
  const ratio = (Math.max(L1, L2) + 0.05) / (Math.min(L1, L2) + 0.05);
  return ratio.toFixed(2);
};
</script>
