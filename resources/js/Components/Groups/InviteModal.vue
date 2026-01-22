<template>
  <div v-if="open" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white w-full max-w-md p-4 rounded">
      <h2>Undang Anggota</h2>
      <form @submit.prevent="inviteEmail">
        <input v-model="email" type="email" placeholder="Email" class="w-full border p-2 mb-2" />
        <input v-model="expires_at" type="date" class="w-full border p-2 mb-2" />
        <button class="bg-blue-600 text-white px-3 py-2">Kirim Undangan Email</button>
      </form>
      <div class="mt-4">
        <button @click="generateLink" class="bg-gray-800 text-white px-3 py-2">Generate Link Undangan</button>
        <div v-if="inviteLink" class="mt-2 break-all">{{ inviteLink }}</div>
      </div>
      <div class="mt-4">
        <button @click="$emit('close')" class="px-3 py-2">Tutup</button>
      </div>
    </div>
  </div>
</template>
<script>
import { router } from '@inertiajs/vue3'
export default {
  props: { open: Boolean, slug: String },
  data() {
    return { email: '', expires_at: '', inviteLink: '' }
  },
  methods: {
    inviteEmail() {
      router.post(route('groups.invites.email', this.slug), { email: this.email, expires_at: this.expires_at }, {
        onSuccess: (page) => { this.inviteLink = page.props?.flash?.invite_link || '' }
      })
    },
    generateLink() {
      router.post(route('groups.invites.link', this.slug), { expires_at: this.expires_at }, {
        onSuccess: (page) => { this.inviteLink = page.props?.flash?.invite_link || '' }
      })
    }
  }
}
</script>
