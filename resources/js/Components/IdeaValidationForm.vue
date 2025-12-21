<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    postId: String,
    userValidation: Object,
});

const newRisk = ref('');
const risks = ref(props.userValidation?.risks || []);

const form = useForm({
    validation_status: props.userValidation?.validation_status || '',
    estimated_capital: props.userValidation?.estimated_capital || '',
    estimated_bep: props.userValidation?.estimated_bep || '',
    feedback: props.userValidation?.feedback || '',
    risks: risks.value,
});

const addRisk = () => {
    if (newRisk.value.trim() && !risks.value.includes(newRisk.value.trim())) {
        risks.value.push(newRisk.value.trim());
        form.risks = risks.value;
        newRisk.value = '';
    }
};

const removeRisk = (index) => {
    risks.value.splice(index, 1);
    form.risks = risks.value;
};

const submit = () => {
    form.post(route('idea-validations.store', props.postId), {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div class="border rounded-lg p-6 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Validasi Ide Ini
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel value="Status Validasi" />
                <div class="mt-2 flex gap-4">
                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="form.validation_status"
                            value="layak"
                            required
                            class="mr-2"
                        />
                        <span class="text-gray-700 dark:text-gray-300">Layak</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="form.validation_status"
                            value="tidak_layak"
                            required
                            class="mr-2"
                        />
                        <span class="text-gray-700 dark:text-gray-300">Tidak Layak</span>
                    </label>
                </div>
                <InputError :message="form.errors.validation_status" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="estimated_capital" value="Estimasi Modal (Rp)" />
                    <TextInput
                        id="estimated_capital"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.estimated_capital"
                        step="0.01"
                        min="0"
                        placeholder="0"
                    />
                    <InputError :message="form.errors.estimated_capital" />
                </div>

                <div>
                    <InputLabel for="estimated_bep" value="Estimasi BEP (Bulan)" />
                    <TextInput
                        id="estimated_bep"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.estimated_bep"
                        step="0.01"
                        min="0"
                        placeholder="0"
                    />
                    <InputError :message="form.errors.estimated_bep" />
                </div>
            </div>

            <div>
                <InputLabel for="feedback" value="Feedback / Saran" />
                <Textarea
                    id="feedback"
                    class="mt-1 block w-full"
                    v-model="form.feedback"
                    rows="4"
                    placeholder="Berikan feedback atau saran untuk ide ini..."
                />
                <InputError :message="form.errors.feedback" />
            </div>

            <div>
                <InputLabel for="risks" value="Risiko yang Ditemukan" />
                <div class="mt-1 flex gap-2">
                    <TextInput
                        id="new_risk"
                        type="text"
                        class="block flex-1"
                        v-model="newRisk"
                        placeholder="Tambah risiko"
                        @keyup.enter.prevent="addRisk"
                    />
                    <PrimaryButton type="button" @click="addRisk">Tambah</PrimaryButton>
                </div>
                <div v-if="risks.length > 0" class="mt-2 flex flex-wrap gap-2">
                    <span
                        v-for="(risk, index) in risks"
                        :key="index"
                        class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-sm text-red-800 dark:bg-red-900 dark:text-red-200"
                    >
                        {{ risk }}
                        <button
                            type="button"
                            @click="removeRisk(index)"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200"
                        >
                            ×
                        </button>
                    </span>
                </div>
                <InputError :message="form.errors.risks" />
            </div>

            <div class="flex justify-end">
                <PrimaryButton :disabled="form.processing">
                    {{ userValidation ? 'Update Validasi' : 'Submit Validasi' }}
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>

