import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

/**
 * Store de Categorías
 * Maneja todo el estado y lógica relacionada con categorías
 */
export const useCategoriasStore = defineStore('categorias', () => {
  // Estado
  const categorias = ref([])
  const categoria = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  // Getters (computed)
  const categoriasActivas = computed(() =>
    categorias.value.filter(c => c.estado === 'activo')
  )

  const categoriasOptions = computed(() =>
    categoriasActivas.value.map(c => ({
      value: c.id,
      label: c.nombre
    }))
  )

  const hasCategorias = computed(() => categorias.value.length > 0)

  // Actions

  /**
   * Obtener todas las categorías (con paginación)
   */
  async function fetchCategorias(page = 1) {
    loading.value = true
    error.value = null

    try {
      const params = {
        page,
        per_page: pagination.value.per_page
      }

      const response = await axios.get('/api/categorias', { params })

      categorias.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener categorías'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener todas las categorías sin paginación (para selects)
   */
  async function fetchAllCategorias() {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get('/api/categorias', {
        params: { per_page: 1000 }
      })

      categorias.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener categorías'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener una categoría por ID
   */
  async function fetchCategoria(id) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/categorias/${id}`)
      categoria.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener categoría'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nueva categoría
   */
  async function createCategoria(data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post('/api/categorias', data)
      categorias.value.unshift(response.data.data)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear categoría'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar categoría existente
   */
  async function updateCategoria(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`/api/categorias/${id}`, data)

      // Actualizar en el array
      const index = categorias.value.findIndex(c => c.id === id)
      if (index !== -1) {
        categorias.value[index] = response.data.data
      }

      // Actualizar categoría actual si es la misma
      if (categoria.value?.id === id) {
        categoria.value = response.data.data
      }

      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar categoría'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar categoría
   */
  async function deleteCategoria(id) {
    loading.value = true
    error.value = null

    try {
      await axios.delete(`/api/categorias/${id}`)

      // Remover del array
      categorias.value = categorias.value.filter(c => c.id !== id)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar categoría'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Resetear estado
   */
  function resetState() {
    categorias.value = []
    categoria.value = null
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
    categorias,
    categoria,
    loading,
    error,
    pagination,

    // Getters
    categoriasActivas,
    categoriasOptions,
    hasCategorias,

    // Actions
    fetchCategorias,
    fetchAllCategorias,
    fetchCategoria,
    createCategoria,
    updateCategoria,
    deleteCategoria,
    resetState
  }
})
