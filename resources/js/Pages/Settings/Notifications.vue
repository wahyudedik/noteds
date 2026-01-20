<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();
const settings = page.props.settings || {};
const notificationPreferences = settings.notification_preferences || {};

const form = useForm({
    notification_preferences: {
        email_notifications: notificationPreferences.email_notifications !== undefined ? notificationPreferences.email_notifications : true,
        in_app_notifications: notificationPreferences.in_app_notifications !== undefined ? notificationPreferences.in_app_notifications : true,
        new_comment_notifications: notificationPreferences.new_comment_notifications !== undefined ? notificationPreferences.new_comment_notifications : true,
        new_follow_notifications: notificationPreferences.new_follow_notifications !== undefined ? notificationPreferences.new_follow_notifications : true,
        order_notifications: notificationPreferences.order_notifications !== undefined ? notificationPreferences.order_notifications : true,
        withdrawal_notifications: notificationPreferences.withdrawal_notifications !== undefined ? notificationPreferences.withdrawal_notifications : true,
        sound_enabled: notificationPreferences.sound_enabled !== undefined ? notificationPreferences.sound_enabled : true,
    },
});

const submit = () => {
    form.post(route('settings.notifications'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Inertia
        },
    });
};
</script>

<template>
    <div>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Notification Preferences
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Choose how you want to be notified about activities on the platform.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <InputLabel for="email_notifications" value="Email Notifications" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Receive notifications via email.
                        </p>
                    </div>
                    <div class="ml-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.notification_preferences.email_notifications"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <InputLabel for="in_app_notifications" value="In-App Notifications" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Receive notifications in the app.
                        </p>
                    </div>
                    <div class="ml-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.notification_preferences.in_app_notifications"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Notification Types
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <InputLabel for="new_comment_notifications" value="New Comments" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Get notified when someone comments on your posts.
                                </p>
                            </div>
                            <div class="ml-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                type="checkbox"
                                v-model="form.notification_preferences.new_comment_notifications"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <InputLabel for="new_follow_notifications" value="New Follows" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Get notified when someone follows you.
                                </p>
                            </div>
                            <div class="ml-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                type="checkbox"
                                v-model="form.notification_preferences.new_follow_notifications"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <InputLabel for="order_notifications" value="Order Notifications" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Get notified about new orders and order updates.
                                </p>
                            </div>
                            <div class="ml-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                type="checkbox"
                                v-model="form.notification_preferences.order_notifications"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <InputLabel for="withdrawal_notifications" value="Withdrawal Notifications" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Get notified about withdrawal requests and status updates.
                                </p>
                            </div>
                            <div class="ml-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                type="checkbox"
                                v-model="form.notification_preferences.withdrawal_notifications"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <InputLabel for="sound_enabled" value="Sound Alerts" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Play a short sound when new in-app notifications arrive.
                        </p>
                    </div>
                    <div class="ml-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.notification_preferences.sound_enabled"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </div>
</template>

