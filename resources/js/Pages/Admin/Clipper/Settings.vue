<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    platform_fee_percent: props.settings.platform_fee_percent ?? 5,
});

const submit = () => {
    form.put(route('admin.clipper.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success message will be shown via Inertia flash message
        },
    });
};

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error || (page.props.errors?.error ? page.props.errors.error[0] : null));

// Preview calculation
const previewAmounts = [50000, 100000, 250000, 500000, 1000000];
const selectedPreviewAmount = ref(100000); // Default preview amount
</script>

<template>
    <Head title="Clipper Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Clipper Platform Fee Settings
                </h2>
                <Link
                    :href="route('admin.dashboard')"
                    class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl space-y-6">
                <!-- Flash Messages -->
                <div v-if="flashSuccess" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800 dark:text-green-200">{{ flashSuccess }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="flashError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-800 dark:text-red-200">{{ flashError }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-blue-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                Configure platform fee percentage for Clipper rewards. This fee is deducted from clipper rewards when approved clips are paid out. Changes will apply to all new payouts after saving.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Settings Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Platform Fee Percentage -->
                        <div>
                            <InputLabel for="platform_fee_percent" value="Platform Fee Percentage (%)" />
                            <div class="mt-1 flex items-center space-x-4">
                                <input
                                    v-model.number="form.platform_fee_percent"
                                    type="range"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                                    id="platform_fee_percent"
                                />
                                <TextInput
                                    v-model.number="form.platform_fee_percent"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    class="w-24"
                                    id="platform_fee_percent-input"
                                />
                            </div>
                            <InputError :message="form.errors.platform_fee_percent" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Percentage of clipper reward (0-100%)
                            </p>
                        </div>

                        <!-- Fee Preview -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                Fee Preview
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                        Preview dengan Reward Amount:
                                    </label>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <button
                                            v-for="amount in previewAmounts"
                                            :key="amount"
                                            type="button"
                                            @click="selectedPreviewAmount = amount"
                                            :class="[
                                                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                                selectedPreviewAmount === amount
                                                    ? 'bg-blue-600 text-white'
                                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                            ]"
                                        >
                                            Rp {{ new Intl.NumberFormat('id-ID').format(amount) }}
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <TextInput
                                            v-model.number="selectedPreviewAmount"
                                            type="number"
                                            min="0"
                                            step="1000"
                                            placeholder="Custom amount"
                                            class="w-full"
                                        />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">atau masukkan custom</p>
                                    </div>
                                </div>

                                <div v-if="selectedPreviewAmount" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 dark:text-gray-400">Reward Amount:</span>
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                                Rp {{ new Intl.NumberFormat('id-ID').format(selectedPreviewAmount) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 dark:text-gray-400">Platform Fee ({{ form.platform_fee_percent }}%):</span>
                                            <span class="text-lg font-semibold text-red-600 dark:text-red-400">
                                                - Rp {{ new Intl.NumberFormat('id-ID').format((selectedPreviewAmount * form.platform_fee_percent / 100)) }}
                                            </span>
                                        </div>
                                        <div class="border-t border-gray-300 dark:border-gray-500 pt-3 flex justify-between items-center">
                                            <span class="text-base font-semibold text-gray-900 dark:text-white">Clipper Receives:</span>
                                            <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                                Rp {{ new Intl.NumberFormat('id-ID').format(selectedPreviewAmount - (selectedPreviewAmount * form.platform_fee_percent / 100)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                :href="route('admin.dashboard')"
                                class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:bg-gray-400 dark:focus:bg-gray-500 active:bg-gray-500 dark:active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Settings' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

