import axios from 'axios'
import router from '@/router'

/**
 * Instancia de Axios configurada para comunicarse con el backend Laravel
 */
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000, // 10 segundos
})

/**
 * Interceptor de Request
 * Añade el token JWT a cada petición si existe
 */
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

/**
 * Interceptor de Response
 * Maneja errores globales y desloguea si el token expiró
 */
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Si el token expiró o es inválido (401), redirigir al login
    if (error.response && error.response.status === 401) {
      // Limpiar datos de autenticación
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')

      // Redirigir al login si no estamos ya ahí
      if (router.currentRoute.value.path !== '/login') {
        router.push({
          name: 'login',
          query: { redirect: router.currentRoute.value.fullPath }
        })
      }
    }

    // Si hay error de red
    if (!error.response) {
      console.error('Error de red:', error.message)
    }

    return Promise.reject(error)
  }
)

/**
 * Servicio de API con métodos helper
 */
export const apiService = {
  // Auth
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
  refresh: () => api.post('/auth/refresh'),

  // Métodos genéricos
  get: (url, config) => api.get(url, config),
  post: (url, data, config) => api.post(url, data, config),
  put: (url, data, config) => api.put(url, data, config),
  patch: (url, data, config) => api.patch(url, data, config),
  delete: (url, config) => api.delete(url, config),
}

export default api
