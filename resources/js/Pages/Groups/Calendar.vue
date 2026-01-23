<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex items-center justify-between">
      <div class="text-lg font-semibold text-gray-900 dark:text-white">Kalender Grup</div>
      <div class="flex items-center gap-2">
        <button @click="prev" class="px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">Prev</button>
        <div class="min-w-[160px] text-center text-sm font-medium text-gray-900 dark:text-white">{{ formatLabel }}</div>
        <button @click="next" class="px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">Next</button>
        <select v-model="view" class="ml-2 px-2 py-1.5 rounded-md border border-gray-200 bg-white text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
          <option value="month">Month</option>
          <option value="week">Week</option>
          <option value="day">Day</option>
        </select>
        <button @click="quickCreate" class="ml-2 px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Add Event</button>
      </div>
    </div>
    <div class="mt-4">
      <div v-if="view==='month'" class="grid grid-cols-7 gap-2">
        <div v-for="d in monthDays" :key="d.key" class="border border-gray-200 dark:border-gray-700 h-32 relative rounded-md bg-gray-50 dark:bg-gray-700/40"
             @mousedown="startCreate(d.date, $event)"
             @mouseup="finishCreate(d.date, $event)">
          <div class="text-xs p-1 text-gray-600 dark:text-gray-300">{{ d.label }}</div>
          <div v-for="e in dayEvents(d.date)" :key="e.id" class="absolute left-1 right-1 bg-indigo-600 text-white text-xs p-1 rounded-md">
            {{ e.title }}
          </div>
        </div>
      </div>
      <!-- Simplified week/day views -->
      <div v-else class="grid grid-cols-1 gap-2">
        <div class="border border-gray-200 dark:border-gray-700 h-96 relative rounded-md bg-gray-50 dark:bg-gray-700/40"
             @mousedown="startCreate(currentStart, $event)"
             @mouseup="finishCreate(currentStart, $event)">
          <div v-for="e in rangeEvents(currentStart, view)" :key="e.id" class="absolute left-1 right-1 bg-indigo-600 text-white text-xs p-1 rounded-md">
            {{ e.title }}
          </div>
        </div>
      </div>
    </div>
    <div v-if="creating" class="fixed bottom-4 right-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-lg shadow-xl w-80">
      <div class="text-sm font-semibold mb-2 text-gray-900 dark:text-white">Buat Event</div>
      <input v-model="form.title" placeholder="Judul" class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md p-2 mb-2 w-full" />
      <input v-model="form.location" placeholder="Lokasi" class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md p-2 mb-2 w-full" />
      <div class="grid grid-cols-1 gap-2">
        <input v-model="form.starts_at" type="datetime-local" class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md p-2 w-full" />
        <input v-model="form.ends_at" type="datetime-local" class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md p-2 w-full" />
      </div>
      <select v-model="form.recurrence" class="mt-2 w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md p-2">
        <option value="">Once</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
      </select>
      <div class="mt-3 flex items-center justify-end gap-2">
        <button @click="cancel" class="px-3 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">Batal</button>
        <button @click="save" class="px-3 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
      </div>
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
      form: { title: '', location: '', starts_at: '', ends_at: '', status: 'upcoming', recurrence: '' }
    }
  },
  computed: {
    formatLabel() { return format(this.current, this.view==='month' ? 'MMMM yyyy' : (this.view==='week' ? 'wo MMM yyyy' : 'PPP')) },
    monthDays() {
      const start = startOfMonth(this.current)
      const end = endOfMonth(this.current)
      return eachDayOfInterval({ start, end }).map(d => ({ date: d, label: format(d, 'd'), key: format(d, 'yyyy-MM-dd') }))
    },
    expandedEvents() {
      const evs = (this.events || []).slice()
      const start = this.view==='month' ? startOfMonth(this.current) : this.current
      const end = this.view==='month' ? endOfMonth(this.current) : new Date(this.current.getTime() + (this.view==='week' ? 7 : 1)*24*60*60*1000)
      const out = []
      for (const e of evs) {
        const rec = e.recurrence || ''
        const baseStart = new Date(e.starts_at)
        if (!rec) {
          out.push(e)
          continue
        }
        let cursor = new Date(baseStart)
        while (cursor <= end) {
          if (cursor >= start) {
            out.push({
              ...e,
              id: `${e.id}-${format(cursor,'yyyyMMdd')}`,
              starts_at: format(cursor, "yyyy-MM-dd'T'HH:mm"),
            })
          }
          if (rec === 'daily') {
            cursor = new Date(cursor.getTime() + 24*60*60*1000)
          } else if (rec === 'weekly') {
            cursor = new Date(cursor.getTime() + 7*24*60*60*1000)
          } else if (rec === 'monthly') {
            const d = new Date(cursor)
            d.setMonth(d.getMonth()+1)
            cursor = d
          } else {
            break
          }
        }
      }
      return out
    }
  },
  methods: {
    quickCreate() {
      this.creating = true
      const startIso = new Date(this.current)
      this.form.starts_at = format(startIso, "yyyy-MM-dd'T'HH:mm")
      this.form.ends_at = format(new Date(startIso.getTime()+60*60*1000), "yyyy-MM-dd'T'HH:mm")
    },
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
      return (this.expandedEvents || []).filter(e => e.starts_at?.slice(0,10)===key)
    },
    rangeEvents(date, view) {
      return (this.expandedEvents || [])
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
        onSuccess: () => { this.creating=false; this.form={ title:'', location:'', starts_at:'', ends_at:'', status:'upcoming', recurrence:'' } }
      })
    },
    cancel() { this.creating=false }
  }
}
</script>
