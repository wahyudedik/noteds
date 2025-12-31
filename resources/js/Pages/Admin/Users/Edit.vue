<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    role: props.user.role || 'user',
    business_name: props.user.business_name || '',
    business_field: props.user.business_field || '',
    is_verified_mentor: props.user.is_verified_mentor || false,
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head :title="'Edit User: ' + (user.business_name || user.name)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit User
                </h2>
                <Link
                    :href="route('admin.users.show', user.id)"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm"
                >
                    Back to User
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <form @submit.prevent="submit">
                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="name" value="Name" />
                                        <TextInput
                                            id="name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.name"
                                            required
                                            autofocus
                                        />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>

                                    <div>
                                        <InputLabel for="email" value="Email" />
                                        <TextInput
                                            id="email"
                                            type="email"
                                            class="mt-1 block w-full"
                                            v-model="form.email"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <div>
                                        <InputLabel for="role" value="Role" />
                                        <select
                                            id="role"
                                            v-model="form.role"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.role" />
                                    </div>
                                </div>
                            </div>

                            <!-- Business Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Business Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="business_name" value="Business Name" />
                                        <TextInput
                                            id="business_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.business_name"
                                        />
                                        <InputError class="mt-2" :message="form.errors.business_name" />
                                    </div>

                                    <div>
                                        <InputLabel for="business_field" value="Business Field" />
                                        <TextInput
                                            id="business_field"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.business_field"
                                            placeholder="e.g., Technology, Finance, Healthcare"
                                        />
                                        <InputError class="mt-2" :message="form.errors.business_field" />
                                    </div>
                                </div>
                            </div>

                            <!-- Verification -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Verification</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <Checkbox
                                            id="is_verified_mentor"
                                            v-model:checked="form.is_verified_mentor"
                                        />
                                        <label
                                            for="is_verified_mentor"
                                            class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                        >
                                            Verified Mentor
                                        </label>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.is_verified_mentor" />
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <Link
                                    :href="route('admin.users.show', user.id)"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton :disabled="form.processing">
                                    Update User
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

