\<template>
  <div>
    <!-- Titre -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-600/20 rounded-xl flex items-center justify-center">
          <Squares2X2Icon class="w-5 h-5 text-green-400" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-white">Dashboard</h1>
          <p class="text-gray-400 text-xs">Vue d'ensemble de votre activité</p>
        </div>
      </div>
      <p class="text-gray-500 text-xs">{{ currentDate }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <div v-for="card in statCards" :key="card.label"
        class="bg-gray-800/80 rounded-2xl p-5 border border-gray-700/50 hover:border-gray-600 transition">
        <div class="flex items-center justify-between mb-4">
          <p class="text-gray-400 text-sm">{{ card.label }}</p>
          <div :class="`w-9 h-9 ${card.bg} rounded-xl flex items-center justify-center`">
            <component :is="card.icon" :class="`w-4 h-4 ${card.color}`" />
          </div>
        </div>
        <p :class="`text-3xl font-bold ${card.color}`">{{ card.value }}</p>
        <p class="text-gray-600 text-xs mt-1">{{ card.sub }}</p>
      </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-3 gap-6 mb-6">

      <!-- Line Chart — Activité -->
      <div class="col-span-2 bg-gray-800/80 rounded-2xl border border-gray-700/50 p-6">
        <h2 class="text-white font-semibold mb-1">Performance</h2>
        <p class="text-gray-500 text-xs mb-4">Tâches créées vs terminées</p>
        <apexchart type="area" height="200"
          :options="lineOptions" :series="lineSeries" />
      </div>

      <!-- Donut — Statuts -->
      <div class="bg-gray-800/80 rounded-2xl border border-gray-700/50 p-6">
        <h2 class="text-white font-semibold mb-1">Répartition</h2>
        <p class="text-gray-500 text-xs mb-4">Par statut</p>
        <apexchart type="donut" height="200"
          :options="donutOptions" :series="donutSeries" />
      </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-3 gap-6 mb-6">

      <!-- Bar — Score IA -->
      <div class="bg-gray-800/80 rounded-2xl border border-gray-700/50 p-6">
        <h2 class="text-white font-semibold mb-1">Score IA</h2>
        <p class="text-gray-500 text-xs mb-4">Top tâches prioritaires</p>
        <apexchart type="bar" height="200"
          :options="barOptions" :series="barSeries" />
      </div>

      <!-- Radar — Complexité -->
      <div class="bg-gray-800/80 rounded-2xl border border-gray-700/50 p-6">
        <h2 class="text-white font-semibold mb-1">Complexité</h2>
        <p class="text-gray-500 text-xs mb-4">Distribution par niveau</p>
        <apexchart type="bar" height="200"
          :options="complexityOptions" :series="complexitySeries" />
      </div>

      <!-- Stats projets -->
      <div class="bg-gray-800/80 rounded-2xl border border-gray-700/50 p-6">
        <h2 class="text-white font-semibold mb-4">Projets actifs</h2>
        <div v-if="projectStore.projects.length === 0"
          class="text-gray-500 text-sm text-center py-8">
          Aucun projet
        </div>
        <div v-else class="space-y-3">
          <div v-for="project in projectStore.projects.slice(0,4)" :key="project.id">
            <div class="flex justify-between text-xs mb-1">
              <span class="text-gray-300 truncate">{{ project.name }}</span>
              <span class="text-gray-500">{{ project.completion || 0 }}%</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-1.5">
              <div class="h-1.5 rounded-full transition-all"
                :class="progressColor(project.completion)"
                :style="{ width: (project.completion || 0) + '%' }">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tâches urgentes -->
    <div class="bg-gray-800/80 rounded-2xl border border-gray-700/50 p-6">
      <div class="flex items-center gap-2 mb-4">
        <FireIcon class="w-5 h-5 text-red-400" />
        <h2 class="text-white font-semibold">Tâches Urgentes</h2>
        <span class="bg-red-500/20 text-red-400 text-xs px-2 py-0.5 rounded-full ml-auto">
          {{ urgentTasks.length }} tâches
        </span>
      </div>
      <div v-if="urgentTasks.length === 0"
        class="text-center py-8">
        <CheckCircleIcon class="w-10 h-10 text-green-500 mx-auto mb-2" />
        <p class="text-gray-400 text-sm">Aucune tâche urgente</p>
      </div>
      <div v-else class="space-y-2">
        <div v-for="task in urgentTasks" :key="task.id"
          class="flex items-center justify-between p-3 bg-gray-700/40 rounded-xl hover:bg-gray-700/60 transition">
          <div class="flex items-center gap-3">
            <div :class="priorityDot(task.priority_score)"
              class="w-2 h-2 rounded-full flex-shrink-0"></div>
            <div>
              <p class="text-white text-sm font-medium">{{ task.title }}</p>
              <p class="text-gray-500 text-xs">
                {{ task.deadline
                  ? new Date(task.deadline).toLocaleDateString('fr-FR')
                  : 'Pas de deadline' }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <span :class="priorityClass(task.priority_score)"
              class="px-2 py-0.5 rounded-full text-xs font-bold">
              {{ task.priority_score }}/100
            </span>
            <span :class="statusClass(task.status)"
              class="px-2 py-0.5 rounded-full text-xs capitalize">
              {{ task.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import {
  Squares2X2Icon, ClipboardDocumentListIcon,
  ArrowPathIcon, CheckCircleIcon,
  ExclamationCircleIcon, FireIcon
} from '@heroicons/vue/24/outline'
import { useTaskStore }    from '../stores/tasks'
import { useProjectStore } from '../stores/projects'

const apexchart   = VueApexCharts
const taskStore    = useTaskStore()
const projectStore = useProjectStore()
const urgentTasks  = ref([])

const currentDate = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
})

// Stat cards
const statCards = computed(() => [
  {
    label: 'Total Tâches',
    value: taskStore.tasks.length,
    sub:   'toutes les tâches',
    icon:  ClipboardDocumentListIcon,
    color: 'text-blue-400',
    bg:    'bg-blue-600/20',
  },
  {
    label: 'En cours',
    value: taskStore.tasks.filter(t => t.status === 'in_progress').length,
    sub:   'en progression',
    icon:  ArrowPathIcon,
    color: 'text-yellow-400',
    bg:    'bg-yellow-600/20',
  },
  {
    label: 'Terminées',
    value: taskStore.tasks.filter(t => t.status === 'done').length,
    sub:   'complétées',
    icon:  CheckCircleIcon,
    color: 'text-green-400',
    bg:    'bg-green-600/20',
  },
  {
    label: 'En retard',
    value: taskStore.tasks.filter(t =>
      t.deadline && new Date(t.deadline) < new Date() &&
      !['done','cancelled'].includes(t.status)
    ).length,
    sub:   'dépassées',
    icon:  ExclamationCircleIcon,
    color: 'text-red-400',
    bg:    'bg-red-600/20',
  },
])

// Couleurs ApexCharts communes
const chartColors = {
  grid:  '#1F2937',
  text:  '#9CA3AF',
  bg:    'transparent',
}

// Line chart
const lineSeries = computed(() => [{
  name: 'Tâches actives',
  data: [
    taskStore.tasks.filter(t => t.status === 'todo').length,
    taskStore.tasks.filter(t => t.status === 'in_progress').length,
    taskStore.tasks.filter(t => t.status === 'waiting').length,
    taskStore.tasks.filter(t => t.status === 'done').length,
    taskStore.tasks.filter(t => t.status === 'cancelled').length,
  ]
}])
const lineOptions = {
  chart: { background: chartColors.bg, toolbar: { show: false }, sparkline: { enabled: false } },
  colors: ['#10B981'],
  stroke: { curve: 'smooth', width: 3 },
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
  xaxis: {
    categories: ['À faire', 'En cours', 'En attente', 'Terminée', 'Annulée'],
    labels: { style: { colors: chartColors.text } },
    axisBorder: { show: false },
  },
  yaxis: { labels: { style: { colors: chartColors.text } } },
  grid: { borderColor: chartColors.grid },
  tooltip: { theme: 'dark' },
  dataLabels: { enabled: false },
}

// Donut chart
const donutSeries  = computed(() => [
  taskStore.tasks.filter(t => t.status === 'todo').length,
  taskStore.tasks.filter(t => t.status === 'in_progress').length,
  taskStore.tasks.filter(t => t.status === 'waiting').length,
  taskStore.tasks.filter(t => t.status === 'done').length,
  taskStore.tasks.filter(t => t.status === 'cancelled').length,
])
const donutOptions = {
  chart:  { background: chartColors.bg, toolbar: { show: false } },
  colors: ['#3B82F6', '#F59E0B', '#8B5CF6', '#10B981', '#EF4444'],
  labels: ['À faire', 'En cours', 'En attente', 'Terminée', 'Annulée'],
  legend: { position: 'bottom', labels: { colors: chartColors.text } },
  dataLabels: { enabled: false },
  plotOptions: { pie: { donut: { size: '65%' } } },
  tooltip: { theme: 'dark' },
}

// Bar chart — Score IA
const top5      = computed(() =>
  [...taskStore.tasks].sort((a, b) => b.priority_score - a.priority_score).slice(0, 5)
)
const barSeries  = computed(() => [{
  name: 'Score IA',
  data: top5.value.map(t => t.priority_score)
}])
const barOptions = computed(() => ({
  chart:  { background: chartColors.bg, toolbar: { show: false } },
  colors: ['#6366F1'],
  xaxis:  {
    categories: top5.value.map(t => t.title.length > 10 ? t.title.slice(0,10)+'...' : t.title),
    labels: { style: { colors: chartColors.text } },
  },
  yaxis:  { min: 0, max: 100, labels: { style: { colors: chartColors.text } } },
  grid:   { borderColor: chartColors.grid },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
  dataLabels: { enabled: false },
  tooltip: { theme: 'dark' },
}))

// Complexity chart
const complexitySeries  = computed(() => [{
  name: 'Tâches',
  data: [1,2,3,4,5].map(n =>
    taskStore.tasks.filter(t => t.complexity === n).length
  )
}])
const complexityOptions = {
  chart:  { background: chartColors.bg, toolbar: { show: false } },
  colors: ['#F59E0B'],
  xaxis:  {
    categories: ['Niveau 1', 'Niveau 2', 'Niveau 3', 'Niveau 4', 'Niveau 5'],
    labels: { style: { colors: chartColors.text } },
  },
  yaxis:  { labels: { style: { colors: chartColors.text } } },
  grid:   { borderColor: chartColors.grid },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
  dataLabels: { enabled: false },
  tooltip: { theme: 'dark' },
}

onMounted(async () => {
  await taskStore.fetchTasks()
  await projectStore.fetchProjects()
  urgentTasks.value = taskStore.tasks
    .filter(t => !['done','cancelled'].includes(t.status))
    .slice(0, 5)
})

function progressColor(pct) {
  if (pct >= 75) return 'bg-green-500'
  if (pct >= 40) return 'bg-yellow-500'
  return 'bg-blue-500'
}
function priorityDot(score) {
  if (score >= 75) return 'bg-red-400'
  if (score >= 50) return 'bg-orange-400'
  return 'bg-green-400'
}
function priorityClass(score) {
  if (score >= 75) return 'bg-red-500/20 text-red-400'
  if (score >= 50) return 'bg-orange-500/20 text-orange-400'
  return 'bg-green-500/20 text-green-400'
}
function statusClass(status) {
  const map = {
    todo:        'bg-gray-500/20 text-gray-400',
    in_progress: 'bg-blue-500/20 text-blue-400',
    waiting:     'bg-yellow-500/20 text-yellow-400',
    done:        'bg-green-500/20 text-green-400',
    cancelled:   'bg-red-500/20 text-red-400',
  }
  return map[status] || 'bg-gray-500/20 text-gray-400'
}
</script>