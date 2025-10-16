import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de inventarios
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useInventarios() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const inventarios = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Paginaci�n
  const currentPage = ref(1)
  const lastPage = ref(1)
  const perPage = ref(15)
  const total = ref(0)

  // B�squeda
  const search = ref('')

  /**
   * Obtener lista de inventarios con paginaci�n y b�squeda
   */
  const fetchInventarios = async (page = 1) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando inventarios...')
    }

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

      if (authStore.showLoadingEffect) {
        alert.close()
      }
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al cargar inventarios'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un inventario espec�fico por ID
   */
  const fetchInventario = async (id) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando inventario...')
    }

    try {
      const response = await apiService.get(`/inventarios/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
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

    if (authStore.showLoadingEffect) {
      alert.loading('Creando inventario...')
    }

    try {
      const response = await apiService.post('/inventarios', inventarioData)

      if (authStore.showLoadingEffect) {
        alert.close()
      }
      alert.success('�Inventario creado!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al crear inventario'

      // Si hay errores de validaci�n, mostrarlos
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat().join('\n')
        alert.error('Errores de validaci�n', errors)
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

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando inventario...')
    }

    try {
      const response = await apiService.put(`/inventarios/${id}`, inventarioData)

      if (authStore.showLoadingEffect) {
        alert.close()
      }
      alert.success('�Inventario actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al actualizar inventario'

      // Si hay errores de validaci�n, mostrarlos
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat().join('\n')
        alert.error('Errores de validaci�n', errors)
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
      '�Eliminar inventario?',
      'Esta acci�n no se puede deshacer',
      'S�, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando inventario...')
    }

    try {
      const response = await apiService.delete(`/inventarios/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }
      alert.success('�Eliminado!', response.data.message)

      // Recargar lista de inventarios
      await fetchInventarios(currentPage.value)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al eliminar inventario'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar m�ltiples inventarios por lotes
   */
  const deleteInventariosBulk = async (inventarioIds) => {
    const result = await alert.confirm(
      `�Eliminar ${inventarioIds.length} inventario(s)?`,
      'Esta acci�n no se puede deshacer',
      'S�, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando inventarios...')
    }

    try {
      const response = await apiService.post('/inventarios/bulk/delete', {
        ids: inventarioIds
      })

      if (authStore.showLoadingEffect) {
        alert.close()
      }
      alert.success('�Eliminados!', response.data.message)

      // Recargar lista de inventarios
      await fetchInventarios(currentPage.value)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
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
    await fetchInventarios(1) // Reiniciar a la p�gina 1
  }

  /**
   * Cambiar de p�gina
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchInventarios(page)
  }

  /**
   * Navegaci�n
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

    // Paginaci�n
    currentPage,
    lastPage,
    perPage,
    total,

    // B�squeda
    search,

    // M�todos
    fetchInventarios,
    fetchInventario,
    createInventario,
    updateInventario,
    deleteInventario,
    deleteInventariosBulk,
    searchInventarios,
    changePage,

    // Navegaci�n
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasInventarios,
    hasPrevPage,
    hasNextPage,
  }
}
