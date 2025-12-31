<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    clipperProfile: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['cancel', 'updated']);

const form = useForm({
    platform: props.clipperProfile?.platform || 'tiktok',
    platform_username: props.clipperProfile?.platform_username || '',
    follower_count: props.clipperProfile?.follower_count || '',
    portfolio_url: props.clipperProfile?.portfolio_url || '',
    bio: props.clipperProfile?.bio || '',
    portfolio_items: props.clipperProfile?.portfolio_items || [],
});

const platforms = [
    { value: 'tiktok', label: 'TikTok' },
    { value: 'instagram', label: 'Instagram' },
    { value: 'youtube', label: 'YouTube' },
    { value: 'other', label: 'Other' },
];

const portfolioItem = ref({
    platform: 'tiktok',
    url: '',
    description: '',
});

const addPortfolioItem = () => {
    if (portfolioItem.value.url.trim()) {
        form.portfolio_items.push({
            platform: portfolioItem.value.platform,
            url: portfolioItem.value.url.trim(),
            description: portfolioItem.value.description.trim(),
        });
        portfolioItem.value = {
            platform: 'tiktok',
            url: '',
            description: '',
        };
    }
};

const removePortfolioItem = (index) => {
    form.portfolio_items.splice(index, 1);
};

const submit = () => {
    const routeName = props.clipperProfile 
        ? 'clipper.profile.update' 
        : 'clipper.profile.store';
    
    const routeParams = props.clipperProfile 
        ? { profile: props.clipperProfile.id }
        : {};
    
    form.put(route(routeName, routeParams), {
        onSuccess: () => {
            emit('updated');
        },
    });
};

const cancel = () => {
    emit('cancel');
};
</script>

<template>
    <div>
        <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700 mb-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ clipperProfile ? 'Edit Clipper Profile' : 'Setup Clipper Profile' }}
            </h2>
            <button
                @click="cancel"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm"
            >
                Cancel
            </button>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Primary Platform -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                    Primary Platform
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel for="platform" value="Main Platform *" />
                        <select
                            id="platform"
                            v-model="form.platform"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option v-for="platform in platforms" :key="platform.value" :value="platform.value">
                                {{ platform.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.platform" />
                    </div>

                    <div>
                        <InputLabel for="platform_username" value="Platform Username *" />
                        <TextInput
                            id="platform_username"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.platform_username"
                            required
                            :placeholder="`Your ${form.platform} username`"
                        />
                        <InputError class="mt-2" :message="form.errors.platform_username" />
                    </div>

                    <div>
                        <InputLabel for="follower_count" value="Follower Count *" />
                        <TextInput
                            id="follower_count"
                            type="number"
                            min="0"
                            class="mt-1 block w-full"
                            v-model="form.follower_count"
                            required
                            placeholder="e.g., 10000"
                        />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Current follower count on {{ form.platform }}
                        </p>
                        <InputError class="mt-2" :message="form.errors.follower_count" />
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                    About You
                </h3>
                
                <div>
                    <InputLabel for="bio" value="Bio" />
                    <Textarea
                        id="bio"
                        class="mt-1 block w-full"
                        v-model="form.bio"
                        rows="4"
                        placeholder="Tell brands about yourself and your content style..."
                    />
                    <InputError class="mt-2" :message="form.errors.bio" />
                </div>
            </div>

            <!-- Portfolio -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                    Portfolio
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel for="portfolio_url" value="Portfolio URL" />
                        <TextInput
                            id="portfolio_url"
                            type="url"
                            class="mt-1 block w-full"
                            v-model="form.portfolio_url"
                            placeholder="https://your-portfolio.com"
                        />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Link to your portfolio website or profile
                        </p>
                        <InputError class="mt-2" :message="form.errors.portfolio_url" />
                    </div>

                    <!-- Portfolio Items -->
                    <div>
                        <InputLabel value="Portfolio Items" />
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                            Add links to your best content pieces
                        </p>

                        <div class="space-y-3 mb-4">
                            <div
                                v-for="(item, index) in form.portfolio_items"
                                :key="index"
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 flex justify-between items-start"
                            >
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded capitalize">
                                            {{ item.platform }}
                                        </span>
                                    </div>
                                    <a
                                        :href="item.url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline break-all block mb-1"
                                    >
                                        {{ item.url }}
                                    </a>
                                    <p v-if="item.description" class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ item.description }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="removePortfolioItem(index)"
                                    class="ml-4 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Add Portfolio Item Form -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                            <div>
                                <InputLabel for="portfolio_platform" value="Platform" />
                                <select
                                    id="portfolio_platform"
                                    v-model="portfolioItem.platform"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option v-for="platform in platforms" :key="platform.value" :value="platform.value">
                                        {{ platform.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="portfolio_url_input" value="Content URL *" />
                                <TextInput
                                    id="portfolio_url_input"
                                    type="url"
                                    class="mt-1 block w-full"
                                    v-model="portfolioItem.url"
                                    placeholder="https://..."
                                />
                            </div>
                            <div>
                                <InputLabel for="portfolio_description" value="Description" />
                                <TextInput
                                    id="portfolio_description"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="portfolioItem.description"
                                    placeholder="Brief description of this content..."
                                />
                            </div>
                            <button
                                type="button"
                                @click="addPortfolioItem"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Add to Portfolio
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    @click="cancel"
                    class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                >
                    Cancel
                </button>
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save Profile' }}
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>

