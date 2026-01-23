<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';

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
      <Link
        v-for="i in items"
        :key="i.id"
        :href="route('posts.show', i.id)"
        class="block p-3 rounded-md bg-gray-50 hover:bg-gray-100 transition border border-gray-200 dark:bg-gray-700/40 dark:hover:bg-gray-700 dark:border-gray-700"
      >
        <div class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">{{ i.title }}</div>
        <div class="text-xs text-gray-600 dark:text-gray-300 mt-1 leading-snug line-clamp-2">{{ i.excerpt }}</div>
      </Link>
    </div>
  </div>
</template>
