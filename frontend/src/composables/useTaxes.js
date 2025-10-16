import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de impuestos (taxes)
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useTaxes() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const taxes = ref([])
  const empresas = ref([]) // Para el select de empresas
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
   * Obtener lista de impuestos con paginación y búsqueda
   */
  const fetchTaxes = async (page = 1) => {
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

      const response = await apiService.get('/taxes', { params })

      taxes.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar impuestos'
      alert.error('Error', error.value)
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Obtener un impuesto específico por ID
   */
  const fetchTax = async (id) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await apiService.get(`/taxes/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar impuesto'
      alert.error('Error', error.value)
      throw err
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Obtener lista de empresas para el select
   */
  const fetchEmpresas = async () => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }

    try {
      const response = await apiService.get('/empresas', {
        params: {
          per_page: 1000 // Obtener todas las empresas
        }
      })
      empresas.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar empresas'
      alert.error('Error', error.value)
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Crear nuevo impuesto
   */
  const createTax = async (taxData) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await apiService.post('/taxes', taxData)

      alert.success('¡Impuesto creado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear impuesto'

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
   * Actualizar impuesto existente
   */
  const updateTax = async (id, taxData) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await apiService.put(`/taxes/${id}`, taxData)
      alert.success('¡Impuesto actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar impuesto'

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
   * Eliminar un impuesto
   */
  const deleteTax = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar impuesto?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await apiService.delete(`/taxes/${id}`)

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de impuestos
      await fetchTaxes(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar impuesto'
      alert.error('Error', error.value)
      return false
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Eliminar múltiples impuestos por lotes
   */
  const deleteTaxesBulk = async (taxIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${taxIds.length} impuesto(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    error.value = null

    try {
      const response = await apiService.post('/taxes/bulk/delete', {
        ids: taxIds
      })

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de impuestos
      await fetchTaxes(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar impuestos'
      alert.error('Error', error.value)
      return false
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  /**
   * Buscar impuestos
   */
  const searchTaxes = async (searchTerm) => {
    search.value = searchTerm
    await fetchTaxes(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchTaxes(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'taxes.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'taxes.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'taxes.index' })
  }

  // Computed
  const hasTaxes = computed(() => taxes.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    taxes,
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
    fetchTaxes,
    fetchTax,
    fetchEmpresas,
    createTax,
    updateTax,
    deleteTax,
    deleteTaxesBulk,
    searchTaxes,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasTaxes,
    hasPrevPage,
    hasNextPage,
  }
}
