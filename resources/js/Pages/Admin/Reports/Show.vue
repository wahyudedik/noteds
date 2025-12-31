<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Textarea from '@/Components/Textarea.vue';
import { ref } from 'vue';

const props = defineProps({
    report: Object,
});

const resolveForm = useForm({
    admin_notes: '',
});

const dismissForm = useForm({
    admin_notes: '',
});

const resolve = () => {
    resolveForm.post(route('admin.reports.resolve', props.report.id), {
        preserveScroll: true,
    });
};

const dismiss = () => {
    if (!dismissForm.admin_notes.trim()) {
        dismissForm.setError('admin_notes', 'Admin notes are required for dismissal.');
        return;
    }
    dismissForm.post(route('admin.reports.dismiss', props.report.id), {
        preserveScroll: true,
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        reviewing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        resolved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        dismissed: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return classes[status] || classes.pending;
};

const getReasonLabel = (reason) => {
    const labels = {
        spam: 'Spam',
        harassment: 'Harassment',
        inappropriate: 'Inappropriate',
        copyright: 'Copyright',
        fake: 'Fake',
        other: 'Other',
    };
    return labels[reason] || reason;
};

const getTypeLabel = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1);
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getReportableLink = () => {
    if (!props.report.reportable) return null;
    
    if (props.report.reportable_type === 'post') {
        return route('posts.show', props.report.reportable.id);
    } else if (props.report.reportable_type === 'comment') {
        // Comments might be linked through posts
        return route('posts.show', props.report.reportable.post_id || props.report.reportable.id);
    } else if (props.report.reportable_type === 'user') {
        return route('profile.show', props.report.reportable.id);
    }
    return null;
};
</script>

<template>
    <Head :title="'Report #' + report.id.slice(0, 8)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Report Review
                </h2>
                <Link
                    :href="route('admin.reports.index')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm"
                >
                    Back to Reports
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <!-- Status Badge -->
                <div class="mb-6">
                    <span
                        :class="['inline-flex px-3 py-1 text-sm font-medium rounded-full', getStatusBadgeClass(report.status)]"
                    >
                        {{ report.status }}
                    </span>
                </div>

                <!-- Report Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        Report Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Report ID</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ report.id }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Report Type</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ getTypeLabel(report.reportable_type) }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Reason</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white capitalize">
                                {{ getReasonLabel(report.reason) }}
                            </div>
                        </div>

                        <div v-if="report.notes">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Report Notes</div>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line bg-gray-50 dark:bg-gray-900 p-3 rounded-md">
                                {{ report.notes }}
                            </p>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Reported by</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ report.user?.name || report.user?.email || 'Unknown' }}
                            </div>
                            <div v-if="report.user?.email" class="text-sm text-gray-500 dark:text-gray-400">
                                {{ report.user.email }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Reported on</div>
                            <div class="text-base text-gray-700 dark:text-gray-300">
                                {{ formatDate(report.created_at) }}
                            </div>
                        </div>

                        <div v-if="report.reportable">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Reported Content</div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-md">
                                <div v-if="report.reportable_type === 'post'" class="space-y-2">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ report.reportable.title || 'Untitled Post' }}
                                    </div>
                                    <div v-if="report.reportable.content" class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
                                        {{ report.reportable.content }}
                                    </div>
                                    <div v-if="getReportableLink()">
                                        <Link
                                            :href="getReportableLink()"
                                            target="_blank"
                                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                                        >
                                            View Post →
                                        </Link>
                                    </div>
                                </div>
                                <div v-else-if="report.reportable_type === 'comment'" class="space-y-2">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ report.reportable.content || 'Comment content' }}
                                    </div>
                                    <div v-if="getReportableLink()">
                                        <Link
                                            :href="getReportableLink()"
                                            target="_blank"
                                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                                        >
                                            View Comment →
                                        </Link>
                                    </div>
                                </div>
                                <div v-else-if="report.reportable_type === 'user'" class="space-y-2">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ report.reportable.business_name || report.reportable.name || 'User' }}
                                    </div>
                                    <div v-if="report.reportable.email" class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ report.reportable.email }}
                                    </div>
                                    <div v-if="getReportableLink()">
                                        <Link
                                            :href="getReportableLink()"
                                            target="_blank"
                                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                                        >
                                            View Profile →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Actions -->
                <div v-if="report.status === 'pending' || report.status === 'reviewing'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        Admin Actions
                    </h3>

                    <!-- Resolve -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Resolve Report</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Mark this report as resolved. The reported content will be reviewed and appropriate action will be taken.
                        </p>
                        <form @submit.prevent="resolve" class="space-y-4">
                            <div>
                                <InputLabel for="resolve_notes" value="Admin Notes (Optional)" />
                                <Textarea
                                    id="resolve_notes"
                                    class="mt-1 block w-full"
                                    v-model="resolveForm.admin_notes"
                                    rows="3"
                                    placeholder="Add any notes about the resolution..."
                                />
                                <InputError class="mt-2" :message="resolveForm.errors.admin_notes" />
                            </div>
                            <PrimaryButton :disabled="resolveForm.processing">
                                Resolve Report
                            </PrimaryButton>
                        </form>
                    </div>

                    <!-- Dismiss -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Dismiss Report</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Dismiss this report if it doesn't violate any guidelines. Admin notes are required.
                        </p>
                        <form @submit.prevent="dismiss" class="space-y-4">
                            <div>
                                <InputLabel for="dismiss_notes" value="Admin Notes (Required)" />
                                <Textarea
                                    id="dismiss_notes"
                                    class="mt-1 block w-full"
                                    v-model="dismissForm.admin_notes"
                                    rows="3"
                                    placeholder="Explain why this report is being dismissed..."
                                    required
                                />
                                <InputError class="mt-2" :message="dismissForm.errors.admin_notes" />
                            </div>
                            <PrimaryButton :disabled="dismissForm.processing">
                                Dismiss Report
                            </PrimaryButton>
                        </form>
                    </div>
                </div>

                <!-- Resolution Details -->
                <div v-if="report.status === 'resolved' || report.status === 'dismissed'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        Resolution Details
                    </h3>
                    
                    <div class="space-y-4">
                        <div v-if="report.admin">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Resolved by</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ report.admin.name || report.admin.email || 'Admin' }}
                            </div>
                        </div>

                        <div v-if="report.resolved_at">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Resolved on</div>
                            <div class="text-base text-gray-700 dark:text-gray-300">
                                {{ formatDate(report.resolved_at) }}
                            </div>
                        </div>

                        <div v-if="report.admin_notes">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Admin Notes</div>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line bg-gray-50 dark:bg-gray-900 p-3 rounded-md">
                                {{ report.admin_notes }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

