<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Gestión de Productos
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Administra el inventario de productos
          </p>
        </div>
        <button
          @click="handleCreate"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 4.5v15m7.5-7.5h-15"
            />
          </svg>
          Nuevo Producto
        </button>
      </div>

      <!-- Filtros y Búsqueda -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Búsqueda -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Buscar
            </label>
            <input
              v-model="searchTerm"
              @input="handleSearch"
              type="text"
              placeholder="Nombre, código o descripción..."
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Filtro por Categoría -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Categoría
            </label>
            <select
              v-model="selectedCategoria"
              @change="handleFilterChange"
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
            >
              <option :value="null">Todas</option>
              <option
                v-for="categoria in categorias"
                :key="categoria.id"
                :value="categoria.id"
              >
                {{ categoria.nombre }}
              </option>
            </select>
          </div>

          <!-- Filtro por Estado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Estado
            </label>
            <select
              v-model="selectedEstado"
              @change="handleFilterChange"
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
            >
              <option :value="null">Todos</option>
              <option :value="1">Activos</option>
              <option :value="0">Inactivos</option>
            </select>
          </div>
        </div>

        <!-- Filtros adicionales -->
        <div class="flex items-center gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="showBajoStock"
              @change="handleFilterChange"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="text-sm text-gray-700 dark:text-gray-300">
              Solo productos bajo stock
            </span>
          </label>

          <button
            v-if="hasActiveFilters"
            @click="handleClearFilters"
            class="ml-auto text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Tabla de Productos -->
      <div
        v-else-if="hasProductos"
        class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
      >
        <ProductosTable
          :productos="productos"
          @edit="handleEdit"
          @delete="handleDelete"
          @update-stock="handleUpdateStock"
          @restore="handleRestore"
        />

        <!-- Paginación -->
        <div
          class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between"
        >
          <div class="text-sm text-gray-700 dark:text-gray-300">
            Mostrando {{ productos.length }} de {{ total }} productos
          </div>

          <div class="flex gap-2">
            <button
              @click="handlePreviousPage"
              :disabled="!hasPrevPage"
              class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              Anterior
            </button>
            <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">
              Página {{ currentPage }} de {{ lastPage }}
            </span>
            <button
              @click="handleNextPage"
              :disabled="!hasNextPage"
              class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>

      <!-- Estado vacío -->
      <div
        v-else
        class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="w-16 h-16 mx-auto text-gray-400 mb-4"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
          />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          No hay productos
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Comienza agregando tu primer producto
        </p>
        <button
          @click="handleCreate"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Crear Producto
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useProductos } from '@/composables/useProductos'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/layout/AppLayout.vue'
import ProductosTable from '@/components/productos/ProductosTable.vue'

const authStore = useAuthStore()

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
  restoreProducto,
  updateStock,
  searchProductos,
  applyFilters,
  clearFilters,
  changePage,
  goToCreate,
  goToEdit,
} = useProductos()

// Estado local para filtros
const searchTerm = ref('')
const selectedCategoria = ref(null)
const selectedEstado = ref(null)
const showBajoStock = ref(false)

// Computed
const hasProductos = computed(() => productos.value.length > 0)
const hasActiveFilters = computed(() => {
  return searchTerm.value || selectedCategoria.value !== null || selectedEstado.value !== null || showBajoStock.value
})

// Carga inicial
onMounted(async () => {
  const token = localStorage.getItem('auth_token')

  if (authStore.isAuthenticated && token) {
    await fetchCategorias()
    await fetchProductos()
  } else {
    console.warn('🔒 [SECURITY] No se cargaron productos: sesión no activa')
  }
})

// Watch autenticación
watch(() => authStore.isAuthenticated, async (isAuth) => {
  if (!isAuth) {
    productos.value = []
    categorias.value = []
  } else if (productos.value.length === 0) {
    await fetchCategorias()
    await fetchProductos()
  }
})

// Manejadores
const handleSearch = () => {
  searchProductos(searchTerm.value)
}

const handleFilterChange = () => {
  applyFilters({
    categoria_id: selectedCategoria.value,
    activo: selectedEstado.value,
    bajo_stock: showBajoStock.value,
  })
}

const handleClearFilters = () => {
  searchTerm.value = ''
  selectedCategoria.value = null
  selectedEstado.value = null
  showBajoStock.value = false
  clearFilters()
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  await deleteProducto(id)
}

const handleRestore = async (id) => {
  await restoreProducto(id)
}

const handleUpdateStock = async (data) => {
  await updateStock(data.id, data.cantidad, data.tipo)
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
