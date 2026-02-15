<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  categories: Array,
  defaults: Object,
});

const form = useForm({
  title: '',
  description: '',
  url: '',
  source: props.defaults?.source || 'Noteds Editorial',
  image: '',
  category: '',
  author: props.defaults?.author || '',
  published_at: new Date(props.defaults?.published_at || new Date()).toISOString().slice(0, 16),
  language: props.defaults?.language || 'id',
  country: '',
});

const submitting = ref(false);
const submit = async () => {
  submitting.value = true;
  form.post(route('admin.articles.store'), {
    onFinish: () => { submitting.value = false; },
    onSuccess: () => {
      router.get(route('explorer.index'));
    },
  });
};
</script>

<template>
  <Head title="Create Article" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Create Article
        </h2>
        <Link
          :href="route('explorer.index')"
          class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
        >
          Back to Explorer
        </Link>
      </div>
    </template>

    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-3xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
            <input v-model="form.title" type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="Article title" />
            <div v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea v-model="form.description" rows="4" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="Short description"></textarea>
            <div v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">External URL (optional)</label>
              <input v-model="form.url" type="url" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="https://example.com/news" />
              <div v-if="form.errors.url" class="text-sm text-red-600 mt-1">{{ form.errors.url }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Source</label>
              <input v-model="form.source" type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="Noteds Editorial" />
              <div v-if="form.errors.source" class="text-sm text-red-600 mt-1">{{ form.errors.source }}</div>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image URL</label>
              <input v-model="form.image" type="url" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="https://example.com/image.jpg" />
              <div v-if="form.errors.image" class="text-sm text-red-600 mt-1">{{ form.errors.image }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
              <select v-model="form.category" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Select category</option>
                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
              </select>
              <div v-if="form.errors.category" class="text-sm text-red-600 mt-1">{{ form.errors.category }}</div>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Author</label>
              <input v-model="form.author" type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
              <div v-if="form.errors.author" class="text-sm text-red-600 mt-1">{{ form.errors.author }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Published At</label>
              <input v-model="form.published_at" type="datetime-local" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
              <div v-if="form.errors.published_at" class="text-sm text-red-600 mt-1">{{ form.errors.published_at }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Language</label>
              <input v-model="form.language" type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="id/en" />
              <div v-if="form.errors.language" class="text-sm text-red-600 mt-1">{{ form.errors.language }}</div>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
              <input v-model="form.country" type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="ID/US/..." />
              <div v-if="form.errors.country" class="text-sm text-red-600 mt-1">{{ form.errors.country }}</div>
            </div>
          </div>
          <div class="pt-2">
            <button
              :disabled="submitting"
              @click="submit"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
            >
              {{ submitting ? 'Saving...' : 'Create Article' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
