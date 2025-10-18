import { ref, computed } from 'vue'
import api from '@/services/api'
import { useAlert } from './useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para Admin Empresa - Configuración AI
 */
export function useAIConfig() {
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const config = ref(null)
  const usageStats = ref(null)
  const users = ref([])
  const plans = ref([])
  const loading = ref(false)
  const updating = ref(false)

  // Computed
  const isAIEnabled = computed(() => config.value?.ai_chat_enabled || false)
  const currentDetectionMode = computed(() => config.value?.ai_detection_mode || 'regex')
  const monthlyBudget = computed(() => config.value?.ai_monthly_budget || 0)
  const currentUsage = computed(() => usageStats.value?.monthly_cost || 0)
  const usagePercentage = computed(() => {
    if (!monthlyBudget.value) return 0
    return Math.round((currentUsage.value / monthlyBudget.value) * 100)
  })

  /**
   * Obtener configuración actual de AI
   */
  const fetchConfig = async () => {
    loading.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando configuración', 'Por favor espere...')
    }

    try {
      const response = await api.get('/ai-config')
      config.value = response.data.data

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return config.value
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al obtener configuración:', error)
      const message = error.response?.data?.message || 'No se pudo cargar la configuración'
      alert.error('Error', message)
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar configuración de AI
   */
  const updateConfig = async (newConfig) => {
    updating.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando configuración', 'Por favor espere...')
    }

    try {
      const response = await api.put('/ai-config', newConfig)
      config.value = response.data.data

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Éxito', 'Configuración actualizada correctamente')

      return response.data
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al actualizar configuración:', error)
      const message = error.response?.data?.message || 'No se pudo actualizar la configuración'

      // Mostrar errores de validación si existen
      if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat()
        alert.error('Error de Validación', errors.join('<br>'))
      } else {
        alert.error('Error', message)
      }

      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Obtener estadísticas de uso
   */
  const fetchUsageStats = async () => {
    try {
      const response = await api.get('/ai-config/usage-stats')
      usageStats.value = response.data.data
      return usageStats.value
    } catch (error) {
      console.error('Error al obtener estadísticas de uso:', error)
      throw error
    }
  }

  /**
   * Obtener lista de usuarios de la empresa
   */
  const fetchUsers = async () => {
    loading.value = true

    try {
      const response = await api.get('/ai-config/users')
      users.value = response.data.data
      return users.value
    } catch (error) {
      console.error('Error al obtener usuarios:', error)
      const message = error.response?.data?.message || 'No se pudieron cargar los usuarios'
      alert.error('Error', message)
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar permisos de usuario para AI
   */
  const updateUserPermission = async (userId, grantPermission) => {
    updating.value = true

    try {
      const response = await api.post('/ai-config/users/permissions', {
        user_id: userId,
        grant_permission: grantPermission
      })

      // Actualizar usuario en la lista local
      const index = users.value.findIndex(u => u.id === userId)
      if (index !== -1) {
        users.value[index] = response.data.data
      }

      alert.toast(
        grantPermission
          ? 'Permiso de AI otorgado'
          : 'Permiso de AI revocado',
        'success'
      )

      return response.data
    } catch (error) {
      console.error('Error al actualizar permisos:', error)
      const message = error.response?.data?.message || 'No se pudieron actualizar los permisos'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Obtener planes disponibles
   */
  const fetchPlans = async () => {
    try {
      const response = await api.get('/ai-config/plans')
      plans.value = response.data.data
      return plans.value
    } catch (error) {
      console.error('Error al obtener planes:', error)
      throw error
    }
  }

  /**
   * Actualizar modo de detección
   */
  const updateDetectionMode = async (mode) => {
    return updateConfig({
      ai_detection_mode: mode
    })
  }

  /**
   * Actualizar credenciales de OpenAI
   */
  const updateOpenAICredentials = async (credentials) => {
    return updateConfig(credentials)
  }

  /**
   * Actualizar presupuesto mensual
   */
  const updateMonthlyBudget = async (budget) => {
    return updateConfig({
      ai_monthly_budget: budget
    })
  }

  /**
   * Validar API Key de OpenAI
   */
  const validateApiKey = (apiKey) => {
    if (!apiKey) return true // Si no hay API key, está ok (usa la compartida)

    // Validar que la API key tenga al menos 20 caracteres
    if (apiKey.length < 20) {
      return 'La API key debe tener al menos 20 caracteres'
    }

    // Validar que comience con 'sk-'
    if (!apiKey.startsWith('sk-')) {
      return 'La API key debe comenzar con "sk-"'
    }

    return true
  }

  /**
   * Validar temperatura (0-2)
   */
  const validateTemperature = (temperature) => {
    const temp = parseFloat(temperature)

    if (isNaN(temp)) {
      return 'La temperatura debe ser un número'
    }

    if (temp < 0 || temp > 2) {
      return 'La temperatura debe estar entre 0 y 2'
    }

    return true
  }

  /**
   * Validar max tokens (100-4000)
   */
  const validateMaxTokens = (maxTokens) => {
    const tokens = parseInt(maxTokens)

    if (isNaN(tokens)) {
      return 'Max tokens debe ser un número entero'
    }

    if (tokens < 100 || tokens > 4000) {
      return 'Max tokens debe estar entre 100 y 4000'
    }

    return true
  }

  /**
   * Validar presupuesto (mayor a 0)
   */
  const validateBudget = (budget) => {
    const budgetValue = parseFloat(budget)

    if (isNaN(budgetValue)) {
      return 'El presupuesto debe ser un número'
    }

    if (budgetValue <= 0) {
      return 'El presupuesto debe ser mayor a 0'
    }

    return true
  }

  return {
    // Estado
    config,
    usageStats,
    users,
    plans,
    loading,
    updating,
    // Computed
    isAIEnabled,
    currentDetectionMode,
    monthlyBudget,
    currentUsage,
    usagePercentage,
    // Métodos
    fetchConfig,
    updateConfig,
    fetchUsageStats,
    fetchUsers,
    updateUserPermission,
    fetchPlans,
    updateDetectionMode,
    updateOpenAICredentials,
    updateMonthlyBudget,
    // Validaciones
    validateApiKey,
    validateTemperature,
    validateMaxTokens,
    validateBudget,
  }
}
