<template>
  <div>
    <h1>Kalender Grup</h1>
    <div class="flex items-center gap-2">
      <button @click="prev">Prev</button>
      <div>{{ formatLabel }}</div>
      <button @click="next">Next</button>
      <select v-model="view">
        <option value="month">Month</option>
        <option value="week">Week</option>
        <option value="day">Day</option>
      </select>
    </div>
    <div class="mt-4">
      <div v-if="view==='month'" class="grid grid-cols-7 gap-2">
        <div v-for="d in monthDays" :key="d.key" class="border h-32 relative"
             @mousedown="startCreate(d.date, $event)"
             @mouseup="finishCreate(d.date, $event)">
          <div class="text-xs p-1">{{ d.label }}</div>
          <div v-for="e in dayEvents(d.date)" :key="e.id" class="absolute left-1 right-1 bg-blue-500 text-white text-xs p-1 rounded">
            {{ e.title }}
          </div>
        </div>
      </div>
      <!-- Simplified week/day views -->
      <div v-else class="grid grid-cols-1 gap-2">
        <div class="border h-96 relative"
             @mousedown="startCreate(currentStart, $event)"
             @mouseup="finishCreate(currentStart, $event)">
          <div v-for="e in rangeEvents(currentStart, view)" :key="e.id" class="absolute left-1 right-1 bg-blue-500 text-white text-xs p-1 rounded">
            {{ e.title }}
          </div>
        </div>
      </div>
    </div>
    <div v-if="creating" class="fixed bottom-4 right-4 bg-white border p-3 rounded shadow">
      <input v-model="form.title" placeholder="Judul" class="border p-2 mb-2 w-64" />
      <input v-model="form.location" placeholder="Lokasi" class="border p-2 mb-2 w-64" />
      <button @click="save" class="bg-blue-600 text-white px-3 py-2">Simpan</button>
      <button @click="cancel" class="ml-2 px-3 py-2">Batal</button>
    </div>
  </div>
</template>
<script>
import { format, startOfMonth, endOfMonth, eachDayOfInterval } from 'date-fns'
import { router } from '@inertiajs/vue3'
export default {
  props: { group: Object, events: Array },
  data() {
    const now = new Date()
    return {
      current: now,
      currentStart: now,
      view: 'month',
      creating: false,
      createStart: null,
      createEnd: null,
      form: { title: '', location: '', starts_at: '', ends_at: '', status: 'upcoming' }
    }
  },
  computed: {
    formatLabel() { return format(this.current, this.view==='month' ? 'MMMM yyyy' : (this.view==='week' ? 'wo MMM yyyy' : 'PPP')) },
    monthDays() {
      const start = startOfMonth(this.current)
      const end = endOfMonth(this.current)
      return eachDayOfInterval({ start, end }).map(d => ({ date: d, label: format(d, 'd'), key: format(d, 'yyyy-MM-dd') }))
    }
  },
  methods: {
    prev() {
      const d = new Date(this.current)
      if (this.view==='month') d.setMonth(d.getMonth()-1)
      else d.setDate(d.getDate() - (this.view==='week' ? 7 : 1))
      this.current = d
    },
    next() {
      const d = new Date(this.current)
      if (this.view==='month') d.setMonth(d.getMonth()+1)
      else d.setDate(d.getDate() + (this.view==='week' ? 7 : 1))
      this.current = d
    },
    dayEvents(date) {
      const key = format(date, 'yyyy-MM-dd')
      return (this.events || []).filter(e => e.starts_at?.slice(0,10)===key)
    },
    rangeEvents(date, view) {
      return (this.events || [])
    },
    startCreate(date, evt) {
      this.creating = true
      this.createStart = date
      const startIso = new Date(date)
      this.form.starts_at = format(startIso, "yyyy-MM-dd'T'HH:mm")
      this.form.ends_at = format(new Date(startIso.getTime()+60*60*1000), "yyyy-MM-dd'T'HH:mm")
    },
    finishCreate(date, evt) {},
    save() {
      router.post(route('groups.events.store', this.group.slug), this.form, {
        onSuccess: () => { this.creating=false; this.form={ title:'', location:'', starts_at:'', ends_at:'', status:'upcoming' } }
      })
    },
    cancel() { this.creating=false }
  }
}
</script>
