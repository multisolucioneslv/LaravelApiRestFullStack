<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductosStore } from '@/stores/productos'
import StockBadge from '@/components/productos/StockBadge.vue'
import Swal from 'sweetalert2'

const route = useRoute()
const router = useRouter()
const productosStore = useProductosStore()

// Estado local
const producto = computed(() => productosStore.producto)
const loading = computed(() => productosStore.loading)

// Lifecycle
onMounted(async () => {
  const id = route.params.id
  try {
    await productosStore.fetchProducto(id)
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo cargar el producto',
      confirmButtonText: 'Aceptar'
    }).then(() => {
      router.push({ name: 'productos.index' })
    })
  }
})

// Computed
const precioFormateado = computed(() => {
  if (!producto.value) return '$0.00'
  return new Intl.NumberFormat('es-US', {
    style: 'currency',
    currency: 'USD'
  }).format(producto.value.precio_venta || 0)
})

const precioCompraFormateado = computed(() => {
  if (!producto.value) return '$0.00'
  return new Intl.NumberFormat('es-US', {
    style: 'currency',
    currency: 'USD'
  }).format(producto.value.precio_compra || 0)
})

const precioMayoreoFormateado = computed(() => {
  if (!producto.value) return '$0.00'
  return new Intl.NumberFormat('es-US', {
    style: 'currency',
    currency: 'USD'
  }).format(producto.value.precio_mayoreo || 0)
})

const stockStatus = computed(() => {
  if (!producto.value) return 'stock-ok'

  const stock = producto.value.stock_actual || 0
  const minimo = producto.value.stock_minimo || 0

  if (stock === 0) return 'sin-stock'
  if (stock <= minimo) return 'bajo-stock'
  return 'stock-ok'
})

const stockClass = computed(() => {
  const classes = {
    'sin-stock': 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20',
    'bajo-stock': 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
    'stock-ok': 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20'
  }
  return classes[stockStatus.value]
})

const margenGanancia = computed(() => {
  if (!producto.value || !producto.value.precio_compra || !producto.value.precio_venta) return null

  const compra = parseFloat(producto.value.precio_compra)
  const venta = parseFloat(producto.value.precio_venta)

  if (compra === 0) return null

  const margen = ((venta - compra) / compra) * 100
  return margen.toFixed(2)
})

// Methods
function goToEdit() {
  router.push({
    name: 'productos.edit',
    params: { id: producto.value.id }
  })
}

function goBack() {
  router.push({ name: 'productos.index' })
}

async function handleDelete() {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: `Se eliminará el producto "${producto.value.nombre}"`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (result.isConfirmed) {
    try {
      await productosStore.deleteProducto(producto.value.id)
      Swal.fire({
        icon: 'success',
        title: 'Eliminado',
        text: 'Producto eliminado correctamente',
        timer: 2000,
        showConfirmButton: false
      })
      router.push({ name: 'productos.index' })
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
</script>

<template>
  <div class="producto-detail-view">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Contenido -->
    <div v-else-if="producto" class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <button
            @click="goBack"
            class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white flex items-center gap-2 mb-3"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a Productos
          </button>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ producto.nombre }}
          </h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            SKU: {{ producto.codigo }}
          </p>
        </div>

        <div class="flex gap-3">
          <button
            @click="goToEdit"
            class="btn-primary flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Editar
          </button>
          <button
            @click="handleDelete"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Grid Principal -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Imagen y Estado -->
        <div class="space-y-6">
          <!-- Imagen -->
          <div class="card">
            <div class="aspect-square rounded-lg bg-gray-100 dark:bg-gray-800 overflow-hidden">
              <img
                v-if="producto.imagen"
                :src="producto.imagen"
                :alt="producto.nombre"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-24 h-24 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Estado -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Estado
            </h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Estado:</span>
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-sm font-semibold',
                    producto.estado === 'activo'
                      ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                      : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                  ]"
                >
                  {{ producto.estado }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Condición Stock:</span>
                <div class="flex items-center gap-2">
                  <StockBadge
                    :stock="producto.stock_actual"
                    :stock-minimo="producto.stock_minimo"
                  />
                  <span :class="['text-sm font-medium', stockClass.split(' ')[0]]">
                    {{ stockStatus === 'sin-stock' ? 'Sin Stock' : stockStatus === 'bajo-stock' ? 'Stock Bajo' : 'Stock OK' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Columna Derecha: Información Detallada -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información General -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Información General
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Nombre:</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">{{ producto.nombre }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Código/SKU:</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">{{ producto.codigo }}</p>
              </div>
              <div v-if="producto.codigo_barras">
                <p class="text-sm text-gray-600 dark:text-gray-400">Código de Barras:</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">{{ producto.codigo_barras }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Unidad de Medida:</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">{{ producto.unidad_medida }}</p>
              </div>
              <div v-if="producto.descripcion" class="md:col-span-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">Descripción:</p>
                <p class="text-base text-gray-900 dark:text-white">{{ producto.descripcion }}</p>
              </div>
            </div>
          </div>

          <!-- Precios -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Precios
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Precio de Compra</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ precioCompraFormateado }}</p>
              </div>
              <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Precio de Venta</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ precioFormateado }}</p>
              </div>
              <div v-if="producto.precio_mayoreo" class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Precio Mayoreo</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ precioMayoreoFormateado }}</p>
              </div>
            </div>

            <!-- Margen de Ganancia -->
            <div v-if="margenGanancia" class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Margen de Ganancia:</span>
                <span class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                  {{ margenGanancia }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Inventario -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Inventario
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 rounded-lg" :class="stockClass">
                <p class="text-sm mb-1">Stock Actual</p>
                <p class="text-3xl font-bold">
                  {{ producto.stock_actual || 0 }}
                  <span class="text-lg font-normal">{{ producto.unidad_medida }}</span>
                </p>
              </div>
              <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Stock Mínimo</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                  {{ producto.stock_minimo || 0 }}
                  <span class="text-lg font-normal text-gray-600 dark:text-gray-400">{{ producto.unidad_medida }}</span>
                </p>
              </div>
            </div>

            <!-- Alertas de Stock -->
            <div v-if="stockStatus !== 'stock-ok'" class="mt-4">
              <div
                v-if="stockStatus === 'sin-stock'"
                class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-start gap-3"
              >
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                  <p class="font-medium text-red-800 dark:text-red-200">Producto sin stock</p>
                  <p class="text-sm text-red-600 dark:text-red-400">No hay unidades disponibles para venta</p>
                </div>
              </div>
              <div
                v-else-if="stockStatus === 'bajo-stock'"
                class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg flex items-start gap-3"
              >
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                  <p class="font-medium text-yellow-800 dark:text-yellow-200">Stock bajo</p>
                  <p class="text-sm text-yellow-600 dark:text-yellow-400">El stock está por debajo del mínimo recomendado</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Categorías -->
          <div v-if="producto.categorias && producto.categorias.length > 0" class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Categorías
            </h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="categoria in producto.categorias"
                :key="categoria.id"
                class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/20 dark:text-primary-300"
              >
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ categoria.nombre }}
              </span>
            </div>
          </div>

          <!-- Fechas -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Información del Sistema
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-600 dark:text-gray-400">Fecha de Creación:</p>
                <p class="font-medium text-gray-900 dark:text-white">
                  {{ new Date(producto.created_at).toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  }) }}
                </p>
              </div>
              <div>
                <p class="text-gray-600 dark:text-gray-400">Última Actualización:</p>
                <p class="font-medium text-gray-900 dark:text-white">
                  {{ new Date(producto.updated_at).toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  }) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado vacío -->
    <div v-else class="text-center py-12">
      <p class="text-gray-500 dark:text-gray-400">No se encontró el producto</p>
      <button @click="goBack" class="btn-primary mt-4">
        Volver a Productos
      </button>
    </div>
  </div>
</template>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
