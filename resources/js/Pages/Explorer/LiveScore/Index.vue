<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    matches: {
        type: Array,
        default: () => []
    }
});

const filter = ref('ALL'); // ALL, LIVE, FINISHED, SCHEDULED

const filteredMatches = computed(() => {
    let result = props.matches;
    
    if (filter.value === 'LIVE') {
        result = result.filter(m => m.status === 'IN_PLAY' || m.status === 'PAUSED');
    } else if (filter.value === 'FINISHED') {
        result = result.filter(m => m.status === 'FINISHED');
    } else if (filter.value === 'SCHEDULED') {
        result = result.filter(m => m.status === 'TIMED' || m.status === 'SCHEDULED');
    }
    
    // Group by competition
    const grouped = {};
    result.forEach(match => {
        const league = match.competition.name;
        if (!grouped[league]) {
            grouped[league] = [];
        }
        grouped[league].push(match);
    });
    
    return grouped;
});

const getStatusClass = (status) => {
    if (status === 'IN_PLAY' || status === 'PAUSED') return 'text-red-600 font-bold animate-pulse';
    if (status === 'FINISHED') return 'text-gray-900 dark:text-gray-100 font-semibold';
    return 'text-gray-500';
};

const getStatusLabel = (match) => {
    if (match.status === 'IN_PLAY' || match.status === 'PAUSED') {
        return match.minute ? `${match.minute}'` : 'LIVE';
    }
    if (match.status === 'FINISHED') return 'FT';
    if (match.status === 'TIMED' || match.status === 'SCHEDULED') {
        return new Date(match.utcDate).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return match.status;
};

const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Live Scores - Explorer" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('explorer.index')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Live Scores & Jadwal Pertandingan
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                    <button 
                        @click="filter = 'ALL'"
                        :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors', 
                            filter === 'ALL' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700']"
                    >
                        Semua
                    </button>
                    <button 
                        @click="filter = 'LIVE'"
                        :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors flex items-center gap-2', 
                            filter === 'LIVE' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700']"
                    >
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        Live
                    </button>
                    <button 
                        @click="filter = 'SCHEDULED'"
                        :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors', 
                            filter === 'SCHEDULED' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700']"
                    >
                        Jadwal
                    </button>
                    <button 
                        @click="filter = 'FINISHED'"
                        :class="['px-4 py-2 rounded-full text-sm font-medium transition-colors', 
                            filter === 'FINISHED' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700']"
                    >
                        Selesai
                    </button>
                </div>

                <!-- Matches List -->
                <div v-if="Object.keys(filteredMatches).length > 0" class="space-y-6">
                    <div v-for="(leagueMatches, leagueName) in filteredMatches" :key="leagueName" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <span v-if="leagueMatches[0].competition.emblem" class="w-6 h-6 object-contain">
                                    <img :src="leagueMatches[0].competition.emblem" :alt="leagueName" class="w-full h-full" />
                                </span>
                                {{ leagueName }}
                            </h3>
                        </div>
                        
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div v-for="match in leagueMatches" :key="match.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                                    <!-- Status / Time -->
                                    <div class="w-full md:w-24 text-center md:text-left shrink-0">
                                        <span :class="['inline-block px-2 py-1 text-xs rounded font-bold', 
                                            match.status === 'IN_PLAY' || match.status === 'PAUSED' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200' : 
                                            match.status === 'FINISHED' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' :
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200'
                                        ]">
                                            {{ getStatusLabel(match) }}
                                        </span>
                                    </div>

                                    <!-- Teams & Score -->
                                    <div class="flex-1 w-full grid grid-cols-3 items-center gap-4">
                                        <!-- Home Team -->
                                        <div class="text-right flex items-center justify-end gap-3">
                                            <span class="font-medium text-gray-900 dark:text-white text-sm md:text-base">{{ match.homeTeam.shortName || match.homeTeam.name }}</span>
                                            <img v-if="match.homeTeam.crest" :src="match.homeTeam.crest" :alt="match.homeTeam.name" class="w-6 h-6 md:w-8 md:h-8 object-contain" />
                                        </div>

                                        <!-- Score -->
                                        <div class="text-center font-bold text-xl text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 py-1 px-3 rounded-lg mx-auto min-w-[80px]">
                                            <span v-if="match.status !== 'SCHEDULED' && match.status !== 'TIMED'">
                                                {{ match.score.fullTime.home ?? 0 }} - {{ match.score.fullTime.away ?? 0 }}
                                            </span>
                                            <span v-else class="text-base font-normal text-gray-500">
                                                vs
                                            </span>
                                        </div>

                                        <!-- Away Team -->
                                        <div class="text-left flex items-center justify-start gap-3">
                                            <img v-if="match.awayTeam.crest" :src="match.awayTeam.crest" :alt="match.awayTeam.name" class="w-6 h-6 md:w-8 md:h-8 object-contain" />
                                            <span class="font-medium text-gray-900 dark:text-white text-sm md:text-base">{{ match.awayTeam.shortName || match.awayTeam.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Tidak ada pertandingan</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tidak ada pertandingan untuk kategori ini hari ini.</p>
                </div>
                
                <div class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
                    Data provided by football-data.org. Times are in your local timezone.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>