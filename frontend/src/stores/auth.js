import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { apiService } from '@/services/api'
import router from '@/router'
import { useAlert } from '@/composables/useAlert'

/**
 * Store de Pinia para gestionar la autenticación con JWT
 */
export const useAuthStore = defineStore('auth', () => {
  // Composables
  const alert = useAlert()

  // Estado
  const user = ref(null)
  const token = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Getters (computed)
  const isAuthenticated = computed(() => !!token.value)
  const userName = computed(() => user.value?.name || '')
  const userEmail = computed(() => user.value?.email || '')
  const userRole = computed(() => user.value?.role || '')

  /**
   * Inicializar el estado desde localStorage
   * IMPORTANTE: Valida el token llamando a /api/auth/me
   */
  const initAuth = async () => {
    const savedToken = localStorage.getItem('auth_token')
    const savedUser = localStorage.getItem('user')

    if (savedToken && savedUser) {
      token.value = savedToken
      user.value = JSON.parse(savedUser)

      // Validar token con el backend
      try {
        await fetchUser()
      } catch (err) {
        console.error('Token inválido, limpiando sesión:', err)
        // Si el token es inválido, limpiar todo
        logout()
      }
    }
  }

  /**
   * Login del usuario
   */
  const login = async (credentials) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.login(credentials)
      const { access_token, user: userData } = response.data

      // Guardar en el estado
      token.value = access_token
      user.value = userData

      // Persistir en localStorage
      localStorage.setItem('auth_token', access_token)
      localStorage.setItem('user', JSON.stringify(userData))

      // Mostrar notificación de éxito
      alert.toast(`¡Bienvenido, ${userData.name}!`, 'success')

      // Redirigir al dashboard
      router.push({ name: 'dashboard' })

      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al iniciar sesión'

      // Mostrar alerta de error
      alert.error('Error al iniciar sesión', error.value)

      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  /**
   * Registro de nuevo usuario
   */
  const register = async (userData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.register(userData)
      const { access_token, user: newUser } = response.data

      // Guardar en el estado
      token.value = access_token
      user.value = newUser

      // Persistir en localStorage
      localStorage.setItem('auth_token', access_token)
      localStorage.setItem('user', JSON.stringify(newUser))

      // Mostrar notificación de éxito
      alert.success('¡Registro exitoso!', `Bienvenido, ${newUser.name}`)

      // Redirigir al dashboard
      router.push({ name: 'dashboard' })

      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al registrarse'

      // Mostrar alerta de error
      alert.error('Error al registrarse', error.value)

      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  /**
   * Logout del usuario
   */
  const logout = async () => {
    const result = await alert.confirm(
      '¿Cerrar sesión?',
      '¿Estás seguro de que deseas cerrar sesión?',
      'Sí, cerrar sesión'
    )

    if (!result.isConfirmed) return

    loading.value = true

    try {
      // Intentar hacer logout en el backend
      await apiService.logout()
    } catch (err) {
      console.error('Error al cerrar sesión:', err)
    } finally {
      // Limpiar estado local independientemente del resultado
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      loading.value = false

      // Mostrar notificación
      alert.toast('Sesión cerrada correctamente', 'success')

      // Redirigir al login
      router.push({ name: 'login' })
    }
  }

  /**
   * Obtener datos del usuario autenticado
   */
  const fetchUser = async () => {
    if (!token.value) return

    loading.value = true

    try {
      const response = await apiService.me()
      // El backend devuelve { success: true, user: {...} }
      user.value = response.data.user
      localStorage.setItem('user', JSON.stringify(response.data.user))
    } catch (err) {
      console.error('Error al obtener usuario:', err)
      // Si falla, hacer logout
      await logout()
    } finally {
      loading.value = false
    }
  }

  /**
   * Refrescar token JWT
   */
  const refreshToken = async () => {
    try {
      const response = await apiService.refresh()
      const { access_token } = response.data

      token.value = access_token
      localStorage.setItem('auth_token', access_token)

      return { success: true }
    } catch (err) {
      console.error('Error al refrescar token:', err)
      await logout()
      return { success: false }
    }
  }

  /**
   * Limpiar errores
   */
  const clearError = () => {
    error.value = null
  }

  // NO inicializar aquí - se hará desde main.js antes de montar la app

  return {
    // Estado
    user,
    token,
    loading,
    error,
    // Getters
    isAuthenticated,
    userName,
    userEmail,
    userRole,
    // Acciones
    initAuth, // Exportar para llamar desde main.js
    login,
    register,
    logout,
    fetchUser,
    refreshToken,
    clearError,
  }
})
