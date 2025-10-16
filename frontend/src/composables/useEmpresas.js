import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de empresas
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useEmpresas() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const empresas = ref([])
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
   * Obtener lista de empresas con paginación y búsqueda
   */
  const fetchEmpresas = async (page = 1, showLoading = true) => {
    loading.value = true
    error.value = null

    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando lista de empresas', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/empresas', { params })

      empresas.value = response.data.data
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

      error.value = err.response?.data?.message || 'Error al cargar empresas'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una empresa específica por ID
   */
  const fetchEmpresa = async (id) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando datos de la empresa', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/empresas/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al cargar empresa'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva empresa
   */
  const createEmpresa = async (empresaData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Creando empresa', 'Por favor espere...')
    }

    try {
      // Si empresaData es FormData, enviar con headers multipart/form-data
      const config = empresaData instanceof FormData
        ? { headers: { 'Content-Type': 'multipart/form-data' } }
        : {}

      const response = await apiService.post('/empresas', empresaData, config)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Empresa creada!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al crear empresa'

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
   * Actualizar empresa existente
   */
  const updateEmpresa = async (id, empresaData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando empresa', 'Por favor espere...')
    }

    try {
      // Si empresaData es FormData, usar POST con _method=PUT
      if (empresaData instanceof FormData) {
        empresaData.append('_method', 'PUT')

        const config = {
          headers: { 'Content-Type': 'multipart/form-data' }
        }

        const response = await apiService.post(`/empresas/${id}`, empresaData, config)

        if (authStore.showLoadingEffect) {
          alert.close()
        }

        alert.success('¡Empresa actualizada!', response.data.message)
        return response.data.data
      } else {
        // Si no es FormData, usar PUT normal
        const response = await apiService.put(`/empresas/${id}`, empresaData)

        if (authStore.showLoadingEffect) {
          alert.close()
        }

        alert.success('¡Empresa actualizada!', response.data.message)
        return response.data.data
      }
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al actualizar empresa'

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
   * Eliminar una empresa
   */
  const deleteEmpresa = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar empresa?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando empresa', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/empresas/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminada!', response.data.message)

      // Recargar lista de empresas (sin mostrar loading adicional)
      await fetchEmpresas(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar empresa'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples empresas por lotes
   */
  const deleteEmpresasBulk = async (empresaIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${empresaIds.length} empresa(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading(`Eliminando ${empresaIds.length} empresa(s)`, 'Por favor espere...')
    }

    try {
      const response = await apiService.post('/empresas/bulk/delete', {
        ids: empresaIds
      })

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminadas!', response.data.message)

      // Recargar lista de empresas (sin mostrar loading adicional)
      await fetchEmpresas(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar empresas'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar empresas
   */
  const searchEmpresas = async (searchTerm) => {
    search.value = searchTerm
    await fetchEmpresas(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchEmpresas(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'empresas.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'empresas.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'empresas.index' })
  }

  /**
   * Inicializar datos (para carga lazy)
   */
  const initialize = async () => {
    await fetchEmpresas()
  }

  /**
   * Limpiar datos (para seguridad al desmontar)
   */
  const cleanup = () => {
    empresas.value = []
    currentPage.value = 1
    lastPage.value = 1
    total.value = 0
    search.value = ''
    error.value = null
  }

  // Computed
  const hasEmpresas = computed(() => empresas.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    empresas,
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
    fetchEmpresas,
    fetchEmpresa,
    createEmpresa,
    updateEmpresa,
    deleteEmpresa,
    deleteEmpresasBulk,
    searchEmpresas,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Seguridad
    initialize,
    cleanup,

    // Computed
    hasEmpresas,
    hasPrevPage,
    hasNextPage,
  }
}
