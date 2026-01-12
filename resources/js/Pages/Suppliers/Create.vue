<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { ref } from 'vue';

const props = defineProps({
    businessTypes: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    supplier_name: '',
    supplier_category: '',
    description: '',
    location: '',
    contact_info: {
        phone: '',
        email: '',
        whatsapp: '',
        address: '',
    },
    specialties: [],
    min_order_amount: '',
    delivery_scope: '',
});

const newSpecialty = ref('');

const addSpecialty = () => {
    if (newSpecialty.value.trim() && !form.specialties.includes(newSpecialty.value.trim())) {
        form.specialties.push(newSpecialty.value.trim());
        newSpecialty.value = '';
    }
};

const removeSpecialty = (index) => {
    form.specialties.splice(index, 1);
};

const submit = () => {
    form.post(route('suppliers.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Daftar sebagai Supplier" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Daftar sebagai Supplier
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <!-- Supplier Name -->
                                <div>
                                    <InputLabel for="supplier_name" value="Nama Supplier *" />
                                    <TextInput
                                        id="supplier_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.supplier_name"
                                        required
                                        placeholder="Contoh: Toko Spare Part Motor ABC"
                                    />
                                    <InputError class="mt-2" :message="form.errors.supplier_name" />
                                </div>

                                <!-- Supplier Category -->
                                <div>
                                    <InputLabel for="supplier_category" value="Kategori Supplier *" />
                                    <TextInput
                                        id="supplier_category"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.supplier_category"
                                        required
                                        placeholder="Contoh: spare_part, ban, sticker, beras, susu"
                                    />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Masukkan kategori produk yang Anda jual
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.supplier_category" />
                                </div>

                                <!-- Description -->
                                <div>
                                    <InputLabel for="description" value="Deskripsi *" />
                                    <Textarea
                                        id="description"
                                        class="mt-1 block w-full"
                                        v-model="form.description"
                                        required
                                        rows="5"
                                        placeholder="Jelaskan tentang supplier Anda, produk yang dijual, dan keunggulan..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <!-- Location -->
                                <div>
                                    <InputLabel for="location" value="Lokasi" />
                                    <TextInput
                                        id="location"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.location"
                                        placeholder="Contoh: Jakarta, Bandung, Surabaya"
                                    />
                                    <InputError class="mt-2" :message="form.errors.location" />
                                </div>

                                <!-- Contact Info -->
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <h3 class="text-lg font-medium mb-4">Informasi Kontak *</h3>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <InputLabel for="phone" value="Nomor Telepon *" />
                                            <TextInput
                                                id="phone"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="form.contact_info.phone"
                                                required
                                                placeholder="081234567890"
                                            />
                                            <InputError class="mt-2" :message="form.errors['contact_info.phone']" />
                                        </div>

                                        <div>
                                            <InputLabel for="email" value="Email" />
                                            <TextInput
                                                id="email"
                                                type="email"
                                                class="mt-1 block w-full"
                                                v-model="form.contact_info.email"
                                                placeholder="supplier@example.com"
                                            />
                                            <InputError class="mt-2" :message="form.errors['contact_info.email']" />
                                        </div>

                                        <div>
                                            <InputLabel for="whatsapp" value="WhatsApp" />
                                            <TextInput
                                                id="whatsapp"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="form.contact_info.whatsapp"
                                                placeholder="081234567890"
                                            />
                                            <InputError class="mt-2" :message="form.errors['contact_info.whatsapp']" />
                                        </div>

                                        <div>
                                            <InputLabel for="address" value="Alamat" />
                                            <Textarea
                                                id="address"
                                                class="mt-1 block w-full"
                                                v-model="form.contact_info.address"
                                                rows="3"
                                                placeholder="Alamat lengkap supplier"
                                            />
                                            <InputError class="mt-2" :message="form.errors['contact_info.address']" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Specialties -->
                                <div>
                                    <InputLabel value="Keunggulan/Spesialisasi" />
                                    <div class="mt-2 flex gap-2">
                                        <TextInput
                                            v-model="newSpecialty"
                                            type="text"
                                            class="flex-1"
                                            placeholder="Contoh: harga_murah, kualitas_premium, ready_stock"
                                            @keyup.enter.prevent="addSpecialty"
                                        />
                                        <button
                                            type="button"
                                            @click="addSpecialty"
                                            class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-lg text-sm font-medium"
                                        >
                                            Tambah
                                        </button>
                                    </div>
                                    <div v-if="form.specialties.length > 0" class="flex flex-wrap gap-2 mt-2">
                                        <span
                                            v-for="(specialty, index) in form.specialties"
                                            :key="index"
                                            class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-sm flex items-center gap-2"
                                        >
                                            {{ specialty }}
                                            <button
                                                type="button"
                                                @click="removeSpecialty(index)"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200"
                                            >
                                                ×
                                            </button>
                                        </span>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.specialties" />
                                </div>

                                <!-- Min Order Amount -->
                                <div>
                                    <InputLabel for="min_order_amount" value="Minimum Order (Rp)" />
                                    <TextInput
                                        id="min_order_amount"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.min_order_amount"
                                        min="0"
                                        step="1000"
                                        placeholder="0"
                                    />
                                    <InputError class="mt-2" :message="form.errors.min_order_amount" />
                                </div>

                                <!-- Delivery Scope -->
                                <div>
                                    <InputLabel for="delivery_scope" value="Cakupan Pengiriman" />
                                    <select
                                        id="delivery_scope"
                                        v-model="form.delivery_scope"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="">Pilih cakupan...</option>
                                        <option value="lokal">Lokal</option>
                                        <option value="nasional">Nasional</option>
                                        <option value="internasional">Internasional</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.delivery_scope" />
                                </div>

                                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <PrimaryButton :disabled="form.processing">
                                        Daftar sebagai Supplier
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


