import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiService from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de inventarios
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useInventarios() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const inventarios = ref([])
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
   * Obtener lista de inventarios con paginación y búsqueda
   */
  const fetchInventarios = async (page = 1) => {
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

      const response = await apiService.get('/inventarios', { params })

      inventarios.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar inventarios'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un inventario específico por ID
   */
  const fetchInventario = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/inventarios/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar inventario'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo inventario
   */
  const createInventario = async (inventarioData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/inventarios', inventarioData)

      alert.success('¡Inventario creado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear inventario'

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
   * Actualizar inventario existente
   */
  const updateInventario = async (id, inventarioData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/inventarios/${id}`, inventarioData)
      alert.success('¡Inventario actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar inventario'

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
   * Eliminar un inventario
   */
  const deleteInventario = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar inventario?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/inventarios/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de inventarios
      await fetchInventarios(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar inventario'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples inventarios por lotes
   */
  const deleteInventariosBulk = async (inventarioIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${inventarioIds.length} inventario(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/inventarios/bulk/delete', {
        ids: inventarioIds
      })

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de inventarios
      await fetchInventarios(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar inventarios'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar inventarios
   */
  const searchInventarios = async (searchTerm) => {
    search.value = searchTerm
    await fetchInventarios(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchInventarios(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'inventarios.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'inventarios.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'inventarios.index' })
  }

  // Computed
  const hasInventarios = computed(() => inventarios.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    inventarios,
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
    fetchInventarios,
    fetchInventario,
    createInventario,
    updateInventario,
    deleteInventario,
    deleteInventariosBulk,
    searchInventarios,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasInventarios,
    hasPrevPage,
    hasNextPage,
  }
}
