<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    campaign: {
        type: Object,
        required: true,
    },
    collaborators: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const emit = defineEmits(['collaborator-added', 'collaborator-removed']);

const showInviteModal = ref(false);
const showPermissionsModal = ref(false);
const selectedCollaboration = ref(null);
const searchQuery = ref('');
const searchResults = ref([]);
const searching = ref(false);

const inviteForm = useForm({
    user_id: '',
    role: 'co_creator',
    can_edit: true,
    can_manage_budget: false,
    can_activate: false,
});

const permissionsForm = useForm({
    can_edit: true,
    can_manage_budget: false,
    can_activate: false,
    role: 'co_creator',
});

const isCampaignOwner = computed(() => {
    return props.campaign.creator_id === currentUser.value?.id;
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
    inviteForm.post(route('campaigns.collaborators.invite', props.campaign.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeInviteModal();
            emit('collaborator-added');
        },
    });
};

const acceptInvitation = (collaboration) => {
    router.post(route('campaigns.collaborators.accept', collaboration.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            emit('collaborator-added');
        },
    });
};

const rejectInvitation = (collaboration) => {
    router.post(route('campaigns.collaborators.reject', collaboration.id), {}, {
        preserveScroll: true,
    });
};

const removeCollaborator = (user) => {
    if (confirm(`Are you sure you want to remove ${user.name || user.business_name} as a collaborator?`)) {
        router.delete(route('campaigns.collaborators.remove', { campaign: props.campaign.id, user: user.id }), {
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
    permissionsForm.can_manage_budget = collaboration.can_manage_budget;
    permissionsForm.can_activate = collaboration.can_activate;
    permissionsForm.role = collaboration.role;
    showPermissionsModal.value = true;
};

const closePermissionsModal = () => {
    showPermissionsModal.value = false;
    selectedCollaboration.value = null;
    permissionsForm.reset();
};

const updatePermissions = () => {
    permissionsForm.put(route('campaigns.collaborators.update-permissions', selectedCollaboration.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closePermissionsModal();
        },
    });
};

const roleLabels = {
    co_creator: 'Co-Creator',
    manager: 'Manager',
    viewer: 'Viewer',
};

const statusLabels = {
    pending: 'Pending',
    accepted: 'Accepted',
    rejected: 'Rejected',
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Collaborators</h3>
            <button
                v-if="isCampaignOwner"
                @click="openInviteModal"
                class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700"
            >
                + Invite Collaborator
            </button>
        </div>

        <!-- Accepted Collaborators -->
        <div v-if="acceptedCollaborators.length > 0" class="space-y-2 mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Collaborators</h4>
            <div
                v-for="collaboration in acceptedCollaborators"
                :key="collaboration.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
            >
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ collaboration.user?.name || collaboration.user?.business_name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ roleLabels[collaboration.role] }}
                        <span v-if="collaboration.can_edit" class="ml-2">• Can Edit</span>
                        <span v-if="collaboration.can_manage_budget" class="ml-2">• Can Manage Budget</span>
                        <span v-if="collaboration.can_activate" class="ml-2">• Can Activate</span>
                    </p>
                </div>
                <div class="flex gap-2" v-if="isCampaignOwner">
                    <button
                        @click="openPermissionsModal(collaboration)"
                        class="px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        Permissions
                    </button>
                    <button
                        @click="removeCollaborator(collaboration.user)"
                        class="px-2 py-1 text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                    >
                        Remove
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Invitations -->
        <div v-if="pendingInvitations.length > 0" class="space-y-2 mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Pending Invitations</h4>
            <div
                v-for="collaboration in pendingInvitations"
                :key="collaboration.id"
                class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ collaboration.user?.name || collaboration.user?.business_name }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ roleLabels[collaboration.role] }} • {{ statusLabels[collaboration.status] }}
                        </p>
                    </div>
                    <div v-if="collaboration.user_id === currentUser?.id" class="flex gap-2">
                        <button
                            @click="acceptInvitation(collaboration)"
                            class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                        >
                            Accept
                        </button>
                        <button
                            @click="rejectInvitation(collaboration)"
                            class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                        >
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="acceptedCollaborators.length === 0 && pendingInvitations.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
            No collaborators yet.
        </div>

        <!-- Invite Modal -->
        <div
            v-if="showInviteModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeInviteModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Invite Collaborator</h3>
                            <button @click="closeInviteModal" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="inviteCollaborator" class="space-y-4">
                            <div>
                                <InputLabel for="search_user" value="Search User" />
                                <TextInput
                                    id="search_user"
                                    v-model="searchQuery"
                                    @input="searchUsers"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Search by name or email..."
                                />
                                <input type="hidden" v-model="inviteForm.user_id" />
                                
                                <div v-if="searchResults.length > 0" class="mt-2 max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md">
                                    <div
                                        v-for="user in searchResults"
                                        :key="user.id"
                                        @click="selectUser(user)"
                                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                                    >
                                        <p class="font-medium">{{ user.name || user.business_name }}</p>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="inviteForm.errors.user_id" />
                            </div>

                            <div>
                                <InputLabel for="role" value="Role" />
                                <select
                                    id="role"
                                    v-model="inviteForm.role"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="co_creator">Co-Creator</option>
                                    <option value="manager">Manager</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                                <InputError class="mt-2" :message="inviteForm.errors.role" />
                            </div>

                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="inviteForm.can_edit"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Edit Campaign</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="inviteForm.can_manage_budget"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Manage Budget</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="inviteForm.can_activate"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Activate Campaign</span>
                                </label>
                            </div>

                            <div class="flex justify-end gap-2 mt-4">
                                <button
                                    type="button"
                                    @click="closeInviteModal"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
                                >
                                    Cancel
                                </button>
                                <PrimaryButton :disabled="inviteForm.processing || !inviteForm.user_id">
                                    Send Invitation
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Modal -->
        <div
            v-if="showPermissionsModal && selectedCollaboration"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closePermissionsModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Update Permissions</h3>
                            <button @click="closePermissionsModal" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ selectedCollaboration.user?.name || selectedCollaboration.user?.business_name }}
                            </p>
                        </div>

                        <form @submit.prevent="updatePermissions" class="space-y-4">
                            <div>
                                <InputLabel for="permission_role" value="Role" />
                                <select
                                    id="permission_role"
                                    v-model="permissionsForm.role"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="co_creator">Co-Creator</option>
                                    <option value="manager">Manager</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                                <InputError class="mt-2" :message="permissionsForm.errors.role" />
                            </div>

                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="permissionsForm.can_edit"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Edit Campaign</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="permissionsForm.can_manage_budget"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Manage Budget</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="permissionsForm.can_activate"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can Activate Campaign</span>
                                </label>
                            </div>

                            <div class="flex justify-end gap-2 mt-4">
                                <button
                                    type="button"
                                    @click="closePermissionsModal"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
                                >
                                    Cancel
                                </button>
                                <PrimaryButton :disabled="permissionsForm.processing">
                                    Update Permissions
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

