<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
  audio: Object,
  waveform: { type: Array, default: () => [] },
});

const canvasRef = ref(null);
let ctx = null;

const draw = () => {
  const canvas = canvasRef.value;
  if (!canvas || !ctx || !props.waveform || props.waveform.length === 0) return;
  const dpr = window.devicePixelRatio || 1;
  const width = canvas.clientWidth;
  const height = canvas.clientHeight;
  canvas.width = width * dpr;
  canvas.height = height * dpr;
  ctx.scale(dpr, dpr);
  ctx.clearRect(0, 0, width, height);
  ctx.fillStyle = 'rgba(255,255,255,0.6)';
  const N = props.waveform.length;
  const step = Math.max(1, Math.floor(N / width));
  let x = 0;
  for (let i = 0; i < N; i += step) {
    const amp = props.waveform[i];
    const barH = Math.max(2, Math.floor((amp || 0) * height));
    ctx.fillRect(x, (height - barH) / 2, 1, barH);
    x += 1;
    if (x > width) break;
  }
  // draw current time indicator
  if (props.audio) {
    const t = props.audio.currentTime;
    const dur = Math.max(props.audio.duration || 1, 1);
    const px = Math.floor((t / dur) * width);
    ctx.strokeStyle = 'rgba(59,130,246,0.9)';
    ctx.beginPath();
    ctx.moveTo(px, 0);
    ctx.lineTo(px, height);
    ctx.stroke();
  }
};

const onClick = (e) => {
  if (!props.audio) return;
  const rect = e.currentTarget.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const width = rect.width;
  const ratio = Math.min(Math.max(x / width, 0), 1);
  const dur = props.audio.duration || 0;
  if (dur > 0) props.audio.currentTime = ratio * dur;
};

onMounted(() => {
  const canvas = canvasRef.value;
  ctx = canvas.getContext('2d');
  draw();
  window.addEventListener('resize', draw);
});

watch(() => props.waveform, draw, { deep: true });
watch(() => props.audio?.currentTime, draw);
</script>

<template>
  <canvas ref="canvasRef" class="w-full h-12 cursor-pointer" @click="onClick"></canvas>
</template>
