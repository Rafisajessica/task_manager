import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref([])
  const unreadCount   = ref(0)

  async function fetchNotifications() {
    const res       = await api.get('/notifications')
    notifications.value = res.data
  }

  async function fetchUnreadCount() {
    const res     = await api.get('/notifications/unread-count')
    unreadCount.value = res.data.count
  }

  async function markRead(id) {
    await api.put(`/notifications/${id}/read`)
    const notif = notifications.value.find(n => n.id === id)
    if (notif) notif.read_at = new Date().toISOString()
    if (unreadCount.value > 0) unreadCount.value--
  }

  async function markAllRead() {
    await api.put('/notifications/read-all')
    notifications.value.forEach(n => n.read_at = new Date().toISOString())
    unreadCount.value = 0
  }

  return { notifications, unreadCount, fetchNotifications, fetchUnreadCount, markRead, markAllRead }
})