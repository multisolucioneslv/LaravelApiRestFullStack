import { ref, computed } from 'vue'
import api from '@/services/api'
import { useAlert } from './useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para SuperAdmin - Gestión Global de AI
 */
export function useAIManagement() {
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const globalStats = ref(null)
  const empresas = ref([])
  const empresaDetail = ref(null)
  const plans = ref([])
  const loading = ref(false)
  const updating = ref(false)

  // Paginación
  const currentPage = ref(1)
  const lastPage = ref(1)
  const total = ref(0)
  const perPage = ref(15)

  // Filtros
  const searchTerm = ref('')
  const filterAiEnabled = ref(null)
  const filterDetectionMode = ref(null)

  // Computed
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  /**
   * Obtener estadísticas globales
   */
  const fetchGlobalStats = async () => {
    loading.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando estadísticas globales', 'Por favor espere...')
    }

    try {
      const response = await api.get('/superadmin/ai/stats/global')
      globalStats.value = response.data.data

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return globalStats.value
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al obtener estadísticas globales:', error)
      const message = error.response?.data?.message || 'No se pudieron cargar las estadísticas'
      alert.error('Error', message)
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener lista de empresas con paginación y filtros
   */
  const fetchEmpresas = async (page = 1) => {
    loading.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando empresas', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (searchTerm.value) {
        params.search = searchTerm.value
      }

      if (filterAiEnabled.value !== null) {
        params.ai_enabled = filterAiEnabled.value
      }

      if (filterDetectionMode.value) {
        params.detection_mode = filterDetectionMode.value
      }

      const response = await api.get('/superadmin/ai/empresas', { params })

      empresas.value = response.data.data
      currentPage.value = response.data.current_page
      lastPage.value = response.data.last_page
      total.value = response.data.total

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return empresas.value
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al obtener empresas:', error)
      const message = error.response?.data?.message || 'No se pudieron cargar las empresas'
      alert.error('Error', message)
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener detalles de una empresa
   */
  const fetchEmpresaDetail = async (empresaId) => {
    loading.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando detalles', 'Por favor espere...')
    }

    try {
      const response = await api.get(`/superadmin/ai/empresas/${empresaId}/config`)
      empresaDetail.value = response.data.data

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return empresaDetail.value
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al obtener detalles de empresa:', error)
      const message = error.response?.data?.message || 'No se pudieron cargar los detalles'
      alert.error('Error', message)
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * Toggle AI habilitado/deshabilitado para una empresa
   */
  const toggleAI = async (empresaId, enabled) => {
    updating.value = true

    if (authStore.showLoadingEffect) {
      alert.loading(
        enabled ? 'Habilitando AI' : 'Deshabilitando AI',
        'Por favor espere...'
      )
    }

    try {
      const response = await api.post(`/superadmin/ai/empresas/${empresaId}/toggle`, {
        ai_chat_enabled: enabled
      })

      // Actualizar la empresa en la lista local
      const index = empresas.value.findIndex(e => e.id === empresaId)
      if (index !== -1) {
        empresas.value[index] = response.data.data
      }

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success(
        'Éxito',
        enabled ? 'AI habilitado correctamente' : 'AI deshabilitado correctamente'
      )

      return response.data
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al cambiar estado de AI:', error)
      const message = error.response?.data?.message || 'No se pudo cambiar el estado'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Actualizar configuración de una empresa
   */
  const updateEmpresaConfig = async (empresaId, config) => {
    updating.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando configuración', 'Por favor espere...')
    }

    try {
      const response = await api.put(
        `/superadmin/ai/empresas/${empresaId}/config`,
        config
      )

      // Actualizar la empresa en la lista local
      const index = empresas.value.findIndex(e => e.id === empresaId)
      if (index !== -1) {
        empresas.value[index] = response.data.data
      }

      // Actualizar detalle si está cargado
      if (empresaDetail.value?.id === empresaId) {
        empresaDetail.value = response.data.data
      }

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
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Resetear uso mensual de una empresa
   */
  const resetUsage = async (empresaId) => {
    const result = await alert.confirm(
      '¿Resetear uso mensual?',
      '¿Estás seguro de que deseas resetear el uso mensual de esta empresa?',
      'Sí, resetear'
    )

    if (!result.isConfirmed) return

    updating.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Reseteando uso', 'Por favor espere...')
    }

    try {
      const response = await api.post(`/superadmin/ai/empresas/${empresaId}/reset-usage`)

      // Actualizar la empresa en la lista local
      const index = empresas.value.findIndex(e => e.id === empresaId)
      if (index !== -1) {
        empresas.value[index] = response.data.data
      }

      // Actualizar detalle si está cargado
      if (empresaDetail.value?.id === empresaId) {
        empresaDetail.value = response.data.data
      }

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Éxito', 'Uso mensual reseteado correctamente')

      return response.data
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al resetear uso:', error)
      const message = error.response?.data?.message || 'No se pudo resetear el uso'
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
      const response = await api.get('/superadmin/ai/plans')
      plans.value = response.data.data
      return plans.value
    } catch (error) {
      console.error('Error al obtener planes:', error)
      throw error
    }
  }

  /**
   * Buscar empresas
   */
  const searchEmpresas = (term) => {
    searchTerm.value = term
    fetchEmpresas(1)
  }

  /**
   * Aplicar filtros
   */
  const applyFilters = (filters) => {
    if (filters.ai_enabled !== undefined) {
      filterAiEnabled.value = filters.ai_enabled
    }
    if (filters.detection_mode !== undefined) {
      filterDetectionMode.value = filters.detection_mode
    }
    fetchEmpresas(1)
  }

  /**
   * Limpiar filtros
   */
  const clearFilters = () => {
    searchTerm.value = ''
    filterAiEnabled.value = null
    filterDetectionMode.value = null
    fetchEmpresas(1)
  }

  /**
   * Cambiar página
   */
  const changePage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
      fetchEmpresas(page)
    }
  }

  return {
    // Estado
    globalStats,
    empresas,
    empresaDetail,
    plans,
    loading,
    updating,
    // Paginación
    currentPage,
    lastPage,
    total,
    perPage,
    hasPrevPage,
    hasNextPage,
    // Filtros
    searchTerm,
    filterAiEnabled,
    filterDetectionMode,
    // Métodos
    fetchGlobalStats,
    fetchEmpresas,
    fetchEmpresaDetail,
    toggleAI,
    updateEmpresaConfig,
    resetUsage,
    fetchPlans,
    searchEmpresas,
    applyFilters,
    clearFilters,
    changePage,
  }
}
