import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de roles y permisos
 * Incluye: listar roles, crear, editar, eliminar, y obtener permisos
 */
export function useRoles() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const roles = ref([])
  const permissions = ref({})
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
   * Obtener lista de roles con paginación y búsqueda
   */
  const fetchRoles = async (page = 1) => {
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

      const response = await apiService.get('/roles', { params })

      roles.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar roles'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un rol específico por ID
   */
  const fetchRole = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/roles/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar rol'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener todos los permisos disponibles (agrupados por módulo)
   */
  const fetchAllPermissions = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get('/roles/permissions')
      permissions.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar permisos'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo rol
   */
  const createRole = async (roleData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/roles', roleData)

      alert.success('¡Rol creado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear rol'

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
   * Actualizar rol existente
   */
  const updateRole = async (id, roleData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/roles/${id}`, roleData)
      alert.success('¡Rol actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar rol'

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
   * Eliminar un rol
   */
  const deleteRole = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar rol?',
      'Esta acción no se puede deshacer. No se pueden eliminar roles del sistema ni roles con usuarios asignados.',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/roles/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de roles
      await fetchRoles(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar rol'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar roles
   */
  const searchRoles = async (searchTerm) => {
    search.value = searchTerm
    await fetchRoles(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchRoles(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'roles.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'roles.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'roles.index' })
  }

  // Computed
  const hasRoles = computed(() => roles.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)
  const permissionsCount = computed(() => {
    return Object.values(permissions.value).reduce((total, modulePerms) => {
      return total + modulePerms.length
    }, 0)
  })

  return {
    // Estado
    roles,
    permissions,
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
    fetchRoles,
    fetchRole,
    fetchAllPermissions,
    createRole,
    updateRole,
    deleteRole,
    searchRoles,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasRoles,
    hasPrevPage,
    hasNextPage,
    permissionsCount,
  }
}
