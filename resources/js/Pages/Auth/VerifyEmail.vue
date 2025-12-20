<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-indigo-50 px-4 py-12 dark:from-gray-900 dark:to-gray-800 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="text-center">
                    <Link :href="route('home')" class="inline-flex items-center gap-2">
                        <ApplicationLogo class="h-12 w-auto" />
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">Noteds</span>
                    </Link>
                    <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Verify your email
                    </h2>
                </div>

                <!-- Form -->
                <div class="mt-8 rounded-xl bg-white shadow-lg dark:bg-gray-800">
                    <div class="px-6 py-8 sm:px-10">
                        <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                            Thanks for signing up! Before getting started, could you verify your
                            email address by clicking on the link we just emailed to you?
                        </div>

                        <div
                            v-if="verificationLinkSent"
                            class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/50 dark:text-green-200"
                        >
                            A new verification link has been sent to the email address you
                            provided during registration.
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
                            <PrimaryButton
                                class="w-full"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Sending...' : 'Resend Verification Email' }}
                            </PrimaryButton>
                        </form>

                        <div class="mt-6 text-center">
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
