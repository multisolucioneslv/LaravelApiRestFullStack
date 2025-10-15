import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de cotizaciones
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 * Gestión de relación maestra-detalle (cotización + detalles)
 */
export function useCotizaciones() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const cotizaciones = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Paginación
  const currentPage = ref(1)
  const lastPage = ref(1)
  const perPage = ref(15)
  const total = ref(0)

  // Búsqueda
  const search = ref('')

  // Estados para catálogos (selects)
  const empresas = ref([])
  const monedas = ref([])
  const taxes = ref([])
  const inventarios = ref([])

  /**
   * Obtener lista de cotizaciones con paginación y búsqueda
   */
  const fetchCotizaciones = async (page = 1) => {
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

      const response = await apiService.get('/cotizaciones', { params })

      cotizaciones.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar cotizaciones'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una cotización específica por ID
   */
  const fetchCotizacion = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/cotizaciones/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar cotización'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva cotización con detalles
   */
  const createCotizacion = async (cotizacionData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/cotizaciones', cotizacionData)

      alert.success('¡Cotización creada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear cotización'

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
   * Actualizar cotización existente con detalles
   */
  const updateCotizacion = async (id, cotizacionData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/cotizaciones/${id}`, cotizacionData)
      alert.success('¡Cotización actualizada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar cotización'

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
   * Eliminar una cotización
   */
  const deleteCotizacion = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar cotización?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/cotizaciones/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de cotizaciones
      await fetchCotizaciones(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar cotización'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples cotizaciones por lotes
   */
  const deleteCotizacionesBulk = async (cotizacionIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${cotizacionIds.length} cotización(es)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/cotizaciones/bulk/delete', {
        ids: cotizacionIds
      })

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de cotizaciones
      await fetchCotizaciones(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar cotizaciones'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar cotizaciones
   */
  const searchCotizaciones = async (searchTerm) => {
    search.value = searchTerm
    await fetchCotizaciones(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchCotizaciones(page)
  }

  // ==========================================
  // MÉTODOS PARA CARGAR CATÁLOGOS (SELECTS)
  // ==========================================

  /**
   * Obtener lista de empresas para select
   */
  const fetchEmpresas = async () => {
    try {
      const response = await apiService.get('/empresas', {
        params: { per_page: 1000 } // Obtener todas para select
      })
      empresas.value = response.data.data
    } catch (err) {
      alert.error('Error', 'Error al cargar empresas')
    }
  }

  /**
   * Obtener lista de monedas para select
   */
  const fetchMonedas = async () => {
    try {
      const response = await apiService.get('/monedas', {
        params: { per_page: 1000 }
      })
      monedas.value = response.data.data
    } catch (err) {
      alert.error('Error', 'Error al cargar monedas')
    }
  }

  /**
   * Obtener lista de impuestos para select
   */
  const fetchTaxes = async () => {
    try {
      const response = await apiService.get('/taxes', {
        params: { per_page: 1000 }
      })
      taxes.value = response.data.data
    } catch (err) {
      alert.error('Error', 'Error al cargar impuestos')
    }
  }

  /**
   * Obtener lista de inventarios para select
   */
  const fetchInventarios = async () => {
    try {
      const response = await apiService.get('/inventarios', {
        params: { per_page: 1000 }
      })
      inventarios.value = response.data.data
    } catch (err) {
      alert.error('Error', 'Error al cargar inventarios')
    }
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'cotizaciones.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'cotizaciones.edit', params: { id } })
  }

  const goToShow = (id) => {
    router.push({ name: 'cotizaciones.show', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'cotizaciones.index' })
  }

  // Computed
  const hasCotizaciones = computed(() => cotizaciones.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    cotizaciones,
    loading,
    error,

    // Paginación
    currentPage,
    lastPage,
    perPage,
    total,

    // Búsqueda
    search,

    // Catálogos
    empresas,
    monedas,
    taxes,
    inventarios,

    // Métodos
    fetchCotizaciones,
    fetchCotizacion,
    createCotizacion,
    updateCotizacion,
    deleteCotizacion,
    deleteCotizacionesBulk,
    searchCotizaciones,
    changePage,

    // Métodos para catálogos
    fetchEmpresas,
    fetchMonedas,
    fetchTaxes,
    fetchInventarios,

    // Navegación
    goToCreate,
    goToEdit,
    goToShow,
    goToIndex,

    // Computed
    hasCotizaciones,
    hasPrevPage,
    hasNextPage,
  }
}
