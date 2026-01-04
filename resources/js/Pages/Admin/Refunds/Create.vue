<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import UserSearch from '@/Components/Admin/UserSearch.vue';

const form = useForm({
    user_id: '',
    wallet_type: 'creator',
    type: 'refund',
    amount: '',
    reason: '',
    admin_notes: '',
});

const submit = () => {
    form.post(route('admin.refunds.store'));
};

const selectUser = (user) => {
    form.user_id = user.id;
};
</script>

<template>
    <Head title="Create Refund" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Create Refund/Adjustment
                </h2>
                <Link
                    :href="route('admin.refunds.index')"
                    class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- User Search -->
                        <div>
                            <InputLabel for="user_id" value="User *" />
                            <UserSearch @user-selected="selectUser" />
                            <InputError class="mt-2" :message="form.errors.user_id" />
                        </div>

                        <!-- Wallet Type -->
                        <div>
                            <InputLabel value="Wallet Type *" />
                            <div class="mt-2 flex gap-4">
                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        v-model="form.wallet_type"
                                        value="creator"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Creator</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        v-model="form.wallet_type"
                                        value="marketplace"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Marketplace</span>
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.wallet_type" />
                        </div>

                        <!-- Type -->
                        <div>
                            <InputLabel value="Type *" />
                            <div class="mt-2 flex gap-4">
                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        v-model="form.type"
                                        value="refund"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Refund (Add Balance)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input
                                        type="radio"
                                        v-model="form.type"
                                        value="adjustment"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Adjustment (Deduct Balance)</span>
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.type" />
                        </div>

                        <!-- Amount -->
                        <div>
                            <InputLabel for="amount" value="Amount *" />
                            <TextInput
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>

                        <!-- Reason -->
                        <div>
                            <InputLabel for="reason" value="Reason (Optional)" />
                            <Textarea
                                id="reason"
                                v-model="form.reason"
                                class="mt-1 block w-full"
                                rows="3"
                            />
                            <InputError class="mt-2" :message="form.errors.reason" />
                        </div>

                        <!-- Admin Notes -->
                        <div>
                            <InputLabel for="admin_notes" value="Admin Notes (Optional)" />
                            <Textarea
                                id="admin_notes"
                                v-model="form.admin_notes"
                                class="mt-1 block w-full"
                                rows="3"
                            />
                            <InputError class="mt-2" :message="form.errors.admin_notes" />
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('admin.refunds.index')"
                                class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                Create {{ form.type === 'refund' ? 'Refund' : 'Adjustment' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

