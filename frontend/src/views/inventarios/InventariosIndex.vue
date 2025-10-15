<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Inventarios
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra el inventario de productos
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <InventariosDataTable
        v-else
        :inventarios="inventarios"
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
import { onMounted } from 'vue'
import { useInventarios } from '@/composables/useInventarios'
import InventariosDataTable from '@/components/inventarios/InventariosDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  inventarios,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchInventarios,
  deleteInventario,
  deleteInventariosBulk,
  searchInventarios,
  changePage,
  goToCreate,
  goToEdit,
} = useInventarios()

// Cargar inventarios al montar
onMounted(() => {
  fetchInventarios()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchInventarios(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteInventario(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteInventariosBulk(ids)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleCreate = () => {
  goToCreate()
}

const handlePreviousPage = () => {
  changePage(currentPage.value - 1)
}

const handleNextPage = () => {
  changePage(currentPage.value + 1)
}
</script>
