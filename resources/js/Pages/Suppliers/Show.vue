<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Textarea from '@/Components/Textarea.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    supplier: Object,
    reviews: Object,
    products: Array,
    stats: Object,
    auth: Object,
});

const reviewForm = useForm({
    rating: 5,
    review: '',
    tags: [],
    is_verified_purchase: false,
});

const showReviewForm = ref(false);
const selectedTags = ref([]);

const availableTags = ['harga_murah', 'kualitas_bagus', 'pelayanan_cepat', 'ready_stock', 'terpercaya'];

const toggleTag = (tag) => {
    const index = selectedTags.value.indexOf(tag);
    if (index > -1) {
        selectedTags.value.splice(index, 1);
    } else {
        selectedTags.value.push(tag);
    }
    reviewForm.tags = selectedTags.value;
};

const submitReview = () => {
    reviewForm.post(route('suppliers.reviews.store', props.supplier.id), {
        preserveScroll: true,
        onSuccess: () => {
            reviewForm.reset();
            selectedTags.value = [];
            showReviewForm.value = false;
        },
    });
};

const formatRating = (rating) => {
    return rating ? rating.toFixed(1) : '0.0';
};
</script>

<template>
    <Head :title="supplier.supplier_name + ' - Supplier'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ supplier.supplier_name }}
                </h2>
                <Link
                    v-if="auth?.user?.id === supplier.user_id"
                    :href="route('suppliers.edit', supplier.id)"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                >
                    Edit Supplier
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Supplier Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                {{ supplier.supplier_name }}
                            </h1>
                            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="flex items-center gap-1">
                                    <span class="text-yellow-500">⭐</span>
                                    <span class="font-medium">{{ formatRating(supplier.rating) }}</span>
                                    <span>({{ stats.total_reviews }} review)</span>
                                </span>
                                <span v-if="supplier.location">{{ supplier.location }}</span>
                                <span v-if="supplier.is_verified" class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded text-xs font-medium">
                                    ✓ Terverifikasi
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        {{ supplier.description }}
                    </p>

                    <div v-if="supplier.specialties && supplier.specialties.length > 0" class="flex flex-wrap gap-2 mb-4">
                        <span
                            v-for="specialty in supplier.specialties"
                            :key="specialty"
                            class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm"
                        >
                            {{ specialty }}
                        </span>
                    </div>

                    <!-- Contact Info -->
                    <div v-if="supplier.contact_info" class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Informasi Kontak</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                            <div v-if="supplier.contact_info.phone">
                                <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ supplier.contact_info.phone }}</span>
                            </div>
                            <div v-if="supplier.contact_info.email">
                                <span class="text-gray-600 dark:text-gray-400">Email:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ supplier.contact_info.email }}</span>
                            </div>
                            <div v-if="supplier.contact_info.whatsapp">
                                <span class="text-gray-600 dark:text-gray-400">WhatsApp:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ supplier.contact_info.whatsapp }}</span>
                            </div>
                            <div v-if="supplier.contact_info.address">
                                <span class="text-gray-600 dark:text-gray-400">Alamat:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ supplier.contact_info.address }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_reviews }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Review</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_orders }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Order</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_views }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Dilihat</div>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div v-if="products && products.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Produk dari Supplier Ini</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Link
                            v-for="product in products"
                            :key="product.id"
                            :href="route('marketplace.product.show', product.id)"
                            class="border rounded-lg p-4 hover:shadow-md transition"
                        >
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ product.name }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ product.description }}</p>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                                Rp {{ Number(product.price).toLocaleString('id-ID') }}
                            </p>
                        </Link>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Review ({{ stats.total_reviews }})
                        </h3>
                        <button
                            v-if="auth?.user && auth.user.id !== supplier.user_id"
                            @click="showReviewForm = !showReviewForm"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                        >
                            Tulis Review
                        </button>
                    </div>

                    <!-- Review Form -->
                    <div v-if="showReviewForm" class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <form @submit.prevent="submitReview">
                            <div class="space-y-4">
                                <div>
                                    <InputLabel value="Rating" />
                                    <div class="flex gap-2 mt-2">
                                        <button
                                            v-for="i in 5"
                                            :key="i"
                                            type="button"
                                            @click="reviewForm.rating = i"
                                            :class="[
                                                'text-2xl',
                                                reviewForm.rating >= i ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600'
                                            ]"
                                        >
                                            ★
                                        </button>
                                    </div>
                                    <InputError :message="reviewForm.errors.rating" />
                                </div>

                                <div>
                                    <InputLabel value="Review" />
                                    <Textarea
                                        v-model="reviewForm.review"
                                        rows="4"
                                        class="mt-1"
                                        placeholder="Tulis review Anda..."
                                    />
                                    <InputError :message="reviewForm.errors.review" />
                                </div>

                                <div>
                                    <InputLabel value="Tags" />
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <button
                                            v-for="tag in availableTags"
                                            :key="tag"
                                            type="button"
                                            @click="toggleTag(tag)"
                                            :class="[
                                                'px-3 py-1 rounded-full text-sm',
                                                selectedTags.includes(tag)
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                            ]"
                                        >
                                            {{ tag.replace('_', ' ') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <PrimaryButton :disabled="reviewForm.processing">
                                        Submit Review
                                    </PrimaryButton>
                                    <button
                                        type="button"
                                        @click="showReviewForm = false"
                                        class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Reviews List -->
                    <div v-if="reviews && reviews.data && reviews.data.length > 0" class="space-y-4">
                        <div
                            v-for="review in reviews.data"
                            :key="review.id"
                            class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ review.user?.name || 'Anonymous' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="flex items-center">
                                            <span class="text-yellow-500">★</span>
                                            {{ review.rating }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ new Date(review.created_at).toLocaleDateString('id-ID') }}</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 mb-2">{{ review.review }}</p>
                            <div v-if="review.tags && review.tags.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in review.tags"
                                    :key="tag"
                                    class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs"
                                >
                                    {{ tag.replace('_', ' ') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Belum ada review untuk supplier ini.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


