<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { setTheme, getStoredTheme, systemPrefersDark } from '@/Utils/theme';
import { ref } from 'vue';

const page = usePage();
const settings = page.props.settings || {};

const form = useForm({
    profile_visibility: settings.profile_visibility !== undefined ? settings.profile_visibility : true,
    search_visibility: settings.search_visibility !== undefined ? settings.search_visibility : true,
    privacy_settings: {
        posts_visibility: 'public',
        comments_permission: 'everyone',
        messaging_permission: 'everyone',
        profile_visibility: 'public',
        activity_visibility: 'public',
        sharing: {
            analytics: true,
            marketing: false,
            recommendations: true,
        },
        theme: 'system',
        ...(settings.privacy_settings || {}),
        sharing: {
            analytics: true,
            marketing: false,
            recommendations: true,
            ...((settings.privacy_settings && settings.privacy_settings.sharing) || {}),
        },
    },
});
const themeMode = {
    get value() {
        const s = getStoredTheme();
        return s ? s : (systemPrefersDark() ? 'dark' : 'light');
    },
    set value(v) {
        setTheme(v);
        form.privacy_settings = { ...(form.privacy_settings || {}), theme: v };
    }
};

const submit = () => {
    form.post(route('settings.privacy'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Inertia
        },
    });
};
const exporting = ref(false);
const deleting = ref(false);
const exportData = async () => {
    exporting.value = true;
    try {
        const res = await fetch(route('gdpr.export'), { credentials: 'include', headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'noteds-user-data.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    } finally {
        exporting.value = false;
    }
};
const deleteAccount = async () => {
    if (!confirm('Penghapusan akun akan permanen. Lanjutkan?')) return;
    deleting.value = true;
    try {
        await fetch(route('gdpr.delete'), { method: 'POST', credentials: 'include', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '' } });
        window.location.href = '/';
    } finally {
        deleting.value = false;
    }
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

            <div class="space-y-2">
                <InputLabel for="theme_pref" value="Theme Preference" />
                <select
                    id="theme_pref"
                    :value="themeMode.value"
                    @change="themeMode.value = $event.target.value"
                    class="border rounded px-3 py-2 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100"
                >
                    <option value="system">System</option>
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Preferensi tema akan disinkronkan sebagai bagian dari pengaturan privasi.
                </p>
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

            <div class="mt-8 border-t pt-6">
                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">GDPR</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="exportData" :disabled="exporting" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 disabled:opacity-50 text-sm">Export My Data</button>
                    <button @click="deleteAccount" :disabled="deleting" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 text-sm">Delete My Account</button>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Ekspor data dalam JSON dan penghapusan akun permanen sesuai GDPR.</p>
            </div>

            <div class="mt-8 border-t pt-6">
                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">Advanced Privacy</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Who can see my posts</label>
                        <select v-model="form.privacy_settings.posts_visibility" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:text-white text-sm">
                            <option value="public">Public</option>
                            <option value="followers">Followers</option>
                            <option value="private">Only Me</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Who can comment on my posts</label>
                        <select v-model="form.privacy_settings.comments_permission" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:text-white text-sm">
                            <option value="everyone">Everyone</option>
                            <option value="followers">Followers</option>
                            <option value="none">No one</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Who can message me</label>
                        <select v-model="form.privacy_settings.messaging_permission" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:text-white text-sm">
                            <option value="everyone">Everyone</option>
                            <option value="followers">Followers</option>
                            <option value="none">No one</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profile visibility</label>
                        <select v-model="form.privacy_settings.profile_visibility" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:text-white text-sm">
                            <option value="public">Public</option>
                            <option value="followers">Followers</option>
                            <option value="private">Only Me</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Activity visibility</label>
                        <select v-model="form.privacy_settings.activity_visibility" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:text-white text-sm">
                            <option value="public">Public</option>
                            <option value="followers">Followers</option>
                            <option value="private">Only Me</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data sharing preferences</label>
                        <div class="space-y-2 text-sm">
                            <label class="flex items-center gap-2"><input type="checkbox" v-model="form.privacy_settings.sharing.analytics" /> Analytics</label>
                            <label class="flex items-center gap-2"><input type="checkbox" v-model="form.privacy_settings.sharing.marketing" /> Marketing</label>
                            <label class="flex items-center gap-2"><input type="checkbox" v-model="form.privacy_settings.sharing.recommendations" /> Personalized recommendations</label>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Kontrol granular untuk visibilitas dan interaksi sesuai preferensi Anda.</p>
            </div>
        </form>
    </div>
</template>

