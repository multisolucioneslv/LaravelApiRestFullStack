import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de Chat IDs
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useChatids() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const chatids = ref([])
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
   * Obtener lista de chat IDs con paginación y búsqueda
   */
  const fetchChatids = async (page = 1) => {
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

      const response = await apiService.get('/chatids', { params })

      chatids.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar chat IDs'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un chat ID específico por ID
   */
  const fetchChatid = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/chatids/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar chat ID'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo chat ID
   */
  const createChatid = async (chatidData) => {
    loading.value = true
    error.value = null

    try {
      // Enviar como JSON (NO FormData)
      const response = await apiService.post('/chatids', chatidData)

      alert.success('¡Chat ID creado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear chat ID'

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
   * Actualizar chat ID existente
   */
  const updateChatid = async (id, chatidData) => {
    loading.value = true
    error.value = null

    try {
      // Enviar como JSON usando PUT
      const response = await apiService.put(`/chatids/${id}`, chatidData)
      alert.success('¡Chat ID actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar chat ID'

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
   * Eliminar un chat ID
   */
  const deleteChatid = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar chat ID?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/chatids/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de chat IDs
      await fetchChatids(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar chat ID'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples chat IDs por lotes
   */
  const deleteChatidsBulk = async (chatidIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${chatidIds.length} chat ID(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete('/chatids/bulk/delete', {
        data: { ids: chatidIds }
      })

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de chat IDs
      await fetchChatids(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar chat IDs'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar chat IDs
   */
  const searchChatids = async (searchTerm) => {
    search.value = searchTerm
    await fetchChatids(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchChatids(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'chatids.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'chatids.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'chatids.index' })
  }

  // Computed
  const hasChatids = computed(() => chatids.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    chatids,
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
    fetchChatids,
    fetchChatid,
    createChatid,
    updateChatid,
    deleteChatid,
    deleteChatidsBulk,
    searchChatids,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasChatids,
    hasPrevPage,
    hasNextPage,
  }
}
