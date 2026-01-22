<template>
  <div>
    <h1>Groups</h1>
    <form @submit.prevent="search">
      <input v-model="q" placeholder="Cari grup" />
      <select v-model="privacy">
        <option value="">Semua</option>
        <option value="public">Public</option>
        <option value="private">Private</option>
        <option value="secret">Secret</option>
      </select>
      <button type="submit">Cari</button>
    </form>
    <div v-if="groups.data && groups.data.length">
      <div v-for="g in groups.data" :key="g.id">
        <a :href="route('groups.show', g.slug)">{{ g.name }}</a>
        <span>{{ g.privacy }}</span>
      </div>
    </div>
  </div>
</template>
<script>
import { router } from '@inertiajs/vue3'
export default {
  props: { groups: Object, filters: Object },
  data() {
    return { q: this.filters?.q || '', privacy: this.filters?.privacy || '' }
  },
  methods: {
    search() {
      router.get(route('groups.index'), { q: this.q, privacy: this.privacy }, { preserveState: true, replace: true })
    }
  }
}
</script>
