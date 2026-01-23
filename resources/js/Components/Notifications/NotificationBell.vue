<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    notifications: {
        default: () => [],
    },
});

const isOpen = ref(false);
const dropdownRef = ref(null);
const page = usePage();
const localNotifications = ref([]);

const normalizeNotifications = (notifications) => {
    if (notifications && typeof notifications === 'object' && 'data' in notifications) {
        return notifications.data || [];
    }

    return Array.isArray(notifications) ? notifications : [];
};

const notificationsList = computed(() => localNotifications.value);

const unreadCount = computed(() => {
    return notificationsList.value.filter(n => !n.read_at).length;
});

const recentLimit = ref(5);
const recentNotifications = computed(() => {
    return notificationsList.value.slice(0, recentLimit.value);
});

const getNotificationType = (notification) => {
    // Try to get type from data field first (new format)
    if (notification.data?.type) {
        return notification.data.type;
    }
    // Fallback to extracting from notification type (class name)
    const type = notification.type || '';
    if (type.includes('NewComment')) return 'new_comment';
    if (type.includes('NewFollow')) return 'new_follow';
    if (type.includes('ContentReported')) return 'content_reported';
    if (type.includes('ReportResolved')) return 'report_resolved';
    if (type.includes('NewCampaign')) return 'new_campaign';
    if (type.includes('ClipApproved')) return 'clip_approved';
    if (type.includes('RewardReceived')) return 'reward_received';
    if (type.includes('CampaignEnded')) return 'campaign_ended';
    if (type.includes('BrandApproved')) return 'brand_approved';
    if (type.includes('BrandRejected')) return 'brand_rejected';
    if (type.includes('NewOrder')) return 'new_order';
    if (type.includes('WithdrawalStatus')) return 'withdrawal_status';
    if (type.includes('PostModerated')) return 'post_moderated';
    if (type.includes('PostRestored')) return 'post_restored';
    if (type.includes('CampaignCreated')) return 'campaign_created';
    if (type.includes('ClipSubmitted')) return 'clip_submitted';
    if (type.includes('ProductCreated')) return 'product_created';
    if (type.includes('FraudDetected')) return 'fraud_detected';
    if (type.includes('OrderCreated')) return 'order_created';
    if (type.includes('CampaignSuspended')) return 'campaign_suspended';
    if (type.includes('ProductApproved')) return 'product_approved';
    if (type.includes('ProductRejected')) return 'product_rejected';
    if (type.includes('OrderCancelled')) return 'order_cancelled';
    if (type.includes('PaymentFailed')) return 'payment_failed';
    if (type.includes('ClipRejected')) return 'clip_rejected';
    if (type.includes('ViewValidated')) return 'view_validated';
    return 'default';
};

const getNotificationIcon = (notification) => {
    const type = getNotificationType(notification);
    const icons = {
        'new_comment': 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'new_follow': 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'content_reported': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'report_resolved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'new_campaign': 'M12 4v16m8-8H4',
        'clip_approved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'reward_received': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'campaign_ended': 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'brand_approved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'brand_rejected': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'new_order': 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        'withdrawal_status': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
        'post_moderated': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'post_restored': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'campaign_created': 'M12 4v16m8-8H4',
        'clip_submitted': 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
        'product_created': 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'fraud_detected': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'order_created': 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        'campaign_suspended': 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
        'product_approved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'product_rejected': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'order_cancelled': 'M6 18L18 6M6 6l12 12',
        'payment_failed': 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'clip_rejected': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'view_validated': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    return icons[type] || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
};

const getNotificationTitle = (notification) => {
    const type = getNotificationType(notification);
    const labels = {
        'new_comment': 'Komentar Baru',
        'new_follow': 'Pengikut Baru',
        'content_reported': 'Konten Dilaporkan',
        'report_resolved': 'Laporan Diselesaikan',
        'new_campaign': 'Kampanye Baru',
        'clip_approved': 'Clip Disetujui',
        'reward_received': 'Reward Diterima',
        'campaign_ended': 'Kampanye Berakhir',
        'brand_approved': 'Brand Disetujui',
        'brand_rejected': 'Brand Ditolak',
        'new_order': 'Pesanan Baru',
        'withdrawal_status': 'Status Penarikan',
        'post_moderated': 'Post Dimoderasi',
        'post_restored': 'Post Dipulihkan',
        'campaign_created': 'Kampanye Dibuat',
        'clip_submitted': 'Clip Dikirim',
        'product_created': 'Produk Dibuat',
        'fraud_detected': 'Kecurangan Terdeteksi',
        'order_created': 'Order Dibuat',
        'campaign_suspended': 'Kampanye Ditangguhkan',
        'product_approved': 'Produk Disetujui',
        'product_rejected': 'Produk Ditolak',
        'order_cancelled': 'Order Dibatalkan',
        'payment_failed': 'Pembayaran Gagal',
        'clip_rejected': 'Clip Ditolak',
        'view_validated': 'View Terverifikasi',
        'default': 'Notifikasi',
    };
    return notification.data?.title || labels[type] || labels.default;
};

const getTitle = (n) => {
  const d = n.data || {};
  if (d.title) return d.title;
  const type = getNotificationType(n);
  switch (type) {
    case 'new_order': return `Pesanan Baru${d.order_number ? ' #' + d.order_number : ''}`;
    case 'order_status_update': return `Status Order Diperbarui${d.order_number ? ' #' + d.order_number : ''}`;
    case 'payment_failed': return 'Pembayaran Gagal';
    case 'withdrawal_status': return 'Status Penarikan';
    case 'product_approved': return 'Produk Disetujui';
    case 'product_rejected': return 'Produk Ditolak';
    case 'support_ticket_response': return `Balasan Tiket${d.ticket_number ? ' #' + d.ticket_number : ''}`;
    case 'new_campaign': return 'Kampanye Baru';
    case 'clip_approved': return 'Clip Disetujui';
    case 'clip_rejected': return 'Clip Ditolak';
    case 'post_moderated': return 'Post Dimoderasi';
    case 'post_restored': return 'Post Dipulihkan';
    default: return getNotificationTitle(n);
  }
};

const getTypeLabel = (n) => {
  const type = getNotificationType(n);
  const map = {
    new_order: 'Pesanan',
    order_status_update: 'Status Pesanan',
    payment_failed: 'Pembayaran',
    withdrawal_status: 'Penarikan',
    product_approved: 'Produk',
    product_rejected: 'Produk',
    support_ticket_response: 'Tiket Dukungan',
    new_campaign: 'Kampanye',
    clip_approved: 'Clip',
    clip_rejected: 'Clip',
    post_moderated: 'Post',
    post_restored: 'Post',
  };
  return map[type] || 'Umum';
};

const getNotificationColor = (notification) => {
    const type = getNotificationType(notification);
    const colors = {
        'new_comment': 'text-blue-600 dark:text-blue-400',
        'new_follow': 'text-green-600 dark:text-green-400',
        'content_reported': 'text-red-600 dark:text-red-400',
        'report_resolved': 'text-green-600 dark:text-green-400',
        'new_campaign': 'text-blue-600 dark:text-blue-400',
        'clip_approved': 'text-green-600 dark:text-green-400',
        'reward_received': 'text-yellow-600 dark:text-yellow-400',
        'campaign_ended': 'text-gray-600 dark:text-gray-400',
        'brand_approved': 'text-green-600 dark:text-green-400',
        'brand_rejected': 'text-red-600 dark:text-red-400',
        'new_order': 'text-indigo-600 dark:text-indigo-400',
        'withdrawal_status': 'text-purple-600 dark:text-purple-400',
        'post_moderated': 'text-orange-600 dark:text-orange-400',
        'post_restored': 'text-green-600 dark:text-green-400',
        'campaign_created': 'text-blue-600 dark:text-blue-400',
        'clip_submitted': 'text-indigo-600 dark:text-indigo-400',
        'product_created': 'text-teal-600 dark:text-teal-400',
        'fraud_detected': 'text-red-600 dark:text-red-400',
        'order_created': 'text-indigo-600 dark:text-indigo-400',
        'campaign_suspended': 'text-orange-600 dark:text-orange-400',
        'product_approved': 'text-green-600 dark:text-green-400',
        'product_rejected': 'text-red-600 dark:text-red-400',
        'order_cancelled': 'text-red-600 dark:text-red-400',
        'payment_failed': 'text-red-600 dark:text-red-400',
        'clip_rejected': 'text-red-600 dark:text-red-400',
        'view_validated': 'text-green-600 dark:text-green-400',
    };
    return colors[type] || 'text-gray-600 dark:text-gray-400';
};

const getMessage = (n) => {
  const d = n.data || {};
  const type = getNotificationType(n);
  if (d.message || d.body) return d.message || d.body;
  switch (type) {
    case 'new_order': return `Order #${d.order_number || d.order_id}`;
    case 'order_status_update': return `Order #${d.order_number || d.order_id} → ${d.status || ''}`;
    case 'payment_failed': return `Pembayaran gagal untuk Order #${d.order_number || d.order_id}`;
    case 'withdrawal_status': return `Penarikan ${d.status || ''}`;
    case 'product_approved': return `Produk disetujui`;
    case 'product_rejected': return `Produk ditolak${d.reason ? `: ${d.reason}` : ''}`;
    case 'support_ticket_response': return `Balasan tiket #${d.ticket_number || d.ticket_id}`;
    default: return '';
  }
};
const isHighPriority = (notification) => {
    const type = getNotificationType(notification);
    const highPriorityTypes = [
        'fraud_detected',
        'content_reported',
        'payment_failed',
        'order_cancelled',
    ];
    return highPriorityTypes.includes(type);
};

const formatDate = (date) => {
    if (!date) return 'Unknown';
    
    const now = new Date();
    const notificationDate = new Date(date);
    
    // Check if date is valid
    if (isNaN(notificationDate.getTime())) return 'Invalid date';
    
    const diffInSeconds = Math.floor((now - notificationDate) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
    
    return notificationDate.toLocaleDateString('id-ID', {
        month: 'short',
        day: 'numeric',
    });
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const markAsRead = (notification) => {
    if (notification.read_at) return;
    
    router.post(route('notifications.read', notification.id), {}, {
        preserveScroll: true,
    });
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

const shouldPlaySound = () => {
    const settings = page.props.settings || {};
    const prefs = settings.notification_preferences || {};

    return prefs.sound_enabled !== false;
};

const playNotificationSound = () => {
    if (!shouldPlaySound()) {
        return;
    }

    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;

        const audioCtx = new AudioContext();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.type = 'triangle';
        oscillator.frequency.value = 880;

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        gainNode.gain.setValueAtTime(0.001, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.2, audioCtx.currentTime + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.25);

        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 0.3);

        oscillator.onended = () => {
            audioCtx.close();
        };
    } catch (e) {
        // Silently ignore audio errors
    }
};

let echoChannel = null;

onMounted(() => {
    localNotifications.value = normalizeNotifications(props.notifications);

    watch(
        () => props.notifications,
        (newVal) => {
            localNotifications.value = normalizeNotifications(newVal);
        },
        { deep: true }
    );

    document.addEventListener('click', handleClickOutside);

    const user = page.props.auth?.user;

    if (user && window.Echo) {
        echoChannel = window.Echo.private(`user.${user.id}.notifications`);

        echoChannel.listen('.user.notification.created', (event) => {
            if (event?.notification) {
                localNotifications.value = [
                    event.notification,
                    ...localNotifications.value,
                ];

                playNotificationSound();
            }
        });
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);

    if (echoChannel && window.Echo) {
        const user = page.props.auth?.user;
        window.Echo.leave(`user.${user?.id}.notifications`);
        echoChannel = null;
    }
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95 translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-hidden flex flex-col"
            >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifikasi</h3>
                    <div class="flex items-center gap-3">
                        <button
                            @click="
                                router.post(route('notifications.read-all'), {}, { preserveScroll: true });
                                localNotifications.value = localNotifications.value.map((n, idx) => idx < recentLimit ? { ...n, read_at: new Date().toISOString() } : n);
                            "
                            class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300"
                        >
                            Mark all read
                        </button>
                        <Link
                            :href="route('notifications.index')"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                            @click="isOpen = false"
                        >
                            View all
                        </Link>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="overflow-y-auto max-h-80">
                    <div v-if="recentNotifications.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="notification in recentNotifications"
                            :key="notification.id"
                            @click="markAsRead(notification)"
                            :class="[
                                'px-4 py-3 cursor-pointer transition-colors relative rounded-md group focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 flex items-start gap-3',
                                !notification.read_at ? 'border border-indigo-200 dark:border-indigo-700' : 'border border-transparent',
                                isHighPriority(notification) && !notification.read_at ? 'border-l-4 border-red-500' : ''
                            ]"
                            role="button"
                            tabindex="0"
                            @keydown.enter="markAsRead(notification)"
                        >
                            <!-- Icon -->
                            <div :class="['flex-shrink-0 h-6 w-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center transition-transform group-hover:scale-105', getNotificationColor(notification)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification)" />
                                </svg>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-2">
                                    <p class="text-sm line-clamp-1 flex-1"
                                       :class="[!notification.read_at ? 'font-semibold text-gray-900 dark:text-white' : 'font-medium text-gray-900 dark:text-white']">
                                        {{ getTitle(notification) }}
                                    </p>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ getTypeLabel(notification) }}
                                    </span>
                                    <span
                                        v-if="isHighPriority(notification) && !notification.read_at"
                                        class="flex-shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400"
                                    >
                                        Urgent
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                    {{ getMessage(notification) }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    {{ formatDate(notification.created_at) }}
                                </p>
                            </div>

                            <!-- Unread indicator -->
                            <div v-if="!notification.read_at" class="flex-shrink-0 mt-1">
                                <span :class="[
                                    'inline-block w-2 h-2 rounded-full',
                                    isHighPriority(notification) ? 'bg-red-600' : 'bg-indigo-600'
                                ]"></span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No notifications</p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>


