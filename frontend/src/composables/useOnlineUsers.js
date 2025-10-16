import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '@/services/api'

export function useOnlineUsers() {
  // Estado reactivo
  const onlineUsers = ref([])
  const count = ref(0)
  const loading = ref(false)

  // Intervalos
  let heartbeatInterval = null
  let pollingInterval = null

  /**
   * Obtener lista de usuarios en línea
   */
  const fetchOnlineUsers = async () => {
    try {
      loading.value = true
      const response = await api.get('/online-users')

      if (response.data.success) {
        onlineUsers.value = response.data.online_users
        count.value = response.data.count
      }
    } catch (error) {
      console.error('Error al obtener usuarios en línea:', error)
    } finally {
      loading.value = false
    }
  }

  /**
   * Marcar usuario como en línea
   */
  const markOnline = async () => {
    try {
      const response = await api.post('/online-users/mark-online')

      if (response.data.success) {
        console.log('Usuario marcado como en línea')
        // Actualizar lista inmediatamente
        await fetchOnlineUsers()
      }
    } catch (error) {
      console.error('Error al marcar usuario en línea:', error)
    }
  }

  /**
   * Actualizar actividad del usuario (heartbeat)
   */
  const updateActivity = async () => {
    try {
      await api.post('/online-users/update-activity')
    } catch (error) {
      console.error('Error al actualizar actividad:', error)
    }
  }

  /**
   * Marcar usuario como fuera de línea
   */
  const markOffline = async () => {
    try {
      await api.post('/online-users/mark-offline')
      console.log('Usuario marcado como fuera de línea')
    } catch (error) {
      console.error('Error al marcar usuario fuera de línea:', error)
    }
  }

  /**
   * Inicializar el sistema de usuarios en línea
   * SOLO se debe llamar cuando el usuario esté autenticado
   */
  const initialize = async () => {
    // Verificar que hay token antes de iniciar
    const token = localStorage.getItem('auth_token')
    if (!token) {
      console.warn('No se puede inicializar useOnlineUsers: sin token de autenticación')
      return
    }

    try {
      // Marcar usuario como en línea
      await markOnline()

      // Iniciar heartbeat y polling
      startHeartbeat()
      startPolling()
    } catch (error) {
      console.error('Error al inicializar usuarios en línea:', error)
    }
  }

  /**
   * Iniciar heartbeat - Actualizar actividad cada 2 minutos
   */
  const startHeartbeat = () => {
    if (heartbeatInterval) {
      clearInterval(heartbeatInterval)
    }

    // Actualizar actividad cada 2 minutos (120000 ms)
    heartbeatInterval = setInterval(() => {
      updateActivity()
    }, 120000)

    console.log('Heartbeat iniciado (cada 2 minutos)')
  }

  /**
   * Detener heartbeat
   */
  const stopHeartbeat = () => {
    if (heartbeatInterval) {
      clearInterval(heartbeatInterval)
      heartbeatInterval = null
      console.log('Heartbeat detenido')
    }
  }

  /**
   * Iniciar polling - Actualizar lista de usuarios cada 10 segundos
   */
  const startPolling = () => {
    if (pollingInterval) {
      clearInterval(pollingInterval)
    }

    // Polling cada 10 segundos (10000 ms)
    pollingInterval = setInterval(() => {
      fetchOnlineUsers()
    }, 10000)

    console.log('Polling iniciado (cada 10 segundos)')
  }

  /**
   * Detener polling
   */
  const stopPolling = () => {
    if (pollingInterval) {
      clearInterval(pollingInterval)
      pollingInterval = null
      console.log('Polling detenido')
    }
  }

  // Lifecycle hooks
  onUnmounted(() => {
    // Marcar usuario como fuera de línea
    markOffline()

    // Detener heartbeat y polling
    stopHeartbeat()
    stopPolling()
  })

  // Computed properties
  const isOnline = computed(() => count.value > 0)
  const hasUsers = computed(() => onlineUsers.value.length > 0)

  // Return
  return {
    // Estado
    onlineUsers,
    count,
    loading,

    // Computed
    isOnline,
    hasUsers,

    // Métodos
    initialize,
    fetchOnlineUsers,
    markOnline,
    updateActivity,
    markOffline,
    startHeartbeat,
    stopHeartbeat,
    startPolling,
    stopPolling
  }
}
