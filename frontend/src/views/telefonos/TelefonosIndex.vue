<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Teléfonos
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra los teléfonos del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <TelefonosDataTable
        v-else
        :telefonos="telefonos"
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
import { useTelefonos } from '@/composables/useTelefonos'
import TelefonosDataTable from '@/components/telefonos/TelefonosDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  telefonos,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchTelefonos,
  deleteTelefono,
  deleteTelefonosBulk,
  searchTelefonos,
  changePage,
  goToCreate,
  goToEdit,
} = useTelefonos()

// Cargar teléfonos al montar
onMounted(() => {
  fetchTelefonos()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchTelefonos(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteTelefono(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteTelefonosBulk(ids)
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
