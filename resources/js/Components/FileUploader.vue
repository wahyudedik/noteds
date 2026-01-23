<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  maxFiles: { type: Number, default: 10 },
  maxSizeKB: { type: Number, default: 5 * 1024 }, // 5 MB
  accept: {
    type: String,
    default: [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-powerpoint',
      'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'text/plain',
      'image/*',
    ].join(','),
  },
  allowArchives: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
const fileInput = ref(null);
const isDragging = ref(false);
const errors = ref([]);

const files = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});

const canAddMore = computed(() => files.value.length < props.maxFiles);
const forbiddenExt = ['.exe','.bat','.cmd','.sh','.js','.php','.py','.pl','.dll','.msi','.com','.scr','.reg'];

const isForbidden = (name) => {
  const lower = String(name || '').toLowerCase();
  const baseForbidden = forbiddenExt.some(ext => lower.endsWith(ext));
  if (baseForbidden) return true;
  if (!props.allowArchives && (lower.endsWith('.zip') || lower.endsWith('.rar'))) return true;
  return false;
};

const addFiles = (list) => {
  errors.value = [];
  const arr = Array.from(list);
  const remaining = props.maxFiles - files.value.length;
  if (arr.length > remaining) {
    errors.value.push(`You can only add ${remaining} more file(s).`);
    arr.splice(remaining);
  }
  arr.forEach((f) => {
    const sizeKB = f.size / 1024;
    if (sizeKB > props.maxSizeKB) {
      errors.value.push(`${f.name} is too large. Max ${props.maxSizeKB}KB.`);
      return;
    }
    if (isForbidden(f.name)) {
      errors.value.push(`${f.name} file type is not allowed.`);
      return;
    }
    // prevent duplicates
    if (files.value.some(x => x.name === f.name && x.size === f.size)) return;
    const isImage = (f.type || '').startsWith('image/');
    if (isImage) {
      const reader = new FileReader();
      reader.onload = (e) => {
        files.value.push({ file: f, name: f.name, size: f.size, preview: e.target.result, isImage: true });
      };
      reader.readAsDataURL(f);
    } else {
      files.value.push({ file: f, name: f.name, size: f.size, isImage: false });
    }
  });
};

const openPicker = () => { if (canAddMore.value) fileInput.value?.click(); };
const onInput = (e) => { const l = e.target.files; if (l?.length) addFiles(l); e.target.value=''; };
const onDrop = (e) => { e.preventDefault(); isDragging.value=false; if (!canAddMore.value) return; const l=e.dataTransfer.files; if (l?.length) addFiles(l); };
const onDragOver = (e) => { e.preventDefault(); if (canAddMore.value) isDragging.value=true; };
const onDragLeave = () => { isDragging.value=false; };
const removeAt = (i) => { files.value.splice(i,1); errors.value=[]; };
const kb = (n) => `${Math.round(n/1024)} KB`;
</script>

<template>
  <div class="space-y-3">
    <div v-if="errors.length" class="text-sm text-red-600 dark:text-red-400 space-y-1">
      <div v-for="(e,i) in errors" :key="i">{{ e }}</div>
    </div>
    <div
      v-if="canAddMore"
      @click="openPicker"
      @drop="onDrop"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
      :class="[
        'border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-colors',
        isDragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-gray-50 dark:hover:bg-gray-700/50',
      ]"
    >
      <input ref="fileInput" type="file" multiple :accept="props.allowArchives ? [accept, 'application/zip,application/x-zip-compressed,application/x-rar-compressed'].join(',') : accept" class="hidden" @change="onInput" />
      <div class="flex flex-col items-center justify-center">
        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          <span class="font-medium text-indigo-600 dark:text-indigo-400">Click to upload</span> or drag and drop
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
          Allowed: PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, TXT, Images — up to {{ maxSizeKB }}KB ({{ maxFiles - files.length }} remaining)
        </p>
      </div>
    </div>
    <div v-if="files.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
      <div v-for="(f,i) in files" :key="i" class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 p-3 bg-white dark:bg-gray-800">
        <img v-if="f.isImage" :src="f.preview" :alt="f.name" class="w-full h-28 object-cover rounded-md" />
        <div v-else class="flex items-start gap-2">
          <svg class="w-6 h-6 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7v10a2 2 0 002 2h6a2 2 0 002-2V7m-8 0V5a2 2 0 012-2h2a2 2 0 012 2v2" />
          </svg>
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ f.name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ kb(f.size) }}</p>
          </div>
        </div>
        <button @click.stop="removeAt(i)" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg" type="button" title="Remove file">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>
  </div>
</template>
