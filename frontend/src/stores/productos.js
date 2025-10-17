import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

/**
 * Store de Productos
 * Maneja todo el estado y lógica relacionada con productos
 */
export const useProductosStore = defineStore('productos', () => {
  // Estado
  const productos = ref([])
  const producto = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const filters = ref({
    search: '',
    categoria_id: null,
    estado: 'activo',
    min_precio: null,
    max_precio: null,
    min_stock: null
  })

  // Getters (computed)
  const productosActivos = computed(() =>
    productos.value.filter(p => p.estado === 'activo')
  )

  const productosBajoStock = computed(() =>
    productos.value.filter(p => p.stock_actual <= p.stock_minimo)
  )

  const hasProductos = computed(() => productos.value.length > 0)

  // Actions

  /**
   * Obtener todos los productos (con paginación y filtros)
   */
  async function fetchProductos(page = 1) {
    loading.value = true
    error.value = null

    try {
      const params = {
        page,
        per_page: pagination.value.per_page,
        ...filters.value
      }

      const response = await axios.get('/api/productos', { params })

      productos.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener productos'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un producto por ID
   */
  async function fetchProducto(id) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/productos/${id}`)
      producto.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener producto'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo producto
   */
  async function createProducto(data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post('/api/productos', data)
      productos.value.unshift(response.data.data)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear producto'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar producto existente
   */
  async function updateProducto(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`/api/productos/${id}`, data)

      // Actualizar en el array
      const index = productos.value.findIndex(p => p.id === id)
      if (index !== -1) {
        productos.value[index] = response.data.data
      }

      // Actualizar producto actual si es el mismo
      if (producto.value?.id === id) {
        producto.value = response.data.data
      }

      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar producto'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar producto
   */
  async function deleteProducto(id) {
    loading.value = true
    error.value = null

    try {
      await axios.delete(`/api/productos/${id}`)

      // Remover del array
      productos.value = productos.value.filter(p => p.id !== id)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar producto'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar productos
   */
  async function searchProductos(query) {
    filters.value.search = query
    return await fetchProductos(1)
  }

  /**
   * Filtrar por categoría
   */
  async function filterByCategoria(categoriaId) {
    filters.value.categoria_id = categoriaId
    return await fetchProductos(1)
  }

  /**
   * Filtrar por estado
   */
  async function filterByEstado(estado) {
    filters.value.estado = estado
    return await fetchProductos(1)
  }

  /**
   * Limpiar filtros
   */
  async function clearFilters() {
    filters.value = {
      search: '',
      categoria_id: null,
      estado: 'activo',
      min_precio: null,
      max_precio: null,
      min_stock: null
    }
    return await fetchProductos(1)
  }

  /**
   * Resetear estado
   */
  function resetState() {
    productos.value = []
    producto.value = null
    loading.value = false
    error.value = null
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    }
  }

  return {
    // Estado
    productos,
    producto,
    loading,
    error,
    pagination,
    filters,

    // Getters
    productosActivos,
    productosBajoStock,
    hasProductos,

    // Actions
    fetchProductos,
    fetchProducto,
    createProducto,
    updateProducto,
    deleteProducto,
    searchProductos,
    filterByCategoria,
    filterByEstado,
    clearFilters,
    resetState
  }
})
