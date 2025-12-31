<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const page = usePage();
const settings = page.props.settings || {};

const form = useForm({
    profile_visibility: settings.profile_visibility !== undefined ? settings.profile_visibility : true,
    search_visibility: settings.search_visibility !== undefined ? settings.search_visibility : true,
    privacy_settings: settings.privacy_settings || {},
});

const submit = () => {
    form.post(route('settings.privacy'), {
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
                Privacy Settings
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Control who can see your profile and find you on the platform.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <InputLabel for="profile_visibility" value="Profile Visibility" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Make your profile visible to everyone or keep it private.
                        </p>
                    </div>
                    <div class="ml-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.profile_visibility"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.profile_visibility" />

                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <InputLabel for="search_visibility" value="Search Visibility" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Allow others to find you in search results.
                        </p>
                    </div>
                    <div class="ml-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.search_visibility"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.search_visibility" />
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

