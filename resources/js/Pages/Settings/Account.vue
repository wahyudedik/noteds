<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    business_name: user.business_name || '',
});

const submit = () => {
    form.post(route('settings.account'), {
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
                Account Settings
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account information and email address.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
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
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="business_name" value="Business Name" />
                <TextInput
                    id="business_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.business_name"
                    autocomplete="organization"
                />
                <InputError class="mt-2" :message="form.errors.business_name" />
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

