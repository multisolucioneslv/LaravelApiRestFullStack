import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de categorías
 * Incluye: listar, crear, editar, eliminar
 */
export function useCategorias() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const categorias = ref([])
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
   * Obtener lista de categorías con paginación y búsqueda
   */
  const fetchCategorias = async (page = 1, showLoading = true) => {
    loading.value = true
    error.value = null

    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando categorías', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/categorias', { params })

      categorias.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total

      if (showLoading && authStore.showLoadingEffect) {
        alert.close()
      }
    } catch (err) {
      if (showLoading && authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al cargar categorías'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener todas las categorías (sin paginación) para selectores
   */
  const fetchAllCategorias = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get('/categorias/all')
      categorias.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar categorías'
      console.error('Error al cargar categorías:', err)
      categorias.value = []
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una categoría específica por ID
   */
  const fetchCategoria = async (id) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando categoría', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/categorias/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al cargar categoría'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva categoría
   */
  const createCategoria = async (categoriaData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Creando categoría', 'Por favor espere...')
    }

    try {
      const response = await apiService.post('/categorias', categoriaData)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Categoría creada!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al crear categoría'

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
   * Actualizar categoría existente
   */
  const updateCategoria = async (id, categoriaData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando categoría', 'Por favor espere...')
    }

    try {
      const response = await apiService.put(`/categorias/${id}`, categoriaData)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Categoría actualizada!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al actualizar categoría'

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
   * Eliminar una categoría
   */
  const deleteCategoria = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar categoría?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando categoría', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/categorias/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminada!', response.data.message)

      // Recargar lista de categorías
      await fetchCategorias(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al eliminar categoría'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar categorías
   */
  const searchCategorias = async (searchTerm) => {
    search.value = searchTerm
    await fetchCategorias(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchCategorias(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'categorias.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'categorias.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'categorias.index' })
  }

  // Computed
  const hasCategorias = computed(() => categorias.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    categorias,
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
    fetchCategorias,
    fetchAllCategorias,
    fetchCategoria,
    createCategoria,
    updateCategoria,
    deleteCategoria,
    searchCategorias,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasCategorias,
    hasPrevPage,
    hasNextPage,
  }
}
