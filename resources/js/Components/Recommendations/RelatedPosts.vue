<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({ postId: { type: String, required: true } });
const items = ref([]);
const loading = ref(false);

const fetchData = async () => {
  loading.value = true;
  const res = await axios.get(route('recommendations.related', props.postId), { params: { limit: 6 } });
  items.value = res.data.data || [];
  loading.value = false;
};

onMounted(fetchData);
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
    <div class="text-sm font-semibold mb-2">Related posts</div>
    <div v-if="loading" class="text-xs text-gray-600 dark:text-gray-300">Loading...</div>
    <div v-else class="space-y-2">
      <div v-for="i in items" :key="i.id" class="p-2 border rounded">
        <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">Post #{{ i.id }}</div>
        <div class="text-sm font-semibold">{{ i.title }}</div>
        <div class="text-xs text-gray-600 dark:text-gray-300">{{ i.excerpt }}</div>
      </div>
    </div>
  </div>
</template>
