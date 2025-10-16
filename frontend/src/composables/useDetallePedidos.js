import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de detalles de pedidos
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useDetallePedidos() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const detallePedidos = ref([])
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
  const pedidos = ref([])
  const inventarios = ref([])

  /**
   * Obtener lista de detalles de pedidos con paginación y filtro opcional por pedido
   */
  const fetchDetallePedidos = async (page = 1, pedidoId = null, showLoading = true) => {
    loading.value = true
    error.value = null

    // Mostrar loading solo si showLoading es true y la empresa tiene habilitado el efecto
    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando lista de detalles de pedidos', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (pedidoId) {
        params.pedido_id = pedidoId
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/detalle-pedidos', { params })

      detallePedidos.value = response.data.data
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

      error.value = err.response?.data?.message || 'Error al cargar detalles de pedidos'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un detalle de pedido específico por ID
   */
  const fetchDetallePedido = async (id) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Cargando datos del detalle de pedido', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/detalle-pedidos/${id}`)

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

      error.value = err.response?.data?.message || 'Error al cargar detalle de pedido'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo detalle de pedido
   */
  const createDetallePedido = async (detalleData) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Creando detalle de pedido', 'Por favor espere...')
    }

    try {
      const response = await apiService.post('/detalle-pedidos', detalleData)

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

      error.value = err.response?.data?.message || 'Error al crear detalle de pedido'

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
   * Actualizar detalle de pedido existente
   */
  const updateDetallePedido = async (id, detalleData) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando detalle de pedido', 'Por favor espere...')
    }

    try {
      const response = await apiService.put(`/detalle-pedidos/${id}`, detalleData)

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

      error.value = err.response?.data?.message || 'Error al actualizar detalle de pedido'

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
   * Eliminar un detalle de pedido
   */
  const deleteDetallePedido = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar detalle de pedido?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    // Mostrar loading durante la eliminación
    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando detalle de pedido', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/detalle-pedidos/${id}`)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Eliminado', response.data.message)

      // Recargar lista de detalles (sin mostrar loading adicional)
      await fetchDetallePedidos(currentPage.value, null, false)

      return true
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar detalle de pedido'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples detalles de pedidos por lotes
   */
  const deleteDetallePedidosBulk = async (detalleIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${detalleIds.length} detalle(s) de pedido?`,
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
      const response = await apiService.post('/detalle-pedidos/bulk/delete', {
        ids: detalleIds
      })

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Eliminados', response.data.message)

      // Recargar lista de detalles (sin mostrar loading adicional)
      await fetchDetallePedidos(currentPage.value, null, false)

      return true
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar detalles de pedidos'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar detalles de pedidos
   */
  const searchDetallePedidos = async (searchTerm) => {
    search.value = searchTerm
    await fetchDetallePedidos(1, null, true) // Reiniciar a la página 1 y mostrar loading
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchDetallePedidos(page)
  }

  // ==========================================
  // MÉTODOS PARA CARGAR CATÁLOGOS (SELECTS)
  // ==========================================

  /**
   * Obtener lista de pedidos para select
   */
  const fetchPedidos = async () => {
    try {
      const response = await apiService.get('/pedidos', {
        params: { per_page: 1000 } // Obtener todas para select
      })
      pedidos.value = response.data.data
    } catch (err) {
      alert.error('Error', 'Error al cargar pedidos')
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
    router.push({ name: 'detalle-pedidos.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'detalle-pedidos.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'detalle-pedidos.index' })
  }

  // Computed
  const hasDetallePedidos = computed(() => detallePedidos.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    detallePedidos,
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
    pedidos,
    inventarios,

    // Métodos
    fetchDetallePedidos,
    fetchDetallePedido,
    createDetallePedido,
    updateDetallePedido,
    deleteDetallePedido,
    deleteDetallePedidosBulk,
    searchDetallePedidos,
    changePage,

    // Métodos para catálogos
    fetchPedidos,
    fetchInventarios,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasDetallePedidos,
    hasPrevPage,
    hasNextPage,
  }
}
