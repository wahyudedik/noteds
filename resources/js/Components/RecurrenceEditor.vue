<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  type: { type: String, default: 'post' },
  id: { type: String, default: '' },
});

const freq = ref('WEEKLY');
const interval = ref(1);
const byday = ref([]);
const bymonthday = ref([]);
const dtstart = ref('');
const until = ref('');
const count = ref(null);
const timezone = ref(Intl.DateTimeFormat().resolvedOptions().timeZone);
const rrule = ref('');
const loading = ref(false);
const errors = ref({});

const weekdayOpts = ['MO','TU','WE','TH','FR','SA','SU'];

const preview = computed(() => {
  const parts = [];
  parts.push(`FREQ=${freq.value}`);
  if (interval.value > 1) parts.push(`INTERVAL=${interval.value}`);
  if (byday.value.length) parts.push(`BYDAY=${byday.value.join(',')}`);
  if (bymonthday.value.length) parts.push(`BYMONTHDAY=${bymonthday.value.join(',')}`);
  if (count.value) parts.push(`COUNT=${count.value}`);
  if (until.value) parts.push(`UNTIL=${new Date(until.value).toISOString().replace(/[-:]/g,'').split('.')[0]}Z`);
  return parts.join(';');
});

const load = async () => {
  if (!props.id) return;
  loading.value = true;
  const res = await axios.get(route('scheduling.recurrence.get', { type: props.type, id: props.id }));
  const data = res.data?.data;
  if (data) {
    freq.value = data.freq || freq.value;
    interval.value = data.interval || interval.value;
    byday.value = data.byday || [];
    bymonthday.value = data.bymonthday || [];
    dtstart.value = data.dtstart ? new Date(data.dtstart).toISOString().slice(0,16) : '';
    until.value = data.until ? new Date(data.until).toISOString().slice(0,16) : '';
    count.value = data.count || null;
    timezone.value = data.timezone || timezone.value;
    rrule.value = data.rrule || '';
  }
  loading.value = false;
};

const save = async () => {
  errors.value = {};
  if (!props.id) { errors.value.id = 'Select item'; return; }
  const payload = {
    timezone: timezone.value,
    rrule: rrule.value || preview.value,
    freq: freq.value,
    interval: interval.value,
    byday: byday.value,
    bymonthday: bymonthday.value,
    dtstart: dtstart.value || null,
    until: until.value || null,
    count: count.value || null,
  };
  await axios.post(route('scheduling.recurrence.save', { type: props.type, id: props.id }), payload);
};

onMounted(load);
</script>

<template>
  <div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div>
        <label class="text-sm">Frequency</label>
        <select v-model="freq" class="mt-1 w-full border rounded px-2 py-1">
          <option value="DAILY">Daily</option>
          <option value="WEEKLY">Weekly</option>
          <option value="MONTHLY">Monthly</option>
          <option value="YEARLY">Yearly</option>
        </select>
      </div>
      <div>
        <label class="text-sm">Interval</label>
        <input type="number" min="1" v-model.number="interval" class="mt-1 w-full border rounded px-2 py-1" />
      </div>
      <div>
        <label class="text-sm">Timezone</label>
        <input type="text" v-model="timezone" class="mt-1 w-full border rounded px-2 py-1" />
      </div>
    </div>

    <div>
      <label class="text-sm">BYDAY</label>
      <div class="flex flex-wrap gap-2 mt-1">
        <label v-for="d in weekdayOpts" :key="d" class="flex items-center gap-1 text-xs">
          <input type="checkbox" :value="d" v-model="byday" /> {{ d }}
        </label>
      </div>
    </div>

    <div>
      <label class="text-sm">BYMONTHDAY</label>
      <input type="text" v-model="bymonthday" placeholder="e.g. 1,15,30" class="mt-1 w-full border rounded px-2 py-1"
        @change="bymonthday = String(bymonthday).split(',').map(x=>parseInt(x.trim())).filter(x=>x>=1 && x<=31)" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div>
        <label class="text-sm">Start</label>
        <input type="datetime-local" v-model="dtstart" class="mt-1 w-full border rounded px-2 py-1" />
      </div>
      <div>
        <label class="text-sm">Until</label>
        <input type="datetime-local" v-model="until" class="mt-1 w-full border rounded px-2 py-1" />
      </div>
      <div>
        <label class="text-sm">Count</label>
        <input type="number" min="1" v-model.number="count" class="mt-1 w-full border rounded px-2 py-1" />
      </div>
    </div>

    <div>
      <label class="text-sm">RRULE</label>
      <input type="text" v-model="rrule" placeholder="Optional raw RRULE" class="mt-1 w-full border rounded px-2 py-1" />
      <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">Preview: {{ preview }}</div>
    </div>

    <div class="flex items-center justify-end gap-2">
      <button @click="save" class="px-3 py-1.5 bg-blue-600 text-white rounded">Save</button>
    </div>
  </div>
</template>
