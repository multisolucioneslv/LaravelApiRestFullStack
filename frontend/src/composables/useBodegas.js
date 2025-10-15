import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiService from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de bodegas
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useBodegas() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const bodegas = ref([])
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
   * Obtener lista de bodegas con paginación y búsqueda
   */
  const fetchBodegas = async (page = 1) => {
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

      const response = await apiService.get('/bodegas', { params })

      bodegas.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar bodegas'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una bodega específica por ID
   */
  const fetchBodega = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/bodegas/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar bodega'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva bodega
   */
  const createBodega = async (bodegaData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/bodegas', bodegaData)

      alert.success('¡Bodega creada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear bodega'

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
   * Actualizar bodega existente
   */
  const updateBodega = async (id, bodegaData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/bodegas/${id}`, bodegaData)
      alert.success('¡Bodega actualizada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar bodega'

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
   * Eliminar una bodega
   */
  const deleteBodega = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar bodega?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/bodegas/${id}`)

      alert.success('¡Eliminada!', response.data.message)

      // Recargar lista de bodegas
      await fetchBodegas(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar bodega'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples bodegas por lotes
   */
  const deleteBodegasBulk = async (bodegaIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${bodegaIds.length} bodega(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/bodegas/bulk/delete', {
        ids: bodegaIds
      })

      alert.success('¡Eliminadas!', response.data.message)

      // Recargar lista de bodegas
      await fetchBodegas(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar bodegas'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar bodegas
   */
  const searchBodegas = async (searchTerm) => {
    search.value = searchTerm
    await fetchBodegas(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchBodegas(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'bodegas.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'bodegas.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'bodegas.index' })
  }

  // Computed
  const hasBodegas = computed(() => bodegas.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    bodegas,
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
    fetchBodegas,
    fetchBodega,
    createBodega,
    updateBodega,
    deleteBodega,
    deleteBodegasBulk,
    searchBodegas,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasBodegas,
    hasPrevPage,
    hasNextPage,
  }
}
