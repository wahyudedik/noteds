<template>
  <div>
    <h1>Undangan Bergabung: {{ group.name }}</h1>
    <p v-if="invite.email">Diundang: {{ invite.email }}</p>
    <div>
      <form :action="route('groups.invites.accept', invite.token)" method="post">
        <button type="submit" class="bg-green-600 text-white px-3 py-2">Terima</button>
      </form>
      <form :action="route('groups.invites.decline', invite.token)" method="post" class="mt-2">
        <button type="submit" class="bg-red-600 text-white px-3 py-2">Tolak</button>
      </form>
    </div>
  </div>
</template>
<script>
export default {
  props: { group: Object, invite: Object }
}
</script>
