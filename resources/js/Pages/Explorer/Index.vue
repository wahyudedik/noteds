<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ArticleCard from '@/Components/Explorer/ArticleCard.vue';
import SearchBar from '@/Components/Explorer/SearchBar.vue';
import LiveScoreWidget from '@/Components/Explorer/LiveScoreWidget.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SocialShareButtons from '@/Components/Social/SocialShareButtons.vue';
import { sharePlacement } from '@/config/sharePlacement';
import YouMightLike from '@/Components/Recommendations/YouMightLike.vue';
import TrendingContent from '@/Components/Recommendations/TrendingContent.vue';
import { ref } from 'vue';

const props = defineProps({
    articles: Object,
    filters: Object,
    categories: Array,
});
const period = ref('week');

const changeCategory = (category) => {
    router.get(route('explorer.index'), { 
        category: category || null,
        search: props.filters?.search || null,
    }, {
        preserveState: true,
        preserveScroll: false,
    });
};

const btoaSafe = (s) => {
    try {
        if (typeof window !== 'undefined' && typeof window.btoa === 'function') {
            return window.btoa(String(s));
        }
        // Node fallback (SSR)
        if (typeof Buffer !== 'undefined') {
            return Buffer.from(String(s)).toString('base64');
        }
    } catch {}
    return String(s);
};
</script>

<template>
    <Head title="Explorer - Artikel Bisnis" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Explorer
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Search Bar -->
                <div class="mb-6">
                    <SearchBar :filters="filters" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Category Filter Sidebar -->
                    <aside class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                                Kategori
                            </h3>
                            <div class="space-y-2">
                                <button
                                    @click="changeCategory(null)"
                                    :class="[
                                        'w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                                        !filters?.category
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-medium'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                    ]"
                                >
                                    Semua Kategori
                                </button>
                                <button
                                    v-for="category in categories"
                                    :key="category"
                                    @click="changeCategory(category)"
                                    :class="[
                                        'w-full text-left px-3 py-2 rounded-lg text-sm transition-colors capitalize',
                                        filters?.category === category
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-medium'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                    ]"
                                >
                                    {{ category }}
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 space-y-4">
                            <LiveScoreWidget />
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Trending</div>
                                    <div class="flex gap-1">
                                        <button
                                          @click="period = 'today'"
                                          :class="['px-2 py-1 text-xs rounded', period === 'today' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']"
                                        >Today</button>
                                        <button
                                          @click="period = 'week'"
                                          :class="['px-2 py-1 text-xs rounded', period === 'week' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']"
                                        >7d</button>
                                        <button
                                          @click="period = 'month'"
                                          :class="['px-2 py-1 text-xs rounded', period === 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300']"
                                        >30d</button>
                                    </div>
                                </div>
                                <TrendingContent :period="period" />
                            </div>
                            <YouMightLike />
                            <div v-if="sharePlacement.explorer.enabled && sharePlacement.explorer.position === 'sidebar'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-3">
                                <div class="text-sm font-semibold mb-2">Bagikan Explorer</div>
                                <SocialShareButtons
                                    :url="route('explorer.index')"
                                    title="Explorer - Noteds"
                                    description="Temukan artikel bisnis pilihan."
                                    :hashtags="[]"
                                    share-type="external"
                                    :share-id="btoaSafe(route('explorer.index'))"
                                />
                            </div>
                        </div>
                    </aside>

                    <!-- Articles Grid -->
                    <div class="lg:col-span-3">
                        <div v-if="sharePlacement.explorer.enabled && sharePlacement.explorer.position === 'grid_top'" class="mb-4">
                            <SocialShareButtons
                                :url="route('explorer.index')"
                                title="Explorer - Noteds"
                                description="Temukan artikel bisnis pilihan."
                                :hashtags="[]"
                                share-type="external"
                                :share-id="btoaSafe(route('explorer.index'))"
                            />
                        </div>
                        <!-- Articles Count -->
                        <div class="mb-4 flex justify-between items-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Menampilkan {{ articles?.total || 0 }} artikel
                            </p>
                        </div>

                        <!-- Articles Grid -->
                        <div v-if="articles?.data && articles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <ArticleCard
                                v-for="article in articles.data"
                                :key="article.id"
                                :article="article"
                            />
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                Tidak ada artikel ditemukan
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Coba cari dengan kata kunci lain atau pilih kategori berbeda.
                            </p>
                            <p v-if="!filters?.search && !filters?.category" class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                Artikel akan otomatis di-sync dari berbagai sumber bisnis setiap hari.
                            </p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="articles?.links && articles.data.length > 0" class="mt-8">
                            <div class="flex justify-center">
                                <nav class="flex gap-1">
                                    <Link
                                        v-for="(link, index) in articles.links"
                                        :key="index"
                                        :href="link.url || '#'"
                                        :class="[
                                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : link.url
                                                    ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-600 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                        :preserve-state="true"
                                        :preserve-scroll="false"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

