<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    order: Object,
    seller: Object,
});

const form = useForm({
    rating: 0,
    comment: '',
});

const selectedRating = ref(0);
const hoverRating = ref(0);

const submit = () => {
    form.post(route('marketplace.seller-rating.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('marketplace.orders.show', props.order.id));
        },
    });
};

const setRating = (rating) => {
    selectedRating.value = rating;
    form.rating = rating;
};
</script>

<template>
    <Head title="Rate Seller" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Rate Seller
                </h2>
                <Link
                    :href="route('marketplace.orders.show', order?.id)"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Back to Order
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-3xl space-y-6">
                <!-- Order Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Order Details
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Order #</p>
                            <p class="text-gray-900 dark:text-white">{{ order?.order_number || order?.id }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Seller</p>
                            <div class="flex items-center gap-3 mt-1">
                                <div v-if="seller?.avatar_url || seller?.avatar" class="h-10 w-10 rounded-full overflow-hidden">
                                    <img
                                        :src="seller?.avatar_url || seller?.avatar"
                                        :alt="seller?.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <p class="text-gray-900 dark:text-white">{{ seller?.name }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Order Date</p>
                            <p class="text-gray-900 dark:text-white">{{ new Date(order?.created_at).toLocaleDateString() }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Total</p>
                            <p class="text-gray-900 dark:text-white">Rp {{ new Intl.NumberFormat('id-ID').format(order?.total || 0) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Rating Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Rate Your Experience
                    </h3>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Overall Rating *
                            </label>
                            <div class="flex items-center gap-2">
                                <button
                                    v-for="i in 5"
                                    :key="i"
                                    type="button"
                                    @click="setRating(i)"
                                    @mouseenter="hoverRating = i"
                                    @mouseleave="hoverRating = 0"
                                    class="text-4xl transition-colors"
                                    :class="
                                        (hoverRating || selectedRating) >= i
                                            ? 'text-yellow-400'
                                            : 'text-gray-300 dark:text-gray-600'
                                    "
                                >
                                    ★
                                </button>
                            </div>
                            <input
                                v-model="form.rating"
                                type="hidden"
                                required
                            />
                            <p v-if="selectedRating === 0" class="text-sm text-red-500 dark:text-red-400 mt-2">
                                Please select a rating
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Comment (optional)
                            </label>
                            <textarea
                                v-model="form.comment"
                                rows="5"
                                maxlength="1000"
                                placeholder="Share your experience with this seller..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ form.comment?.length || 0 }}/1000 characters
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                :href="route('marketplace.orders.show', order?.id)"
                                class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing || selectedRating === 0"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                Submit Rating
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

