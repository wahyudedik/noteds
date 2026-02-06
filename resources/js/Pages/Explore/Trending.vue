<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PostCard from '@/Components/PostCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const props = defineProps({
  period: { type: String, default: 'today' },
  posts: { type: Array, default: () => [] },
  hashtags: { type: Array, default: () => [] },
  topics: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
});

const tabs = [
  { id: 'posts', label: 'Trending Posts' },
  { id: 'hashtags', label: 'Hashtags' },
  { id: 'topics', label: 'Topics' },
  { id: 'users', label: 'Users' },
];
const activeTab = ref('posts');
const periods = [
  { id: 'today', label: 'Today' },
  { id: 'week', label: 'Week' },
  { id: 'month', label: 'Month' },
];
const changePeriod = (p) => {
  router.get(route('explore.trending'), { period: p }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
  <Head title="Trending" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Trending</h2>
        <div class="flex items-center gap-2">
          <button
            v-for="p in periods"
            :key="p.id"
            @click="changePeriod(p.id)"
            :class="[
              'px-3 py-1.5 rounded-md text-sm',
              props.period === p.id ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300'
            ]"
          >
            {{ p.label }}
          </button>
        </div>
      </div>
    </template>

    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-6xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="-mb-px flex space-x-4 px-4 sm:px-6 min-w-max">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'group inline-flex items-center py-3 px-2 border-b-2 font-medium text-sm transition whitespace-nowrap flex-shrink-0',
                  activeTab === tab.id ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                ]"
              >
                <span>{{ tab.label }}</span>
              </button>
            </nav>
          </div>

          <div class="p-6">
            <div v-if="activeTab === 'posts'">
              <div class="grid md:grid-cols-2 gap-4">
                <PostCard
                  v-for="post in props.posts"
                  :key="post.id"
                  :post="post"
                  :user-vote="null"
                  :is-bookmarked="false"
                  :is-reposted="false"
                />
              </div>
              <p v-if="props.posts.length === 0" class="text-sm text-gray-500 dark:text-gray-400">Tidak ada post trending untuk periode ini.</p>
            </div>

            <div v-else-if="activeTab === 'hashtags'">
              <div class="flex flex-wrap gap-2">
                <Link
                  v-for="h in props.hashtags"
                  :key="h.id"
                  :href="route('posts.index', { hashtag: h.slug })"
                  class="inline-flex items-center px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 text-sm"
                  :title="`#${h.name} • ${h.posts_count} posts`"
                >
                  #{{ h.name }}
                  <span class="ml-2 text-xs text-gray-500">({{ h.posts_count }})</span>
                </Link>
              </div>
              <p v-if="props.hashtags.length === 0" class="text-sm text-gray-500 dark:text-gray-400 mt-2">Tidak ada hashtag trending untuk periode ini.</p>
            </div>

            <div v-else-if="activeTab === 'topics'">
              <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="t in props.topics"
                  :key="t.id"
                  class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                >
                  <div class="font-semibold text-gray-900 dark:text-gray-100">{{ t.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ t.count }} posts</div>
                </div>
              </div>
              <p v-if="!props.topics || props.topics.length === 0" class="text-sm text-gray-500 dark:text-gray-400 mt-2">Tidak ada topic trending untuk periode ini.</p>
            </div>

            <div v-else-if="activeTab === 'users'">
              <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                  v-for="u in props.users"
                  :key="u.id"
                  :href="route('profile.show', u.id)"
                  class="flex items-center gap-3 p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:shadow transition"
                >
                  <div class="w-10 h-10 rounded-full bg-indigo-500 text-white flex items-center justify-center overflow-hidden">
                    <img v-if="u.avatar_url" :src="u.avatar_url" :alt="u.name" class="w-full h-full object-cover" />
                    <span v-else>{{ (u.business_name || u.name).charAt(0).toUpperCase() }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                      {{ u.business_name || u.name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                      {{ u.posts_count }} posts {{ props.period === 'today' ? 'today' : props.period === 'week' ? 'this week' : 'this month' }}
                    </div>
                  </div>
                </Link>
              </div>
              <p v-if="props.users.length === 0" class="text-sm text-gray-500 dark:text-gray-400 mt-2">Tidak ada user trending untuk periode ini.</p>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
