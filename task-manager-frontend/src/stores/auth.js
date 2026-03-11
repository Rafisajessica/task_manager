import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('user')) || null)
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin         = computed(() => user.value?.role === 'admin')
  const isManager       = computed(() => user.value?.role === 'manager')

  async function register(data) {
    const res = await api.post('/auth/register', data)
    setAuth(res.data)
    return res.data
  }

  async function login(data) {
    const res = await api.post('/auth/login', data)
    setAuth(res.data)
    return res.data
  }

  async function logout() {
    await api.post('/auth/logout')
    clearAuth()
  }

  function setAuth(data) {
    user.value  = data.user
    token.value = data.token
    localStorage.setItem('user',  JSON.stringify(data.user))
    localStorage.setItem('token', data.token)
  }

  function clearAuth() {
    user.value  = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  return {
    user, token, isAuthenticated, isAdmin, isManager,
    register, login, logout
  }
})