<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Monedas
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra las monedas del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <MonedasDataTable
        v-else
        :monedas="currencies"
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
import { useCurrencies } from '@/composables/useCurrencies'
import MonedasDataTable from '@/components/monedas/MonedasDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  currencies,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchCurrencies,
  deleteCurrency,
  deleteCurrenciesBulk,
  searchCurrencies,
  changePage,
  goToCreate,
  goToEdit,
} = useCurrencies()

// Cargar monedas al montar
onMounted(() => {
  fetchCurrencies()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchCurrencies(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteCurrency(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteCurrenciesBulk(ids)
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
