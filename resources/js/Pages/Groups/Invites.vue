<template>
  <div>
    <h1>Undangan Grup</h1>
    <a :href="route('groups.show', group.slug)">Kembali</a>
    <div v-if="invites.data && invites.data.length">
      <table class="w-full">
        <thead>
          <tr><th>Email</th><th>Status</th><th>Expires</th><th>Link</th></tr>
        </thead>
        <tbody>
          <tr v-for="i in invites.data" :key="i.id">
            <td>{{ i.email || '-' }}</td>
            <td>{{ i.status }}</td>
            <td>{{ i.expires_at }}</td>
            <td><a :href="route('groups.invites.show', i.token)">Buka</a></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script>
export default {
  props: { group: Object, invites: Object }
}
</script>
