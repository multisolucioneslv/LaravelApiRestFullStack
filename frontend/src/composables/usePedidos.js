import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const API_URL = import.meta.env.VITE_API_URL

export function usePedidos() {
  const router = useRouter()
  const pedidos = ref([])
  const pedido = ref(null)
  const loading = ref(false)
  const empresas = ref([])

  // Obtener lista de pedidos con paginación
  const fetchPedidos = async (page = 1, search = '') => {
    loading.value = true
    try {
      const response = await axios.get(`${API_URL}/pedidos`, {
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
      loading.value = false
    }
  }

  // Obtener un pedido específico
  const fetchPedido = async (id) => {
    loading.value = true
    try {
      const response = await axios.get(`${API_URL}/pedidos/${id}`)
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
      loading.value = false
    }
  }

  // Crear nuevo pedido
  const createPedido = async (pedidoData) => {
    loading.value = true
    try {
      const response = await axios.post(`${API_URL}/pedidos`, pedidoData)
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
      loading.value = false
    }
  }

  // Actualizar pedido
  const updatePedido = async (id, pedidoData) => {
    loading.value = true
    try {
      const response = await axios.put(`${API_URL}/pedidos/${id}`, pedidoData)
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
      loading.value = false
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
      loading.value = true
      try {
        await axios.delete(`${API_URL}/pedidos/${id}`)
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
        loading.value = false
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
      loading.value = true
      try {
        await axios.post(`${API_URL}/pedidos/bulk/delete`, { ids })
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
        loading.value = false
      }
    }
    return false
  }

  // Obtener lista de empresas
  const fetchEmpresas = async () => {
    try {
      const response = await axios.get(`${API_URL}/empresas`, {
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
