<template>
  <div>
    <h1>Events Grup</h1>
    <a :href="route('groups.show', group.slug)">Kembali</a>
    <form @submit.prevent="createEvent" class="mt-4">
      <input v-model="form.title" placeholder="Judul" class="border p-2 mb-2 w-full" />
      <textarea v-model="form.description" placeholder="Deskripsi" class="border p-2 mb-2 w-full"></textarea>
      <input v-model="form.starts_at" type="datetime-local" class="border p-2 mb-2 w-full" />
      <input v-model="form.ends_at" type="datetime-local" class="border p-2 mb-2 w-full" />
      <input v-model="form.location" placeholder="Lokasi" class="border p-2 mb-2 w-full" />
      <select v-model="form.status" class="border p-2 mb-2 w-full">
        <option value="upcoming">Upcoming</option>
        <option value="ongoing">Ongoing</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
      <button class="bg-blue-600 text-white px-3 py-2">Buat Event</button>
    </form>
    <div v-if="events.data && events.data.length" class="mt-6">
      <div v-for="e in events.data" :key="e.id" class="border p-3 mb-2">
        <h3>{{ e.title }} <small>({{ e.status }})</small></h3>
        <p>{{ e.description }}</p>
        <p>{{ e.starts_at }} - {{ e.ends_at || '-' }}</p>
        <p>{{ e.location || '-' }}</p>
        <div class="mt-2">
          <button @click="rsvp(e.id, 'accepted')" class="px-2 py-1 bg-green-600 text-white">Hadir</button>
          <button @click="rsvp(e.id, 'declined')" class="px-2 py-1 bg-red-600 text-white ml-2">Tidak</button>
          <button @click="rsvp(e.id, 'maybe')" class="px-2 py-1 bg-gray-600 text-white ml-2">Mungkin</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { router } from '@inertiajs/vue3'
export default {
  props: { group: Object, events: Object, calendar: Object },
  data() {
    return { form: { title: '', description: '', starts_at: '', ends_at: '', location: '', status: 'upcoming' } }
  },
  methods: {
    createEvent() {
      router.post(route('groups.events.store', this.group.slug), this.form)
    },
    rsvp(eventId, status) {
      router.post(route('groups.events.rsvp', { slug: this.group.slug, event: eventId }), { rsvp_status: status })
    }
  }
}
</script>
