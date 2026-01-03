<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import MarketplaceCommissionPreview from '@/Components/Admin/MarketplaceCommissionPreview.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    enabled: props.settings.enabled ?? true,
    percentage: props.settings.percentage ?? 5,
    flat_fee: props.settings.flat_fee ?? 0,
});

const submit = () => {
    form.put(route('admin.marketplace.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success message will be shown via Inertia flash message
        },
    });
};

const hasWarning = computed(() => {
    if (!form.enabled) return false;
    // Check if commission could exceed typical order amounts
    const testAmount = 10000; // Smallest reasonable order
    const commission = (testAmount * form.percentage) / 100 + form.flat_fee;
    return commission >= testAmount;
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error || (page.props.errors?.error ? page.props.errors.error[0] : null));
</script>

<template>
    <Head title="Marketplace Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Marketplace Commission Settings
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
                                Configure commission settings for marketplace transactions. Commission is calculated as
                                percentage + flat fee. Changes will apply to all new orders after saving.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Settings Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Enable/Disable Toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <InputLabel for="enabled" value="Enable Commission System" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    When disabled, sellers receive 100% of the order total.
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    v-model="form.enabled"
                                    type="checkbox"
                                    class="sr-only peer"
                                    id="enabled"
                                />
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
                                ></div>
                            </label>
                        </div>

                        <div v-if="form.enabled" class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <!-- Commission Percentage -->
                            <div>
                                <InputLabel for="percentage" value="Commission Percentage (%)" />
                                <div class="mt-1 flex items-center space-x-4">
                                    <input
                                        v-model.number="form.percentage"
                                        type="range"
                                        min="0"
                                        max="100"
                                        step="0.1"
                                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                                        id="percentage"
                                    />
                                    <TextInput
                                        v-model.number="form.percentage"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.1"
                                        class="w-24"
                                        id="percentage-input"
                                    />
                                </div>
                                <InputError :message="form.errors.percentage" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Percentage of order total (0-100%)
                                </p>
                            </div>

                            <!-- Flat Fee -->
                            <div>
                                <InputLabel for="flat_fee" value="Flat Fee (Rp)" />
                                <div class="mt-1">
                                    <TextInput
                                        v-model.number="form.flat_fee"
                                        type="number"
                                        min="0"
                                        step="1000"
                                        class="w-full"
                                        id="flat_fee"
                                        placeholder="0"
                                    />
                                </div>
                                <InputError :message="form.errors.flat_fee" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Fixed fee amount per transaction (in Rupiah)
                                </p>
                            </div>

                            <!-- Warning -->
                            <div
                                v-if="hasWarning"
                                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
                            >
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-5 w-5 text-yellow-400"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            Warning: Current settings may result in commission exceeding small order
                                            amounts. Please review carefully.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Commission Preview -->
                        <div
                            v-if="form.enabled"
                            class="border-t border-gray-200 dark:border-gray-700 pt-6"
                        >
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                Commission Preview
                            </h3>
                            <MarketplaceCommissionPreview
                                :commission-percentage="form.percentage"
                                :commission-flat-fee="form.flat_fee"
                            />
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

