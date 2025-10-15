import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiService from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de usuarios
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useUsers() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const users = ref([])
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
   * Obtener lista de usuarios con paginación y búsqueda
   */
  const fetchUsers = async (page = 1) => {
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

      const response = await apiService.get('/users', { params })

      users.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar usuarios'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un usuario específico por ID
   */
  const fetchUser = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/users/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar usuario'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo usuario
   */
  const createUser = async (userData) => {
    loading.value = true
    error.value = null

    try {
      // Si userData es FormData, enviar con headers multipart/form-data
      const config = userData instanceof FormData
        ? { headers: { 'Content-Type': 'multipart/form-data' } }
        : {}

      const response = await apiService.post('/users', userData, config)

      alert.success('¡Usuario creado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear usuario'

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
   * Actualizar usuario existente
   */
  const updateUser = async (id, userData) => {
    loading.value = true
    error.value = null

    try {
      // Si userData es FormData, usar POST con _method=PUT
      if (userData instanceof FormData) {
        userData.append('_method', 'PUT')

        const config = {
          headers: { 'Content-Type': 'multipart/form-data' }
        }

        const response = await apiService.post(`/users/${id}`, userData, config)
        alert.success('¡Usuario actualizado!', response.data.message)
        return response.data.data
      } else {
        // Si no es FormData, usar PUT normal
        const response = await apiService.put(`/users/${id}`, userData)
        alert.success('¡Usuario actualizado!', response.data.message)
        return response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar usuario'

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
   * Eliminar un usuario
   */
  const deleteUser = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar usuario?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/users/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de usuarios
      await fetchUsers(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar usuario'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples usuarios por lotes
   */
  const deleteUsersBulk = async (userIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${userIds.length} usuario(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete('/users/bulk/delete', {
        data: { ids: userIds }
      })

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de usuarios
      await fetchUsers(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar usuarios'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar usuarios
   */
  const searchUsers = async (searchTerm) => {
    search.value = searchTerm
    await fetchUsers(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchUsers(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'users.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'users.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'users.index' })
  }

  // Computed
  const hasUsers = computed(() => users.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    users,
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
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    deleteUsersBulk,
    searchUsers,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasUsers,
    hasPrevPage,
    hasNextPage,
  }
}
