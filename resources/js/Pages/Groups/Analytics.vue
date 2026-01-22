<template>
  <div>
    <h1>Analytics Grup</h1>
    <div class="grid grid-cols-2 gap-4">
      <div class="p-4 border rounded">
        <h2>Active Members (30d): {{ metrics.activeMembers30d }}</h2>
      </div>
      <div class="p-4 border rounded">
        <canvas id="growthChart"></canvas>
      </div>
    </div>
    <div class="mt-4">
      <h2>Engagement</h2>
      <canvas id="engagementChart"></canvas>
    </div>
    <div class="mt-4">
      <a :href="route('groups.analytics.export.csv', group.slug)" class="px-3 py-2 bg-gray-800 text-white">Export CSV</a>
      <a :href="route('groups.analytics.export.pdf', group.slug)" class="ml-2 px-3 py-2 bg-gray-800 text-white">Export PDF</a>
    </div>
  </div>
</template>
<script>
import { onMounted } from 'vue'
import { Chart, BarController, BarElement, CategoryScale, LinearScale } from 'chart.js'
Chart.register(BarController, BarElement, CategoryScale, LinearScale)
export default {
  props: { group: Object, metrics: Object, engagement: Array, growth: Array },
  setup(props) {
    onMounted(() => {
      const growthCtx = document.getElementById('growthChart')
      if (growthCtx) {
        new Chart(growthCtx, {
          type: 'bar',
          data: {
            labels: props.growth.map(g => g.d),
            datasets: [{ label: 'New Members', data: props.growth.map(g => g.c), backgroundColor: '#2563eb' }]
          }
        })
      }
      const engCtx = document.getElementById('engagementChart')
      if (engCtx) {
        new Chart(engCtx, {
          type: 'bar',
          data: {
            labels: props.engagement.map(e => e.title),
            datasets: [
              { label: 'RSVP', data: props.engagement.map(e => e.rsvp), backgroundColor: '#10b981' },
              { label: 'Accepted', data: props.engagement.map(e => e.accepted), backgroundColor: '#ef4444' }
            ]
          }
        })
      }
    })
  }
}
</script>
