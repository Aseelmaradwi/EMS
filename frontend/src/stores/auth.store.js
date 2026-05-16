import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import api from '../api/axios'

const TOKEN_KEY = 'ems_token'
const USER_KEY = 'ems_user'

function loadStoredUser() {
  const rawUser = localStorage.getItem(USER_KEY)

  if (!rawUser) {
    return null
  }

  try {
    return JSON.parse(rawUser)
  } catch {
    localStorage.removeItem(USER_KEY)
    return null
  }
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem(TOKEN_KEY) || '')
  const user = ref(loadStoredUser())
  const isAuthenticated = computed(() => Boolean(token.value))

  function setAuth(authToken, authUser) {
    token.value = authToken
    user.value = authUser

    localStorage.setItem(TOKEN_KEY, authToken)
    localStorage.setItem(USER_KEY, JSON.stringify(authUser))
  }

  function clearAuth() {
    token.value = ''
    user.value = null

    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }

  async function login(credentials) {
    try {
      const response = await api.post('/auth/login', credentials)
      const payload = response.data?.data
      const authToken = payload?.token
      const authUser = payload?.user

      if (!authToken || !authUser) {
        throw new Error('Invalid login response from server.')
      }

      setAuth(authToken, authUser)
      return response.data
    } catch (error) {
      // Handle API errors properly
      if (error.response) {
        // Server responded with error status
        const errorMessage = error.response.data?.message || 'Login failed'
        throw new Error(errorMessage)
      } else if (error.request) {
        // Network error
        throw new Error('Network error. Please check your connection.')
      } else {
        // Other error
        throw new Error(error.message || 'Login failed')
      }
    }
  }

  function logout() {
    clearAuth()
  }

  function getRedirectPath() {
    const userRole = user.value?.role?.name
    
    switch (userRole) {
      case 'admin':
        return '/admin/dashboard'
      case 'manager':
        return '/manager/dashboard'
      case 'employee':
        return '/employee/dashboard'
      default:
        return '/employee/dashboard' // fallback
    }
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout,
    setAuth,
    clearAuth,
    getRedirectPath,
  }
})
