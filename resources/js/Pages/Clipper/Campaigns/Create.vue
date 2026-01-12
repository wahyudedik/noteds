<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

const props = defineProps({
    availableBalance: {
        type: [Number, String],
        default: 0,
    },
});

const page = usePage();
const isBrand = computed(() => page.props.auth?.user?.clipper_role === 'brand' || page.props.auth?.user?.role === 'brand');

const selectedTemplateId = ref(props.templateId || null);
const selectedTemplate = computed(() => {
    if (!selectedTemplateId.value) return null;
    return [...(props.templates || []), ...(props.publicTemplates || [])].find(t => t.id === selectedTemplateId.value);
});

const loadTemplate = (template) => {
    if (!template) {
        selectedTemplateId.value = null;
        return;
    }
    selectedTemplateId.value = template.id;
    form.title = template.title || '';
    form.description = template.description || '';
    form.video_references = template.video_references || [{ url: '', title: '' }];
    form.cpm = template.cpm || '';
    form.max_budget = template.max_budget || '';
    form.max_reward_per_clipper = template.max_reward_per_clipper || '';
    form.duration_days = template.duration_days || '';
};

// Load template on mount if templateId is provided
onMounted(() => {
    if (props.templateId && selectedTemplate.value) {
        loadTemplate();
    }
});

// Watch for template selection changes
watch(selectedTemplateId, () => {
    if (selectedTemplateId.value) {
        loadTemplate();
    }
});

const form = useForm({
    template_id: props.templateId || null,
    title: '',
    description: '',
    video_references: [{ url: '', title: '' }],
    cpm: '',
    max_budget: '',
    max_reward_per_clipper: '',
    duration_days: '',
    scheduled_start_at: null,
    scheduled_end_at: null,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const budgetExceedsBalance = computed(() => {
    const budget = parseFloat(form.max_budget) || 0;
    const available = Number(props.availableBalance) || 0;
    return budget > available;
});

const canSubmit = computed(() => {
    return !budgetExceedsBalance.value && !form.processing && hasValidVideoReferences.value;
});

// Video URL validation
const validateVideoUrl = (url) => {
    if (!url) return { valid: false, type: null };
    
    const youtubePattern = /^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)/i;
    const drivePattern = /^(https?:\/\/)?(drive|docs)\.google\.com\/(file\/d\/|open\?id=|file\/d\/)/i;
    
    if (youtubePattern.test(url)) {
        return { valid: true, type: 'youtube' };
    }
    if (drivePattern.test(url)) {
        return { valid: true, type: 'google_drive' };
    }
    
    return { valid: false, type: null };
};

const videoValidations = ref({});

const validateVideoReference = (index) => {
    const videoRef = form.video_references[index];
    if (!videoRef || !videoRef.url) {
        videoValidations.value[index] = { valid: false, type: null };
        return;
    }
    const validation = validateVideoUrl(videoRef.url);
    videoValidations.value[index] = validation;
    return validation;
};

const hasValidVideoReferences = computed(() => {
    if (!form.video_references || form.video_references.length === 0) {
        return false;
    }
    return form.video_references.every((ref, index) => {
        const validation = validateVideoReference(index);
        return validation && validation.valid && ref.url && ref.url.trim() !== '';
    });
});

const addVideoReference = () => {
    form.video_references.push({ url: '', title: '' });
};

const removeVideoReference = (index) => {
    if (form.video_references.length > 1) {
        form.video_references.splice(index, 1);
        delete videoValidations.value[index];
        // Re-index validations
        const newValidations = {};
        form.video_references.forEach((_, i) => {
            if (videoValidations.value[i] !== undefined) {
                newValidations[i] = videoValidations.value[i];
            }
        });
        videoValidations.value = newValidations;
    }
};

const getYouTubeThumbnail = (url) => {
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/);
    if (match) {
        return `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`;
    }
    return null;
};

const submit = () => {
    if (!canSubmit.value) return;
    
    // Ensure all video references have type
    form.video_references = form.video_references.map(ref => {
        const validation = validateVideoUrl(ref.url);
        return {
            url: ref.url.trim(),
            title: ref.title?.trim() || null,
            type: validation.type,
        };
    }).filter(ref => ref.url !== '');
    
    // Set template_id
    form.template_id = selectedTemplateId.value;
    
    // Convert datetime-local to ISO format if set
    if (form.scheduled_start_at) {
        form.scheduled_start_at = new Date(form.scheduled_start_at).toISOString();
    }
    if (form.scheduled_end_at) {
        form.scheduled_end_at = new Date(form.scheduled_end_at).toISOString();
    }
    
    form.post(route('clipper.campaigns.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Campaign" />

    <ClipperLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('clipper.campaigns.index')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Create Campaign
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Alert if not brand -->
                <div v-if="!isBrand" class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Brand Registration Required
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>You must register as a brand first to create campaigns.</p>
                            </div>
                            <div class="mt-4">
                                <Link
                                    :href="route('clipper.brand-registration.create')"
                                    class="text-sm font-medium text-yellow-800 dark:text-yellow-200 hover:text-yellow-900 dark:hover:text-yellow-100 underline"
                                >
                                    Register as Brand →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" v-if="isBrand">
                            <div class="space-y-6">
                                <!-- Template Selector -->
                                <div v-if="(templates && templates.length > 0) || (publicTemplates && publicTemplates.length > 0)">
                                    <InputLabel for="template" value="Start from Template (Optional)" />
                                    <select
                                        id="template"
                                        v-model="selectedTemplateId"
                                        @change="loadTemplate(selectedTemplate)"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option :value="null">Create from scratch</option>
                                        <optgroup v-if="templates && templates.length > 0" label="My Templates">
                                            <option v-for="template in templates" :key="template.id" :value="template.id">
                                                {{ template.name }}
                                            </option>
                                        </optgroup>
                                        <optgroup v-if="publicTemplates && publicTemplates.length > 0" label="Public Templates">
                                            <option v-for="template in publicTemplates" :key="template.id" :value="template.id">
                                                {{ template.name }} (Public)
                                            </option>
                                        </optgroup>
                                    </select>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Select a template to pre-fill the campaign form
                                    </p>
                                </div>

                                <!-- Title -->
                                <div>
                                    <InputLabel for="title" value="Campaign Title" />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.title"
                                        required
                                        autofocus
                                        placeholder="Enter campaign title"
                                    />
                                    <InputError class="mt-2" :message="form.errors.title" />
                                </div>

                                <!-- Description -->
                                <div>
                                    <InputLabel for="description" value="Description" />
                                    <Textarea
                                        id="description"
                                        class="mt-1 block w-full"
                                        v-model="form.description"
                                        required
                                        rows="5"
                                        placeholder="Describe your campaign..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <!-- Video References -->
                                <div>
                                    <InputLabel value="Video References *" />
                                    <p class="mt-1 mb-3 text-sm text-gray-500 dark:text-gray-400">
                                        Add YouTube or Google Drive links to the videos that clippers should reference when creating clips. At least one video reference is required.
                                    </p>
                                    
                                    <div class="space-y-4">
                                        <div
                                            v-for="(videoRef, index) in form.video_references"
                                            :key="index"
                                            class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50"
                                        >
                                            <div class="flex justify-between items-start mb-3">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Video Reference #{{ index + 1 }}
                                                </span>
                                                <button
                                                    v-if="form.video_references.length > 1"
                                                    type="button"
                                                    @click="removeVideoReference(index)"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm"
                                                >
                                                    Remove
                                                </button>
                                            </div>

                                            <!-- URL Input -->
                                            <div class="mb-3">
                                                <InputLabel :for="`video_url_${index}`" value="Video URL *" />
                                                <div class="mt-1 relative">
                                                    <TextInput
                                                        :id="`video_url_${index}`"
                                                        type="url"
                                                        class="block w-full pr-10"
                                                        :class="{
                                                            'border-green-500': videoValidations[index]?.valid,
                                                            'border-red-500': videoValidations[index] && !videoValidations[index].valid && videoRef.url
                                                        }"
                                                        v-model="videoRef.url"
                                                        @input="validateVideoReference(index)"
                                                        @blur="validateVideoReference(index)"
                                                        required
                                                        placeholder="https://www.youtube.com/watch?v=... or https://drive.google.com/file/d/..."
                                                    />
                                                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                                        <span
                                                            v-if="videoValidations[index]?.valid"
                                                            class="text-green-600 dark:text-green-400"
                                                            title="Valid URL"
                                                        >
                                                            ✓
                                                        </span>
                                                        <span
                                                            v-else-if="videoValidations[index] && !videoValidations[index].valid && videoRef.url"
                                                            class="text-red-600 dark:text-red-400"
                                                            title="Invalid URL - must be YouTube or Google Drive"
                                                        >
                                                            ✗
                                                        </span>
                                                    </div>
                                                </div>
                                                <div v-if="videoValidations[index]?.type === 'youtube' && videoRef.url" class="mt-2">
                                                    <img
                                                        :src="getYouTubeThumbnail(videoRef.url)"
                                                        alt="YouTube thumbnail"
                                                        class="w-32 h-20 object-cover rounded border border-gray-300 dark:border-gray-600"
                                                        @error="$event.target.style.display='none'"
                                                    />
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    <span v-if="videoValidations[index]?.type === 'youtube'">✓ YouTube link detected</span>
                                                    <span v-else-if="videoValidations[index]?.type === 'google_drive'">✓ Google Drive link detected</span>
                                                    <span v-else-if="videoRef.url && !videoValidations[index]?.valid">✗ Must be a YouTube or Google Drive URL</span>
                                                    <span v-else>Enter a YouTube or Google Drive URL</span>
                                                </p>
                                                <InputError class="mt-2" :message="form.errors[`video_references.${index}.url`]" />
                                            </div>

                                            <!-- Title Input (Optional) -->
                                            <div>
                                                <InputLabel :for="`video_title_${index}`" value="Title (Optional)" />
                                                <TextInput
                                                    :id="`video_title_${index}`"
                                                    type="text"
                                                    class="mt-1 block w-full"
                                                    v-model="videoRef.title"
                                                    placeholder="e.g., Main Video Reference, Additional Reference"
                                                />
                                                <InputError class="mt-2" :message="form.errors[`video_references.${index}.title`]" />
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            @click="addVideoReference"
                                            class="w-full px-4 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
                                        >
                                            + Add Another Video Reference
                                        </button>
                                    </div>
                                    
                                    <InputError class="mt-2" :message="form.errors.video_references" />
                                </div>

                                <!-- CPM (Cost Per Mille) -->
                                <div>
                                    <InputLabel for="cpm" value="CPM (Cost Per 1000 Views)" />
                                    <div class="mt-1 relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                        <TextInput
                                            id="cpm"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full pl-10"
                                            v-model="form.cpm"
                                            required
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Amount you'll pay per 1000 views
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.cpm" />
                                </div>

                                <!-- Available Balance Info -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Available Balance:</span>
                                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                            Rp {{ formatCurrency(Number(availableBalance) || 0) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Max Budget -->
                                <div>
                                    <InputLabel for="max_budget" value="Maximum Budget" />
                                    <div class="mt-1 relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                        <TextInput
                                            id="max_budget"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full pl-10"
                                            :class="budgetExceedsBalance ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                            v-model="form.max_budget"
                                            required
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Total budget for this campaign (will be locked in escrow)
                                    </p>
                                    <div v-if="budgetExceedsBalance" class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                        <p class="text-sm text-red-800 dark:text-red-200">
                                            <strong>Warning:</strong> Budget exceeds available balance. Please top up your wallet or reduce the budget.
                                        </p>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.max_budget" />
                                </div>

                                <!-- Max Reward Per Clipper -->
                                <div>
                                    <InputLabel for="max_reward_per_clipper" value="Max Reward Per Clipper (Optional)" />
                                    <div class="mt-1 relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                        <TextInput
                                            id="max_reward_per_clipper"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full pl-10"
                                            v-model="form.max_reward_per_clipper"
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Maximum reward a single clipper can earn from this campaign (leave empty for no limit)
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.max_reward_per_clipper" />
                                </div>

                                <!-- Duration -->
                                <div>
                                    <InputLabel for="duration_days" value="Campaign Duration (Days)" />
                                    <TextInput
                                        id="duration_days"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full"
                                        v-model="form.duration_days"
                                        required
                                        placeholder="30"
                                    />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        How long this campaign will run
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.duration_days" />
                                </div>

                                <!-- Scheduling (Optional) -->
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Schedule Campaign (Optional)</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <InputLabel for="scheduled_start_at" value="Scheduled Start Date & Time" />
                                            <input
                                                id="scheduled_start_at"
                                                type="datetime-local"
                                                v-model="form.scheduled_start_at"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                :min="new Date().toISOString().slice(0, 16)"
                                            />
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                Campaign will automatically start at this time
                                            </p>
                                            <InputError class="mt-2" :message="form.errors.scheduled_start_at" />
                                        </div>
                                        <div>
                                            <InputLabel for="scheduled_end_at" value="Scheduled End Date & Time (Optional)" />
                                            <input
                                                id="scheduled_end_at"
                                                type="datetime-local"
                                                v-model="form.scheduled_end_at"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                :min="form.scheduled_start_at || new Date().toISOString().slice(0, 16)"
                                            />
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                Campaign will automatically end at this time
                                            </p>
                                            <InputError class="mt-2" :message="form.errors.scheduled_end_at" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex items-center justify-end gap-4">
                                    <Link
                                        :href="route('clipper.campaigns.index')"
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        Cancel
                                    </Link>
                                    <PrimaryButton :disabled="!canSubmit">
                                        Create Campaign
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

