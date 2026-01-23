<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Tooltip from '@/Components/UI/Tooltip.vue';

const viewMode = ref('month');
const today = new Date();
const currentDate = ref(new Date(today.getFullYear(), today.getMonth(), 1));
const selectedDate = ref(today);
const events = ref([]);
const loading = ref(false);
const showQuickCreate = ref(false);
const quickCreateData = ref({ title: '', start_at: null, end_at: null, location: '', is_virtual: false, privacy: 'public', timezone: Intl.DateTimeFormat().resolvedOptions().timeZone });
const props = defineProps({
  categories: { type: Array, default: () => [] },
});
const filters = ref({
  include_invited: true,
  q: '',
  category_ids: JSON.parse(localStorage.getItem('calendar_category_ids') || '[]'),
  status: ''
});

const startOfWeek = (d) => {
  const date = new Date(d);
  const day = date.getDay();
  const diff = date.getDate() - day + (day === 0 ? -6 : 1);
  return new Date(date.setDate(diff));
};

const startEndRange = computed(() => {
  if (viewMode.value === 'month') {
    const start = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
    const end = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0);
    return { from: start, to: end };
  }
  if (viewMode.value === 'week') {
    const start = startOfWeek(selectedDate.value);
    const end = new Date(start);
    end.setDate(end.getDate() + 6);
    return { from: start, to: end };
  }
  const start = new Date(selectedDate.value);
  const end = new Date(selectedDate.value);
  end.setHours(23, 59, 59, 999);
  return { from: start, to: end };
});

const formatISO = (d) => new Date(d).toISOString();
const sameDay = (a, b) => a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();

const fetchEvents = async () => {
  loading.value = true;
  const params = {
    from: formatISO(startEndRange.value.from),
    to: formatISO(startEndRange.value.to),
    include_invited: filters.value.include_invited,
    q: filters.value.q || undefined,
    status: filters.value.status || undefined,
    category_ids: (filters.value.category_ids && filters.value.category_ids.length) ? filters.value.category_ids : undefined,
  };
  const res = await axios.get(route('calendar.events.index'), { params });
  events.value = res.data.data || res.data;
  loading.value = false;
};

const prev = () => {
  if (viewMode.value === 'month') {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
  } else if (viewMode.value === 'week') {
    const s = new Date(selectedDate.value);
    s.setDate(s.getDate() - 7);
    selectedDate.value = s;
  } else {
    const s = new Date(selectedDate.value);
    s.setDate(s.getDate() - 1);
    selectedDate.value = s;
  }
  fetchEvents();
};

const next = () => {
  if (viewMode.value === 'month') {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
  } else if (viewMode.value === 'week') {
    const s = new Date(selectedDate.value);
    s.setDate(s.getDate() + 7);
    selectedDate.value = s;
  } else {
    const s = new Date(selectedDate.value);
    s.setDate(s.getDate() + 1);
    selectedDate.value = s;
  }
  fetchEvents();
};

const pickDate = (d) => {
  selectedDate.value = d;
  if (viewMode.value !== 'month') {
    fetchEvents();
  }
};

const monthDays = computed(() => {
  const start = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
  const end = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0);
  const days = [];
  for (let d = 1; d <= end.getDate(); d++) {
    days.push(new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), d));
  }
  return days;
});

const eventsForDay = (d) => events.value.filter(e => {
  const sd = new Date(e.start_at);
  return sameDay(sd, d);
});

const weekHours = Array.from({ length: 24 }, (_, i) => i);
const dragging = ref(false);
const dragStart = ref(null);
const dragEnd = ref(null);
const dragDay = ref(null);

const onMouseDownHour = (dayIndex, hour) => {
  dragging.value = true;
  const start = new Date(startOfWeek(selectedDate.value));
  start.setDate(start.getDate() + dayIndex);
  start.setHours(hour, 0, 0, 0);
  dragStart.value = start;
  dragDay.value = dayIndex;
  dragEnd.value = start;
};

const onMouseMoveHour = (dayIndex, hour) => {
  if (!dragging.value || dayIndex !== dragDay.value) return;
  const end = new Date(startOfWeek(selectedDate.value));
  end.setDate(end.getDate() + dayIndex);
  end.setHours(hour + 1, 0, 0, 0);
  dragEnd.value = end;
};

const onMouseUpHour = () => {
  if (!dragging.value) return;
  dragging.value = false;
  showQuickCreate.value = true;
  quickCreateData.value.start_at = dragStart.value.toISOString();
  quickCreateData.value.end_at = dragEnd.value.toISOString();
};

const createQuickEvent = async () => {
  if (!quickCreateData.value.title || !quickCreateData.value.start_at || !quickCreateData.value.end_at) return;
  const payload = { ...quickCreateData.value };
  const res = await axios.post(route('calendar.events.store'), payload);
  showQuickCreate.value = false;
  quickCreateData.value = { title: '', start_at: null, end_at: null, location: '', is_virtual: false, privacy: 'public', timezone: Intl.DateTimeFormat().resolvedOptions().timeZone };
  await fetchEvents();
};

const changeView = (mode) => {
  viewMode.value = mode;
  fetchEvents();
};

const exportPrint = () => {
  window.print();
};

// Category multi-select helpers
const selectAllCategories = () => {
  filters.value.category_ids = props.categories.map(c => c.id);
  localStorage.setItem('calendar_category_ids', JSON.stringify(filters.value.category_ids));
  fetchEvents();
};
const clearAllCategories = () => {
  filters.value.category_ids = [];
  localStorage.setItem('calendar_category_ids', JSON.stringify([]));
  fetchEvents();
};
const toggleCategory = (id) => {
  const i = filters.value.category_ids.indexOf(id);
  if (i >= 0) filters.value.category_ids.splice(i, 1);
  else filters.value.category_ids.push(id);
  localStorage.setItem('calendar_category_ids', JSON.stringify(filters.value.category_ids));
  fetchEvents();
};

// Drag/resize existing events
const draggingEvent = ref(null);
const resizingEvent = ref(null);
const dragOriginY = ref(0);
const originalStart = ref(null);
const originalEnd = ref(null);

const startDragEvent = (ev) => {
  draggingEvent.value = ev;
  resizingEvent.value = null;
  dragOriginY.value = null;
  originalStart.value = new Date(ev.start_at);
  originalEnd.value = new Date(ev.end_at || ev.start_at);
  document.addEventListener('mousemove', onDocumentMouseMove);
  document.addEventListener('mouseup', onDocumentMouseUp);
};

const startResizeEvent = (ev, edge) => {
  resizingEvent.value = ev;
  draggingEvent.value = null;
  originalStart.value = new Date(ev.start_at);
  originalEnd.value = new Date(ev.end_at || ev.start_at);
  document.addEventListener('mousemove', onDocumentMouseMove);
  document.addEventListener('mouseup', onDocumentMouseUp);
};

const onDocumentMouseMove = (e) => {
  // Assume 32px per hour in week view; 40px per hour in day view
  const perHour = viewMode.value === 'day' ? 40 : 32;
  if (draggingEvent.value) {
    if (dragOriginY.value === null) dragOriginY.value = e.clientY;
    const dy = e.clientY - dragOriginY.value;
    const hoursDelta = Math.round(dy / perHour);
    const newStart = new Date(originalStart.value);
    newStart.setHours(originalStart.value.getHours() + hoursDelta, originalStart.value.getMinutes(), 0, 0);
    const newEnd = new Date(originalEnd.value);
    newEnd.setHours(originalEnd.value.getHours() + hoursDelta, originalEnd.value.getMinutes(), 0, 0);
    draggingEvent.value.start_at = newStart.toISOString();
    draggingEvent.value.end_at = newEnd.toISOString();
  } else if (resizingEvent.value) {
    if (dragOriginY.value === null) dragOriginY.value = e.clientY;
    const dy = e.clientY - dragOriginY.value;
    const hoursDelta = Math.round(dy / perHour);
    const newEnd = new Date(originalEnd.value);
    newEnd.setHours(originalEnd.value.getHours() + hoursDelta, originalEnd.value.getMinutes(), 0, 0);
    if (newEnd <= originalStart.value) return;
    resizingEvent.value.end_at = newEnd.toISOString();
  }
};

const onDocumentMouseUp = async () => {
  document.removeEventListener('mousemove', onDocumentMouseMove);
  document.removeEventListener('mouseup', onDocumentMouseUp);
  if (draggingEvent.value) {
    const payload = { start_at: draggingEvent.value.start_at, end_at: draggingEvent.value.end_at };
    await axios.put(route('calendar.events.update', draggingEvent.value.id), payload);
    draggingEvent.value = null;
    await fetchEvents();
  }
  if (resizingEvent.value) {
    const payload = { end_at: resizingEvent.value.end_at };
    await axios.put(route('calendar.events.update', resizingEvent.value.id), payload);
    resizingEvent.value = null;
    await fetchEvents();
  }
};

onMounted(() => {
  fetchEvents();
});
</script>

<template>
  <Head title="Calendar" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Calendar</h2>
        <div class="flex items-center gap-2">
          <button @click="exportPrint" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md">Print</button>
          <button
            @click="window.location.href = route('calendar.export', {
              from: formatISO(startEndRange.from),
              to: formatISO(startEndRange.to),
              view: viewMode,
              include_invited: filters.include_invited,
              category_ids: filters.category_ids,
              status: filters.status,
              format: 'a4',
              orientation: 'portrait',
            })"
            class="px-3 py-1.5 bg-blue-600 text-white rounded-md"
          >
            Export PDF
          </button>
        </div>
      </div>
    </template>

    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <button @click="prev" class="px-3 py-1.5 bg-white dark:bg-gray-800 border rounded-md">Prev</button>
            <button @click="next" class="px-3 py-1.5 bg-white dark:bg-gray-800 border rounded-md">Next</button>
            <span class="text-sm text-gray-700 dark:text-gray-300">
              {{ currentDate.toLocaleString('default', { month: 'long' }) }} {{ currentDate.getFullYear() }}
            </span>
          </div>
          <div class="flex items-center gap-2">
            <button @click="changeView('month')" :class="['px-3 py-1.5 rounded-md text-sm', viewMode==='month' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 border']">Month</button>
            <button @click="changeView('week')" :class="['px-3 py-1.5 rounded-md text-sm', viewMode==='week' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 border']">Week</button>
            <button @click="changeView('day')" :class="['px-3 py-1.5 rounded-md text-sm', viewMode==='day' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 border']">Day</button>
          </div>
        </div>

        <div v-if="loading" class="p-6 bg-white dark:bg-gray-800 rounded-lg border">
          <div class="animate-pulse h-6 w-1/3 bg-gray-200 dark:bg-gray-700 mb-4"></div>
          <div class="animate-pulse h-40 w-full bg-gray-200 dark:bg-gray-700"></div>
        </div>

        <div v-else>
          <div v-if="viewMode==='month'" class="grid grid-cols-7 gap-2">
            <div
              v-for="d in monthDays"
              :key="d.toISOString()"
              class="p-2 bg-white dark:bg-gray-800 rounded-lg border hover:shadow"
              :class="sameDay(d, today) ? 'ring-2 ring-blue-500' : ''"
              @click="pickDate(d)"
            >
              <div class="flex items-center justify-between">
                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ d.getDate() }}</div>
              </div>
              <div class="mt-2 space-y-1">
                <div
                  v-for="e in eventsForDay(d)"
                  :key="e.id"
                  class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 truncate"
                >
                  <Tooltip :label="e.title" position="top">
                    <span class="inline-block">{{ e.title }}</span>
                  </Tooltip>
                </div>
              </div>
            </div>
          </div>

          <div v-if="viewMode==='week'" class="grid grid-cols-7 gap-2">
            <div v-for="i in 7" :key="i" class="bg-white dark:bg-gray-800 rounded-lg border p-2">
              <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                {{ new Date(startOfWeek(selectedDate)).toLocaleDateString('default', { weekday: 'short' }) }}
              </div>
              <div class="relative">
                <div
                  v-for="e in events.filter(ev => {
                    const s = startOfWeek(selectedDate);
                    const d = new Date(s);
                    d.setDate(d.getDate() + (i-1));
                    return sameDay(new Date(ev.start_at), d);
                  })"
                  :key="e.id"
                  class="absolute left-1 right-1 bg-blue-500/70 text-white rounded px-2 text-xs"
                  :style="{
                    top: ((new Date(e.start_at).getHours()) * 32) + 'px',
                    height: Math.max(32, ((new Date(e.end_at||e.start_at).getHours() - new Date(e.start_at).getHours()) * 32)) + 'px'
                  }"
                  @mousedown.stop="startDragEvent(e)"
                >
                  <div class="flex justify-between items-center">
                    <span class="truncate">{{ e.title }}</span>
                    <span class="cursor-ns-resize" @mousedown.stop="startResizeEvent(e, 'end')">▮</span>
                  </div>
                </div>
                <div
                  v-for="h in weekHours"
                  :key="h"
                  class="h-8 border rounded flex items-center px-2 text-xs"
                  @mousedown="onMouseDownHour(i-1, h)"
                  @mousemove="onMouseMoveHour(i-1, h)"
                  @mouseup="onMouseUpHour"
                >
                  {{ h }}:00
                </div>
              </div>
            </div>
          </div>

          <div v-if="viewMode==='day'" class="bg-white dark:bg-gray-800 rounded-lg border p-2">
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              {{ selectedDate.toDateString() }}
            </div>
            <div class="relative">
              <div
                v-for="e in events.filter(ev => sameDay(new Date(ev.start_at), selectedDate))"
                :key="e.id"
                class="absolute left-1 right-1 bg-blue-500/70 text-white rounded px-2 text-xs"
                :style="{
                  top: ((new Date(e.start_at).getHours()) * 40) + 'px',
                  height: Math.max(40, ((new Date(e.end_at||e.start_at).getHours() - new Date(e.start_at).getHours()) * 40)) + 'px'
                }"
                @mousedown.stop="startDragEvent(e)"
              >
                <div class="flex justify-between items-center">
                  <span class="truncate">{{ e.title }}</span>
                  <span class="cursor-ns-resize" @mousedown.stop="startResizeEvent(e, 'end')">▮</span>
                </div>
              </div>
              <div
                v-for="h in weekHours"
                :key="h"
                class="h-10 border rounded flex items-center px-2 text-xs"
                @mousedown="onMouseDownHour(0, h)"
                @mousemove="onMouseMoveHour(0, h)"
                @mouseup="onMouseUpHour"
              >
                {{ h }}:00
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <input v-model="filters.q" type="text" placeholder="Search" class="px-3 py-2 border rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white" @input="fetchEvents" />
          <select v-model="filters.status" class="px-3 py-2 border rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white" @change="fetchEvents">
            <option value="">All Status</option>
            <option value="scheduled">Scheduled</option>
            <option value="cancelled">Cancelled</option>
            <option value="completed">Completed</option>
          </select>
          <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" v-model="filters.include_invited" @change="fetchEvents" />
            Include Invited
          </label>
          <div class="flex items-center gap-2">
            <div class="text-sm text-gray-700 dark:text-gray-300">Categories ({{ filters.category_ids.length }})</div>
            <button @click="selectAllCategories" class="px-2 py-1 text-xs bg-white dark:bg-gray-800 border rounded">Select All</button>
            <button @click="clearAllCategories" class="px-2 py-1 text-xs bg-white dark:bg-gray-800 border rounded">Clear All</button>
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="c in categories"
            :key="c.id"
            @click="toggleCategory(c.id)"
            :class="[
              'px-2 py-1 rounded border text-xs',
              filters.category_ids.includes(c.id) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'
            ]"
            :aria-pressed="filters.category_ids.includes(c.id)"
          >
            {{ c.display_icon }} {{ c.name }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showQuickCreate" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 w-full max-w-md border">
        <div class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Quick Create Event</div>
        <div class="space-y-3">
          <input v-model="quickCreateData.title" type="text" placeholder="Title" class="w-full px-3 py-2 border rounded" />
          <input v-model="quickCreateData.location" type="text" placeholder="Location" class="w-full px-3 py-2 border rounded" />
          <div class="grid grid-cols-2 gap-2">
            <input v-model="quickCreateData.start_at" type="datetime-local" class="px-3 py-2 border rounded" />
            <input v-model="quickCreateData.end_at" type="datetime-local" class="px-3 py-2 border rounded" />
          </div>
          <div class="flex items-center gap-2">
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" v-model="quickCreateData.is_virtual" />
              Virtual
            </label>
            <select v-model="quickCreateData.privacy" class="px-3 py-2 border rounded">
              <option value="public">Public</option>
              <option value="private">Private</option>
            </select>
          </div>
          <div class="flex justify-end gap-2">
            <button @click="showQuickCreate=false" class="px-3 py-1.5 bg-white border rounded">Cancel</button>
            <button @click="createQuickEvent" class="px-3 py-1.5 bg-blue-600 text-white rounded">Create</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
