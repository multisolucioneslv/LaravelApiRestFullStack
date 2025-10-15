<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Impuestos
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra los impuestos del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <TaxesDataTable
        v-else
        :taxes="taxes"
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
import { useTaxes } from '@/composables/useTaxes'
import TaxesDataTable from '@/components/taxes/TaxesDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  taxes,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchTaxes,
  deleteTax,
  deleteTaxesBulk,
  searchTaxes,
  changePage,
  goToCreate,
  goToEdit,
} = useTaxes()

// Cargar impuestos al montar
onMounted(() => {
  fetchTaxes()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchTaxes(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteTax(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteTaxesBulk(ids)
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
