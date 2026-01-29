<template>
  <Head title="Messaging Prototype" />
  <MessagingLayout>
    <div class="h-full flex">
      <aside v-show="showSidebar" class="w-full md:w-1/3 border-r border-gray-200 flex flex-col" role="navigation" aria-label="Conversation list">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
          <h1 class="text-xl font-bold">Messages</h1>
          <button class="px-2 py-1 text-sm bg-gray-100 rounded" @click="toggleSidebar">Hide</button>
        </div>
        <div class="p-2">
          <input type="search" v-model="q" placeholder="Search conversations" class="w-full border rounded px-3 py-2" aria-label="Search conversations" />
        </div>
        <ul class="flex-1 overflow-y-auto" role="list">
          <li v-for="c in filtered" :key="c.id" role="listitem" class="p-3 border-b hover:bg-gray-50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-semibold">{{ initials(c.name) }}</div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 truncate">{{ c.name }}</p>
                <span v-if="c.unread>0" class="ml-2 bg-blue-600 text-white text-xs rounded-full px-2 py-0.5">{{ c.unread }}</span>
              </div>
              <p class="text-xs text-gray-500 truncate">{{ c.preview }}</p>
            </div>
          </li>
          <li v-if="loadingMore" class="p-3 text-center text-gray-400 text-xs">Loading more...</li>
        </ul>
      </aside>
      <main class="flex-1 flex flex-col" role="main" aria-label="Conversation thread">
        <div class="p-3 border-b flex items-center justify-between">
          <div class="flex items-center gap-3">
            <button class="md:hidden px-2 py-1 text-sm bg-gray-100 rounded" @click="toggleSidebar">Menu</button>
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-semibold">{{ initials(active.name) }}</div>
            <div>
              <h2 class="font-semibold">{{ active.name }}</h2>
              <p class="text-xs text-gray-500" aria-live="polite">{{ presence }}</p>
            </div>
          </div>
          <div class="hidden md:flex items-center gap-2">
            <button class="p-2 text-gray-600 hover:text-gray-900" aria-label="Start call">📞</button>
            <button class="p-2 text-gray-600 hover:text-gray-900" aria-label="More">⚙️</button>
          </div>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-4" @scroll.passive="onScroll" ref="listRef">
          <div v-for="block in timeline" :key="block.id">
            <div v-if="block.type==='date'" class="text-center text-xs text-gray-500 my-2">{{ block.label }}</div>
            <div v-else class="flex gap-2 items-start">
              <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-semibold">{{ initials(block.user) }}</div>
              <div class="max-w-[70%] bg-white border rounded p-2 text-sm">
                <p class="text-gray-900">{{ block.text }}</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ block.time }}</p>
              </div>
            </div>
          </div>
          <div v-if="loadingOlder" class="text-center text-xs text-gray-400">Loading older messages…</div>
        </div>
        <div class="border-t p-3">
          <div class="border rounded p-2 bg-gray-50">
            <div class="border-2 border-dashed rounded p-3 text-xs text-gray-500 mb-2" aria-label="Attachment dropzone">Drop files here or click attachment</div>
            <div class="flex items-center gap-2">
              <button class="px-2 py-1 bg-gray-100 rounded" aria-label="Attach file">📎</button>
              <input type="text" v-model="draft" class="flex-1 border rounded px-3 py-2" placeholder="Type a message" aria-label="Message input" />
              <button class="px-3 py-1.5 bg-blue-600 text-white rounded" @click="send">Send</button>
            </div>
          </div>
        </div>
        <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t p-2 flex items-center justify-around" aria-label="Bottom actions">
          <button class="p-2" aria-label="Messages">💬</button>
          <button class="p-2" aria-label="Call">📞</button>
          <button class="p-2" aria-label="Settings">⚙️</button>
        </nav>
      </main>
    </div>
  </MessagingLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';
import { ref, computed, onMounted } from 'vue';
const showSidebar = ref(true);
const q = ref('');
const loadingMore = ref(false);
const loadingOlder = ref(false);
const listRef = ref(null);
const conversations = ref(Array.from({length: 12}).map((_,i)=>({id:i+1,name:`Conversation ${i+1}`,unread:i%3===0?i%5:0,preview:'Last message preview...'})));
const filtered = computed(()=>conversations.value.filter(c=>c.name.toLowerCase().includes(q.value.toLowerCase())));
const active = ref({name:'Design Team'});
const presence = 'Online';
const timeline = ref([
  {id:1,type:'date',label:'Today'},
  {id:2,type:'msg',user:'Alice',text:'Let’s iterate the mockups.',time:'10:21'},
  {id:3,type:'msg',user:'Bob',text:'I added a new wireframe.',time:'10:23'},
]);
const draft = ref('');
const send = ()=>{};
const toggleSidebar = ()=>{ showSidebar.value = !showSidebar.value; };
const onScroll = async ()=>{
  const el = listRef.value;
  if (!el || loadingOlder.value) return;
  if (el.scrollTop < 100) {
    loadingOlder.value = true;
    setTimeout(()=>{ timeline.value.unshift({id:Date.now(),type:'msg',user:'Eve',text:'Older content…',time:'09:01'}); loadingOlder.value=false; }, 500);
  }
};
const initials = (name)=> {
  const base = (name||'').trim();
  if (!base) return '?';
  const parts = base.split(/\s+/).slice(0,2);
  return parts.map(p=>p[0]?.toUpperCase()).join('');
};
onMounted(()=>{ if (window.innerWidth < 768) showSidebar.value = false; });
</script>
