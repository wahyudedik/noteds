<template>
  <MessagingLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-2">Segmentasi Kampanye</h1>
      <div class="space-y-2">
        <div class="flex gap-2 items-center">
          <select v-model="field" class="border rounded px-2 py-1">
            <option value="subscribed_at_range">Tanggal Bergabung</option>
            <option value="preference_frequency">Frekuensi</option>
            <option value="opens_gt">Opens ≥</option>
            <option value="clicks_gt">Clicks ≥</option>
          </select>
          <input v-if="field==='preference_frequency'" v-model="value" class="border rounded px-2 py-1" placeholder="high|medium|low" />
          <div v-else-if="field==='subscribed_at_range'" class="flex gap-2">
            <input v-model="dateStart" type="date" class="border rounded px-2 py-1" />
            <input v-model="dateEnd" type="date" class="border rounded px-2 py-1" />
          </div>
          <input v-else v-model.number="value" type="number" class="border rounded px-2 py-1 w-32" />
          <button class="px-2 py-1 bg-green-600 text-white rounded text-sm" @click="addRule">Tambah Rule</button>
        </div>
        <div>
          <div class="text-sm font-semibold">Rules</div>
          <div v-for="(r,i) in rules" :key="i" class="flex gap-2 items-center text-sm">
            <span>{{ r.field }}</span>
            <span>-</span>
            <span>{{ Array.isArray(r.value) ? r.value.join('..') : r.value }}</span>
            <button class="px-1 py-0.5 bg-red-600 text-white rounded text-xs" @click="removeRule(i)">Hapus</button>
          </div>
        </div>
        <div class="flex gap-2 items-center">
          <label class="text-sm">Logic</label>
          <select v-model="logic" class="border rounded px-2 py-1">
            <option value="AND">AND</option>
            <option value="OR">OR</option>
          </select>
          <button class="px-2 py-1 bg-gray-800 text-white rounded text-sm" @click="estimate">Estimasi</button>
          <div class="text-sm">Estimated Audience: {{ estimated }}</div>
        </div>
      </div>
    </div>
  </MessagingLayout>
  <ToastContainer />
</template>

<script setup>
import { ref } from 'vue';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';
import ToastContainer from '@/Components/Common/ToastContainer.vue';

const rules = ref([]);
const logic = ref('AND');
const estimated = ref(0);
const field = ref('subscribed_at_range');
const value = ref('');
const dateStart = ref('');
const dateEnd = ref('');

const addRule = () => {
  if (field.value === 'subscribed_at_range') {
    rules.value.push({ field: field.value, op: 'between', value: [dateStart.value, dateEnd.value] });
  } else {
    rules.value.push({ field: field.value, op: 'eq', value: value.value });
  }
};
const removeRule = (i) => { rules.value.splice(i, 1); };

const estimate = async () => {
  const res = await fetch(route('admin.newsletter.segmentation.estimate'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ rules: rules.value, logic: logic.value }),
  });
  if (res.ok) {
    const data = await res.json();
    estimated.value = data.estimated;
  }
};
</script>
