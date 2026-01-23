<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  profile: { type: Object, default: () => ({ points: {}, badges: [], levels: [] }) },
  leaderboard: { type: Object, default: () => ({ daily: [], weekly: [], monthly: [] }) },
});

const currentLevel = props.levels?.[0] || null;
const totalPoints = props.profile?.points?.total || 0;
</script>

<template>
  <Head title="Gamification" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Gamification</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-7xl space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Points</div>
            <div class="text-2xl font-bold">{{ totalPoints }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-300">
              Daily: {{ profile.points.daily }} • Weekly: {{ profile.points.weekly }} • Monthly: {{ profile.points.monthly }}
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Current Level</div>
            <div v-if="profile.levels.length" class="text-lg font-bold">{{ profile.levels[0].name }}</div>
            <div v-else class="text-sm text-gray-600 dark:text-gray-300">Belum ada level</div>
            <div class="mt-2 h-2 bg-gray-200 dark:bg-gray-700 rounded">
              <div class="h-2 bg-blue-600 rounded" :style="{ width: Math.min(100, Math.round((totalPoints / ((profile.levels[0]?.min_points || 1) * 2)) * 100)) + '%' }"></div>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Badges</div>
            <div class="flex flex-wrap gap-2">
              <div v-for="b in profile.badges" :key="b.id" class="px-2 py-1 rounded border text-xs">
                <span class="mr-1">{{ b.icon || '🏅' }}</span>{{ b.name }}
              </div>
              <div v-if="profile.badges.length === 0" class="text-xs text-gray-600 dark:text-gray-300">Belum ada badge</div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Leaderboard (Daily)</div>
            <ul class="space-y-1">
              <li v-for="row in leaderboard.daily" :key="row.user_id" class="flex items-center justify-between text-sm">
                <span>{{ row.name || row.user_id }}</span>
                <span class="font-semibold">{{ row.total }}</span>
              </li>
            </ul>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Leaderboard (Weekly)</div>
            <ul class="space-y-1">
              <li v-for="row in leaderboard.weekly" :key="row.user_id" class="flex items-center justify-between text-sm">
                <span>{{ row.name || row.user_id }}</span>
                <span class="font-semibold">{{ row.total }}</span>
              </li>
            </ul>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
            <div class="text-sm font-semibold mb-2">Leaderboard (Monthly)</div>
            <ul class="space-y-1">
              <li v-for="row in leaderboard.monthly" :key="row.user_id" class="flex items-center justify-between text-sm">
                <span>{{ row.name || row.user_id }}</span>
                <span class="font-semibold">{{ row.total }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
