import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de ventas
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 * Gestión de relación maestra-detalle (venta + detalles)
 */
export function useVentas() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const ventas = ref([])
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
  const cotizaciones = ref([])

  /**
   * Obtener lista de ventas con paginación y búsqueda
   */
  const fetchVentas = async (page = 1) => {
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

      const response = await apiService.get('/ventas', { params })

      ventas.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar ventas'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una venta específica por ID
   */
  const fetchVenta = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/ventas/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar venta'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva venta con detalles
   */
  const createVenta = async (ventaData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/ventas', ventaData)

      alert.success('¡Venta creada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear venta'

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
   * Actualizar venta existente con detalles
   */
  const updateVenta = async (id, ventaData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/ventas/${id}`, ventaData)
      alert.success('¡Venta actualizada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar venta'

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
   * Eliminar una venta
   */
  const deleteVenta = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar venta?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/ventas/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de ventas
      await fetchVentas(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar venta'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples ventas por lotes
   */
  const deleteVentasBulk = async (ventaIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${ventaIds.length} venta(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/ventas/bulk/delete', {
        ids: ventaIds
      })

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de ventas
      await fetchVentas(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar ventas'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar ventas
   */
  const searchVentas = async (searchTerm) => {
    search.value = searchTerm
    await fetchVentas(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchVentas(page)
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
      const response = await apiService.get('/currencies', {
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
   * Obtener lista de cotizaciones para select
   */
  const fetchCotizaciones = async () => {
    try {
      const response = await apiService.get('/cotizaciones', {
        params: { per_page: 1000 }
      })
      cotizaciones.value = response.data.data
    } catch (err) {
      alert.error('Error', 'Error al cargar cotizaciones')
    }
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'ventas.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'ventas.edit', params: { id } })
  }

  const goToShow = (id) => {
    router.push({ name: 'ventas.show', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'ventas.index' })
  }

  // Computed
  const hasVentas = computed(() => ventas.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    ventas,
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
    cotizaciones,

    // Métodos
    fetchVentas,
    fetchVenta,
    createVenta,
    updateVenta,
    deleteVenta,
    deleteVentasBulk,
    searchVentas,
    changePage,

    // Métodos para catálogos
    fetchEmpresas,
    fetchMonedas,
    fetchTaxes,
    fetchInventarios,
    fetchCotizaciones,

    // Navegación
    goToCreate,
    goToEdit,
    goToShow,
    goToIndex,

    // Computed
    hasVentas,
    hasPrevPage,
    hasNextPage,
  }
}
