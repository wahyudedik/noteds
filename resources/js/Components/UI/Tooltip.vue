<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  label: { type: String, default: '' },
  position: { type: String, default: 'top' }, // top|right|bottom|left
});

const open = ref(false);
const triggerEl = ref(null);
const tooltipEl = ref(null);

const onKeyDown = (e) => {
  if (e.key === 'Escape') open.value = false;
};

onMounted(() => {
  document.addEventListener('keydown', onKeyDown);
});
onUnmounted(() => {
  document.removeEventListener('keydown', onKeyDown);
});
</script>

<template>
  <span class="relative inline-flex" ref="triggerEl"
        @mouseenter="open=true" @mouseleave="open=false"
        @focusin="open=true" @focusout="open=false"
        role="button" tabindex="0" aria-describedby="tooltip"
  >
    <slot />
    <transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div v-if="open"
           ref="tooltipEl"
           id="tooltip"
           role="tooltip"
           class="absolute z-50 px-2 py-1 text-xs rounded bg-black text-white"
           :class="{
             'bottom-full mb-1 left-1/2 -translate-x-1/2': position==='top',
             'top-full mt-1 left-1/2 -translate-x-1/2': position==='bottom',
             'right-full mr-1 top-1/2 -translate-y-1/2': position==='left',
             'left-full ml-1 top-1/2 -translate-y-1/2': position==='right',
           }"
      >
        {{ label }}
      </div>
    </transition>
  </span>
</template>
