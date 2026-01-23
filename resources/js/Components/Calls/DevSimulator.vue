<script setup>
import { ref, onMounted } from 'vue';

const enabled = import.meta.env.DEV;
const count = ref(20);
const participants = ref([]);
const fps = ref(0);
let last = performance.now();
let frames = 0;

const generate = (n) => {
  const arr = [];
  for (let i = 0; i < n; i++) {
    arr.push({
      id: i + 1,
      name: 'User ' + (i + 1),
      avatar: 'https://via.placeholder.com/64',
      status: 'connected',
    });
  }
  participants.value = arr;
};

const loop = () => {
  const now = performance.now();
  frames++;
  if (now - last >= 1000) {
    fps.value = frames;
    frames = 0;
    last = now;
  }
  requestAnimationFrame(loop);
};

const setCount = (n) => {
  count.value = n;
  generate(n);
};

onMounted(() => {
  if (!enabled) return;
  generate(count.value);
  requestAnimationFrame(loop);
});
</script>

<template>
  <div v-if="enabled" class="p-2 border rounded mt-2">
    <div class="flex items-center gap-2 mb-2">
      <div class="text-sm">Dev Simulator</div>
      <div class="text-xs">FPS: {{ fps }}</div>
      <label class="text-xs">Participants:</label>
      <input type="number" class="border rounded px-1 text-xs w-20" :value="count" @input="setCount(Number($event.target.value))" />
    </div>
    <div class="grid grid-cols-5 gap-2">
      <div v-for="p in participants" :key="p.id" class="border rounded p-1 flex items-center gap-2">
        <img :src="p.avatar" class="w-8 h-8 rounded-full" />
        <div class="text-xs">{{ p.name }}</div>
        <span class="text-[10px] text-green-600">{{ p.status }}</span>
      </div>
    </div>
  </div>
</template>
