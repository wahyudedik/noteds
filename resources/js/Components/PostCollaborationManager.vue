<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    collaborators: {
        type: Array,
        default: () => [],
    },
    currentUser: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['collaborator-added', 'collaborator-removed']);

const showInviteModal = ref(false);
const showPermissionsModal = ref(false);
const selectedCollaboration = ref(null);
const searchQuery = ref('');
const searchResults = ref([]);
const searching = ref(false);

const inviteForm = useForm({
    user_id: '',
    role: 'co_author',
    can_edit: true,
    can_publish: false,
});

const permissionsForm = useForm({
    can_edit: true,
    can_publish: false,
    role: 'co_author',
});

const isPostOwner = computed(() => {
    return props.post.user_id === props.currentUser.id;
});

const acceptedCollaborators = computed(() => {
    return props.collaborators.filter(c => c.status === 'accepted');
});

const pendingInvitations = computed(() => {
    return props.collaborators.filter(c => c.status === 'pending');
});

const openInviteModal = () => {
    showInviteModal.value = true;
    inviteForm.reset();
    searchQuery.value = '';
    searchResults.value = [];
};

const closeInviteModal = () => {
    showInviteModal.value = false;
    inviteForm.reset();
    searchQuery.value = '';
    searchResults.value = [];
};

const searchUsers = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    searching.value = true;
    try {
        const response = await fetch(route('search.index', { q: searchQuery.value, type: 'users' }), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        searchResults.value = data.users || [];
    } catch (error) {
        console.error('Error searching users:', error);
        searchResults.value = [];
    } finally {
        searching.value = false;
    }
};

const selectUser = (user) => {
    inviteForm.user_id = user.id;
    searchQuery.value = user.name || user.business_name;
    searchResults.value = [];
};

const inviteCollaborator = () => {
    inviteForm.post(route('posts.collaborators.invite', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeInviteModal();
            emit('collaborator-added');
        },
    });
};

const acceptInvitation = (collaboration) => {
    router.post(route('posts.collaborators.accept', collaboration.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            emit('collaborator-added');
        },
    });
};

const rejectInvitation = (collaboration) => {
    router.post(route('posts.collaborators.reject', collaboration.id), {}, {
        preserveScroll: true,
    });
};

const removeCollaborator = (user) => {
    if (confirm(`Are you sure you want to remove ${user.name || user.business_name} as a collaborator?`)) {
        router.delete(route('posts.collaborators.remove', { post: props.post.id, user: user.id }), {
            preserveScroll: true,
            onSuccess: () => {
                emit('collaborator-removed');
            },
        });
    }
};

const openPermissionsModal = (collaboration) => {
    selectedCollaboration.value = collaboration;
    permissionsForm.can_edit = collaboration.can_edit;
    permissionsForm.can_publish = collaboration.can_publish;
    permissionsForm.role = collaboration.role;
    showPermissionsModal.value = true;
};

const closePermissionsModal = () => {
    showPermissionsModal.value = false;
    selectedCollaboration.value = null;
    permissionsForm.reset();
};

const updatePermissions = () => {
    permissionsForm.put(route('posts.collaborators.update-permissions', selectedCollaboration.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closePermissionsModal();
        },
    });
};

const roleLabels = {
    co_author: 'Co-Author',
    editor: 'Editor',
    contributor: 'Contributor',
};

const statusLabels = {
    pending: 'Pending',
    accepted: 'Accepted',
    rejected: 'Rejected',
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Collaborators</h3>
            <button
                v-if="isPostOwner"
                @click="openInviteModal"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
                + Invite Collaborator
            </button>
        </div>

        <!-- Accepted Collaborators -->
        <div v-if="acceptedCollaborators.length > 0" class="space-y-2">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Collaborators</h4>
            <div
                v-for="collaboration in acceptedCollaborators"
                :key="collaboration.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
            >
                <div class="flex items-center gap-3">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">
                            {{ collaboration.user?.name || collaboration.user?.business_name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ roleLabels[collaboration.role] }}
                            <span v-if="collaboration.can_edit" class="ml-2">• Can Edit</span>
                            <span v-if="collaboration.can_publish" class="ml-2">• Can Publish</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        v-if="isPostOwner"
                        @click="openPermissionsModal(collaboration)"
                        class="px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                        Permissions
                    </button>
                    <button
                        v-if="isPostOwner"
                        @click="removeCollaborator(collaboration.user)"
                        class="px-3 py-1 text-xs font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-700 border border-red-300 dark:border-red-600 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20"
                    >
                        Remove
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Invitations -->
        <div v-if="pendingInvitations.length > 0" class="space-y-2">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Pending Invitations</h4>
            <div
                v-for="collaboration in pendingInvitations"
                :key="collaboration.id"
                class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg"
            >
                <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        {{ collaboration.user?.name || collaboration.user?.business_name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ roleLabels[collaboration.role] }} • {{ statusLabels[collaboration.status] }}
                    </p>
                </div>
                <div v-if="collaboration.user_id === currentUser.id" class="flex gap-2">
                    <button
                        @click="acceptInvitation(collaboration)"
                        class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
                    >
                        Accept
                    </button>
                    <button
                        @click="rejectInvitation(collaboration)"
                        class="px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                        Reject
                    </button>
                </div>
                <div v-else-if="isPostOwner" class="text-xs text-gray-500 dark:text-gray-400">
                    Waiting for response
                </div>
            </div>
        </div>

        <div v-if="acceptedCollaborators.length === 0 && pendingInvitations.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
            No collaborators yet.
        </div>

        <!-- Invite Modal -->
        <div
            v-if="showInviteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeInviteModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Invite Collaborator</h3>
                    <button
                        @click="closeInviteModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="inviteCollaborator" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Search User
                        </label>
                        <input
                            v-model="searchQuery"
                            @input="searchUsers"
                            type="text"
                            placeholder="Search by name or email..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        />
                        <div v-if="searchResults.length > 0" class="mt-2 border border-gray-200 dark:border-gray-700 rounded-md max-h-40 overflow-y-auto">
                            <button
                                v-for="user in searchResults"
                                :key="user.id"
                                @click="selectUser(user)"
                                type="button"
                                class="w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 text-sm"
                            >
                                {{ user.name || user.business_name }}
                            </button>
                        </div>
                        <input
                            v-model="inviteForm.user_id"
                            type="hidden"
                        />
                        <p v-if="inviteForm.errors.user_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ inviteForm.errors.user_id }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Role
                        </label>
                        <select
                            v-model="inviteForm.role"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        >
                            <option value="co_author">Co-Author</option>
                            <option value="editor">Editor</option>
                            <option value="contributor">Contributor</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input
                                v-model="inviteForm.can_edit"
                                type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Edit</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                v-model="inviteForm.can_publish"
                                type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Publish</span>
                        </label>
                    </div>

                    <div class="flex gap-2 justify-end">
                        <button
                            @click="closeInviteModal"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="inviteForm.processing || !inviteForm.user_id"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Send Invitation
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Permissions Modal -->
        <div
            v-if="showPermissionsModal && selectedCollaboration"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closePermissionsModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Update Permissions</h3>
                    <button
                        @click="closePermissionsModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="updatePermissions" class="p-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ selectedCollaboration.user?.name || selectedCollaboration.user?.business_name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Role
                        </label>
                        <select
                            v-model="permissionsForm.role"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        >
                            <option value="co_author">Co-Author</option>
                            <option value="editor">Editor</option>
                            <option value="contributor">Contributor</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input
                                v-model="permissionsForm.can_edit"
                                type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Edit</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                v-model="permissionsForm.can_publish"
                                type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Publish</span>
                        </label>
                    </div>

                    <div class="flex gap-2 justify-end">
                        <button
                            @click="closePermissionsModal"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="permissionsForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

