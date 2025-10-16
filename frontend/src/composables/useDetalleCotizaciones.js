import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de detalles de cotizaciones
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useDetalleCotizaciones() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const detalleCotizaciones = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Paginación
  const currentPage = ref(1)
  const lastPage = ref(1)
  const perPage = ref(15)
  const total = ref(0)

  // Búsqueda/Filtro
  const search = ref('')

  // Estados para catálogos (selects)
  const cotizaciones = ref([])
  const inventarios = ref([])

  /**
   * Obtener lista de detalles de cotizaciones con paginación y filtro opcional por cotización
   */
  const fetchDetalleCotizaciones = async (page = 1, cotizacionId = null, showLoading = true) => {
    loading.value = true
    error.value = null

    // Mostrar loading solo si showLoading es true y la empresa tiene habilitado el efecto
    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando lista de detalles de cotizaciones', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (cotizacionId) {
        params.cotizacion_id = cotizacionId
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/detalle-cotizaciones', { params })

      detalleCotizaciones.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total

      // Cerrar loading
      if (showLoading && authStore.showLoadingEffect) {
        alert.close()
      }
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (showLoading && authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al cargar detalles de cotizaciones'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un detalle de cotización específico por ID
   */
  const fetchDetalleCotizacion = async (id) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Cargando datos del detalle de cotización', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/detalle-cotizaciones/${id}`)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return response.data.data
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al cargar detalle de cotización'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo detalle de cotización
   */
  const createDetalleCotizacion = async (detalleData) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Creando detalle de cotización', 'Por favor espere...')
    }

    try {
      const response = await apiService.post('/detalle-cotizaciones', detalleData)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Detalle creado', response.data.message)
      return response.data.data
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al crear detalle de cotización'

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
   * Actualizar detalle de cotización existente
   */
  const updateDetalleCotizacion = async (id, detalleData) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando detalle de cotización', 'Por favor espere...')
    }

    try {
      const response = await apiService.put(`/detalle-cotizaciones/${id}`, detalleData)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Detalle actualizado', response.data.message)
      return response.data.data
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al actualizar detalle de cotización'

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
   * Eliminar un detalle de cotización
   */
  const deleteDetalleCotizacion = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar detalle de cotización?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    // Mostrar loading durante la eliminación
    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando detalle de cotización', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/detalle-cotizaciones/${id}`)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Eliminado', response.data.message)

      // Recargar lista de detalles (sin mostrar loading adicional)
      await fetchDetalleCotizaciones(currentPage.value, null, false)

      return true
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar detalle de cotización'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples detalles de cotizaciones por lotes
   */
  const deleteDetalleCotizacionesBulk = async (detalleIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${detalleIds.length} detalle(s) de cotización?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    // Mostrar loading durante la eliminación
    if (authStore.showLoadingEffect) {
      alert.loading(`Eliminando ${detalleIds.length} detalle(s)`, 'Por favor espere...')
    }

    try {
      const response = await apiService.post('/detalle-cotizaciones/bulk/delete', {
        ids: detalleIds
      })

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Eliminados', response.data.message)

      // Recargar lista de detalles (sin mostrar loading adicional)
      await fetchDetalleCotizaciones(currentPage.value, null, false)

      return true
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar detalles de cotizaciones'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar detalles de cotizaciones
   */
  const searchDetalleCotizaciones = async (searchTerm) => {
    search.value = searchTerm
    await fetchDetalleCotizaciones(1, null, true) // Reiniciar a la página 1 y mostrar loading
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchDetalleCotizaciones(page)
  }

  // ==========================================
  // MÉTODOS PARA CARGAR CATÁLOGOS (SELECTS)
  // ==========================================

  /**
   * Obtener lista de cotizaciones para select
   */
  const fetchCotizaciones = async () => {
    try {
      const response = await apiService.get('/cotizaciones', {
        params: { per_page: 1000 } // Obtener todas para select
      })
      cotizaciones.value = response.data.data
    } catch (err) {
      alert.error('Error', 'Error al cargar cotizaciones')
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
    router.push({ name: 'detalle-cotizaciones.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'detalle-cotizaciones.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'detalle-cotizaciones.index' })
  }

  // Computed
  const hasDetalleCotizaciones = computed(() => detalleCotizaciones.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    detalleCotizaciones,
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
    cotizaciones,
    inventarios,

    // Métodos
    fetchDetalleCotizaciones,
    fetchDetalleCotizacion,
    createDetalleCotizacion,
    updateDetalleCotizacion,
    deleteDetalleCotizacion,
    deleteDetalleCotizacionesBulk,
    searchDetalleCotizaciones,
    changePage,

    // Métodos para catálogos
    fetchCotizaciones,
    fetchInventarios,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasDetalleCotizaciones,
    hasPrevPage,
    hasNextPage,
  }
}
