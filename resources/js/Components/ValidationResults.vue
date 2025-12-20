<script setup>
defineProps({
    stats: Object,
});
</script>

<template>
    <div v-if="stats" class="border rounded-lg p-6 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Hasil Validasi Komunitas
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ stats.total }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Total Validasi
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ stats.layak }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Layak
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                    {{ stats.tidak_layak }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Tidak Layak
                </div>
            </div>
        </div>

        <!-- Approval Meter -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tingkat Persetujuan
                </span>
                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                    {{ stats.approval_percentage }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                <div
                    class="bg-indigo-600 h-4 rounded-full transition-all duration-500"
                    :style="{ width: stats.approval_percentage + '%' }"
                ></div>
            </div>
        </div>

        <!-- Average Estimates -->
        <div v-if="stats.avg_capital || stats.avg_bep" class="grid grid-cols-2 gap-4 mb-6">
            <div v-if="stats.avg_capital" class="bg-white dark:bg-gray-800 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                    Rata-rata Estimasi Modal
                </div>
                <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Rp {{ new Intl.NumberFormat('id-ID').format(stats.avg_capital) }}
                </div>
            </div>

            <div v-if="stats.avg_bep" class="bg-white dark:bg-gray-800 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                    Rata-rata Estimasi BEP
                </div>
                <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    {{ stats.avg_bep }} Bulan
                </div>
            </div>
        </div>

        <!-- Common Risks -->
        <div v-if="stats.common_risks && stats.common_risks.length > 0" class="mt-6">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                Risiko yang Sering Disebutkan
            </h4>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="(risk, index) in stats.common_risks"
                    :key="index"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                >
                    {{ risk }}
                </span>
            </div>
        </div>
    </div>
</template>

