<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(false);

const fetchData = async () => {
  loading.value = true;
  const res = await axios.get(route('recommendations.similar_users'), { params: { limit: 6 } });
  items.value = res.data.data || [];
  loading.value = false;
};

onMounted(fetchData);
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
    <div class="text-sm font-semibold mb-2">Similar users</div>
    <div v-if="loading" class="text-xs text-gray-600 dark:text-gray-300">Loading...</div>
    <div v-else class="space-y-2">
      <div v-for="u in items" :key="u.id" class="p-2 border rounded flex items-center gap-2">
        <img v-if="u.avatar_url" :src="u.avatar_url" class="w-6 h-6 rounded-full" />
        <div class="text-sm">{{ u.name }}</div>
      </div>
    </div>
  </div>
</template>
