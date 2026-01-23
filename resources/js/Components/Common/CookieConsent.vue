<template>
  <div v-if="show" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 max-w-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 z-50">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">Cookie Consent</h3>
    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">Kami menggunakan cookie untuk meningkatkan pengalaman. Pilih kategori yang diizinkan.</p>
    <div class="grid grid-cols-2 gap-2 text-xs">
      <label class="inline-flex items-center gap-2"><input type="checkbox" disabled checked /> Esensial</label>
      <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="consent.functional" /> Fungsional</label>
      <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="consent.analytics" /> Analitik</label>
      <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="consent.marketing" /> Pemasaran</label>
    </div>
    <div class="mt-3 flex gap-2">
      <button @click="accept" class="px-3 py-2 bg-indigo-600 text-white rounded text-xs">Accept Selected</button>
      <button @click="reject" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs">Reject Non-essential</button>
    </div>
  </div>
  <div v-if="showManager" class="fixed bottom-4 right-4">
    <button @click="open" class="px-3 py-2 bg-gray-800 text-white rounded text-xs">Cookie Settings</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const show = ref(false);
const showManager = ref(true);
const consent = ref({ functional: false, analytics: false, marketing: false });
const policyVersion = '1.0';
const storageKey = 'gdpr.cookieConsent';

const load = () => {
  try {
    const v = localStorage.getItem(storageKey);
    if (v) {
      consent.value = JSON.parse(v);
      show.value = false;
      showManager.value = true;
    } else {
      show.value = true;
      showManager.value = false;
    }
  } catch { show.value = true; }
};
const save = async () => {
  localStorage.setItem(storageKey, JSON.stringify(consent.value));
  try {
    await fetch(route('gdpr.consent'), {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
      body: JSON.stringify({ policy_version: policyVersion, cookie_categories: consent.value }),
    });
  } catch {}
};
const accept = async () => {
  await save();
  show.value = false;
  showManager.value = true;
};
const reject = async () => {
  consent.value = { functional: false, analytics: false, marketing: false };
  await save();
  show.value = false;
  showManager.value = true;
};
const open = () => { show.value = true; };
onMounted(load);
</script>
