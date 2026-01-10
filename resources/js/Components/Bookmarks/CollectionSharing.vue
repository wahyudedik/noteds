<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
    shares: {
        type: Array,
        default: () => [],
    },
});

const isPublic = ref(props.collection.is_public);
const publicUrl = ref(props.collection.public_url);
const sharedUsers = ref([...props.shares]);
const inviteUserId = ref('');
const invitePermission = ref('view');

const togglePublic = async () => {
    try {
        const response = await router.post(
            route('bookmarks.collections.toggle-public', props.collection.id),
            {},
            { preserveScroll: true }
        );
        isPublic.value = response.data.is_public;
        publicUrl.value = response.data.public_url;
    } catch (error) {
        console.error('Error toggling public:', error);
    }
};

const generateLink = async () => {
    try {
        const response = await router.post(
            route('bookmarks.collections.generate-link', props.collection.id),
            {},
            { preserveScroll: true }
        );
        publicUrl.value = response.data.public_url;
        isPublic.value = true;
    } catch (error) {
        console.error('Error generating link:', error);
    }
};

const copyLink = () => {
    navigator.clipboard.writeText(publicUrl.value);
    alert('Link copied to clipboard!');
};

const inviteUser = async () => {
    if (!inviteUserId.value) return;
    
    try {
        await router.post(
            route('bookmarks.collections.invite', props.collection.id),
            {
                user_id: inviteUserId.value,
                permission: invitePermission.value,
            },
            { preserveScroll: true }
        );
        inviteUserId.value = '';
    } catch (error) {
        console.error('Error inviting user:', error);
    }
};

const updatePermission = async (userId, permission) => {
    try {
        await router.put(
            route('bookmarks.collections.update-permission', [props.collection.id, userId]),
            { permission },
            { preserveScroll: true }
        );
    } catch (error) {
        console.error('Error updating permission:', error);
    }
};

const revokeAccess = async (userId) => {
    if (!confirm('Revoke access for this user?')) return;
    
    try {
        await router.delete(
            route('bookmarks.collections.revoke', [props.collection.id, userId]),
            { preserveScroll: true }
        );
    } catch (error) {
        console.error('Error revoking access:', error);
    }
};
</script>

<template>
    <div class="space-y-4">
        <!-- Public/Private Toggle -->
        <div>
            <label class="flex items-center gap-2">
                <input
                    type="checkbox"
                    :checked="isPublic"
                    @change="togglePublic"
                    class="rounded"
                />
                <span>Make collection public</span>
            </label>
        </div>
        
        <!-- Public Link -->
        <div v-if="isPublic && publicUrl" class="space-y-2">
            <label class="block text-sm font-medium">Public Link</label>
            <div class="flex gap-2">
                <input
                    :value="publicUrl"
                    readonly
                    class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300 text-sm"
                />
                <button
                    @click="copyLink"
                    class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                >
                    Copy
                </button>
            </div>
            <button
                @click="generateLink"
                class="text-sm text-indigo-600 hover:text-indigo-700"
            >
                Regenerate Link
            </button>
        </div>
        
        <!-- Invite User -->
        <div class="space-y-2">
            <label class="block text-sm font-medium">Invite User</label>
            <div class="flex gap-2">
                <input
                    v-model="inviteUserId"
                    type="text"
                    placeholder="User ID or email"
                    class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                />
                <select
                    v-model="invitePermission"
                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                >
                    <option value="view">View</option>
                    <option value="edit">Edit</option>
                </select>
                <button
                    @click="inviteUser"
                    class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                >
                    Invite
                </button>
            </div>
        </div>
        
        <!-- Shared Users List -->
        <div v-if="sharedUsers.length > 0" class="space-y-2">
            <label class="block text-sm font-medium">Shared With</label>
            <div class="space-y-1">
                <div
                    v-for="share in sharedUsers"
                    :key="share.id"
                    class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded"
                >
                    <div>
                        <div class="font-medium">{{ share.shared_with.name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ share.permission }} • {{ share.accepted_at ? 'Accepted' : 'Pending' }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select
                            :value="share.permission"
                            @change="updatePermission(share.shared_with_user_id, $event.target.value)"
                            class="text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300"
                        >
                            <option value="view">View</option>
                            <option value="edit">Edit</option>
                        </select>
                        <button
                            @click="revokeAccess(share.shared_with_user_id)"
                            class="text-red-600 hover:text-red-700 text-sm"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

