import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de teléfonos
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useTelefonos() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const telefonos = ref([])
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
   * Obtener lista de teléfonos con paginación y búsqueda
   */
  const fetchTelefonos = async (page = 1, showLoading = true) => {
    loading.value = true
    error.value = null

    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando lista de teléfonos', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/telefonos', { params })

      telefonos.value = response.data.data
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

      error.value = err.response?.data?.message || 'Error al cargar teléfonos'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un teléfono específico por ID
   */
  const fetchTelefono = async (id) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando datos del teléfono', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/telefonos/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al cargar teléfono'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo teléfono
   */
  const createTelefono = async (telefonoData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Creando teléfono', 'Por favor espere...')
    }

    try {
      const response = await apiService.post('/telefonos', telefonoData)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Teléfono creado!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al crear teléfono'

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
   * Actualizar teléfono existente
   */
  const updateTelefono = async (id, telefonoData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando teléfono', 'Por favor espere...')
    }

    try {
      const response = await apiService.put(`/telefonos/${id}`, telefonoData)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Teléfono actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al actualizar teléfono'

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
   * Eliminar un teléfono
   */
  const deleteTelefono = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar teléfono?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando teléfono', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/telefonos/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de teléfonos (sin mostrar loading adicional)
      await fetchTelefonos(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar teléfono'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples teléfonos por lotes
   */
  const deleteTelefonosBulk = async (telefonoIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${telefonoIds.length} teléfono(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading(`Eliminando ${telefonoIds.length} teléfono(s)`, 'Por favor espere...')
    }

    try {
      const response = await apiService.delete('/telefonos/bulk/delete', {
        data: { ids: telefonoIds }
      })

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de teléfonos (sin mostrar loading adicional)
      await fetchTelefonos(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar teléfonos'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar teléfonos
   */
  const searchTelefonos = async (searchTerm) => {
    search.value = searchTerm
    await fetchTelefonos(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchTelefonos(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'telefonos.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'telefonos.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'telefonos.index' })
  }

  /**
   * Función de limpieza para resetear el estado
   * Se llama cuando el componente se desmonta o cuando se cierra sesión
   */
  const cleanupTelefonos = () => {
    telefonos.value = []
    search.value = ''
    error.value = null
    currentPage.value = 1
    lastPage.value = 1
    total.value = 0
  }

  // Computed
  const hasTelefonos = computed(() => telefonos.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    telefonos,
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
    fetchTelefonos,
    fetchTelefono,
    createTelefono,
    updateTelefono,
    deleteTelefono,
    deleteTelefonosBulk,
    searchTelefonos,
    changePage,
    cleanupTelefonos,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasTelefonos,
    hasPrevPage,
    hasNextPage,
  }
}
