<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Categorías
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra las categorías de productos
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <CategoriasDataTable
        v-else
        :categorias="categorias"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="total"
        :has-prev-page="hasPrevPage"
        :has-next-page="hasNextPage"
        @search="handleSearch"
        @edit="handleEdit"
        @delete="handleDelete"
        @create="handleCreate"
        @previous-page="handlePreviousPage"
        @next-page="handleNextPage"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import { useCategorias } from '@/composables/useCategorias'
import CategoriasDataTable from '@/components/categorias/CategoriasDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  categorias,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchCategorias,
  deleteCategoria,
  searchCategorias,
  changePage,
  goToCreate,
  goToEdit,
} = useCategorias()

// Cargar categorías al montar
onMounted(() => {
  fetchCategorias()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchCategorias(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  await deleteCategoria(id)
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
