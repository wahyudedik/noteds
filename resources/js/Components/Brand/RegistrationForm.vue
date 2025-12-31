<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    isEdit: {
        type: Boolean,
        default: false,
    },
    brandRegistration: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['cancel', 'submitted']);

const form = useForm({
    company_name: props.brandRegistration?.company_name || '',
    business_type: props.brandRegistration?.business_type || '',
    contact_name: props.brandRegistration?.contact_name || '',
    contact_email: props.brandRegistration?.contact_email || '',
    contact_phone: props.brandRegistration?.contact_phone || '',
    website_url: props.brandRegistration?.website_url || '',
    description: props.brandRegistration?.description || '',
});

const businessTypes = [
    { value: 'brand', label: 'Brand' },
    { value: 'influencer', label: 'Influencer' },
    { value: 'agency', label: 'Agency' },
    { value: 'other', label: 'Other' },
];

const submit = () => {
    const routeName = props.isEdit && props.brandRegistration
        ? 'clipper.brand-registration.update'
        : 'clipper.brand-registration.store';
    
    const routeParams = props.isEdit && props.brandRegistration
        ? { registration: props.brandRegistration.id }
        : {};
    
    const method = props.isEdit ? 'put' : 'post';
    
    form[method](route(routeName, routeParams), {
        onSuccess: () => {
            emit('submitted');
        },
    });
};

const cancel = () => {
    emit('cancel');
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Company Information -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                Company Information
            </h3>
            
            <div class="space-y-4">
                <div>
                    <InputLabel for="company_name" value="Company/Brand Name *" />
                    <TextInput
                        id="company_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.company_name"
                        required
                        placeholder="Your company or brand name"
                    />
                    <InputError class="mt-2" :message="form.errors.company_name" />
                </div>

                <div>
                    <InputLabel for="business_type" value="Business Type *" />
                    <select
                        id="business_type"
                        v-model="form.business_type"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Select business type</option>
                        <option v-for="type in businessTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.business_type" />
                </div>

                <div>
                    <InputLabel for="website_url" value="Website URL" />
                    <TextInput
                        id="website_url"
                        type="url"
                        class="mt-1 block w-full"
                        v-model="form.website_url"
                        placeholder="https://your-website.com"
                    />
                    <InputError class="mt-2" :message="form.errors.website_url" />
                </div>

                <div>
                    <InputLabel for="description" value="Description" />
                    <Textarea
                        id="description"
                        class="mt-1 block w-full"
                        v-model="form.description"
                        rows="4"
                        placeholder="Tell us about your brand and what kind of campaigns you're looking for..."
                    />
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                Contact Information
            </h3>
            
            <div class="space-y-4">
                <div>
                    <InputLabel for="contact_name" value="Contact Name *" />
                    <TextInput
                        id="contact_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.contact_name"
                        required
                        placeholder="Your full name"
                    />
                    <InputError class="mt-2" :message="form.errors.contact_name" />
                </div>

                <div>
                    <InputLabel for="contact_email" value="Contact Email *" />
                    <TextInput
                        id="contact_email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.contact_email"
                        required
                        placeholder="contact@example.com"
                    />
                    <InputError class="mt-2" :message="form.errors.contact_email" />
                </div>

                <div>
                    <InputLabel for="contact_phone" value="Contact Phone" />
                    <TextInput
                        id="contact_phone"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.contact_phone"
                        placeholder="+62 812-3456-7890"
                    />
                    <InputError class="mt-2" :message="form.errors.contact_phone" />
                </div>
            </div>
        </div>

        <!-- Info Message -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-medium mb-1">Registration Review</p>
                    <p>Your brand registration will be reviewed by our admin team. You'll receive a notification once your registration is approved or rejected.</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
                v-if="!isEdit"
                type="button"
                @click="cancel"
                class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
            >
                Cancel
            </button>
            <PrimaryButton :disabled="form.processing">
                {{ form.processing ? 'Submitting...' : (isEdit ? 'Update Registration' : 'Submit Registration') }}
            </PrimaryButton>
        </div>
    </form>
</template>

