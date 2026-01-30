<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const showShareMenu = ref(false);
const copySuccess = ref(false);

const productUrl = computed(() => {
    const baseUrl = window.location.origin;
    // Use Ziggy route helper if available, otherwise construct URL manually
    if (typeof route !== 'undefined') {
        return `${baseUrl}${route('marketplace.products.show', props.product.id)}`;
    }
    return `${baseUrl}/marketplace/products/${props.product.id}`;
});

const shareText = computed(() => {
    return `Check out this product: ${props.product.name} - Rp ${new Intl.NumberFormat('id-ID').format(props.product.price)}`;
});

const shareWithDescription = computed(() => {
    const desc = props.product.description?.substring(0, 100) || '';
    return `${shareText.value}\n\n${desc}${desc.length >= 100 ? '...' : ''}\n\n${productUrl.value}`;
});

const withUtm = (platform) => {
    const params = new URLSearchParams({
        utm_source: platform,
        utm_medium: 'social',
        utm_campaign: 'marketplace_share',
        utm_product: props.product.id,
    });
    const url = new URL(productUrl.value);
    url.search = params.toString();
    return url.toString();
};

const shareWhatsApp = () => {
    const url = `https://wa.me/?text=${encodeURIComponent(shareWithDescription.value + '\n' + withUtm('whatsapp'))}`;
    window.open(url, '_blank', 'width=600,height=400');
    trackShare('whatsapp');
};

const shareFacebook = () => {
    const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(withUtm('facebook'))}`;
    window.open(url, '_blank', 'width=600,height=400');
    trackShare('facebook');
};

const shareTwitter = () => {
    const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText.value)}&url=${encodeURIComponent(withUtm('twitter'))}`;
    window.open(url, '_blank', 'width=600,height=400');
    trackShare('twitter');
};

const shareLinkedIn = () => {
    const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(withUtm('linkedin'))}`;
    window.open(url, '_blank', 'width=600,height=400');
    trackShare('linkedin');
};

const shareTelegram = () => {
    const url = `https://t.me/share/url?url=${encodeURIComponent(withUtm('telegram'))}&text=${encodeURIComponent(shareText.value)}`;
    window.open(url, '_blank', 'width=600,height=400');
    trackShare('telegram');
};

const shareEmail = () => {
    const subject = encodeURIComponent(`Check out: ${props.product.name}`);
    const body = encodeURIComponent(`${shareWithDescription.value}\n${withUtm('email')}`);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
    trackShare('email');
};

const shareInstagram = () => {
    copyToClipboard();
    window.open(withUtm('instagram'), '_blank');
    trackShare('instagram');
};

const shareTikTok = () => {
    copyToClipboard();
    window.open(withUtm('tiktok'), '_blank');
    trackShare('tiktok');
};

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(productUrl.value);
        copySuccess.value = true;
        trackShare('copy_link');
        
        setTimeout(() => {
            copySuccess.value = false;
        }, 2000);
    } catch (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = productUrl.value;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            copySuccess.value = true;
            trackShare('copy_link');
            setTimeout(() => {
                copySuccess.value = false;
            }, 2000);
        } catch (err) {
            console.error('Failed to copy:', err);
        }
        document.body.removeChild(textArea);
    }
};

const trackShare = (platform) => {
    // Optional: Track share analytics
    if (typeof window !== 'undefined' && window.axios) {
        const shareUrl = typeof route !== 'undefined' 
            ? route('marketplace.products.share', props.product.id)
            : `/marketplace/products/${props.product.id}/share`;
            
        window.axios.post(shareUrl, {
            platform: platform,
        }).catch(() => {
            // Silently fail if tracking fails
        });
    }
};

const toggleShareMenu = () => {
    showShareMenu.value = !showShareMenu.value;
};

// Close menu when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest('.share-menu-container')) {
        showShareMenu.value = false;
    }
};

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('click', handleClickOutside);
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('click', handleClickOutside);
    }
});

const shareNotedsHome = () => {
    const base = window.location.origin;
    const params = new URLSearchParams({
        utm_source: 'noteds_home',
        utm_medium: 'social',
        utm_campaign: 'project_post',
        utm_product: props.product.id,
        product_id: props.product.id,
    });
    const url = `${base}/home?${params.toString()}`;
    copyToClipboard();
    window.open(url, '_blank');
    trackShare('noteds_home');
};
</script>

<template>
    <div class="share-menu-container relative">
        <!-- Share Button -->
        <button
            @click="toggleShareMenu"
            :class="[
                'flex items-center justify-center gap-2 px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors',
                compact
                    ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    : 'bg-gradient-to-r from-purple-500 to-pink-500 text-white hover:from-purple-600 hover:to-pink-600 shadow-lg hover:shadow-xl',
                'focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2',
            ]"
            aria-label="Share product"
        >
            <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                />
            </svg>
            <span v-if="!compact" class="hidden sm:inline">Share</span>
        </button>

        <!-- Share Menu Dropdown -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="showShareMenu"
                class="absolute right-0 mt-2 w-56 sm:w-64 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 py-2"
            >
                <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Share Product</p>
                </div>

                <div class="py-1">
                    <!-- Noteds Home -->
                    <button
                        @click="shareNotedsHome"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors"
                    >
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3l8 7-1.5 1.5L18 10.5V20h-5v-5H11v5H6v-9.5L5.5 11.5 4 10l8-7z"/>
                        </svg>
                        <span>Noteds Home</span>
                    </button>
                    <!-- WhatsApp -->
                    <button
                        @click="shareWhatsApp"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                    >
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </button>

                    <!-- Facebook -->
                    <button
                        @click="shareFacebook"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                    >
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Facebook</span>
                    </button>

                    <!-- Twitter/X -->
                    <button
                        @click="shareTwitter"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5 text-gray-900 dark:text-gray-100" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                        <span>Twitter/X</span>
                    </button>

                    <!-- LinkedIn -->
                    <button
                        @click="shareLinkedIn"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                    >
                        <svg class="w-5 h-5 text-blue-700 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        <span>LinkedIn</span>
                    </button>

                    <!-- Telegram -->
                    <button
                        @click="shareTelegram"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                    >
                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                        <span>Telegram</span>
                    </button>

                    <!-- Email -->
                    <button
                        @click="shareEmail"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Email</span>
                    </button>

                    <!-- Instagram -->
                    <button
                        @click="shareInstagram"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-pink-50 dark:hover:bg-pink-900/20 transition-colors"
                    >
                        <svg class="w-5 h-5 text-pink-600 dark:text-pink-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 2C4.243 2 2 4.243 2 7v10c0 2.757 2.243 5 5 5h10c2.757 0 5-2.243 5-5V7c0-2.757-2.243-5-5-5H7zm10 2a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h10zm-5 3a5 5 0 100 10 5 5 0 000-10zm0 2a3 3 0 110 6 3 3 0 010-6zm5.5-3a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"/>
                        </svg>
                        <span>Instagram</span>
                    </button>

                    <!-- TikTok -->
                    <button
                        @click="shareTikTok"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5 text-gray-900 dark:text-gray-100" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16.636 3.133c.71.55 1.57.955 2.525 1.082V7.35c-1.695-.21-3.273-.897-4.545-1.95v7.753c0 3.135-2.543 5.677-5.677 5.677-1.07 0-2.076-.305-2.927-.833l.01-.01c1.39-.59 2.364-1.97 2.364-3.565 0-2.144-1.737-3.881-3.881-3.881-.472 0-.922.082-1.34.234v-3.32c.433-.074.876-.113 1.326-.113 3.133 0 5.676 2.543 5.676 5.677 0 .196-.01.39-.03.58 1.02-.99 1.656-2.356 1.656-3.873V3h2.843z"/>
                        </svg>
                        <span>TikTok</span>
                    </button>

                    <!-- Copy Link -->
                    <button
                        @click="copyToClipboard"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg
                            v-if="!copySuccess"
                            class="w-5 h-5 text-gray-600 dark:text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <svg
                            v-else
                            class="w-5 h-5 text-green-600 dark:text-green-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ copySuccess ? 'Copied!' : 'Copy Link' }}</span>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

