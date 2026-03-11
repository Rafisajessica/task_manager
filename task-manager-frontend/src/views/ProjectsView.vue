<template>
  <div>
    <div class="flex items-center justify-between mb-6">
     <div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 bg-yellow-600/20 rounded-xl flex items-center justify-center">
      <FolderOpenIcon class="w-5 h-5 text-yellow-400" />
    </div>
    <div>
      <h1 class="text-2xl font-bold text-white">Projets</h1>
      <p class="text-gray-400 text-xs">Gérez vos projets</p>
    </div>
  </div>
      <button
        v-if="auth && ['admin', 'manager'].includes(auth.user?.role)"
        @click="showForm = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
        + Nouveau projet
      </button>
    </div>

    <!-- Formulaire -->
    <div v-if="showForm" class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
      <h2 class="text-white font-semibold mb-4">Nouveau projet</h2>
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="text-gray-400 text-sm mb-1 block">Nom *</label>
          <input v-model="form.name" type="text" placeholder="Nom du projet"
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                   focus:border-blue-500 outline-none" />
        </div>
        <div>
          <label class="text-gray-400 text-sm mb-1 block">Priorité</label>
          <select v-model="form.priority_level"
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600 outline-none">
            <option value="low">Faible</option>
            <option value="normal">Normale</option>
            <option value="high">Haute</option>
            <option value="critical">Critique</option>
          </select>
        </div>
        <div>
          <label class="text-gray-400 text-sm mb-1 block">Date de fin</label>
          <input v-model="form.end_date" type="date"
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                   focus:border-blue-500 outline-none" />
        </div>
      </div>
      <div class="flex gap-3 mt-4">
        <button @click="handleCreate" :disabled="creating"
          class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2
                 rounded-lg text-sm font-medium transition">
          {{ creating ? 'Création...' : 'Créer' }}
        </button>
        <button @click="showForm = false"
          class="bg-gray-700 hover:bg-gray-600 text-gray-300 px-6 py-2 rounded-lg text-sm transition">
          Annuler
        </button>
      </div>
    </div>

    <!-- Liste projets -->
    <div v-if="projectStore.loading" class="text-gray-400">Chargement...</div>
    <div v-else-if="projectStore.projects.length === 0"
      class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
      <p class="text-5xl mb-3">📁</p>
      <p class="text-gray-400">Aucun projet pour l'instant</p>
    </div>
    <div v-else class="grid grid-cols-2 gap-4">
      <div v-for="project in projectStore.projects" :key="project.id"
        class="bg-gray-800 rounded-xl border border-gray-700 p-6 hover:border-gray-600 transition">
        <div class="flex items-start justify-between mb-3">
          <h3 class="text-white font-semibold">{{ project.name }}</h3>
          <span :class="priorityClass(project.priority_level)"
            class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
            {{ project.priority_level }}
          </span>
        </div>
        <div class="mb-3">
          <div class="flex justify-between text-xs text-gray-400 mb-1">
            <span>Progression</span>
            <span>{{ project.completion || 0 }}%</span>
          </div>
          <div class="w-full bg-gray-700 rounded-full h-2">
            <div class="bg-blue-500 h-2 rounded-full transition-all"
              :style="{ width: (project.completion || 0) + '%' }"></div>
          </div>
        </div>
        <div class="flex justify-between items-center text-xs text-gray-500">
          <span>
            {{ project.end_date
              ? new Date(project.end_date).toLocaleDateString('fr-FR')
              : 'Pas de deadline' }}
          </span>
          <button @click="handleDelete(project.id)"
            class="text-gray-500 hover:text-red-400 transition">
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProjectStore } from '../stores/projects'
import { useAuthStore } from '../stores/auth'
import { FolderOpenIcon } from '@heroicons/vue/24/outline'

const projectStore = useProjectStore()
const auth         = useAuthStore()  // ← auth pas authStore
const showForm     = ref(false)
const creating     = ref(false)

const form = ref({
  name: '', priority_level: 'normal', end_date: ''
})

onMounted(() => projectStore.fetchProjects())

async function handleCreate() {
  if (!form.value.name) return
  creating.value = true
  try {
    await projectStore.createProject(form.value)
    showForm.value = false
    form.value = { name: '', priority_level: 'normal', end_date: '' }
  } finally {
    creating.value = false
  }
}

async function handleDelete(id) {
  if (confirm('Supprimer ce projet ?')) {
    await projectStore.deleteProject(id)
  }
}

function priorityClass(level) {
  const map = {
    low:      'bg-gray-500/20 text-gray-400',
    normal:   'bg-blue-500/20 text-blue-400',
    high:     'bg-orange-500/20 text-orange-400',
    critical: 'bg-red-500/20 text-red-400',
  }
  return map[level] || 'bg-gray-500/20 text-gray-400'
}
</script>