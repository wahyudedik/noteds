<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-indigo-50 px-4 py-12 dark:from-gray-900 dark:to-gray-800 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="text-center">
                    <Link :href="route('home')" class="inline-flex items-center gap-2">
                        <ApplicationLogo class="h-12 w-auto" />
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">Noteds</span>
                    </Link>
                    <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Welcome back
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Sign in to your account to continue
                    </p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="mt-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/50 dark:text-green-200">
                    {{ status }}
                </div>

                <!-- Login Form -->
                <div class="mt-8 rounded-xl bg-white shadow-lg dark:bg-gray-800">
                    <div class="px-6 py-8 sm:px-10">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="email" value="Email address" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="you@example.com"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <InputLabel for="password" value="Password" />
                                    <Link
                                        v-if="canResetPassword"
                                        :href="route('password.request')"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >
                                        Forgot password?
                                    </Link>
                                </div>
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <label class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                    Remember me
                                </label>
                            </div>

                            <div>
                                <PrimaryButton
                                    class="w-full"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Signing in...' : 'Sign in' }}
                                </PrimaryButton>
                            </div>
                        </form>

                        <div class="mt-6 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Don't have an account?
                                <Link
                                    :href="route('register')"
                                    class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                                >
                                    Sign up
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
