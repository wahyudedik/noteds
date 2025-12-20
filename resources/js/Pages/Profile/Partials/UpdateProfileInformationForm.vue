<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    business_name: user.business_name || '',
    business_field: user.business_field || '',
    skills: user.skills || [],
    goals: user.goals || [],
    portfolio_url: user.portfolio_url || '',
    website_url: user.website_url || '',
    is_verified_mentor: user.is_verified_mentor || false,
});

const newSkill = ref('');
const newGoal = ref('');

const addSkill = () => {
    if (newSkill.value.trim() && !form.skills.includes(newSkill.value.trim())) {
        form.skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
};

const removeSkill = (index) => {
    form.skills.splice(index, 1);
};

const addGoal = () => {
    if (newGoal.value.trim() && !form.goals.includes(newGoal.value.trim())) {
        form.goals.push(newGoal.value.trim());
        newGoal.value = '';
    }
};

const removeGoal = (index) => {
    form.goals.splice(index, 1);
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information, business details, and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
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

            <div>
                <InputLabel for="business_field" value="Business Field" />

                <TextInput
                    id="business_field"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.business_field"
                    placeholder="e.g., E-commerce, Technology, Consulting"
                />

                <InputError class="mt-2" :message="form.errors.business_field" />
            </div>

            <div>
                <InputLabel for="skills" value="Skills" />

                <div class="mt-1 flex gap-2">
                    <TextInput
                        id="new_skill"
                        type="text"
                        class="block flex-1"
                        v-model="newSkill"
                        placeholder="Add a skill"
                        @keyup.enter.prevent="addSkill"
                    />
                    <PrimaryButton type="button" @click="addSkill">Add</PrimaryButton>
                </div>

                <div v-if="form.skills.length > 0" class="mt-2 flex flex-wrap gap-2">
                    <span
                        v-for="(skill, index) in form.skills"
                        :key="index"
                        class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-sm text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200"
                    >
                        {{ skill }}
                        <button
                            type="button"
                            @click="removeSkill(index)"
                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200"
                        >
                            ×
                        </button>
                    </span>
                </div>

                <InputError class="mt-2" :message="form.errors.skills" />
            </div>

            <div>
                <InputLabel for="goals" value="Goals" />

                <div class="mt-1 flex gap-2">
                    <TextInput
                        id="new_goal"
                        type="text"
                        class="block flex-1"
                        v-model="newGoal"
                        placeholder="Add a goal"
                        @keyup.enter.prevent="addGoal"
                    />
                    <PrimaryButton type="button" @click="addGoal">Add</PrimaryButton>
                </div>

                <div v-if="form.goals.length > 0" class="mt-2 space-y-1">
                    <div
                        v-for="(goal, index) in form.goals"
                        :key="index"
                        class="flex items-center justify-between rounded-md bg-gray-100 px-3 py-2 text-sm dark:bg-gray-700"
                    >
                        <span>{{ goal }}</span>
                        <button
                            type="button"
                            @click="removeGoal(index)"
                            class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <InputError class="mt-2" :message="form.errors.goals" />
            </div>

            <div>
                <InputLabel for="portfolio_url" value="Portfolio URL" />

                <TextInput
                    id="portfolio_url"
                    type="url"
                    class="mt-1 block w-full"
                    v-model="form.portfolio_url"
                    placeholder="https://yourportfolio.com"
                />

                <InputError class="mt-2" :message="form.errors.portfolio_url" />
            </div>

            <div>
                <InputLabel for="website_url" value="Website URL" />

                <TextInput
                    id="website_url"
                    type="url"
                    class="mt-1 block w-full"
                    v-model="form.website_url"
                    placeholder="https://yourwebsite.com"
                />

                <InputError class="mt-2" :message="form.errors.website_url" />
            </div>

            <div class="flex items-center">
                <Checkbox
                    id="is_verified_mentor"
                    v-model:checked="form.is_verified_mentor"
                />
                <InputLabel for="is_verified_mentor" value="Verified Mentor" class="ml-2" />
            </div>

            <InputError class="mt-2" :message="form.errors.is_verified_mentor" />

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

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
    </section>
</template>
