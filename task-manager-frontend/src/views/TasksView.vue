<template>
  <div>
    <!-- Titre -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-600/20 rounded-xl flex items-center justify-center">
          <ClipboardDocumentListIcon class="w-5 h-5 text-blue-400" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-white">Mes Tâches</h1>
          <p class="text-gray-400 text-xs">Triées par priorité IA</p>
        </div>
      </div>
      <button
        v-if="auth && ['admin', 'manager'].includes(auth.user?.role)"
        @click="showForm = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm
               font-medium transition flex items-center gap-2">
        <PlusIcon class="w-4 h-4" />
        Nouvelle tâche
      </button>
    </div>

    <!-- Formulaire création -->
    <div v-if="showForm" class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
      <h2 class="text-white font-semibold mb-4">Nouvelle tâche</h2>
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="text-gray-400 text-sm mb-1 block">Titre *</label>
          <input v-model="form.title" type="text" placeholder="Titre de la tâche"
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                   focus:border-blue-500 outline-none" />
        </div>
        <div class="col-span-2">
          <label class="text-gray-400 text-sm mb-1 block">Description</label>
          <textarea v-model="form.description" rows="2" placeholder="Description..."
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                   focus:border-blue-500 outline-none resize-none" />
        </div>
        <div>
          <label class="text-gray-400 text-sm mb-1 block">Deadline</label>
          <input v-model="form.deadline" type="date"
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                   focus:border-blue-500 outline-none" />
        </div>
        <div>
          <label class="text-gray-400 text-sm mb-1 block">Complexité (1-5)</label>
          <select v-model="form.complexity"
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                   focus:border-blue-500 outline-none">
            <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
          </select>
        </div>
        <div>
          <label class="text-gray-400 text-sm mb-1 block">Statut</label>
          <select v-model="form.status"
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                   focus:border-blue-500 outline-none">
            <option value="todo">À faire</option>
            <option value="in_progress">En cours</option>
            <option value="waiting">En attente</option>
          </select>
        </div>
        <div v-if="auth && ['admin', 'manager'].includes(auth.user?.role)">
          <label class="text-gray-400 text-sm mb-1 block">Assigner à</label>
          <select v-model="form.assigned_to"
            class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                   focus:border-blue-500 outline-none">
            <option :value="null">Non assigné</option>
            <option v-for="user in userStore.users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.role }})
            </option>
          </select>
        </div>
      </div>
      <div class="flex gap-3 mt-4">
        <button @click="handleCreate" :disabled="creating"
          class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2
                 rounded-lg text-sm font-medium transition">
          {{ creating ? 'Création...' : 'Créer la tâche' }}
        </button>
        <button @click="showForm = false"
          class="bg-gray-700 hover:bg-gray-600 text-gray-300 px-6 py-2 rounded-lg text-sm transition">
          Annuler
        </button>
      </div>
    </div>

    <!-- Liste des tâches -->
    <div v-if="taskStore.loading" class="text-center py-12">
      <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
    </div>
    <div v-else-if="taskStore.tasks.length === 0"
      class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
      <ClipboardDocumentListIcon class="w-12 h-12 text-gray-600 mx-auto mb-3" />
      <p class="text-gray-400">Aucune tâche pour l'instant</p>
      <button
        v-if="auth && ['admin', 'manager'].includes(auth.user?.role)"
        @click="showForm = true"
        class="text-blue-400 text-sm mt-2 hover:underline">
        Créer votre première tâche
      </button>
    </div>
    <div v-else class="space-y-3">
      <div v-for="task in taskStore.tasks" :key="task.id"
        class="bg-gray-800 rounded-xl border border-gray-700 p-5 hover:border-gray-600 transition">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-1">
              <h3 class="text-white font-medium">{{ task.title }}</h3>
              <span :class="priorityClass(task.priority_score)"
                class="px-2 py-0.5 rounded-full text-xs font-bold flex items-center gap-1">
                <CpuChipIcon class="w-3 h-3" />
                {{ task.priority_score }}/100
              </span>
            </div>
            <p v-if="task.description" class="text-gray-400 text-sm mb-2">
              {{ task.description }}
            </p>
            <div class="flex items-center gap-4 text-xs text-gray-500">
              <span v-if="task.deadline" class="flex items-center gap-1">
                <CalendarIcon class="w-3 h-3" />
                {{ new Date(task.deadline).toLocaleDateString('fr-FR') }}
              </span>
              <span class="flex items-center gap-1">
                <BoltIcon class="w-3 h-3" />
                Complexité : {{ task.complexity }}/5
              </span>
              <span v-if="task.assignee" class="flex items-center gap-1">
                <UserIcon class="w-3 h-3" />
                {{ task.assignee.name }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 ml-4">
            <select :value="task.status" @change="handleStatusChange(task, $event)"
              class="bg-gray-700 text-gray-300 text-xs rounded-lg px-2 py-1 border border-gray-600
                     focus:border-blue-500 outline-none">
              <option value="todo">À faire</option>
              <option value="in_progress">En cours</option>
              <option value="waiting">En attente</option>
              <option value="done">Terminée</option>
              <option value="cancelled">Annulée</option>
            </select>

            <!-- Bouton Commentaires — visible par tous -->
            <button @click="openComments(task)"
              class="text-gray-500 hover:text-blue-400 transition p-1">
              <ChatBubbleLeftIcon class="w-4 h-4" />
            </button>

            <!-- Bouton Modifier — admin/manager seulement -->
            <button
              v-if="auth && ['admin', 'manager'].includes(auth.user?.role)"
              @click="openEdit(task)"
              class="text-gray-500 hover:text-blue-400 transition p-1">
              <PencilIcon class="w-4 h-4" />
            </button>

            <!-- Bouton Supprimer — admin/manager seulement -->
            <button
              v-if="auth && ['admin', 'manager'].includes(auth.user?.role)"
              @click="handleDelete(task.id)"
              class="text-gray-500 hover:text-red-400 transition p-1">
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Modifier -->
    <div v-if="editTask"
      class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 w-full max-w-lg">
        <h2 class="text-white font-semibold mb-4">Modifier la tâche</h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="text-gray-400 text-sm mb-1 block">Titre</label>
            <input v-model="editForm.title" type="text"
              class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                     focus:border-blue-500 outline-none" />
          </div>
          <div class="col-span-2">
            <label class="text-gray-400 text-sm mb-1 block">Description</label>
            <textarea v-model="editForm.description" rows="2"
              class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                     focus:border-blue-500 outline-none resize-none" />
          </div>
          <div>
            <label class="text-gray-400 text-sm mb-1 block">Deadline</label>
            <input v-model="editForm.deadline" type="date"
              class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                     focus:border-blue-500 outline-none" />
          </div>
          <div>
            <label class="text-gray-400 text-sm mb-1 block">Complexité (1-5)</label>
            <select v-model="editForm.complexity"
              class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                     focus:border-blue-500 outline-none">
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div>
            <label class="text-gray-400 text-sm mb-1 block">Statut</label>
            <select v-model="editForm.status"
              class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                     focus:border-blue-500 outline-none">
              <option value="todo">À faire</option>
              <option value="in_progress">En cours</option>
              <option value="waiting">En attente</option>
              <option value="done">Terminée</option>
              <option value="cancelled">Annulée</option>
            </select>
          </div>
          <div>
            <label class="text-gray-400 text-sm mb-1 block">Assigner à</label>
            <select v-model="editForm.assigned_to"
              class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 border border-gray-600
                     focus:border-blue-500 outline-none">
              <option :value="null">Non assigné</option>
              <option v-for="user in userStore.users" :key="user.id" :value="user.id">
                {{ user.name }} ({{ user.role }})
              </option>
            </select>
          </div>
        </div>
        <div class="flex gap-3 mt-4">
          <button @click="handleUpdate" :disabled="updating"
            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2
                   rounded-lg text-sm font-medium transition">
            {{ updating ? 'Mise à jour...' : 'Enregistrer' }}
          </button>
          <button @click="editTask = null"
            class="bg-gray-700 hover:bg-gray-600 text-gray-300 px-6 py-2 rounded-lg text-sm transition">
            Annuler
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Commentaires -->
    <div v-if="commentTask"
      class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 backdrop-blur-sm">
      <div class="bg-gray-900 rounded-2xl border border-gray-800 w-full max-w-lg
                  flex flex-col shadow-2xl" style="max-height: 80vh">

        <!-- Header -->
        <div class="flex items-center justify-between p-5 border-b border-gray-800">
          <div>
            <h2 class="text-white font-semibold">Commentaires</h2>
            <p class="text-gray-500 text-xs mt-0.5 truncate max-w-xs">
              {{ commentTask.title }}
            </p>
          </div>
          <button @click="commentTask = null"
            class="text-gray-500 hover:text-white transition">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Liste commentaires -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
          <div v-if="loadingComments" class="flex items-center justify-center py-8">
            <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent
                        rounded-full animate-spin"></div>
          </div>
          <div v-else-if="comments.length === 0" class="text-center py-8">
            <ChatBubbleLeftIcon class="w-8 h-8 text-gray-700 mx-auto mb-2" />
            <p class="text-gray-500 text-sm">Aucun commentaire pour l'instant</p>
          </div>
          <div v-else v-for="comment in comments" :key="comment.id" class="flex gap-3">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600
                        rounded-full flex items-center justify-center flex-shrink-0">
              <span class="text-white text-xs font-bold">
                {{ comment.user.name.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                  <span class="text-white text-sm font-medium">{{ comment.user.name }}</span>
                  <span class="text-gray-600 text-xs">
                    {{ formatCommentDate(comment.created_at) }}
                  </span>
                </div>
                <button
                  v-if="comment.user_id === auth.user?.id || auth.user?.role === 'admin'"
                  @click="deleteComment(comment.id)"
                  class="text-gray-600 hover:text-red-400 transition">
                  <TrashIcon class="w-3.5 h-3.5" />
                </button>
              </div>
              <p class="text-gray-300 text-sm bg-gray-800 rounded-xl px-3 py-2">
                {{ comment.content }}
              </p>
            </div>
          </div>
        </div>

        <!-- Input commentaire -->
        <div class="p-4 border-t border-gray-800">
          <div class="flex gap-3">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-700
                        rounded-full flex items-center justify-center flex-shrink-0">
              <span class="text-white text-xs font-bold">
                {{ auth.user?.name?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div class="flex-1 flex gap-2">
              <input v-model="newComment" @keyup.enter="submitComment" type="text"
                placeholder="Écrire un commentaire..."
                class="flex-1 bg-gray-800 text-white rounded-xl px-4 py-2 border
                       border-gray-700 focus:border-blue-500 outline-none text-sm
                       placeholder-gray-600" />
              <button @click="submitComment"
                :disabled="!newComment.trim() || submittingComment"
                class="bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white
                       px-4 py-2 rounded-xl transition">
                <PaperAirplaneIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
  ClipboardDocumentListIcon, PlusIcon, CpuChipIcon,
  CalendarIcon, BoltIcon, TrashIcon, UserIcon,
  PencilIcon, ChatBubbleLeftIcon, XMarkIcon, PaperAirplaneIcon
} from '@heroicons/vue/24/outline'
import { useTaskStore } from '../stores/tasks'
import { useAuthStore } from '../stores/auth'
import { useUserStore } from '../stores/users'
import api from '../api/axios'

const taskStore = useTaskStore()
const auth      = useAuthStore()
const userStore = useUserStore()
const showForm  = ref(false)
const creating  = ref(false)
const editTask  = ref(null)
const updating  = ref(false)
const editForm  = ref({})

const commentTask       = ref(null)
const comments          = ref([])
const newComment        = ref('')
const loadingComments   = ref(false)
const submittingComment = ref(false)

const form = ref({
  title: '', description: '', deadline: '',
  complexity: 3, status: 'todo', assigned_to: null
})

onMounted(() => {
  taskStore.fetchTasks()
  userStore.fetchUsers()
})

async function handleCreate() {
  if (!form.value.title) return
  creating.value = true
  try {
    await taskStore.createTask(form.value)
    showForm.value = false
    form.value = { title: '', description: '', deadline: '', complexity: 3, status: 'todo', assigned_to: null }
  } finally {
    creating.value = false
  }
}

async function handleStatusChange(task, event) {
  await taskStore.updateTask(task.id, { status: event.target.value })
}

async function handleDelete(id) {
  if (confirm('Supprimer cette tâche ?')) {
    await taskStore.deleteTask(id)
  }
}

function openEdit(task) {
  editTask.value = task
  editForm.value = {
    title:       task.title,
    description: task.description || '',
    deadline:    task.deadline ? new Date(task.deadline).toISOString().split('T')[0] : '',
    complexity:  task.complexity,
    status:      task.status,
    assigned_to: task.assigned_to,
  }
}

async function handleUpdate() {
  updating.value = true
  try {
    await taskStore.updateTask(editTask.value.id, editForm.value)
    editTask.value = null
  } finally {
    updating.value = false
  }
}

async function openComments(task) {
  commentTask.value     = task
  loadingComments.value = true
  try {
    const res      = await api.get(`/tasks/${task.id}/comments`)
    comments.value = res.data
  } finally {
    loadingComments.value = false
  }
}

async function submitComment() {
  if (!newComment.value.trim()) return
  submittingComment.value = true
  try {
    const res = await api.post(`/tasks/${commentTask.value.id}/comments`, {
      content: newComment.value
    })
    comments.value.push(res.data)
    newComment.value = ''
  } finally {
    submittingComment.value = false
  }
}

async function deleteComment(id) {
  await api.delete(`/comments/${id}`)
  comments.value = comments.value.filter(c => c.id !== id)
}

function priorityClass(score) {
  if (score >= 75) return 'bg-red-500/20 text-red-400'
  if (score >= 50) return 'bg-orange-500/20 text-orange-400'
  return 'bg-green-500/20 text-green-400'
}

function formatCommentDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
  })
}
</script>