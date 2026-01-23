<script setup>
import { computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  url: { type: String, required: true },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  hashtags: { type: Array, default: () => [] },
  shareType: { type: String, default: 'posts' }, // posts|products|stories (future)
  shareId: { type: String, required: true },
});

const fullUrl = computed(() => {
  const base = props.url;
  const utm = new URLSearchParams({
    utm_source: 'noteds',
    utm_medium: 'share',
    utm_campaign: props.shareType,
  }).toString();
  return base.includes('?') ? `${base}&${utm}` : `${base}?${utm}`;
});

const hashParam = computed(() => props.hashtags.length ? props.hashtags.map(h => `#${h}`).join(' ') : '');

const openWindow = (link) => {
  window.open(link, '_blank', 'noopener,noreferrer');
};

const track = async (platform) => {
  try {
    if (props.shareType === 'products' && props.shareId) {
      try {
        await axios.post(route('marketplace.products.share', props.shareId), { platform, url: props.url });
        return;
      } catch (e) {
        // fallback to generic
      }
    }
    await axios.post(route('share.track', { type: props.shareType, id: props.shareId }), { platform, url: props.url });
  } catch (_) {}
};

const shareNative = async () => {
  const platform = 'web_share';
  try {
    if (navigator.share) {
      await navigator.share({
        title: props.title,
        text: props.description || props.title,
        url: fullUrl.value,
      });
      track(platform);
    } else {
      shareTwitter();
    }
  } catch (_) {
    // silently ignore
  }
};

const shareFacebook = () => {
  track('facebook');
  const link = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(fullUrl.value)}`;
  openWindow(link);
};
const shareTwitter = () => {
  track('twitter');
  const text = `${props.title} ${hashParam.value}`.trim();
  const link = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(fullUrl.value)}`;
  openWindow(link);
};
const shareLinkedIn = () => {
  track('linkedin');
  const link = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(fullUrl.value)}`;
  openWindow(link);
};
const shareWhatsApp = () => {
  track('whatsapp');
  const text = `${props.title} ${fullUrl.value}`.trim();
  const link = `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
  openWindow(link);
};
const shareTelegram = () => {
  track('telegram');
  const text = `${props.title} ${props.description}`.trim();
  const link = `https://t.me/share/url?url=${encodeURIComponent(fullUrl.value)}&text=${encodeURIComponent(text)}`;
  openWindow(link);
};
const copyLink = async () => {
  track('copy_link');
  try {
    await navigator.clipboard.writeText(fullUrl.value);
    alert('Link copied!');
  } catch (_) {
    const ta = document.createElement('textarea');
    ta.value = fullUrl.value;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    alert('Link copied!');
  }
};
</script>

<template>
  <div class="flex flex-wrap items-center gap-2">
    <button class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-700" @click="shareNative">Share</button>
    <button class="px-2 py-1 text-xs rounded bg-blue-600 text-white" @click="shareFacebook">Facebook</button>
    <button class="px-2 py-1 text-xs rounded bg-sky-500 text-white" @click="shareTwitter">Twitter</button>
    <button class="px-2 py-1 text-xs rounded bg-blue-800 text-white" @click="shareLinkedIn">LinkedIn</button>
    <button class="px-2 py-1 text-xs rounded bg-green-500 text-white" @click="shareWhatsApp">WhatsApp</button>
    <button class="px-2 py-1 text-xs rounded bg-blue-500 text-white" @click="shareTelegram">Telegram</button>
    <button class="px-2 py-1 text-xs rounded bg-gray-200 dark:bg-gray-600" @click="copyLink">Copy Link</button>
  </div>
</template>
