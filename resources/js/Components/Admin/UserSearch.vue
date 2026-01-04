<script setup>
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';

const emit = defineEmits(['user-selected']);

const searchQuery = ref('');
const users = ref([]);
const selectedUser = ref(null);
const showDropdown = ref(false);
const isSearching = ref(false);

const searchUsers = async () => {
    if (searchQuery.value.length < 2) {
        users.value = [];
        showDropdown.value = false;
        return;
    }

    isSearching.value = true;
    try {
        const response = await window.axios.get(route('admin.api.users.search'), {
            params: {
                q: searchQuery.value,
            },
        });
        users.value = response.data;
        showDropdown.value = users.value.length > 0;
    } catch (error) {
        console.error('Error searching users:', error);
        users.value = [];
        showDropdown.value = false;
    } finally {
        isSearching.value = false;
    }
};

const selectUser = (user) => {
    selectedUser.value = user;
    searchQuery.value = `${user.name} (${user.email})`;
    showDropdown.value = false;
    emit('user-selected', user);
};

const clearSelection = () => {
    selectedUser.value = null;
    searchQuery.value = '';
    users.value = [];
    showDropdown.value = false;
    emit('user-selected', null);
};

// Debounce search
let searchTimeout = null;
watch(searchQuery, (newValue) => {
    if (selectedUser.value && newValue !== `${selectedUser.value.name} (${selectedUser.value.email})`) {
        clearSelection();
    }
    
    if (!selectedUser.value) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchUsers();
        }, 300);
    }
});
</script>

<template>
    <div class="relative">
        <div class="flex gap-2">
            <div class="flex-1 relative">
                <TextInput
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search user by name or email..."
                    class="w-full"
                    autocomplete="off"
                    @focus="searchUsers"
                />
                <div
                    v-if="selectedUser"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2"
                >
                    <button
                        type="button"
                        @click="clearSelection"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <!-- Dropdown -->
        <div
            v-if="showDropdown && users.length > 0"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-auto"
        >
            <div
                v-for="user in users"
                :key="user.id"
                @click="selectUser(user)"
                class="px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer flex items-center gap-3"
            >
                <div
                    v-if="user.avatar"
                    class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center overflow-hidden"
                >
                    <img :src="user.avatar" :alt="user.name" class="h-full w-full object-cover" />
                </div>
                <div
                    v-else
                    class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold"
                >
                    {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                </div>
            </div>
        </div>

        <div
            v-if="isSearching"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg px-4 py-3"
        >
            <div class="text-gray-500 dark:text-gray-400">Searching...</div>
        </div>
    </div>
</template>

