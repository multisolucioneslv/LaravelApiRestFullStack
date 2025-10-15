<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Pedidos
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra los pedidos del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <PedidosDataTable
        v-else
        :pedidos="pedidos"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="total"
        :has-prev-page="hasPrevPage"
        :has-next-page="hasNextPage"
        @search="handleSearch"
        @edit="handleEdit"
        @delete="handleDelete"
        @bulk-delete="handleBulkDelete"
        @create="handleCreate"
        @previous-page="handlePreviousPage"
        @next-page="handleNextPage"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePedidos } from '@/composables/usePedidos'
import PedidosDataTable from '@/components/pedidos/PedidosDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  pedidos,
  loading,
  fetchPedidos,
  deletePedido,
  bulkDeletePedidos,
  goToCreate,
  goToEdit,
} = usePedidos()

// Estado de paginación
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const hasPrevPage = ref(false)
const hasNextPage = ref(false)
const searchTerm = ref('')

// Cargar pedidos al montar
onMounted(() => {
  loadPedidos()
})

// Cargar pedidos con paginación
const loadPedidos = async () => {
  const response = await fetchPedidos(currentPage.value, searchTerm.value)
  if (response) {
    currentPage.value = response.current_page
    lastPage.value = response.last_page
    total.value = response.total
    hasPrevPage.value = response.current_page > 1
    hasNextPage.value = response.current_page < response.last_page
  }
}

// Manejadores
const handleSearch = (search) => {
  searchTerm.value = search
  currentPage.value = 1
  loadPedidos()
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deletePedido(id)
  if (deleted) {
    loadPedidos()
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await bulkDeletePedidos(ids)
  if (deleted) {
    loadPedidos()
  }
}

const handleCreate = () => {
  goToCreate()
}

const handlePreviousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    loadPedidos()
  }
}

const handleNextPage = () => {
  if (currentPage.value < lastPage.value) {
    currentPage.value++
    loadPedidos()
  }
}
</script>
