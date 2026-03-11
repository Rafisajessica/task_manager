import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useTaskStore = defineStore('tasks', () => {
  const tasks   = ref([])
  const loading = ref(false)
  const error   = ref(null)

  async function fetchTasks() {
    loading.value = true
    try {
      const res = await api.get('/tasks')
      tasks.value = res.data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function createTask(data) {
    const res = await api.post('/tasks', data)
    tasks.value.unshift(res.data)
    return res.data
  }

  async function updateTask(id, data) {
    const res = await api.put(`/tasks/${id}`, data)
    const index = tasks.value.findIndex(t => t.id === id)
    if (index !== -1) tasks.value[index] = res.data
    return res.data
  }

  async function deleteTask(id) {
    await api.delete(`/tasks/${id}`)
    tasks.value = tasks.value.filter(t => t.id !== id)
  }

  return { tasks, loading, error, fetchTasks, createTask, updateTask, deleteTask }
})