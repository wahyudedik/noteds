<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductReviews from '@/Components/Marketplace/ProductReviews.vue';
import ReviewForm from '@/Components/Marketplace/ReviewForm.vue';
import ProductShare from '@/Components/Marketplace/ProductShare.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    product: Object,
    reviews: Object,
    averageRating: {
        type: Number,
        default: 0,
    },
    reviewsCount: {
        type: Number,
        default: 0,
    },
    hasPurchased: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const editingReview = ref(null);
const origin = (typeof window !== 'undefined' && window.location) ? window.location.origin : '';
const ogUrl = computed(() => {
    const rel = route('marketplace.products.show', props.product.id);
    return origin ? (origin + rel) : rel;
});
const ogImage = computed(() => {
    return props.product.image_webp_url || props.product.image_url || (origin ? origin + '/images/placeholder.png' : '/images/placeholder.png');
});

const form = useForm({
    quantity: 1,
});

const addToCartForm = useForm({
    product_id: props.product.id,
    quantity: 1,
});

const addingToCart = ref(false);

const buyProduct = () => {
    form.post(route('marketplace.orders.store', { product_id: props.product.id }));
};

const addToCart = () => {
    addingToCart.value = true;
    addToCartForm.post(route('marketplace.cart.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addingToCart.value = false;
        },
        onFinish: () => {
            addingToCart.value = false;
        },
    });
};

const canEdit = computed(() => {
    return props.product.user_id === page.props.auth?.user?.id;
});

const handleEditReview = (review) => {
    editingReview.value = review;
};

const handleReviewSubmitted = () => {
    editingReview.value = null;
    router.reload({ only: ['reviews', 'averageRating', 'reviewsCount'] });
};

const handleCancelReview = () => {
    editingReview.value = null;
};
</script>

<template>
    <Head :title="product.name">
        <meta name="description" :content="(product.description || '').slice(0, 160)" />
        <meta property="og:title" :content="product.name" />
        <meta property="og:description" :content="(product.description || '').slice(0, 160)" />
        <meta property="og:type" content="product" />
        <meta property="og:image" :content="ogImage" />
        <meta property="og:url" :content="ogUrl" />
        <meta property="product:price:amount" :content="product.price" />
        <meta property="product:price:currency" content="IDR" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="product.name" />
        <meta name="twitter:description" :content="(product.description || '').slice(0, 160)" />
        <meta name="twitter:image" :content="ogImage" />
    </Head>

    <AuthenticatedLayout>
        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Back Button -->
                <Link
                    :href="route('marketplace.index')"
                    class="mb-3 sm:mb-4 inline-flex items-center text-sm sm:text-base text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                >
                    ← Back to Marketplace
                </Link>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Product Image -->
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
                        <img
                            v-if="product.image_webp_url || product.image_url || product.image"
                            :src="product.image_webp_url || product.image_url || product.image"
                            :alt="product.name"
                            class="w-full h-48 sm:h-64 lg:h-80 object-cover"
                            @error="$event.target.src = '/images/placeholder.png'"
                        />
                        <div v-else class="w-full h-48 sm:h-64 lg:h-80 flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm sm:text-base">
                            No Image
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-3">
                            <div class="flex-1">
                                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                    {{ product.name }}
                                </h1>
                                <p class="text-lg sm:text-xl lg:text-2xl font-semibold text-blue-600 dark:text-blue-400">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(product.price) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Share Button -->
                                <ProductShare :product="product" />
                                
                                <!-- Edit Button (only for owner) -->
                                <Link
                                    v-if="canEdit"
                                    :href="route('marketplace.products.edit', product.id)"
                                    class="px-3 sm:px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm sm:text-base font-medium transition-colors"
                                >
                                    Edit
                                </Link>
                            </div>
                        </div>

                        <div class="mb-3 sm:mb-4">
                            <span class="inline-block px-2 sm:px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs sm:text-sm">
                                {{ product.category || 'Uncategorized' }}
                            </span>
                        </div>

                        <div class="mb-4 sm:mb-6">
                            <h2 class="text-base sm:text-lg lg:text-xl font-semibold mb-2 text-gray-900 dark:text-white">Description</h2>
                            <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                {{ product.description }}
                            </p>
                        </div>

                        <!-- Product Stats -->
                        <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-4 sm:mb-6 text-center">
                            <div>
                                <div class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ product.sales_count || 0 }}
                                </div>
                                <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Sales</div>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ product.views_count || 0 }}
                                </div>
                                <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Views</div>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 dark:text-white truncate">
                                    {{ product.seller?.name || 'Unknown' }}
                                </div>
                                <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Seller</div>
                            </div>
                        </div>

                        <!-- Buy Button -->
                        <div v-if="!canEdit" class="border-t border-gray-200 dark:border-gray-700 pt-4 sm:pt-6">
                            <div class="space-y-3 sm:space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                                    <label class="text-sm sm:text-base font-medium text-gray-900 dark:text-white">Quantity:</label>
                                    <input
                                        v-model.number="form.quantity"
                                        @input="addToCartForm.quantity = form.quantity"
                                        type="number"
                                        min="1"
                                        :max="product.stock"
                                        class="w-full sm:w-20 px-3 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                    <form @submit.prevent="buyProduct" class="flex-1">
                                        <button
                                            type="submit"
                                            :disabled="form.processing || (product.stock !== null && product.stock < form.quantity)"
                                            class="w-full px-4 sm:px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-semibold text-sm sm:text-base transition-colors min-h-[44px]"
                                        >
                                            Buy Now
                                        </button>
                                    </form>
                                    <form @submit.prevent="addToCart" class="flex-1">
                                        <button
                                            type="submit"
                                            :disabled="addToCartForm.processing || addingToCart || (product.stock !== null && product.stock < addToCartForm.quantity)"
                                            class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 font-semibold transition-colors"
                                        >
                                            <span v-if="addingToCart">Adding...</span>
                                            <span v-else>Add to Cart</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="mt-6">
                    <!-- Review Form (if user has purchased and not editing) -->
                    <div v-if="hasPurchased && !editingReview && page.props.auth?.user" class="mb-6">
                        <ReviewForm
                            :product-id="product.id"
                            @review-submitted="handleReviewSubmitted"
                        />
                    </div>

                    <!-- Edit Review Form -->
                    <div v-if="editingReview" class="mb-6">
                        <ReviewForm
                            :product-id="product.id"
                            :existing-review="editingReview"
                            @review-submitted="handleReviewSubmitted"
                            @cancel="handleCancelReview"
                        />
                    </div>

                    <!-- Reviews Display -->
                    <ProductReviews
                        :reviews="reviews"
                        :average-rating="averageRating"
                        :reviews-count="reviewsCount"
                        :current-user-id="page.props.auth?.user?.id"
                        @edit-review="handleEditReview"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

