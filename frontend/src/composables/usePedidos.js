import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import Swal from 'sweetalert2'
import { useAuthStore } from '@/stores/auth'

export function usePedidos() {
  const router = useRouter()
  const authStore = useAuthStore()
  const pedidos = ref([])
  const pedido = ref(null)
  const loading = ref(false)
  const empresas = ref([])

  // Obtener lista de pedidos con paginación
  const fetchPedidos = async (page = 1, search = '') => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    try {
      const response = await api.get('/pedidos', {
        params: { page, search, per_page: 15 }
      })
      pedidos.value = response.data.data
      return response.data
    } catch (error) {
      console.error('Error al obtener pedidos:', error)
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron cargar los pedidos'
      })
      return null
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  // Obtener un pedido específico
  const fetchPedido = async (id) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    try {
      const response = await api.get(`/pedidos/${id}`)
      pedido.value = response.data.data
      return response.data.data
    } catch (error) {
      console.error('Error al obtener pedido:', error)
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo cargar el pedido'
      })
      return null
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  // Crear nuevo pedido
  const createPedido = async (pedidoData) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    try {
      const response = await api.post('/pedidos', pedidoData)
      Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: response.data.message || 'Pedido creado correctamente'
      })
      return response.data.data
    } catch (error) {
      console.error('Error al crear pedido:', error)
      const errorMessage = error.response?.data?.message || 'No se pudo crear el pedido'
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errorMessage
      })
      return null
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  // Actualizar pedido
  const updatePedido = async (id, pedidoData) => {
    if (authStore.showLoadingEffect) {
      loading.value = true
    }
    try {
      const response = await api.put(`/pedidos/${id}`, pedidoData)
      Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: response.data.message || 'Pedido actualizado correctamente'
      })
      return response.data.data
    } catch (error) {
      console.error('Error al actualizar pedido:', error)
      const errorMessage = error.response?.data?.message || 'No se pudo actualizar el pedido'
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errorMessage
      })
      return null
    } finally {
      if (authStore.showLoadingEffect) {
        loading.value = false
      }
    }
  }

  // Eliminar pedido
  const deletePedido = async (id) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: 'Esta acción no se puede deshacer',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    })

    if (result.isConfirmed) {
      if (authStore.showLoadingEffect) {
        loading.value = true
      }
      try {
        await api.delete(`/pedidos/${id}`)
        Swal.fire({
          icon: 'success',
          title: '¡Eliminado!',
          text: 'El pedido ha sido eliminado'
        })
        return true
      } catch (error) {
        console.error('Error al eliminar pedido:', error)
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo eliminar el pedido'
        })
        return false
      } finally {
        if (authStore.showLoadingEffect) {
          loading.value = false
        }
      }
    }
    return false
  }

  // Eliminar múltiples pedidos
  const bulkDeletePedidos = async (ids) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `Se eliminarán ${ids.length} pedidos`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    })

    if (result.isConfirmed) {
      if (authStore.showLoadingEffect) {
        loading.value = true
      }
      try {
        await api.post('/pedidos/bulk/delete', { ids })
        Swal.fire({
          icon: 'success',
          title: '¡Eliminados!',
          text: 'Los pedidos han sido eliminados'
        })
        return true
      } catch (error) {
        console.error('Error al eliminar pedidos:', error)
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron eliminar los pedidos'
        })
        return false
      } finally {
        if (authStore.showLoadingEffect) {
          loading.value = false
        }
      }
    }
    return false
  }

  // Obtener lista de empresas
  const fetchEmpresas = async () => {
    try {
      const response = await api.get('/empresas', {
        params: { per_page: 1000 }
      })
      empresas.value = response.data.data
      return response.data.data
    } catch (error) {
      console.error('Error al obtener empresas:', error)
      return []
    }
  }

  // Navegación
  const goToCreate = () => router.push({ name: 'pedidos.create' })
  const goToEdit = (id) => router.push({ name: 'pedidos.edit', params: { id } })
  const goToIndex = () => router.push({ name: 'pedidos.index' })

  return {
    pedidos,
    pedido,
    loading,
    empresas,
    fetchPedidos,
    fetchPedido,
    createPedido,
    updatePedido,
    deletePedido,
    bulkDeletePedidos,
    fetchEmpresas,
    goToCreate,
    goToEdit,
    goToIndex
  }
}
