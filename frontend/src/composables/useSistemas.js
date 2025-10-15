import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de sistemas
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useSistemas() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const sistemas = ref([])
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
   * Obtener lista de sistemas con paginación y búsqueda
   */
  const fetchSistemas = async (page = 1) => {
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

      const response = await apiService.get('/sistemas', { params })

      sistemas.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar sistemas'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un sistema específico por ID
   */
  const fetchSistema = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/sistemas/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar sistema'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo sistema
   */
  const createSistema = async (sistemaData) => {
    loading.value = true
    error.value = null

    try {
      // Si sistemaData es FormData, enviar con headers multipart/form-data
      const config = sistemaData instanceof FormData
        ? { headers: { 'Content-Type': 'multipart/form-data' } }
        : {}

      const response = await apiService.post('/sistemas', sistemaData, config)

      alert.success('¡Sistema creado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear sistema'

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
   * Actualizar sistema existente
   */
  const updateSistema = async (id, sistemaData) => {
    loading.value = true
    error.value = null

    try {
      // Si sistemaData es FormData, usar POST con _method=PUT
      if (sistemaData instanceof FormData) {
        sistemaData.append('_method', 'PUT')

        const config = {
          headers: { 'Content-Type': 'multipart/form-data' }
        }

        const response = await apiService.post(`/sistemas/${id}`, sistemaData, config)
        alert.success('¡Sistema actualizado!', response.data.message)
        return response.data.data
      } else {
        // Si no es FormData, usar PUT normal
        const response = await apiService.put(`/sistemas/${id}`, sistemaData)
        alert.success('¡Sistema actualizado!', response.data.message)
        return response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar sistema'

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
   * Eliminar un sistema
   */
  const deleteSistema = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar sistema?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/sistemas/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de sistemas
      await fetchSistemas(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar sistema'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples sistemas por lotes
   */
  const deleteSistemasBulk = async (sistemaIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${sistemaIds.length} sistema(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete('/sistemas/bulk/delete', {
        data: { ids: sistemaIds }
      })

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de sistemas
      await fetchSistemas(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar sistemas'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar sistemas
   */
  const searchSistemas = async (searchTerm) => {
    search.value = searchTerm
    await fetchSistemas(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchSistemas(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'sistemas.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'sistemas.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'sistemas.index' })
  }

  // Computed
  const hasSistemas = computed(() => sistemas.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    sistemas,
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
    fetchSistemas,
    fetchSistema,
    createSistema,
    updateSistema,
    deleteSistema,
    deleteSistemasBulk,
    searchSistemas,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasSistemas,
    hasPrevPage,
    hasNextPage,
  }
}
