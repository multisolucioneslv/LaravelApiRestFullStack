import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'

/**
 * Composable para gestionar el CRUD de monedas (currencies)
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useCurrencies() {
  const router = useRouter()
  const alert = useAlert()

  // Estado
  const currencies = ref([])
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
   * Obtener lista de monedas con paginación y búsqueda
   */
  const fetchCurrencies = async (page = 1) => {
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

      const response = await apiService.get('/currencies', { params })

      currencies.value = response.data.data
      currentPage.value = response.data.meta.current_page
      lastPage.value = response.data.meta.last_page
      perPage.value = response.data.meta.per_page
      total.value = response.data.meta.total
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar monedas'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una moneda específica por ID
   */
  const fetchCurrency = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.get(`/currencies/${id}`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar moneda'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva moneda
   */
  const createCurrency = async (currencyData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.post('/currencies', currencyData)

      alert.success('¡Moneda creada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear moneda'

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
   * Actualizar moneda existente
   */
  const updateCurrency = async (id, currencyData) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiService.put(`/currencies/${id}`, currencyData)
      alert.success('¡Moneda actualizada!', response.data.message)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar moneda'

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
   * Eliminar una moneda
   */
  const deleteCurrency = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar moneda?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete(`/currencies/${id}`)

      alert.success('¡Eliminada!', response.data.message)

      // Recargar lista de monedas
      await fetchCurrencies(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar moneda'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples monedas por lotes
   */
  const deleteCurrenciesBulk = async (currencyIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${currencyIds.length} moneda(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    try {
      const response = await apiService.delete('/currencies/bulk/delete', {
        data: { ids: currencyIds }
      })

      alert.success('¡Eliminadas!', response.data.message)

      // Recargar lista de monedas
      await fetchCurrencies(currentPage.value)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar monedas'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar monedas
   */
  const searchCurrencies = async (searchTerm) => {
    search.value = searchTerm
    await fetchCurrencies(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchCurrencies(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'currencies.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'currencies.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'currencies.index' })
  }

  // Computed
  const hasCurrencies = computed(() => currencies.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    currencies,
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
    fetchCurrencies,
    fetchCurrency,
    createCurrency,
    updateCurrency,
    deleteCurrency,
    deleteCurrenciesBulk,
    searchCurrencies,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasCurrencies,
    hasPrevPage,
    hasNextPage,
  }
}
