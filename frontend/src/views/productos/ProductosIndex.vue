<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Productos
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra el catálogo de productos del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <ProductosDataTable
        v-else
        :productos="productos"
        :categorias="categorias"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="total"
        :has-prev-page="hasPrevPage"
        :has-next-page="hasNextPage"
        @search="handleSearch"
        @filter="handleFilter"
        @view="handleView"
        @edit="handleEdit"
        @delete="handleDelete"
        @update-stock="handleUpdateStock"
        @create="handleCreate"
        @previous-page="handlePreviousPage"
        @next-page="handleNextPage"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import { useProductos } from '@/composables/useProductos'
import ProductosDataTable from '@/components/productos/ProductosDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  productos,
  categorias,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchProductos,
  fetchCategorias,
  deleteProducto,
  updateStock,
  searchProductos,
  applyFilters,
  changePage,
  goToCreate,
  goToEdit,
  goToDetail,
} = useProductos()

// Cargar productos y categorías al montar
onMounted(async () => {
  await fetchCategorias()
  await fetchProductos()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchProductos(searchTerm)
}

const handleFilter = (filters) => {
  applyFilters(filters)
}

const handleView = (id) => {
  goToDetail(id)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  await deleteProducto(id)
}

const handleUpdateStock = async (id, cantidad, tipo) => {
  await updateStock(id, cantidad, tipo)
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
