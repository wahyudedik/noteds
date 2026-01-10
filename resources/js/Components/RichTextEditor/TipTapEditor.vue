<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Youtube from '@tiptap/extension-youtube';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import Link from '@tiptap/extension-link';
import { createLowlight } from 'lowlight';
import { watch, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Write your post...',
    },
});

const emit = defineEmits(['update:modelValue']);

const showVideoModal = ref(false);
const videoUrl = ref('');
const showCodeLanguageModal = ref(false);
const codeLanguage = ref('javascript');
const imageInputRef = ref(null);

const lowlight = createLowlight();

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3],
            },
            codeBlock: false, // Disable default code block
        }),
        Image.configure({
            inline: true,
            allowBase64: true,
        }),
        Youtube.configure({
            controls: true,
            nocookie: true,
        }),
        CodeBlockLowlight.configure({
            lowlight,
            defaultLanguage: 'javascript',
        }),
        Link.configure({
            openOnClick: false,
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-2xl mx-auto focus:outline-none min-h-[200px] p-4',
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
        const response = await fetch(route('posts.upload-image'), {
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

const openVideoModal = () => {
    showVideoModal.value = true;
    videoUrl.value = '';
};

const insertVideo = () => {
    if (videoUrl.value && editor.value) {
        editor.value.chain().focus().setYoutubeVideo({
            src: videoUrl.value,
        }).run();
        showVideoModal.value = false;
        videoUrl.value = '';
    }
};

const openCodeLanguageModal = () => {
    showCodeLanguageModal.value = true;
    codeLanguage.value = 'javascript';
};

const insertCodeBlock = () => {
    if (editor.value) {
        editor.value.chain().focus().toggleCodeBlock({ language: codeLanguage.value }).run();
        showCodeLanguageModal.value = false;
    }
};

const commonLanguages = [
    { value: 'javascript', label: 'JavaScript' },
    { value: 'typescript', label: 'TypeScript' },
    { value: 'php', label: 'PHP' },
    { value: 'python', label: 'Python' },
    { value: 'java', label: 'Java' },
    { value: 'html', label: 'HTML' },
    { value: 'css', label: 'CSS' },
    { value: 'json', label: 'JSON' },
    { value: 'sql', label: 'SQL' },
    { value: 'bash', label: 'Bash' },
    { value: 'plaintext', label: 'Plain Text' },
];
</script>

<template>
    <div class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
        <div v-if="editor" class="border-b border-gray-200 dark:border-gray-700 p-2 flex gap-2 flex-wrap">
            <button
                @click="editor.chain().focus().toggleBold().run()"
                :class="[
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
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
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('italic') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Italic"
            >
                <em>I</em>
            </button>
            <button
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="[
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('heading', { level: 1 }) ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Heading 1"
            >
                H1
            </button>
            <button
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="[
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('heading', { level: 2 }) ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Heading 2"
            >
                H2
            </button>
            <button
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="[
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('bulletList') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Bullet List"
            >
                •
            </button>
            <button
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="[
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('orderedList') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Ordered List"
            >
                1.
            </button>
            <button
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="[
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('blockquote') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Blockquote"
            >
                "
            </button>
            <button
                @click="openCodeLanguageModal"
                :class="[
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('codeBlock') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Code Block"
            >
                &lt;/&gt;
            </button>
            <div class="border-l border-gray-300 dark:border-gray-600 mx-1"></div>
            <button
                @click="handleImageUpload"
                class="px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                type="button"
                title="Insert Image"
            >
                🖼️
            </button>
            <input
                id="editor-image-input"
                name="editor_image"
                ref="imageInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onImageSelected"
            />
            <button
                @click="openVideoModal"
                class="px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                type="button"
                title="Insert Video"
            >
                ▶️
            </button>
            <button
                @click="editor.chain().focus().setLink({ href: '' }).run()"
                :class="[
                    'px-3 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700',
                    editor.isActive('link') ? 'bg-gray-200 dark:bg-gray-600' : ''
                ]"
                type="button"
                title="Insert Link"
            >
                🔗
            </button>
        </div>
        <EditorContent :editor="editor" />

        <!-- Video Embed Modal -->
        <div
            v-if="showVideoModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showVideoModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Insert Video</h3>
                <div class="space-y-4">
                    <div>
                        <label for="video-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            YouTube or Vimeo URL
                        </label>
                        <input
                            id="video-url"
                            name="video_url"
                            v-model="videoUrl"
                            type="url"
                            placeholder="https://www.youtube.com/watch?v=..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        />
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button
                            @click="showVideoModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <button
                            @click="insertVideo"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                        >
                            Insert
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Code Language Modal -->
        <div
            v-if="showCodeLanguageModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showCodeLanguageModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Select Code Language</h3>
                <div class="space-y-4">
                    <div>
                        <label for="code-language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Language
                        </label>
                        <select
                            id="code-language"
                            name="code_language"
                            v-model="codeLanguage"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        >
                            <option v-for="lang in commonLanguages" :key="lang.value" :value="lang.value">
                                {{ lang.label }}
                            </option>
                        </select>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button
                            @click="showCodeLanguageModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <button
                            @click="insertCodeBlock"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                        >
                            Insert
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
