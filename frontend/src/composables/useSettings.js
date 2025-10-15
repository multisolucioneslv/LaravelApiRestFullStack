import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de configuraciones (settings)
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useSettings() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const settings = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Paginación
  const currentPage = ref(1)
  const lastPage = ref(1)
  const perPage = ref(15)
  const total = ref(0)

  // Búsqueda
  const search = ref('')

  /**
   * Obtener lista de configuraciones con paginación y búsqueda
   */
  const fetchSettings = async (page = 1) => {
    loading.value = true
    error.value = null

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/settings', { params })

      settings.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar configuraciones'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una configuración específica por ID
   */
  const fetchSetting = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/settings/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar configuración'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva configuración
   */
  const createSetting = async (settingData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/settings', settingData)

      alert.success('¡Configuración creada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear configuración'

      // Si hay errores de validación, mostrarlos
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat().join('\n')
        alert.error('Errores de validación', errors)
      } else {
        alert.error('Error', error.value)
      }

      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar configuración existente
   */
  const updateSetting = async (id, settingData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/settings/${id}`, settingData)
      alert.success('¡Configuración actualizada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar configuración'

      // Si hay errores de validación, mostrarlos
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat().join('\n')
        alert.error('Errores de validación', errors)
      } else {
        alert.error('Error', error.value)
      }

      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar una configuración
   */
  const deleteSetting = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar configuración?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/settings/${id}`)

      alert.success('¡Eliminada!', response.data.message)

      // Recargar lista de configuraciones
      await fetchSettings(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar configuración'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples configuraciones por lotes
   */
  const deleteSettingsBulk = async (settingIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${settingIds.length} configuración(es)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/settings/bulk/delete', {
        ids: settingIds
      })

      alert.success('¡Eliminadas!', response.data.message)

      // Recargar lista de configuraciones
      await fetchSettings(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar configuraciones'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar configuraciones
   */
  const searchSettings = async (searchTerm) => {
    search.value = searchTerm
    await fetchSettings(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchSettings(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'settings.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'settings.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'settings.index' })
  }

  // Computed
  const hasSettings = computed(() => settings.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    settings,
    loading,
    error,

    // Paginación
    currentPage,
    lastPage,
    perPage,
    total,

    // Búsqueda
    search,

    // Métodos
    fetchSettings,
    fetchSetting,
    createSetting,
    updateSetting,
    deleteSetting,
    deleteSettingsBulk,
    searchSettings,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasSettings,
    hasPrevPage,
    hasNextPage,
  }
}
