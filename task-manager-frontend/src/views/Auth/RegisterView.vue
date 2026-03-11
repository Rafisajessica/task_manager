<template>
  <div class="min-h-screen bg-gray-950 flex items-center justify-center p-6">
    <div class="w-full max-w-lg">

      <!-- Header -->
      <div class="text-center mb-8">
        <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center
                    mx-auto mb-5 shadow-lg shadow-blue-600/30">
          <ClipboardDocumentCheckIcon class="w-7 h-7 text-white" />
        </div>
        <h1 class="text-3xl font-bold text-white">Créer un compte</h1>
        <p class="text-gray-400 mt-2">Rejoignez Task Manager IA gratuitement</p>
      </div>

      <!-- Card -->
      <div class="bg-gray-800/60 backdrop-blur rounded-2xl border border-gray-700/50 p-8">

        <!-- Erreur -->
        <div v-if="error"
          class="flex items-center gap-3 bg-red-500/10 border border-red-500/30
                 text-red-400 rounded-xl p-4 mb-6 text-sm">
          <ExclamationCircleIcon class="w-5 h-5 flex-shrink-0" />
          {{ error }}
        </div>

        <div class="space-y-4">

          <!-- Nom -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Nom complet</label>
            <div class="relative">
              <UserIcon class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="form.name" type="text" placeholder="Votre nom complet"
                class="w-full bg-gray-700/50 text-white rounded-xl pl-10 pr-4 py-3
                       border border-gray-600 focus:border-blue-500 focus:ring-1
                       focus:ring-blue-500 outline-none transition placeholder-gray-600" />
            </div>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
            <div class="relative">
              <EnvelopeIcon class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="form.email" type="email" placeholder="vous@exemple.com"
                class="w-full bg-gray-700/50 text-white rounded-xl pl-10 pr-4 py-3
                       border border-gray-600 focus:border-blue-500 focus:ring-1
                       focus:ring-blue-500 outline-none transition placeholder-gray-600" />
            </div>
          </div>

          <!-- Rôle -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Rôle</label>
            <div class="grid grid-cols-3 gap-3">
              <button v-for="role in roles" :key="role.value"
                @click="form.role = role.value"
                :class="[
                  'flex flex-col items-center gap-2 p-3 rounded-xl border transition',
                  form.role === role.value
                    ? 'bg-blue-600/20 border-blue-500 text-blue-400'
                    : 'bg-gray-700/50 border-gray-600 text-gray-400 hover:border-gray-500'
                ]">
                <component :is="role.icon" class="w-5 h-5" />
                <span class="text-xs font-medium">{{ role.label }}</span>
              </button>
            </div>
          </div>

          <!-- Mot de passe -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Mot de passe</label>
            <div class="relative">
              <LockClosedIcon class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Minimum 8 caractères"
                class="w-full bg-gray-700/50 text-white rounded-xl pl-10 pr-12 py-3
                       border border-gray-600 focus:border-blue-500 focus:ring-1
                       focus:ring-blue-500 outline-none transition placeholder-gray-600" />
              <button @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Confirmation -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Confirmer le mot de passe
            </label>
            <div class="relative">
              <LockClosedIcon class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                @keyup.enter="handleRegister"
                class="w-full bg-gray-700/50 text-white rounded-xl pl-10 pr-4 py-3
                       border border-gray-600 focus:border-blue-500 focus:ring-1
                       focus:ring-blue-500 outline-none transition placeholder-gray-600" />
            </div>
          </div>

          <!-- Bouton -->
          <button @click="handleRegister" :disabled="loading"
            class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white
                   font-semibold py-3 rounded-xl transition flex items-center justify-center
                   gap-2 shadow-lg shadow-blue-600/20 mt-2">
            <div v-if="loading"
              class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin">
            </div>
            <UserPlusIcon v-else class="w-5 h-5" />
            {{ loading ? 'Création...' : 'Créer mon compte' }}
          </button>
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">
          Déjà un compte ?
          <router-link to="/login" class="text-blue-400 hover:text-blue-300 font-medium ml-1">
            Se connecter
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import {
  ClipboardDocumentCheckIcon, EnvelopeIcon, LockClosedIcon,
  EyeIcon, EyeSlashIcon, UserPlusIcon, UserIcon,
  ExclamationCircleIcon, ShieldCheckIcon, BriefcaseIcon
} from '@heroicons/vue/24/outline'

const router       = useRouter()
const auth         = useAuthStore()
const loading      = ref(false)
const error        = ref(null)
const showPassword = ref(false)

const form = ref({
  name: '', email: '', password: '',
  password_confirmation: '', role: 'collaborator'
})

const roles = [
  { value: 'collaborator', label: 'Collaborateur', icon: UserIcon },
  { value: 'manager',      label: 'Manager',       icon: BriefcaseIcon },
  { value: 'admin',        label: 'Admin',         icon: ShieldCheckIcon },
]

async function handleRegister() {
  error.value   = null
  loading.value = true
  try {
    await auth.register(form.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'inscription'
  } finally {
    loading.value = false
  }
}
</script>