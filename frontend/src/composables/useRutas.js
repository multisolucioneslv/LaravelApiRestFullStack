import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de rutas API del sistema
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useRutas() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const rutas = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Paginación
  const currentPage = ref(1)
  const lastPage = ref(1)
  const perPage = ref(15)
  const total = ref(0)

  // Búsqueda
  const search = ref('')

  // Sistemas para select
  const sistemas = ref([])

  /**
   * Obtener lista de rutas con paginación y búsqueda
   */
  const fetchRutas = async (page = 1, showLoading = true) => {
    loading.value = true
    error.value = null

    // Mostrar loading solo si showLoading es true y la empresa tiene habilitado el efecto
    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando lista de rutas API', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/rutas', { params })

      rutas.value = response.data.data
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

      error.value = err.response?.data?.message || 'Error al cargar rutas'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una ruta específica por ID
   */
  const fetchRuta = async (id) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Cargando datos de la ruta API', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/rutas/${id}`)

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

      error.value = err.response?.data?.message || 'Error al cargar ruta'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva ruta
   */
  const createRuta = async (rutaData) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Creando ruta API', 'Por favor espere...')
    }

    try {
      const response = await apiService.post('/rutas', rutaData)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Ruta API creada', response.data.message)
      return response.data.data
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al crear ruta'

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
   * Actualizar ruta existente
   */
  const updateRuta = async (id, rutaData) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando ruta API', 'Por favor espere...')
    }

    try {
      const response = await apiService.put(`/rutas/${id}`, rutaData)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Ruta API actualizada', response.data.message)
      return response.data.data
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al actualizar ruta'

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
   * Eliminar una ruta
   */
  const deleteRuta = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar ruta API?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    // Mostrar loading durante la eliminación
    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando ruta API', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/rutas/${id}`)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Eliminada', response.data.message)

      // Recargar lista de rutas (sin mostrar loading adicional)
      await fetchRutas(currentPage.value, false)

      return true
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar ruta'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples rutas por lotes
   */
  const deleteRutasBulk = async (rutaIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${rutaIds.length} ruta(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    // Mostrar loading durante la eliminación
    if (authStore.showLoadingEffect) {
      alert.loading(`Eliminando ${rutaIds.length} ruta(s) API`, 'Por favor espere...')
    }

    try {
      const response = await apiService.delete('/rutas/bulk/delete', {
        data: { ids: rutaIds }
      })

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Eliminadas', response.data.message)

      // Recargar lista de rutas (sin mostrar loading adicional)
      await fetchRutas(currentPage.value, false)

      return true
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar rutas'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener lista de sistemas para select
   */
  const fetchSistemas = async () => {
    try {
      const response = await apiService.get('/sistemas', {
        params: { per_page: 1000 } // Traer todos los sistemas
      })
      sistemas.value = response.data.data
      return sistemas.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar sistemas'
      alert.error('Error', error.value)
      return []
    }
  }

  /**
   * Buscar rutas
   */
  const searchRutas = async (searchTerm) => {
    search.value = searchTerm
    await fetchRutas(1, true) // Reiniciar a la página 1 y mostrar loading
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchRutas(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'rutas.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'rutas.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'rutas.index' })
  }

  // Computed
  const hasRutas = computed(() => rutas.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    rutas,
    loading,
    error,
    sistemas,

    // Paginación
    currentPage,
    lastPage,
    perPage,
    total,

    // Búsqueda
    search,

    // Métodos
    fetchRutas,
    fetchRuta,
    createRuta,
    updateRuta,
    deleteRuta,
    deleteRutasBulk,
    fetchSistemas,
    searchRutas,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasRutas,
    hasPrevPage,
    hasNextPage,
  }
}
