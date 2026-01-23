<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const viewMode = ref('month');
const today = new Date();
const currentDate = ref(new Date(today.getFullYear(), today.getMonth(), 1));
const selectedDate = ref(today);
const items = ref([]);
const loading = ref(false);
const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

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

const fetchItems = async () => {
  loading.value = true;
  const res = await axios.get(route('scheduling.calendar'), {
    params: {
      from: formatISO(startEndRange.value.from),
      to: formatISO(startEndRange.value.to),
      timezone,
    }
  });
  items.value = res.data.data || res.data;
  loading.value = false;
};

const monthDays = computed(() => {
  const end = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0);
  const days = [];
  for (let d = 1; d <= end.getDate(); d++) {
    days.push(new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), d));
  }
  return days;
});

const itemsForDay = (d) => items.value.filter(e => sameDay(new Date(e.scheduled_at), d));

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
  fetchItems();
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
  fetchItems();
};

const startDragItem = (item) => {
  dragItem.value = item;
};
const dragItem = ref(null);
const onDropDay = async (d) => {
  if (!dragItem.value) return;
  const scheduled = new Date(d);
  scheduled.setHours(10, 0, 0, 0);
  if (dragItem.value.type === 'post') {
    await axios.put(route('scheduling.posts.update', dragItem.value.id), {
      scheduled_at: scheduled.toISOString(),
      timezone,
    });
  }
  dragItem.value = null;
  await fetchItems();
};

const inlineEdit = async (item) => {
  const current = new Date(item.scheduled_at);
  const iso = prompt('Edit scheduled time (ISO 8601)', current.toISOString());
  if (!iso) return;
  if (item.type === 'post') {
    await axios.put(route('scheduling.posts.update', item.id), { scheduled_at: iso, timezone });
    await fetchItems();
  }
};

onMounted(() => {
  fetchItems();
});
</script>

<template>
  <Head title="Scheduling Calendar" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Scheduled Content</h2>
        <div class="flex items-center gap-2">
          <button @click="viewMode='month'; fetchItems()" :class="['px-3 py-1.5 rounded-md text-sm', viewMode==='month' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 border']">Month</button>
          <button @click="viewMode='week'; fetchItems()" :class="['px-3 py-1.5 rounded-md text-sm', viewMode==='week' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 border']">Week</button>
          <button @click="viewMode='day'; fetchItems()" :class="['px-3 py-1.5 rounded-md text-sm', viewMode==='day' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 border']">Day</button>
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
        </div>

        <div v-if="loading" class="p-6 bg-white dark:bg-gray-800 rounded-lg border">
          <div class="animate-pulse h-6 w-1/3 bg-gray-200 dark:bg-gray-700 mb-4"></div>
          <div class="animate-pulse h-40 w-full bg-gray-200 dark:bg-gray-700"></div>
        </div>

        <div v-else class="grid grid-cols-7 gap-2">
          <div
            v-for="d in monthDays"
            :key="d.toISOString()"
            class="p-2 bg-white dark:bg-gray-800 rounded-lg border hover:shadow min-h-32"
            :class="sameDay(d, today) ? 'ring-2 ring-blue-500' : ''"
            @dragover.prevent
            @drop="onDropDay(d)"
          >
            <div class="flex items-center justify-between">
              <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ d.getDate() }}</div>
            </div>
            <div class="mt-2 space-y-1">
              <div
                v-for="e in itemsForDay(d)"
                :key="e.type + ':' + e.id"
                class="text-xs px-2 py-1 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 truncate cursor-move"
                draggable="true"
                @dragstart="startDragItem(e)"
                @dblclick="inlineEdit(e)"
                :title="e.title"
              >
                <span class="uppercase">{{ e.type }}</span> • {{ e.title }}
                <span v-if="e.conflict" class="ml-1 text-red-600">• conflict</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
