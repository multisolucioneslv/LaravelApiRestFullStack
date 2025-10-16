import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiService } from '@/services/api'
import { useAlert } from '@/composables/useAlert'
import { useAuthStore } from '@/stores/auth'

/**
 * Composable para gestionar el CRUD de géneros
 * Incluye: listar, crear, editar, eliminar individual y por lotes
 */
export function useGenders() {
  const router = useRouter()
  const alert = useAlert()
  const authStore = useAuthStore()

  // Estado
  const genders = ref([])
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
   * Obtener lista de géneros con paginación y búsqueda
   */
  const fetchGenders = async (page = 1, showLoading = true) => {
    loading.value = true
    error.value = null

    if (showLoading && authStore.showLoadingEffect) {
      alert.loading('Cargando lista de géneros', 'Por favor espere...')
    }

    try {
      const params = {
        page,
        per_page: perPage.value,
      }

      if (search.value) {
        params.search = search.value
      }

      const response = await apiService.get('/genders', { params })

      genders.value = response.data.data
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

      error.value = err.response?.data?.message || 'Error al cargar géneros'
      alert.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  /**
   * Obtener un género específico por ID
   */
  const fetchGender = async (id) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando datos del género', 'Por favor espere...')
    }

    try {
      const response = await apiService.get(`/genders/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al cargar género'
      alert.error('Error', error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crear nuevo género
   */
  const createGender = async (genderData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Creando género', 'Por favor espere...')
    }

    try {
      const response = await apiService.post('/genders', genderData)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Género creado!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al crear género'

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
   * Actualizar género existente
   */
  const updateGender = async (id, genderData) => {
    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando género', 'Por favor espere...')
    }

    try {
      const response = await apiService.put(`/genders/${id}`, genderData)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Género actualizado!', response.data.message)
      return response.data.data
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al actualizar género'

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
   * Eliminar un género
   */
  const deleteGender = async (id) => {
    const result = await alert.confirm(
      '¿Eliminar género?',
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando género', 'Por favor espere...')
    }

    try {
      const response = await apiService.delete(`/genders/${id}`)

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminado!', response.data.message)

      // Recargar lista de géneros (sin mostrar loading adicional)
      await fetchGenders(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar género'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Eliminar múltiples géneros por lotes
   */
  const deleteGendersBulk = async (genderIds) => {
    const result = await alert.confirm(
      `¿Eliminar ${genderIds.length} género(s)?`,
      'Esta acción no se puede deshacer',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return false

    loading.value = true
    error.value = null

    if (authStore.showLoadingEffect) {
      alert.loading(`Eliminando ${genderIds.length} género(s)`, 'Por favor espere...')
    }

    try {
      const response = await apiService.delete('/genders/bulk/delete', {
        data: { ids: genderIds }
      })

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('¡Eliminados!', response.data.message)

      // Recargar lista de géneros (sin mostrar loading adicional)
      await fetchGenders(currentPage.value, false)

      return true
    } catch (err) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      error.value = err.response?.data?.message || 'Error al eliminar géneros'
      alert.error('Error', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Buscar géneros
   */
  const searchGenders = async (searchTerm) => {
    search.value = searchTerm
    await fetchGenders(1) // Reiniciar a la página 1
  }

  /**
   * Cambiar de página
   */
  const changePage = async (page) => {
    if (page < 1 || page > lastPage.value) return
    await fetchGenders(page)
  }

  /**
   * Navegación
   */
  const goToCreate = () => {
    router.push({ name: 'genders.create' })
  }

  const goToEdit = (id) => {
    router.push({ name: 'genders.edit', params: { id } })
  }

  const goToIndex = () => {
    router.push({ name: 'genders.index' })
  }

  // Computed
  const hasGenders = computed(() => genders.value.length > 0)
  const hasPrevPage = computed(() => currentPage.value > 1)
  const hasNextPage = computed(() => currentPage.value < lastPage.value)

  return {
    // Estado
    genders,
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
    fetchGenders,
    fetchGender,
    createGender,
    updateGender,
    deleteGender,
    deleteGendersBulk,
    searchGenders,
    changePage,

    // Navegación
    goToCreate,
    goToEdit,
    goToIndex,

    // Computed
    hasGenders,
    hasPrevPage,
    hasNextPage,
  }
}
