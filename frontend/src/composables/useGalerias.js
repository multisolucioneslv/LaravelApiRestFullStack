import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de galerías
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 * ESPECIAL: Manejo de múltiples imágenes con FormData
 */
export function useGalerias() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const galerias = ref([])
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
   * Obtener lista de galerías con paginación y búsqueda
   */
  const fetchGalerias = async (page = 1) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/galerias', { params })

      galerias.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar galerías'
      alert.error('Error', error.value)
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Obtener una galería específica por ID
   */
  const fetchGaleria = async (id) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await apiService.get(`/galerias/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar galería'
      alert.error('Error', error.value)
      throw err
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Crear nueva galería con múltiples imágenes
   */
  const createGaleria = async (galeriaData) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      // Las galerías siempre usan FormData para las imágenes
      const config = {
        headers: { 'Content-Type': 'multipart/form-data' }
      }

      const response = await apiService.post('/galerias', galeriaData, config)

      alert.success('¡Galería creada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear galería'

      // Si hay errores de validación, mostrarlos
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat().join('\n')
        alert.error('Errores de validación', errors)
      } else {
        alert.error('Error', error.value)
      }

      throw err
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Actualizar galería existente
   */
  const updateGaleria = async (id, galeriaData) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      // Las galerías siempre usan FormData para las imágenes
      // Usar POST con _method=PUT para enviar archivos
      const config = {
        headers: { 'Content-Type': 'multipart/form-data' }
      }

      const response = await apiService.post(`/galerias/${id}`, galeriaData, config)
      alert.success('¡Galería actualizada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar galería'

      // Si hay errores de validación, mostrarlos
      if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat().join('\n')
        alert.error('Errores de validación', errors)
      } else {
        alert.error('Error', error.value)
      }

      throw err
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Eliminar una galería
   */
  const deleteGaleria = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar galería?',
      'Esta acción eliminará todas las imágenes de la galería y no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await apiService.delete(`/galerias/${id}`)

      alert.success('¡Eliminada!', response.data.message)

      // Recargar lista de galerías
      await fetchGalerias(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar galería'
      alert.error('Error', error.value)
      return false
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Eliminar múltiples galerías por lotes
   */
  const deleteGaleriasBulk = async (galeriaIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${galeriaIds.length} galería(s)?`,
      'Esta acción eliminará todas las imágenes y no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await apiService.post('/galerias/bulk/delete', {
        ids: galeriaIds
      })

      alert.success('¡Eliminadas!', response.data.message)

      // Recargar lista de galerías
      await fetchGalerias(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar galerías'
      alert.error('Error', error.value)
      return false
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Buscar galerías
   */
  const searchGalerias = async (searchTerm) => {
    search.value = searchTerm
    await fetchGalerias(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchGalerias(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'galerias.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'galerias.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'galerias.index' })
  }

  // Computed
  const hasGalerias = computed(() => galerias.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    galerias,
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
    fetchGalerias,
    fetchGaleria,
    createGaleria,
    updateGaleria,
    deleteGaleria,
    deleteGaleriasBulk,
    searchGalerias,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasGalerias,
    hasPrevPage,
    hasNextPage,
  }
}
