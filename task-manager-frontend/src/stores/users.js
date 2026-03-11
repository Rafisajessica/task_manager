import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useUserStore = defineStore('users', () => {
  const users = ref([])

  async function fetchUsers() {
    const res = await api.get('/users')
    users.value = res.data
  }

  return { users, fetchUsers }
})