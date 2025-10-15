import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiService from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de monedas
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useMonedas() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const monedas = ref([])
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
   * Obtener lista de monedas con paginación y búsqueda
   */
  const fetchMonedas = async (page = 1) => {
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

      const response = await apiService.get('/monedas', { params })

      monedas.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar monedas'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una moneda específica por ID
   */
  const fetchMoneda = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/monedas/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar moneda'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva moneda
   */
  const createMoneda = async (monedaData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/monedas', monedaData)

      alert.success('¡Moneda creada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear moneda'

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
   * Actualizar moneda existente
   */
  const updateMoneda = async (id, monedaData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/monedas/${id}`, monedaData)
      alert.success('¡Moneda actualizada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar moneda'

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
   * Eliminar una moneda
   */
  const deleteMoneda = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar moneda?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/monedas/${id}`)

      alert.success('¡Eliminada!', response.data.message)

      // Recargar lista de monedas
      await fetchMonedas(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar moneda'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples monedas por lotes
   */
  const deleteMonedasBulk = async (monedaIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${monedaIds.length} moneda(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete('/monedas/bulk/delete', {
        data: { ids: monedaIds }
      })

      alert.success('¡Eliminadas!', response.data.message)

      // Recargar lista de monedas
      await fetchMonedas(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar monedas'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar monedas
   */
  const searchMonedas = async (searchTerm) => {
    search.value = searchTerm
    await fetchMonedas(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchMonedas(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'monedas.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'monedas.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'monedas.index' })
  }

  // Computed
  const hasMonedas = computed(() => monedas.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    monedas,
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
    fetchMonedas,
    fetchMoneda,
    createMoneda,
    updateMoneda,
    deleteMoneda,
    deleteMonedasBulk,
    searchMonedas,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasMonedas,
    hasPrevPage,
    hasNextPage,
  }
}
