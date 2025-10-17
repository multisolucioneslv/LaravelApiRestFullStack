<script setup>
import { ref, onMounted, computed } from 'vue'
import { useProductosStore } from '@/stores/productos'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import ProductoCard from './ProductoCard.vue'
import ProductoFilters from './ProductoFilters.vue'

const productosStore = useProductosStore()
const router = useRouter()

// Estado local
const viewMode = ref('grid') // grid o table

// Computed
const productos = computed(() => productosStore.productos)
const loading = computed(() => productosStore.loading)
const pagination = computed(() => productosStore.pagination)

// Lifecycle
onMounted(() => {
  loadProductos()
})

// Methods
async function loadProductos(page = 1) {
  try {
    await productosStore.fetchProductos(page)
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron cargar los productos',
      confirmButtonText: 'Aceptar'
    })
  }
}

function handleEdit(producto) {
  router.push({
    name: 'productos.edit',
    params: { id: producto.id }
  })
}

function handleView(producto) {
  router.push({
    name: 'productos.detail',
    params: { id: producto.id }
  })
}

async function handleDelete(producto) {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: `Se eliminará el producto "${producto.nombre}"`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (result.isConfirmed) {
    try {
      await productosStore.deleteProducto(producto.id)
      Swal.fire({
        icon: 'success',
        title: 'Eliminado',
        text: 'Producto eliminado correctamente',
        timer: 2000,
        showConfirmButton: false
      })
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || 'No se pudo eliminar el producto',
        confirmButtonText: 'Aceptar'
      })
    }
  }
}

function handleFiltersChange(filters) {
  productosStore.filters = filters
  loadProductos(1)
}

function handlePageChange(page) {
  loadProductos(page)
}

function goToCreate() {
  router.push({ name: 'productos.create' })
}
</script>

<template>
  <div class="productos-list">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
          Productos
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          Gestiona tu catálogo de productos
        </p>
      </div>

      <div class="flex gap-3">
        <!-- Toggle View Mode -->
        <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700">
          <button
            @click="viewMode = 'grid'"
            :class="[
              'px-4 py-2 text-sm font-medium transition-colors',
              viewMode === 'grid'
                ? 'bg-primary-600 text-white'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            Cuadrícula
          </button>
          <button
            @click="viewMode = 'table'"
            :class="[
              'px-4 py-2 text-sm font-medium transition-colors',
              viewMode === 'table'
                ? 'bg-primary-600 text-white'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            Lista
          </button>
        </div>

        <!-- Botón Crear -->
        <button
          @click="goToCreate"
          class="btn-primary flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Nuevo Producto
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <ProductoFilters
      @update:filters="handleFiltersChange"
      class="mb-6"
    />

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!loading && productos.length === 0"
      class="text-center py-12"
    >
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
        No hay productos
      </h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Comienza creando un nuevo producto
      </p>
      <div class="mt-6">
        <button @click="goToCreate" class="btn-primary">
          Crear Producto
        </button>
      </div>
    </div>

    <!-- Grid View -->
    <div
      v-else-if="viewMode === 'grid'"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
    >
      <ProductoCard
        v-for="producto in productos"
        :key="producto.id"
        :producto="producto"
        @edit="handleEdit"
        @delete="handleDelete"
        @view="handleView"
      />
    </div>

    <!-- Table View -->
    <div v-else class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Producto
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Código
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Categoría
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Precio
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Stock
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Estado
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="producto in productos"
              :key="producto.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div
                      v-if="producto.imagen"
                      class="h-10 w-10 rounded-lg bg-gray-200 dark:bg-gray-700 overflow-hidden"
                    >
                      <img
                        :src="producto.imagen"
                        :alt="producto.nombre"
                        class="h-full w-full object-cover"
                      />
                    </div>
                    <div
                      v-else
                      class="h-10 w-10 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                    >
                      <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ producto.nombre }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ producto.codigo }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ producto.categoria?.nombre || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                ${{ Number(producto.precio_venta).toFixed(2) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'text-sm font-medium',
                    producto.stock_actual <= producto.stock_minimo
                      ? 'text-red-600 dark:text-red-400'
                      : 'text-gray-900 dark:text-white'
                  ]"
                >
                  {{ producto.stock_actual }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                    producto.estado === 'activo'
                      ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                      : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                  ]"
                >
                  {{ producto.estado }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button
                    @click="handleView(producto)"
                    class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300"
                    title="Ver detalles"
                  >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button
                    @click="handleEdit(producto)"
                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                    title="Editar"
                  >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="handleDelete(producto)"
                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                    title="Eliminar"
                  >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Paginación -->
    <div
      v-if="pagination.last_page > 1"
      class="mt-6 flex items-center justify-between"
    >
      <div class="text-sm text-gray-700 dark:text-gray-300">
        Mostrando
        <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
        a
        <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
        de
        <span class="font-medium">{{ pagination.total }}</span>
        resultados
      </div>

      <div class="flex gap-2">
        <button
          @click="handlePageChange(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          :class="[
            'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
            pagination.current_page === 1
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
              : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600'
          ]"
        >
          Anterior
        </button>

        <button
          @click="handlePageChange(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          :class="[
            'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
            pagination.current_page === pagination.last_page
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
              : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600'
          ]"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
