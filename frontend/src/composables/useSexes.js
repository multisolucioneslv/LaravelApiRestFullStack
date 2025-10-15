import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiService from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de sexos
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useSexes() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const sexes = ref([])
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
   * Obtener lista de sexos con paginación y búsqueda
   */
  const fetchSexes = async (page = 1) => {
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

      const response = await apiService.get('/sexes', { params })

      sexes.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar sexos'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un sexo específico por ID
   */
  const fetchSex = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/sexes/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar sexo'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo sexo
   */
  const createSex = async (sexData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/sexes', sexData)

      alert.success('¡Sexo creado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear sexo'

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
   * Actualizar sexo existente
   */
  const updateSex = async (id, sexData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/sexes/${id}`, sexData)
      alert.success('¡Sexo actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar sexo'

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
   * Eliminar un sexo
   */
  const deleteSex = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar sexo?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/sexes/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de sexos
      await fetchSexes(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar sexo'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples sexos por lotes
   */
  const deleteSexesBulk = async (sexIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${sexIds.length} sexo(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete('/sexes/bulk/delete', {
        data: { ids: sexIds }
      })

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de sexos
      await fetchSexes(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar sexos'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar sexos
   */
  const searchSexes = async (searchTerm) => {
    search.value = searchTerm
    await fetchSexes(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchSexes(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'sexes.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'sexes.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'sexes.index' })
  }

  // Computed
  const hasSexes = computed(() => sexes.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    sexes,
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
    fetchSexes,
    fetchSex,
    createSex,
    updateSex,
    deleteSex,
    deleteSexesBulk,
    searchSexes,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasSexes,
    hasPrevPage,
    hasNextPage,
  }
}
