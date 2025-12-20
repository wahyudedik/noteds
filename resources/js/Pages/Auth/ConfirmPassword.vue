<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-indigo-50 px-4 py-12 dark:from-gray-900 dark:to-gray-800 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="text-center">
                    <Link :href="route('home')" class="inline-flex items-center gap-2">
                        <ApplicationLogo class="h-12 w-auto" />
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">Noteds</span>
                    </Link>
                    <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Confirm password
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        This is a secure area. Please confirm your password before continuing.
                    </p>
                </div>

                <!-- Form -->
                <div class="mt-8 rounded-xl bg-white shadow-lg dark:bg-gray-800">
                    <div class="px-6 py-8 sm:px-10">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    autofocus
                                    placeholder="••••••••"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div>
                                <PrimaryButton
                                    class="w-full"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Confirming...' : 'Confirm' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
