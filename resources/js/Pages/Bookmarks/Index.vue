<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PostCard from '@/Components/PostCard.vue';
import CollectionTree from '@/Components/Bookmarks/CollectionTree.vue';
import CollectionForm from '@/Components/Bookmarks/CollectionForm.vue';
import BookmarkNotesEditor from '@/Components/Bookmarks/BookmarkNotesEditor.vue';
import TagList from '@/Components/Bookmarks/TagList.vue';
import TagInput from '@/Components/Bookmarks/TagInput.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    bookmarks: Object,
    collections: {
        type: Array,
        default: () => [],
    },
    selectedCollectionId: {
        type: String,
        default: null,
    },
    selectedTagId: {
        type: String,
        default: null,
    },
    userTags: {
        type: Array,
        default: () => [],
    },
    globalTags: {
        type: Array,
        default: () => [],
    },
});

const selectedCollection = ref(props.selectedCollectionId);
const selectedTag = ref(props.selectedTagId);
const showCollectionForm = ref(false);
const editingCollection = ref(null);
const showNotesEditor = ref(false);
const editingBookmark = ref(null);
const searchQuery = ref('');

const filteredBookmarks = computed(() => {
    let filtered = props.bookmarks.data || [];

    if (selectedCollection.value) {
        filtered = filtered.filter(b => b.collection_id === selectedCollection.value);
    }

    if (selectedTag.value) {
        filtered = filtered.filter(b => 
            b.tags && b.tags.some(t => t.id === selectedTag.value)
        );
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(b => 
            b.post.title.toLowerCase().includes(query) ||
            (b.notes && b.notes.toLowerCase().includes(query))
        );
    }

    return filtered;
});

const selectCollection = (collection) => {
    selectedCollection.value = collection?.id || null;
    router.get(route('bookmarks.index'), {
        collection: selectedCollection.value,
        tag: selectedTag.value,
    }, { preserveState: true });
};

const selectTag = (tagId) => {
    selectedTag.value = tagId;
    router.get(route('bookmarks.index'), {
        collection: selectedCollection.value,
        tag: selectedTag.value,
    }, { preserveState: true });
};

const openCollectionForm = (collection = null) => {
    editingCollection.value = collection;
    showCollectionForm.value = true;
};

const closeCollectionForm = () => {
    showCollectionForm.value = false;
    editingCollection.value = null;
    router.reload({ only: ['collections'] });
};

const openNotesEditor = (bookmark) => {
    editingBookmark.value = bookmark;
    showNotesEditor.value = true;
};

const closeNotesEditor = () => {
    showNotesEditor.value = false;
    editingBookmark.value = null;
    router.reload({ only: ['bookmarks'] });
};

const deleteCollection = async (collection) => {
    try {
        await router.delete(route('bookmarks.collections.destroy', collection.id), {
            preserveScroll: true,
        });
        router.reload({ only: ['collections'] });
    } catch (error) {
        console.error('Error deleting collection:', error);
    }
};
</script>

<template>
    <Head title="Bookmarks" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Bookmarks
                </h2>
                <button
                    @click="openCollectionForm()"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                >
                    + New Collection
                </button>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Sidebar: Collections & Filters -->
                    <div class="lg:col-span-3">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-4">
                            <!-- Collections -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-sm font-semibold">Collections</h3>
                                </div>
                                <CollectionTree
                                    :collections="collections"
                                    :selected-id="selectedCollection"
                                    @select="selectCollection"
                                    @edit="openCollectionForm"
                                    @delete="deleteCollection"
                                />
                            </div>
                            
                            <!-- Tags Filter -->
                            <div>
                                <h3 class="text-sm font-semibold mb-2">Filter by Tag</h3>
                                <div class="space-y-1">
                                    <button
                                        @click="selectTag(null)"
                                        :class="[
                                            'w-full text-left px-2 py-1 text-sm rounded',
                                            !selectedTag ? 'bg-indigo-100 dark:bg-indigo-900' : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                                        ]"
                                    >
                                        All Tags
                                    </button>
                                    <button
                                        v-for="tag in [...userTags, ...globalTags].slice(0, 10)"
                                        :key="tag.id"
                                        @click="selectTag(tag.id)"
                                        :class="[
                                            'w-full text-left px-2 py-1 text-sm rounded',
                                            selectedTag === tag.id ? 'bg-indigo-100 dark:bg-indigo-900' : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                                        ]"
                                    >
                                        {{ tag.name }}
                                        <span v-if="tag.is_global" class="text-xs">🌐</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <!-- Search -->
                        <div class="mb-4">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search bookmarks..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                            />
                        </div>

                        <!-- Bookmarks List -->
                        <div v-if="filteredBookmarks.length > 0" class="space-y-4">
                            <div
                                v-for="bookmark in filteredBookmarks"
                                :key="bookmark.id"
                                class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <PostCard
                                        :post="bookmark.post"
                                        :show-actions="false"
                                    />
                                </div>
                                
                                <!-- Notes Preview -->
                                <div v-if="bookmark.notes" class="mb-2 p-2 bg-gray-50 dark:bg-gray-700 rounded text-sm">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-500">Notes</span>
                                        <button
                                            @click="openNotesEditor(bookmark)"
                                            class="text-xs text-indigo-600 hover:text-indigo-700"
                                        >
                                            Edit
                                        </button>
                                    </div>
                                    <p class="text-gray-700 dark:text-gray-300 line-clamp-2">
                                        {{ bookmark.notes_preview }}
                                    </p>
                                </div>
                                
                                <!-- Tags -->
                                <div v-if="bookmark.tags && bookmark.tags.length > 0" class="mb-2">
                                    <TagList :tags="bookmark.tags" />
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex gap-2 mt-2">
                                    <button
                                        @click="openNotesEditor(bookmark)"
                                        class="text-xs text-indigo-600 hover:text-indigo-700"
                                    >
                                        {{ bookmark.notes ? 'Edit Notes' : 'Add Notes' }}
                                    </button>
                                    <Link
                                        :href="route('posts.show', bookmark.post.id)"
                                        class="text-xs text-indigo-600 hover:text-indigo-700"
                                    >
                                        View Post
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400">
                                No bookmarks found{{ selectedCollection || selectedTag ? ' matching filters' : '' }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Form Modal -->
        <CollectionForm
            :show="showCollectionForm"
            :collection="editingCollection"
            :collections="collections"
            @close="closeCollectionForm"
            @saved="closeCollectionForm"
        />

        <!-- Notes Editor Modal -->
        <BookmarkNotesEditor
            v-if="editingBookmark"
            :show="showNotesEditor"
            :bookmark="editingBookmark"
            @close="closeNotesEditor"
            @updated="closeNotesEditor"
        />
    </AuthenticatedLayout>
</template>
