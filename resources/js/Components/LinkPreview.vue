<script setup>
const props = defineProps({
    preview: {
        type: Object,
        required: true,
    },
    showRemove: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['remove']);

const handleClick = () => {
    if (props.preview?.url) {
        window.open(props.preview.url, '_blank', 'noopener,noreferrer');
    }
};
</script>

<template>
    <div class="mt-3 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div
            @click="handleClick"
            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
        >
            <div class="flex">
                <!-- Image -->
                <div
                    v-if="preview.image"
                    class="flex-shrink-0 w-32 h-32 sm:w-40 sm:h-40 bg-gray-100 dark:bg-gray-700"
                >
                    <img
                        :src="preview.image"
                        :alt="preview.title || 'Link preview'"
                        class="w-full h-full object-cover"
                        @error="$event.target.style.display = 'none'"
                    />
                </div>

                <!-- Content -->
                <div class="flex-1 p-3 min-w-0">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-1">
                        {{ preview.site_name || 'Link' }}
                    </div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1 line-clamp-2">
                        {{ preview.title }}
                    </h4>
                    <p
                        v-if="preview.description"
                        class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2"
                    >
                        {{ preview.description }}
                    </p>
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1 truncate">
                        {{ preview.url }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Remove Button -->
        <div v-if="showRemove" class="px-3 pb-2 pt-1 flex justify-end">
            <button
                @click.stop="emit('remove')"
                class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors"
                type="button"
            >
                Remove
            </button>
        </div>
    </div>
</template>

