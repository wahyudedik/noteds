<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const matches = ref([]);
const loading = ref(true);

const fetchLiveScores = async () => {
    try {
        const response = await axios.get(route('explorer.livescore.data'));
        matches.value = response.data.map(m => ({
            id: m.id,
            home_team: m.homeTeam.shortName || m.homeTeam.name,
            away_team: m.awayTeam.shortName || m.awayTeam.name,
            home_score: m.score.fullTime.home ?? 0,
            away_score: m.score.fullTime.away ?? 0,
            status: m.status === 'IN_PLAY' || m.status === 'PAUSED' ? 'LIVE' : m.status,
            minute: m.minute ?? null,
            league: m.competition.name
        }));
    } catch (error) {
        console.error('Failed to fetch live scores', error);
    } finally {
        loading.value = false;
    }
};

let interval;
onMounted(() => {
    fetchLiveScores();
    // Poll every 60 seconds to respect API rate limits and server cache
    interval = setInterval(fetchLiveScores, 60000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                  <span v-if="matches.length > 0" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3" :class="matches.length > 0 ? 'bg-red-500' : 'bg-gray-400'"></span>
                </span>
                Live Scores
            </h3>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                Football
            </span>
        </div>

        <div v-if="loading" class="flex justify-center py-4">
            <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <div v-else-if="matches.length === 0" class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm">
            <p>Tidak ada pertandingan live saat ini.</p>
        </div>

        <div v-else class="space-y-4">
            <div v-for="match in matches" :key="match.id" class="border-b border-gray-100 dark:border-gray-700 last:border-0 pb-3 last:pb-0">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1 flex justify-between">
                    <span class="truncate max-w-[120px]">{{ match.league }}</span>
                    <span :class="{'text-red-500 font-bold': match.status === 'LIVE', 'text-gray-500': match.status !== 'LIVE'}">
                        {{ match.status === 'LIVE' && match.minute ? match.minute + "'" : match.status }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-200 truncate max-w-[140px]" :title="match.home_team">{{ match.home_team }}</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ match.home_score }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-200 truncate max-w-[140px]" :title="match.away_team">{{ match.away_team }}</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ match.away_score }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <Link :href="route('explorer.livescore')" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                Lihat semua pertandingan →
            </Link>
        </div>
    </div>
</template>
