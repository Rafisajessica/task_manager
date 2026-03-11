import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useProjectStore = defineStore('projects', () => {
  const projects = ref([])
  const loading  = ref(false)

  async function fetchProjects() {
    loading.value = true
    try {
      const res = await api.get('/projects')
      projects.value = res.data
    } finally {
      loading.value = false
    }
  }

  async function createProject(data) {
    const res = await api.post('/projects', data)
    projects.value.unshift(res.data)
    return res.data
  }

  async function updateProject(id, data) {
    const res = await api.put(`/projects/${id}`, data)
    const index = projects.value.findIndex(p => p.id === id)
    if (index !== -1) projects.value[index] = res.data
    return res.data
  }

  async function deleteProject(id) {
    await api.delete(`/projects/${id}`)
    projects.value = projects.value.filter(p => p.id !== id)
  }

  return { projects, loading, fetchProjects, createProject, updateProject, deleteProject }
})