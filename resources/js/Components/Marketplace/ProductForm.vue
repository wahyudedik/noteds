<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    name: props.product?.name || '',
    description: props.product?.description || '',
    price: props.product?.price || '',
    category: props.product?.category || '',
    image: null,
    file_download: null,
    license_key: props.product?.license_key ? true : false,
    stock: props.product?.stock || null,
    is_active: props.product?.is_active ?? true,
});

const isEdit = computed(() => !!props.product);

const submit = () => {
    if (isEdit.value) {
        form.put(route('marketplace.products.update', props.product.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('marketplace.products.store'), {
            forceFormData: true,
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-2">Product Name *</label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Description *</label>
                <textarea
                    v-model="form.description"
                    required
                    rows="5"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                ></textarea>
                <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                    {{ form.errors.description }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Price (Rp) *</label>
                    <input
                        v-model.number="form.price"
                        type="number"
                        min="0"
                        step="1000"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    />
                    <div v-if="form.errors.price" class="text-red-500 text-sm mt-1">
                        {{ form.errors.price }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <input
                        v-model="form.category"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Product Image</label>
                <input
                    @input="form.image = $event.target.files[0]"
                    type="file"
                    accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
                <div v-if="product?.image" class="mt-2">
                    <img :src="product.image" alt="Current image" class="h-32 object-cover rounded" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Digital File</label>
                <input
                    @input="form.file_download = $event.target.files[0]"
                    type="file"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
                <p class="text-sm text-gray-500 mt-1">Upload the digital product file</p>
            </div>

            <div>
                <label class="flex items-center space-x-2">
                    <input
                        v-model="form.license_key"
                        type="checkbox"
                        class="rounded"
                    />
                    <span>Requires License Key</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Stock (optional, leave empty for unlimited)</label>
                <input
                    v-model.number="form.stock"
                    type="number"
                    min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
            </div>

            <div v-if="isEdit">
                <label class="flex items-center space-x-2">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded"
                    />
                    <span>Active</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                <Link
                    :href="isEdit ? route('marketplace.products.show', product.id) : route('marketplace.index')"
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ isEdit ? 'Update' : 'Create' }} Product
                </button>
            </div>
        </div>
    </form>
</template>

