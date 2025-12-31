<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    company_name: '',
    business_type: '',
    business_registration_number: '',
    address: '',
    city: '',
    province: '',
    postal_code: '',
    phone: '',
    email: '',
    website: '',
    description: '',
    contact_person_name: '',
    contact_person_position: '',
    contact_person_email: '',
    contact_person_phone: '',
});

const businessTypes = [
    'E-commerce',
    'SaaS',
    'Agency',
    'Retail',
    'Manufacturing',
    'Service',
    'Influencer',
    'Content Creator',
    'Other',
];

const submit = () => {
    form.post(route('clipper.brand-registrations.store'), {
        onSuccess: () => {
            // Redirect to show page after successful registration
        },
    });
};
</script>

<template>
    <Head title="Brand Registration" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Brand Registration
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Apply as Brand/Influencer
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Complete the form below to register as a Brand or Influencer. Your application will be reviewed by our admin team. You'll receive a notification once your registration is approved.
                    </p>
                </div>

                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
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
                                    placeholder="Your Company or Brand Name"
                                />
                                <InputError class="mt-2" :message="form.errors.company_name" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="business_type" value="Business Type *" />
                                    <select
                                        id="business_type"
                                        v-model="form.business_type"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Select Business Type</option>
                                        <option v-for="type in businessTypes" :key="type" :value="type">
                                            {{ type }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.business_type" />
                                </div>

                                <div>
                                    <InputLabel for="business_registration_number" value="Business Registration Number" />
                                    <TextInput
                                        id="business_registration_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.business_registration_number"
                                        placeholder="Optional"
                                    />
                                    <InputError class="mt-2" :message="form.errors.business_registration_number" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="description" value="Company Description *" />
                                <Textarea
                                    id="description"
                                    class="mt-1 block w-full"
                                    v-model="form.description"
                                    required
                                    rows="4"
                                    placeholder="Describe your company, brand, or business..."
                                />
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Address Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="address" value="Street Address *" />
                                <TextInput
                                    id="address"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.address"
                                    required
                                    placeholder="Street address"
                                />
                                <InputError class="mt-2" :message="form.errors.address" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="city" value="City *" />
                                    <TextInput
                                        id="city"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.city"
                                        required
                                        placeholder="City"
                                    />
                                    <InputError class="mt-2" :message="form.errors.city" />
                                </div>

                                <div>
                                    <InputLabel for="province" value="Province *" />
                                    <TextInput
                                        id="province"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.province"
                                        required
                                        placeholder="Province"
                                    />
                                    <InputError class="mt-2" :message="form.errors.province" />
                                </div>

                                <div>
                                    <InputLabel for="postal_code" value="Postal Code *" />
                                    <TextInput
                                        id="postal_code"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.postal_code"
                                        required
                                        placeholder="Postal code"
                                    />
                                    <InputError class="mt-2" :message="form.errors.postal_code" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Contact Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="phone" value="Phone Number *" />
                                    <TextInput
                                        id="phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.phone"
                                        required
                                        placeholder="+62 xxx xxxx xxxx"
                                    />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>

                                <div>
                                    <InputLabel for="email" value="Company Email *" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.email"
                                        required
                                        placeholder="contact@company.com"
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="website" value="Website" />
                                <TextInput
                                    id="website"
                                    type="url"
                                    class="mt-1 block w-full"
                                    v-model="form.website"
                                    placeholder="https://www.example.com"
                                />
                                <InputError class="mt-2" :message="form.errors.website" />
                            </div>
                        </div>
                    </div>

                    <!-- Contact Person -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Contact Person
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="contact_person_name" value="Contact Person Name *" />
                                    <TextInput
                                        id="contact_person_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.contact_person_name"
                                        required
                                        placeholder="Full name"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_person_name" />
                                </div>

                                <div>
                                    <InputLabel for="contact_person_position" value="Position *" />
                                    <TextInput
                                        id="contact_person_position"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.contact_person_position"
                                        required
                                        placeholder="e.g., Marketing Manager"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_person_position" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="contact_person_email" value="Contact Person Email *" />
                                    <TextInput
                                        id="contact_person_email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.contact_person_email"
                                        required
                                        placeholder="person@company.com"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_person_email" />
                                </div>

                                <div>
                                    <InputLabel for="contact_person_phone" value="Contact Person Phone *" />
                                    <TextInput
                                        id="contact_person_phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.contact_person_phone"
                                        required
                                        placeholder="+62 xxx xxxx xxxx"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_person_phone" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <Link
                            :href="route('dashboard')"
                            class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Submitting...' : 'Submit Registration' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

