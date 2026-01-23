<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
  stream: Object,
  auth: Object,
});

const chatForm = useForm({ content: '' });
const chatMessages = ref(props.stream.chat_messages || []);

const submitChat = async () => {
  if (!chatForm.content) return;
  await chatForm.post(route('streams.chat.store', props.stream.id), {
    preserveScroll: true,
    onSuccess: (res) => {
      chatForm.reset();
    },
  });
};

onMounted(() => {
  if (window.Echo) {
    window.Echo.channel(`livestream.${props.stream.id}`).listen('LiveChatMessageCreated', (e) => {
      chatMessages.value.unshift(e.message);
    });
  }
});
</script>

<template>
  <Head :title="stream.title">
    <meta name="description" :content="stream.description || ''" />
    <meta property="og:title" :content="stream.title" />
    <meta property="og:description" :content="stream.description || ''" />
    <meta property="og:url" :content="route('streams.show', stream.id)" />
    <meta property="og:type" content="video.other" />
  </Head>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ stream.title }}</h2>
    </template>
    <div class="px-4 py-6 lg:px-6">
      <div class="mx-auto max-w-6xl grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <div class="bg-black rounded-lg overflow-hidden">
            <template v-if="stream.provider === 'youtube'">
              <iframe
                :src="stream.playback_url"
                title="Live Player"
                class="w-full h-[360px]"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
              />
            </template>
            <template v-else>
              <video :src="stream.playback_url" controls class="w-full h-[360px]"></video>
            </template>
          </div>
          <div class="mt-4 text-sm text-gray-700 dark:text-gray-300">
            {{ stream.description }}
          </div>
        </div>
        <div>
          <div class="bg-white dark:bg-gray-800 border rounded-lg p-3">
            <div class="text-sm font-semibold mb-2">Live Chat</div>
            <div class="space-y-2 max-h-[420px] overflow-y-auto">
              <div v-for="m in chatMessages" :key="m.id" class="text-xs">
                <span class="font-semibold">{{ m.user?.name || 'User' }}</span>:
                <span>{{ m.content }}</span>
              </div>
            </div>
            <div v-if="auth?.user" class="mt-3 flex gap-2">
              <input v-model="chatForm.content" class="flex-1 px-2 py-1 text-xs border rounded" placeholder="Tulis pesan..." />
              <button @click="submitChat" class="px-2 py-1 text-xs bg-indigo-600 text-white rounded">Kirim</button>
            </div>
            <div v-else class="text-xs text-gray-500">Login untuk ikut chat</div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
