<script setup>
import HashtagLink from '@/Components/HashtagLink.vue';

const props = defineProps({
    hashtags: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="hashtags && hashtags.length > 0" class="flex flex-wrap gap-2 mt-2">
        <HashtagLink
            v-for="hashtag in hashtags"
            :key="hashtag.id"
            :hashtag="hashtag"
        />
    </div>
</template>

