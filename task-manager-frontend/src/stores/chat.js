import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useChatStore = defineStore('chat', () => {
  const messages = ref([])
  const loading  = ref(false)

  async function fetchHistory() {
    const res = await api.get('/chat/history')
    messages.value = res.data
  }

  async function sendMessage(content) {
    // Ajoute le message utilisateur immédiatement
    messages.value.push({
      role: 'user',
      content,
      created_at: new Date().toISOString()
    })

    loading.value = true
    try {
      const res = await api.post('/chat', { message: content })
      messages.value.push({
        role: 'assistant',
        content: res.data.message,
        created_at: new Date().toISOString()
      })
    } finally {
      loading.value = false
    }
  }

  return { messages, loading, fetchHistory, sendMessage }
})