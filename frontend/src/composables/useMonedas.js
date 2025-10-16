import { useCurrencies } from './useCurrencies'

/**
 * Composable para gestionar el CRUD de monedas (currencies)
 * Este es un wrapper en español de useCurrencies
 */
export function useMonedas() {
  // Obtener todas las funciones y propiedades de useCurrencies
  const {
    currencies,
    loading,
    error,
    currentPage,
    lastPage,
    perPage,
    total,
    search,
    fetchCurrencies,
    fetchCurrency,
    createCurrency,
    updateCurrency,
    deleteCurrency,
    deleteCurrenciesBulk,
    searchCurrencies,
    changePage,
    goToCreate,
    goToEdit,
    goToIndex,
    hasCurrencies,
    hasPrevPage,
    hasNextPage,
  } = useCurrencies()

  /**
   * Función de limpieza para resetear el estado
   * Se llama cuando el componente se desmonta o cuando se cierra sesión
   */
  const cleanupMonedas = () => {
    currencies.value = []
    search.value = ''
    error.value = null
    currentPage.value = 1
    lastPage.value = 1
    total.value = 0
  }

  // Retornar con nombres en español
  return {
    // Estado (con nombres en español)
    monedas: currencies,
    loading,
    error,

    // Paginación
    currentPage,
    lastPage,
    perPage,
    total,

    // Búsqueda
    search,

    // Métodos (con nombres en español)
    fetchMonedas: fetchCurrencies,
    fetchMoneda: fetchCurrency,
    createMoneda: createCurrency,
    updateMoneda: updateCurrency,
    deleteMoneda: deleteCurrency,
    deleteMonedasBulk: deleteCurrenciesBulk,
    searchMonedas: searchCurrencies,
    changePage,
    cleanupMonedas,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasMonedas: hasCurrencies,
    hasPrevPage,
    hasNextPage,
  }
}
