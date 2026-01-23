<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    notification: {
        type: Object,
        required: true,
    },
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
    return props.notification.data?.title || labels[type] || labels.default;
};
const getTitle = (n) => {
    const d = n.data || {};
    if (d.title) return d.title;
    const type = getNotificationType(n);
    switch (type) {
        case 'new_order':
            return `Pesanan Baru${d.order_number ? ' #' + d.order_number : ''}`;
        case 'order_status_update':
            return `Status Order Diperbarui${d.order_number ? ' #' + d.order_number : ''}`;
        case 'payment_failed':
            return 'Pembayaran Gagal';
        case 'withdrawal_status':
            return 'Status Penarikan';
        case 'product_approved':
            return 'Produk Disetujui';
        case 'product_rejected':
            return 'Produk Ditolak';
        case 'support_ticket_response':
            return `Balasan Tiket${d.ticket_number ? ' #' + d.ticket_number : ''}`;
        case 'new_campaign':
            return 'Kampanye Baru';
        case 'clip_approved':
            return 'Clip Disetujui';
        case 'clip_rejected':
            return 'Clip Ditolak';
        case 'post_moderated':
            return 'Post Dimoderasi';
        case 'post_restored':
            return 'Post Dipulihkan';
        default:
            return getNotificationTitle(n);
    }
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
    };
    return colors[type] || 'text-gray-600 dark:text-gray-400';
};

const formatDate = (date) => {
    const now = new Date();
    const notificationDate = new Date(date);
    const diffInSeconds = Math.floor((now - notificationDate) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    
    return notificationDate.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const markAsRead = (notification) => {
    if (notification.read_at) return;
    
    router.post(route('notifications.read', notification.id), {}, {
        preserveScroll: true,
    });
};

const getPreview = (n) => {
    const d = n.data || {};
    if (d.preview && typeof d.preview === 'object') {
        return d.preview;
    }
    if (d.post_id) {
        return {
            heading: `Post #${d.post_id}`,
            title: d.post_title || d.title || '',
            text: d.post_excerpt || d.message || d.body || '',
        };
    }
    if (d.order_id) {
        return {
            heading: `Order #${d.order_number || d.order_id}`,
            title: d.title || 'Order',
            text: d.message || '',
        };
    }
    return null;
};

const getPreviewList = (n) => {
    const d = n.data || {};
    if (Array.isArray(d.preview_list)) {
        return d.preview_list.map((item) => ({
            heading: item.heading || '',
            title: item.title || '',
            text: item.text || '',
        }));
    }
    if (Array.isArray(d.list)) {
        return d.list.map((item) => ({
            heading: item.heading || '',
            title: item.title || '',
            text: item.text || '',
        }));
    }
    return null;
};

const getMessage = (n) => {
    const d = n.data || {};
    const type = getNotificationType(n);
    if (d.message || d.body) return d.message || d.body;
    switch (type) {
        case 'new_order':
            return `Order #${d.order_number || d.order_id} untuk ${d.product_name || ''}`;
        case 'order_status_update':
            return `Order #${d.order_number || d.order_id} → ${d.status || ''}`;
        case 'payment_failed':
            return `Pembayaran gagal untuk Order #${d.order_number || d.order_id}`;
        case 'withdrawal_status':
            return `Penarikan ${d.status || ''}`;
        case 'product_approved':
            return `Produk disetujui`;
        case 'product_rejected':
            return `Produk ditolak${d.reason ? `: ${d.reason}` : ''}`;
        case 'new_campaign':
            return `Kampanye baru tersedia`;
        case 'clip_approved':
            return `Clip disetujui`;
        case 'clip_rejected':
            return `Clip ditolak`;
        case 'support_ticket_response':
            return `Balasan tiket #${d.ticket_number || d.ticket_id}`;
        case 'post_moderated':
        case 'post_restored':
            return `Perubahan pada post #${d.post_id}`;
        default:
            return '';
    }
};
</script>

<template>
    <div
        @click="markAsRead(notification)"
        :class="[
            'bg-white dark:bg-gray-800 rounded-xl border p-4 cursor-pointer transition-all hover:bg-gray-50 dark:hover:bg-gray-700',
            notification.read_at 
                ? 'border-gray-200 dark:border-gray-700' 
                : 'border-indigo-300 dark:border-indigo-700'
        ]"
    >
        <div class="flex gap-4">
            <!-- Icon -->
            <div :class="['flex-shrink-0 h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center', getNotificationColor(notification)]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification)" />
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                        <p :class="['text-sm', notification.read_at ? 'font-medium' : 'font-semibold', 'text-gray-900 dark:text-white']">
                            {{ getTitle(notification) }}
                        </p>
                        <p class="mt-1 text-sm leading-5 text-gray-700 dark:text-gray-300 line-clamp-2">
                            {{ getMessage(notification) }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <span v-if="!notification.read_at" class="inline-block w-2 h-2 bg-indigo-600 rounded-full"></span>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDate(notification.created_at) }}
                </div>

                <div v-if="getPreview(notification) || getPreviewList(notification)" class="mt-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template v-if="getPreviewList(notification)">
                            <div
                                v-for="(item, idx) in getPreviewList(notification)"
                                :key="idx"
                                class="p-3 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition dark:bg-gray-800 dark:border-gray-700"
                            >
                                <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">
                                    {{ item.heading }}
                                </div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1">
                                    {{ item.title }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2">
                                    {{ item.text }}
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="p-3 rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition dark:bg-gray-800 dark:border-gray-700">
                                <div class="text-xs text-gray-600 dark:text-gray-300 mb-1">
                                    {{ getPreview(notification).heading }}
                                </div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1">
                                    {{ getPreview(notification).title }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2">
                                    {{ getPreview(notification).text }}
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

