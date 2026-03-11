<template>
  <div>
    <!-- Titre -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-purple-600/20 rounded-xl flex items-center justify-center">
          <UsersIcon class="w-5 h-5 text-purple-400" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-white">Gestion des Utilisateurs</h1>
          <p class="text-gray-400 text-xs">Administration des comptes</p>
        </div>
      </div>
      <div class="bg-gray-900 rounded-xl px-4 py-2 border border-gray-800">
        <span class="text-gray-400 text-sm">Total : </span>
        <span class="text-white font-bold">{{ users.length }}</span>
      </div>
    </div>

    <!-- Filtres -->
    <div class="flex items-center gap-3 mb-6">
      <div class="relative flex-1 max-w-sm">
        <MagnifyingGlassIcon class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input v-model="search" type="text" placeholder="Rechercher un utilisateur..."
          class="w-full bg-gray-900 text-white rounded-xl pl-9 pr-4 py-2.5 border border-gray-800
                 focus:border-purple-500 outline-none text-sm placeholder-gray-600" />
      </div>
      <select v-model="filterRole"
        class="bg-gray-900 text-gray-300 rounded-xl px-4 py-2.5 border border-gray-800
               focus:border-purple-500 outline-none text-sm">
        <option value="">Tous les rôles</option>
        <option value="admin">Admin</option>
        <option value="manager">Manager</option>
        <option value="collaborator">Collaborateur</option>
      </select>
      <select v-model="filterStatus"
        class="bg-gray-900 text-gray-300 rounded-xl px-4 py-2.5 border border-gray-800
               focus:border-purple-500 outline-none text-sm">
        <option value="">Tous les statuts</option>
        <option value="active">Actifs</option>
        <option value="inactive">Inactifs</option>
      </select>
    </div>

    <!-- Tableau -->
    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
      <div v-if="loading" class="flex items-center justify-center py-16">
        <div class="w-8 h-8 border-2 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <table v-else class="w-full">
        <thead>
          <tr class="border-b border-gray-800 bg-gray-800/50">
            <th class="text-left text-gray-500 text-xs font-medium px-6 py-4 uppercase tracking-wider">
              Utilisateur
            </th>
            <th class="text-left text-gray-500 text-xs font-medium px-6 py-4 uppercase tracking-wider">
              Rôle
            </th>
            <th class="text-left text-gray-500 text-xs font-medium px-6 py-4 uppercase tracking-wider">
              Statut
            </th>
            <th class="text-left text-gray-500 text-xs font-medium px-6 py-4 uppercase tracking-wider">
              Membre depuis
            </th>
            <th class="text-right text-gray-500 text-xs font-medium px-6 py-4 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          <tr v-for="user in filteredUsers" :key="user.id"
            class="hover:bg-gray-800/60 transition">

            <!-- Utilisateur -->
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div :class="avatarColor(user.role)"
                  class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0">
                  <span class="text-white text-sm font-bold">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div>
                  <p class="text-white text-sm font-medium">{{ user.name }}</p>
                  <p class="text-gray-500 text-xs">{{ user.email }}</p>
                </div>
              </div>
            </td>

            <!-- Rôle -->
            <td class="px-6 py-4">
              <select
                :value="user.role"
                @change="handleRoleChange(user, $event)"
                :disabled="user.id === auth.user?.id"
                class="bg-gray-800 text-gray-300 text-xs rounded-lg px-3 py-1.5
                       border border-gray-700 focus:border-purple-500 outline-none
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="collaborator">Collaborateur</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
              </select>
            </td>

            <!-- Statut -->
            <td class="px-6 py-4">
              <button
                @click="handleToggleStatus(user)"
                :disabled="user.id === auth.user?.id"
                :class="[
                  'flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition',
                  user.is_active
                    ? 'bg-green-500/10 text-green-400 hover:bg-green-500/20'
                    : 'bg-red-500/10 text-red-400 hover:bg-red-500/20',
                  user.id === auth.user?.id ? 'opacity-50 cursor-not-allowed' : ''
                ]">
                <span :class="user.is_active ? 'bg-green-400' : 'bg-red-400'"
                  class="w-1.5 h-1.5 rounded-full"></span>
                {{ user.is_active ? 'Actif' : 'Inactif' }}
              </button>
            </td>

            <!-- Date -->
            <td class="px-6 py-4">
              <p class="text-gray-400 text-sm">
                {{ formatDate(user.created_at) }}
              </p>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-2">
                <button
                  v-if="user.id !== auth.user?.id"
                  @click="confirmDelete(user)"
                  class="p-2 text-gray-600 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition">
                  <TrashIcon class="w-4 h-4" />
                </button>
                <span v-else
                  class="text-xs text-gray-600 bg-gray-800 px-3 py-1 rounded-full border border-gray-700">
                  Vous
                </span>
              </div>
            </td>
          </tr>

          <!-- Aucun résultat -->
          <tr v-if="filteredUsers.length === 0">
            <td colspan="5" class="px-6 py-16 text-center">
              <UsersIcon class="w-10 h-10 text-gray-700 mx-auto mb-3" />
              <p class="text-gray-500">Aucun utilisateur trouvé</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal suppression -->
    <div v-if="userToDelete"
      class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 backdrop-blur-sm">
      <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 w-full max-w-sm shadow-2xl">
        <div class="w-12 h-12 bg-red-500/10 rounded-xl flex items-center justify-center mb-4">
          <TrashIcon class="w-6 h-6 text-red-400" />
        </div>
        <h3 class="text-white font-semibold mb-2">Supprimer l'utilisateur</h3>
        <p class="text-gray-400 text-sm mb-6">
          Voulez-vous vraiment supprimer
          <span class="text-white font-medium">{{ userToDelete.name }}</span> ?
          Cette action est irréversible.
        </p>
        <div class="flex gap-3">
          <button @click="handleDelete"
            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl
                   text-sm font-medium transition">
            Supprimer
          </button>
          <button @click="userToDelete = null"
            class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-2.5 rounded-xl
                   text-sm transition border border-gray-700">
            Annuler
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { UsersIcon, MagnifyingGlassIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '../stores/auth'
import api from '../api/axios'

const auth         = useAuthStore()
const users        = ref([])
const loading      = ref(true)
const search       = ref('')
const filterRole   = ref('')
const filterStatus = ref('')
const userToDelete = ref(null)

const filteredUsers = computed(() =>
  users.value.filter(u => {
    const matchSearch = u.name.toLowerCase().includes(search.value.toLowerCase()) ||
                        u.email.toLowerCase().includes(search.value.toLowerCase())
    const matchRole   = !filterRole.value || u.role === filterRole.value
    const matchStatus = !filterStatus.value ||
      (filterStatus.value === 'active' ? u.is_active : !u.is_active)
    return matchSearch && matchRole && matchStatus
  })
)

onMounted(fetchUsers)

async function fetchUsers() {
  loading.value = true
  try {
    const res   = await api.get('/users')
    users.value = res.data
  } finally {
    loading.value = false
  }
}

async function handleRoleChange(user, event) {
  try {
    await api.put(`/users/${user.id}`, { role: event.target.value })
    user.role = event.target.value
  } catch (e) { console.error(e) }
}

async function handleToggleStatus(user) {
  try {
    await api.put(`/users/${user.id}`, { is_active: !user.is_active })
    user.is_active = !user.is_active
  } catch (e) { console.error(e) }
}

function confirmDelete(user) { userToDelete.value = user }

async function handleDelete() {
  try {
    await api.delete(`/users/${userToDelete.value.id}`)
    users.value    = users.value.filter(u => u.id !== userToDelete.value.id)
    userToDelete.value = null
  } catch (e) { console.error(e) }
}

function avatarColor(role) {
  const map = {
    admin:        'bg-gradient-to-br from-red-500 to-orange-500',
    manager:      'bg-gradient-to-br from-blue-500 to-blue-700',
    collaborator: 'bg-gradient-to-br from-purple-500 to-purple-700',
  }
  return map[role] || 'bg-gradient-to-br from-gray-500 to-gray-700'
}

function formatDate(date) {
  if (!date) return '—'
  const d = new Date(date)
  if (isNaN(d)) return '—'
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>