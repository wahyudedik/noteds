<template>
  <div>
    <h1>{{ group.name }}</h1>
    <div v-if="sharePlacement.groups.enabled && sharePlacement.groups.position === 'below_title'" class="mt-2 mb-4">
      <SocialShareButtons
        :url="route('groups.show', group.slug)"
        :title="group.name"
        :description="group.description || ''"
        :hashtags="[]"
        share-type="groups"
        :share-id="group.id"
      />
    </div>
    <p>{{ group.description }}</p>
    <div>
      <button @click="join">Gabung</button>
      <button @click="leave">Keluar</button>
      <button @click="openInvite=true">Undang</button>
    </div>
    <h2>Anggota</h2>
    <ul>
      <li v-for="m in members" :key="m.id">{{ m.user?.name }} - {{ m.role }} ({{ m.status }})</li>
    </ul>
    <h2>Buat Post</h2>
    <form @submit.prevent="createPost">
      <input v-model="title" placeholder="Judul" />
      <textarea v-model="content" placeholder="Konten"></textarea>
      <select v-model="visibility">
        <option value="members">Members</option>
        <option value="public">Public</option>
      </select>
      <button type="submit">Kirim</button>
    </form>
    <div v-if="posts && posts.data && posts.data.length">
      <h2>Postingan</h2>
      <div v-for="p in posts.data" :key="p.id">
        <h3>{{ p.title }}</h3>
        <p>{{ p.content }}</p>
      </div>
    </div>
    <InviteModal :open="openInvite" :slug="group.slug" @close="openInvite=false" />
  </div>
</template>
<script>
import { router } from '@inertiajs/vue3'
import InviteModal from '../../Components/Groups/InviteModal.vue'
import SocialShareButtons from '../../Components/Social/SocialShareButtons.vue'
import { sharePlacement } from '@/config/sharePlacement'
export default {
  props: { group: Object, members: Array, posts: Object },
  mounted() {
    if (window.Echo && this.group?.id) {
      window.Echo.channel(`group.${this.group.id}`).listen('GroupEventCreated', (e) => {
        alert(`Event baru: ${e.title}`)
      }).listen('GroupInviteCreated', (e) => {
        alert(`Undangan baru dibuat`)
      })
    }
  },
  data() {
    return { title: '', content: '', visibility: 'members', openInvite: false }
  },
  components: { InviteModal, SocialShareButtons },
  methods: {
    join() {
      router.post(route('groups.join', this.group.slug))
    },
    leave() {
      router.post(route('groups.leave', this.group.slug))
    },
    createPost() {
      router.post(route('groups.posts.store', this.group.slug), {
        title: this.title, content: this.content, visibility: this.visibility
      })
    }
  }
}
</script>
