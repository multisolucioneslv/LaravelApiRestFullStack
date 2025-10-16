import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de usuarios
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useUsers() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

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
  const fetchUsers = async (page = 1, showLoading = true) => {
    loading.value = true
    error.value = null

    // Mostrar loading solo si showLoading es true y la empresa tiene habilitado el efecto
    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando lista de usuarios', 'Por favor espere...')
    }

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

      // Cerrar loading
      if (showLoading && authStore.showLoadingEffect) {
        alert.close()
      }
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (showLoading && authStore.showLoadingEffect) {
        alert.close()
      }

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

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Cargando datos del usuario', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/users/${id}`)

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

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Creando usuario', 'Por favor espere...')
    }

    try {
      // Si userData es FormData, enviar con headers multipart/form-data
      const config = userData instanceof FormData
        ? { headers: { 'Content-Type': 'multipart/form-data' } }
        : {}

      const response = await apiService.post('/users', userData, config)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Usuario creado!', response.data.message)
      return response.data.data
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

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

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando usuario', 'Por favor espere...')
    }

    try {
      // Si userData es FormData, usar POST con _method=PUT
      if (userData instanceof FormData) {
        userData.append('_method', 'PUT')

        const config = {
          headers: { 'Content-Type': 'multipart/form-data' }
        }

        const response = await apiService.post(`/users/${id}`, userData, config)

        // Cerrar loading
        if (authStore.showLoadingEffect) {
          alert.close()
        }

        alert.success('¡Usuario actualizado!', response.data.message)
        return response.data.data
      } else {
        // Si no es FormData, usar PUT normal
        const response = await apiService.put(`/users/${id}`, userData)

        // Cerrar loading
        if (authStore.showLoadingEffect) {
          alert.close()
        }

        alert.success('¡Usuario actualizado!', response.data.message)
        return response.data.data
      }
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

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

    // Mostrar loading durante la eliminación
    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando usuario', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/users/${id}`)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de usuarios (sin mostrar loading adicional)
      await fetchUsers(currentPage.value, false)

      return true
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

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

    // Mostrar loading durante la eliminación
    if (authStore.showLoadingEffect) {
      alert.loading(`Eliminando ${userIds.length} usuario(s)`, 'Por favor espere...')
    }

    try {
      const response = await apiService.delete('/users/bulk/delete', {
        data: { ids: userIds }
      })

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de usuarios (sin mostrar loading adicional)
      await fetchUsers(currentPage.value, false)

      return true
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

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
    await fetchUsers(1, true) // Reiniciar a la página 1 y mostrar loading
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchUsers(page)
  }

  /**
   * Cambiar estado de cuenta del usuario
   */
  const updateAccountStatus = async (userId, statusData) => {
    loading.value = true
    error.value = null

    // Mostrar loading
    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando estado de cuenta', 'Por favor espere...')
    }

    try {
      const response = await apiService.put(`/users/${userId}/account-status`, statusData)

      // Cerrar loading
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Estado actualizado!', response.data.message)

      // Recargar lista de usuarios (sin mostrar loading adicional)
      await fetchUsers(currentPage.value, false)

      return response.data.data
    } catch (err) {
      // Cerrar loading antes de mostrar error
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al actualizar estado de cuenta'

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
    updateAccountStatus,
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
