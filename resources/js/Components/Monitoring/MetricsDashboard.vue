<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  conversationId: [String, Number],
  sessionId: [String, Number],
});

const logs = ref([]);
const metrics = ref([]);
const filterEvent = ref('');
const loading = ref(false);

const fetchLogs = async () => {
  const url = filterEvent.value ? `/api/logs?event=${encodeURIComponent(filterEvent.value)}` : '/api/logs';
  const res = await fetch(url, { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    logs.value = data.logs || [];
  }
};

const fetchMetrics = async () => {
  const url = route('calls.metrics.list', { conversation: props.conversationId, session: props.sessionId });
  const res = await fetch(url, { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (res.ok) {
    const data = await res.json();
    metrics.value = data.metrics || [];
  }
};

const refresh = async () => {
  loading.value = true;
  await Promise.all([fetchLogs(), fetchMetrics()]);
  loading.value = false;
};

onMounted(refresh);
</script>

<template>
  <div class="mt-4 border rounded p-3">
    <div class="flex items-center gap-2 mb-2">
      <div class="font-semibold">Diagnostics</div>
      <input class="border rounded px-2 text-sm" placeholder="Filter event" v-model="filterEvent" @input="fetchLogs" />
      <button class="px-2 py-1 bg-gray-800 text-white rounded text-sm" @click="refresh" :disabled="loading">Refresh</button>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <div class="text-sm font-semibold mb-1">Logs</div>
        <div class="max-h-64 overflow-auto border rounded">
          <table class="w-full text-xs">
            <thead><tr><th class="text-left p-1">Time</th><th class="text-left p-1">Event</th><th class="text-left p-1">Payload</th></tr></thead>
            <tbody>
              <tr v-for="l in logs" :key="l.id">
                <td class="p-1">{{ l.created_at }}</td>
                <td class="p-1">{{ l.event }}</td>
                <td class="p-1">{{ l.payload }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div>
        <div class="text-sm font-semibold mb-1">Metrics</div>
        <div class="max-h-64 overflow-auto border rounded">
          <table class="w-full text-xs">
            <thead><tr><th class="text-left p-1">Time</th><th class="text-left p-1">Latency</th><th class="text-left p-1">Loss %</th><th class="text-left p-1">Jitter</th></tr></thead>
            <tbody>
              <tr v-for="m in metrics" :key="m.id">
                <td class="p-1">{{ m.created_at }}</td>
                <td class="p-1">{{ m.latency_ms }}</td>
                <td class="p-1">{{ m.packet_loss_pct }}</td>
                <td class="p-1">{{ m.jitter_ms }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
