<template>
  <div class="min-h-screen bg-gray-950 flex" @click="showNotifs = false">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col fixed h-full overflow-visible">

      <!-- Logo -->
      <div class="p-6 border-b border-gray-800">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
            <ClipboardDocumentCheckIcon class="w-5 h-5 text-white" />
          </div>
          <div>
            <h1 class="text-white font-bold text-sm">Task Manager</h1>
            <p class="text-blue-400 text-xs font-medium">IA Powered</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-1">
        <router-link
          v-for="item in navItems" :key="item.path"
          :to="item.path"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400
                 hover:text-white hover:bg-gray-800 transition duration-200"
          active-class="bg-blue-600/10 text-blue-400 border border-blue-500/20"
        >
          <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
          <span class="font-medium text-sm">{{ item.label }}</span>
        </router-link>
      </nav>

      <!-- Cloche notifications -->
      <div class="px-4 mb-2 relative" @click.stop>
        <button
          @click="showNotifs = !showNotifs"
          class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-gray-400
                 hover:text-white hover:bg-gray-800 transition">
          <div class="relative">
            <BellIcon class="w-5 h-5" />
            <span v-if="notifStore.unreadCount > 0"
              class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full
                     text-white text-xs flex items-center justify-center font-bold">
              {{ notifStore.unreadCount > 9 ? '9+' : notifStore.unreadCount }}
            </span>
          </div>
          <span class="text-sm font-medium">Notifications</span>
        </button>

        <!-- Dropdown -->
        <div v-if="showNotifs"
          class="absolute bottom-14 left-0 w-80 bg-gray-900 border border-gray-800
                 rounded-2xl shadow-2xl z-[100] overflow-hidden">
          <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
            <p class="text-white text-sm font-semibold">Notifications</p>
            <button @click="notifStore.markAllRead()"
              class="text-blue-400 text-xs hover:text-blue-300 transition">
              Tout lire
            </button>
          </div>
          <div class="max-h-72 overflow-y-auto">
            <div v-if="notifStore.notifications.length === 0"
              class="px-4 py-8 text-center">
              <BellIcon class="w-8 h-8 text-gray-700 mx-auto mb-2" />
              <p class="text-gray-500 text-xs">Aucune notification</p>
            </div>
            <div v-else
              v-for="notif in notifStore.notifications" :key="notif.id"
              @click="notifStore.markRead(notif.id)"
              :class="[
                'px-4 py-3 border-b border-gray-800/50 cursor-pointer transition',
                notif.read_at ? 'opacity-50' : 'hover:bg-gray-800'
              ]">
              <div class="flex items-start gap-3">
                <div :class="notifIconBg(notif.type)"
                  class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                  <component :is="notifIcon(notif.type)" class="w-4 h-4" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-white text-xs font-medium">{{ notif.title }}</p>
                  <p class="text-gray-400 text-xs mt-0.5 truncate">{{ notif.message }}</p>
                  <p class="text-gray-600 text-xs mt-1">
                    {{ new Date(notif.created_at).toLocaleDateString('fr-FR', {
                      day: '2-digit', month: 'short',
                      hour: '2-digit', minute: '2-digit'
                    }) }}
                  </p>
                </div>
                <div v-if="!notif.read_at"
                  class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User info + Logout -->
      <div class="p-4 border-t border-gray-800">
        <div class="flex items-center gap-3 mb-3 px-2">
          <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-blue-700
                      rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-white text-sm font-bold">
              {{ auth.user?.name?.charAt(0).toUpperCase() }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-white text-sm font-medium truncate">{{ auth.user?.name }}</p>
            <p class="text-gray-400 text-xs capitalize">{{ auth.user?.role }}</p>
          </div>
        </div>
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-2 px-4 py-2 text-gray-400
                 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition text-sm">
          <ArrowRightOnRectangleIcon class="w-4 h-4" />
          Déconnexion
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 ml-64 p-8 min-h-screen bg-gray-950">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  Squares2X2Icon, ClipboardDocumentListIcon, FolderIcon,
  ChatBubbleLeftRightIcon, ClipboardDocumentCheckIcon,
  ArrowRightOnRectangleIcon, BellIcon, UsersIcon,
  ChatBubbleLeftIcon, UserIcon, ArrowPathIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore }         from '../../stores/auth'
import { useNotificationStore } from '../../stores/notification'
import { useRouter }            from 'vue-router'

const auth       = useAuthStore()
const notifStore = useNotificationStore()
const router     = useRouter()
const showNotifs = ref(false)

const navItems = computed(() => {
  const items = [
    { path: '/',         icon: Squares2X2Icon,            label: 'Dashboard'    },
    { path: '/tasks',    icon: ClipboardDocumentListIcon,  label: 'Tâches'       },
    { path: '/projects', icon: FolderIcon,                 label: 'Projets'      },
    { path: '/chat',     icon: ChatBubbleLeftRightIcon,    label: 'Assistant IA' },
  ]
  if (auth.user?.role === 'admin') {
    items.push({ path: '/users', icon: UsersIcon, label: 'Utilisateurs' })
  }
  return items
})

onMounted(() => {
  notifStore.fetchNotifications()
  notifStore.fetchUnreadCount()
  setInterval(() => {
    notifStore.fetchUnreadCount()
    notifStore.fetchNotifications()
  }, 30000)
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

function notifIcon(type) {
  const map = {
    task_assigned:       UserIcon,
    task_commented:      ChatBubbleLeftIcon,
    task_status_changed: ArrowPathIcon,
  }
  return map[type] || BellIcon
}

function notifIconBg(type) {
  const map = {
    task_assigned:       'bg-blue-500/20 text-blue-400',
    task_commented:      'bg-purple-500/20 text-purple-400',
    task_status_changed: 'bg-yellow-500/20 text-yellow-400',
  }
  return map[type] || 'bg-gray-500/20 text-gray-400'
}
</script>