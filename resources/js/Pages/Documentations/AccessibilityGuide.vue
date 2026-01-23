<template>
  <MessagingLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Accessibility Guide (WCAG 2.1 AA)</h1>
      <p class="text-gray-600 dark:text-gray-400 mb-4">Pedoman dan checklist untuk pengembang/pengujian aksesibilitas.</p>
      <div class="space-y-4 text-sm">
        <section>
          <h2 class="text-lg font-semibold mb-2">Screen Reader</h2>
          <ul class="list-disc ml-5 space-y-1">
            <li>Gunakan alt pada gambar, aria-label pada tombol/icon, dan landmark semantik.</li>
            <li>Gunakan ARIA live regions untuk status dinamis; tersedia #aria-live di layout.</li>
          </ul>
        </section>
        <section>
          <h2 class="text-lg font-semibold mb-2">Keyboard Navigation</h2>
          <ul class="list-disc ml-5 space-y-1">
            <li>Skip to content tersedia; fokus jelas dengan ring.</li>
            <li>Pastikan semua aksi dapat diakses via Enter/Space.</li>
          </ul>
        </section>
        <section>
          <h2 class="text-lg font-semibold mb-2">Contrast & Fonts</h2>
          <ul class="list-disc ml-5 space-y-1">
            <li>Mode kontras tinggi dapat diaktifkan di Settings → Accessibility.</li>
            <li>Pengaturan font size hingga 200% tanpa kehilangan fungsi.</li>
          </ul>
        </section>
        <section>
          <h2 class="text-lg font-semibold mb-2">Color Blind Friendly</h2>
          <ul class="list-disc ml-5 space-y-1">
            <li>Tambahkan label teks/pola selain warna pada status atau grafik.</li>
          </ul>
        </section>
        <section>
          <h2 class="text-lg font-semibold mb-2">Testing</h2>
          <ul class="list-disc ml-5 space-y-1">
            <li>Audit Lighthouse target > 90 (Accessibility).</li>
            <li>Uji dengan NVDA/VoiceOver untuk alur utama.</li>
            <li>Gunakan axe DevTools untuk validasi ARIA.</li>
          </ul>
        </section>
      </div>
    </div>
  </MessagingLayout>
</template>

<script setup>
import MessagingLayout from '@/Layouts/MessagingLayout.vue';
</script>
