import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de productos
 * Incluye: listar, crear, editar, eliminar, filtros, búsqueda, bajo stock
 */
export function useProductos() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const productos = ref([])
  const categorias = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Paginación
  const currentPage = ref(1)
  const lastPage = ref(1)
  const perPage = ref(15)
  const total = ref(0)

  // Búsqueda y Filtros
  const search = ref('')
  const filters = ref({
    categoria_id: null,
    bajo_stock: false,
    activo: null,
  })

  /**
   * Obtener lista de productos con paginación, búsqueda y filtros
   */
  const fetchProductos = async (page = 1, showLoading = true) => {
    loading.value = true
    error.value = null

    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando productos', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (search.value) {
        params.search = search.value
      }

      if (filters.value.categoria_id) {
        params.categoria_id = filters.value.categoria_id
      }

      if (filters.value.activo !== null) {
        params.activo = filters.value.activo
      }

      const endpoint = filters.value.bajo_stock
        ? '/productos/bajo-stock'
        : '/productos'

      const response = await apiService.get(endpoint, { params })

      productos.value = response.data.data
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
      error.value = err.response?.data?.message || 'Error al cargar productos'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un producto específico por ID
   */
  const fetchProducto = async (id) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando producto', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/productos/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al cargar producto'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener lista de categorías para el selector
   */
  const fetchCategorias = async () => {
    try {
      const response = await apiService.get('/categorias/all')
      categorias.value = response.data.data
    } catch (err) {
      console.error('Error al cargar categorías:', err)
      categorias.value = []
    }
  }

  /**
   * Crear nuevo producto
   */
  const createProducto = async (productoData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Creando producto', 'Por favor espere...')
    }

    try {
      // Si hay imagen, usar FormData
      let data = productoData
      if (productoData.imagen instanceof File) {
        data = new FormData()
        Object.keys(productoData).forEach(key => {
          if (productoData[key] !== null && productoData[key] !== undefined) {
            data.append(key, productoData[key])
          }
        })
      }

      const response = await apiService.post('/productos', data, {
        headers: productoData.imagen instanceof File
          ? { 'Content-Type': 'multipart/form-data' }
          : {}
      })

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Producto creado!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al crear producto'

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
   * Actualizar producto existente
   */
  const updateProducto = async (id, productoData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando producto', 'Por favor espere...')
    }

    try {
      // Si hay imagen, usar FormData con _method POST
      let data = productoData
      let config = {}

      if (productoData.imagen instanceof File) {
        data = new FormData()
        Object.keys(productoData).forEach(key => {
          if (productoData[key] !== null && productoData[key] !== undefined) {
            data.append(key, productoData[key])
          }
        })
        config.headers = { 'Content-Type': 'multipart/form-data' }
      }

      // Laravel espera POST con imagen, no PUT
      const response = productoData.imagen instanceof File
        ? await apiService.post(`/productos/${id}`, data, config)
        : await apiService.put(`/productos/${id}`, data)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Producto actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al actualizar producto'

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
   * Actualizar stock de un producto
   */
  const updateStock = async (id, cantidad, tipo = 'aumentar') => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando stock', 'Por favor espere...')
    }

    try {
      const response = await apiService.post(`/productos/${id}/stock`, {
        cantidad,
        tipo
      })

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Stock actualizado!', response.data.message)

      // Recargar lista de productos
      await fetchProductos(currentPage.value, false)

      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al actualizar stock'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar un producto (soft delete)
   */
  const deleteProducto = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar producto?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando producto', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/productos/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de productos
      await fetchProductos(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al eliminar producto'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Restaurar un producto eliminado
   */
  const restoreProducto = async (id) => {
    const result = await alert.confirm(
      '¿Restaurar producto?',
      'El producto será reactivado',
      'Sí, restaurar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Restaurando producto', 'Por favor espere...')
    }

    try {
      const response = await apiService.post(`/productos/${id}/restore`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Restaurado!', response.data.message)

      // Recargar lista de productos
      await fetchProductos(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }
      error.value = err.response?.data?.message || 'Error al restaurar producto'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar productos
   */
  const searchProductos = async (searchTerm) => {
    search.value = searchTerm
    await fetchProductos(1) // Reiniciar a la página 1
  }

  /**
   * Aplicar filtros
   */
  const applyFilters = async (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    await fetchProductos(1)
  }

  /**
   * Limpiar filtros
   */
  const clearFilters = async () => {
    filters.value = {
      categoria_id: null,
      bajo_stock: false,
      activo: null,
    }
    search.value = ''
    await fetchProductos(1)
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchProductos(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'productos.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'productos.edit', params: { id } })
  }

  const goToDetail = (id) => {
    router.push({ name: 'productos.detail', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'productos.index' })
  }

  // Computed
  const hasProductos = computed(() => productos.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)
  const productosBajoStock = computed(() => {
    return productos.value.filter(p => p.stock <= p.stock_minimo)
  })

  return {
    // Estado
    productos,
    categorias,
    loading,
    error,

    // Paginación
    currentPage,
    lastPage,
    perPage,
    total,

    // Búsqueda y Filtros
    search,
    filters,

    // Métodos
    fetchProductos,
    fetchProducto,
    fetchCategorias,
    createProducto,
    updateProducto,
    updateStock,
    deleteProducto,
    restoreProducto,
    searchProductos,
    applyFilters,
    clearFilters,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToDetail,
    goToIndex,

    // Computed
    hasProductos,
    hasPrevPage,
    hasNextPage,
    productosBajoStock,
  }
}
