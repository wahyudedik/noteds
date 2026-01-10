<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import { watch, ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Write a comment...',
    },
});

const emit = defineEmits(['update:modelValue']);

const imageInputRef = ref(null);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: false, // No headings in comments
            codeBlock: false, // No code blocks in comments
            blockquote: false, // No blockquotes in comments
        }),
        Image.configure({
            inline: true,
            allowBase64: true,
        }),
        Link.configure({
            openOnClick: false,
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none focus:outline-none min-h-[100px] p-3 text-sm',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(() => props.modelValue, (value) => {
    if (editor.value && editor.value.getHTML() !== value) {
        editor.value.commands.setContent(value || '');
    }
});

const handleImageUpload = () => {
    imageInputRef.value?.click();
};

const onImageSelected = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await fetch(route('comments.upload-image'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.url && editor.value) {
            editor.value.chain().focus().setImage({ src: data.url }).run();
        }
    } catch (error) {
        console.error('Error uploading image:', error);
    }

    // Reset input
    if (imageInputRef.value) {
        imageInputRef.value.value = '';
    }
};
</script>

<template>
    <div class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
        <div v-if="editor" class="border-b border-gray-200 dark:border-gray-700 p-2 flex gap-2 flex-wrap">
            <button
                @click="editor.chain().focus().toggleBold().run()"
                :class="[
                    'px-2 py-1 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('bold') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Bold"
            >
                <strong>B</strong>
            </button>
            <button
                @click="editor.chain().focus().toggleItalic().run()"
                :class="[
                    'px-2 py-1 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('italic') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Italic"
            >
                <em>I</em>
            </button>
            <button
                @click="editor.chain().focus().setLink({ href: '' }).run()"
                :class="[
                    'px-2 py-1 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('link') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Insert Link"
            >
                🔗
            </button>
            <div class="border-l border-gray-300 dark:border-gray-600 mx-1"></div>
            <button
                @click="handleImageUpload"
                class="px-2 py-1 text-xs rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                type="button"
                title="Insert Image"
            >
                🖼️
            </button>
            <input
                id="comment-image-input"
                name="comment_image"
                ref="imageInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onImageSelected"
            />
        </div>
        <EditorContent :editor="editor" />
    </div>
</template>

