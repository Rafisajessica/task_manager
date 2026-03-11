<template>
  <div class="min-h-screen bg-gray-950 flex">

    <!-- Partie gauche — Visuelle -->
    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-blue-600/20 via-gray-900 to-purple-600/20
                flex-col items-center justify-center p-12 relative overflow-hidden">

      <!-- Cercles décoratifs -->
      <div class="absolute top-20 left-20 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 right-20 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl"></div>

      <!-- Contenu -->
      <div class="relative z-10 text-center">
        <div class="w-20 h-20 bg-blue-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-lg shadow-blue-600/30">
          <ClipboardDocumentCheckIcon class="w-10 h-10 text-white" />
        </div>
        <h1 class="text-4xl font-bold text-white mb-4">Task Manager IA</h1>
        <p class="text-gray-400 text-lg mb-12 max-w-sm">
          Gérez vos tâches intelligemment grâce à l'IA
        </p>

        <!-- Features -->
        <div class="space-y-4 text-left">
          <div v-for="feature in features" :key="feature.text"
            class="flex items-center gap-3 bg-gray-800/50 rounded-xl px-4 py-3 border border-gray-700/50">
            <div class="w-8 h-8 bg-blue-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
              <component :is="feature.icon" class="w-4 h-4 text-blue-400" />
            </div>
            <p class="text-gray-300 text-sm">{{ feature.text }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Partie droite — Formulaire -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
      <div class="w-full max-w-md">

        <!-- Header -->
        <div class="mb-8">
          <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center mb-6 lg:hidden">
            <ClipboardDocumentCheckIcon class="w-6 h-6 text-white" />
          </div>
          <h2 class="text-3xl font-bold text-white">Bon retour !</h2>
          <p class="text-gray-400 mt-2">Connectez-vous à votre espace de travail</p>
        </div>

        <!-- Erreur -->
        <div v-if="error"
          class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400
                 rounded-xl p-4 mb-6 text-sm">
          <ExclamationCircleIcon class="w-5 h-5 flex-shrink-0" />
          {{ error }}
        </div>

        <!-- Formulaire -->
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Adresse email
            </label>
            <div class="relative">
              <EnvelopeIcon class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                v-model="form.email"
                type="email"
                placeholder="vous@exemple.com"
                class="w-full bg-gray-800 text-white rounded-xl pl-10 pr-4 py-3
                       border border-gray-700 focus:border-blue-500 focus:ring-1
                       focus:ring-blue-500 outline-none transition placeholder-gray-600"
              />
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-sm font-medium text-gray-300">Mot de passe</label>
            </div>
            <div class="relative">
              <LockClosedIcon class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                @keyup.enter="handleLogin"
                class="w-full bg-gray-800 text-white rounded-xl pl-10 pr-12 py-3
                       border border-gray-700 focus:border-blue-500 focus:ring-1
                       focus:ring-blue-500 outline-none transition placeholder-gray-600"
              />
              <button @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>

          <button
            @click="handleLogin"
            :disabled="loading"
            class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-50
                   disabled:cursor-not-allowed text-white font-semibold py-3 rounded-xl
                   transition duration-200 flex items-center justify-center gap-2 shadow-lg
                   shadow-blue-600/20">
            <div v-if="loading"
              class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin">
            </div>
            <ArrowRightIcon v-else class="w-5 h-5" />
            <span>{{ loading ? 'Connexion...' : 'Se connecter' }}</span>
          </button>
        </div>

        <!-- Divider -->
        <div class="flex items-center gap-4 my-6">
          <div class="flex-1 h-px bg-gray-800"></div>
          <p class="text-gray-600 text-xs">Pas encore de compte ?</p>
          <div class="flex-1 h-px bg-gray-800"></div>
        </div>

        <router-link to="/register"
          class="w-full flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-700
                 text-gray-300 font-medium py-3 rounded-xl transition border border-gray-700
                 hover:border-gray-600">
          <UserPlusIcon class="w-5 h-5" />
          Créer un compte
        </router-link>

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
  EyeIcon, EyeSlashIcon, ArrowRightIcon, UserPlusIcon,
  ExclamationCircleIcon, CpuChipIcon, ChartBarIcon,
  BellIcon
} from '@heroicons/vue/24/outline'

const router       = useRouter()
const auth         = useAuthStore()
const loading      = ref(false)
const error        = ref(null)
const showPassword = ref(false)

const form = ref({ email: '', password: '' })

const features = [
  { icon: CpuChipIcon,    text: 'Priorisation automatique par IA' },
  { icon: ChartBarIcon,   text: 'Dashboard analytique en temps réel' },
  { icon: BellIcon,       text: 'Assistant IA conversationnel' },
]

async function handleLogin() {
  error.value   = null
  loading.value = true
  try {
    await auth.login(form.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Email ou mot de passe incorrect'
  } finally {
    loading.value = false
  }
}
</script>