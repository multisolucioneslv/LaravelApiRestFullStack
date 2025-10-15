<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Galerías
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra las galerías de imágenes del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <GaleriasDataTable
        v-else
        :galerias="galerias"
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
import { useGalerias } from '@/composables/useGalerias'
import GaleriasDataTable from '@/components/galerias/GaleriasDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  galerias,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchGalerias,
  deleteGaleria,
  deleteGaleriasBulk,
  searchGalerias,
  changePage,
  goToCreate,
  goToEdit,
} = useGalerias()

// Cargar galerías al montar
onMounted(() => {
  fetchGalerias()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchGalerias(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteGaleria(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteGaleriasBulk(ids)
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
