<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Detalles del Producto
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Información completa del producto
          </p>
        </div>
        <div class="flex gap-2">
          <Button
            variant="outline"
            @click="handleEdit"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
              <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
              <path d="m15 5 4 4"/>
            </svg>
            Editar
          </Button>
          <Button
            variant="outline"
            @click="handleBack"
          >
            Volver
          </Button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Contenido del Producto -->
      <div v-else-if="producto" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Imagen y Acciones -->
        <div class="space-y-6">
          <!-- Imagen del Producto -->
          <div class="card">
            <div class="aspect-square w-full bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden">
              <img
                v-if="producto.imagen"
                :src="producto.imagen"
                :alt="producto.nombre"
                class="w-full h-full object-cover"
                @error="handleImageError"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                  <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                  <circle cx="9" cy="9" r="2"/>
                  <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Estado y Badges -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Estado
            </h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Activo:</span>
                <Badge :variant="producto.activo ? 'default' : 'secondary'" :class="producto.activo ? 'bg-green-600' : 'bg-gray-400'">
                  {{ producto.activo ? 'Activo' : 'Inactivo' }}
                </Badge>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Stock:</span>
                <Badge :variant="esBajoStock ? 'destructive' : 'default'" :class="!esBajoStock && 'bg-green-600'">
                  {{ esBajoStock ? 'Bajo Stock' : 'Disponible' }}
                </Badge>
              </div>
            </div>
          </div>

          <!-- Acciones Rápidas -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Acciones Rápidas
            </h3>
            <div class="space-y-2">
              <Button class="w-full" variant="outline" @click="openStockModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                  <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                  <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                  <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                Actualizar Stock
              </Button>
              <Button class="w-full" variant="destructive" @click="handleDelete">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                  <path d="M3 6h18"/>
                  <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                  <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                </svg>
                Eliminar Producto
              </Button>
            </div>
          </div>
        </div>

        <!-- Columna Derecha: Información Detallada -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información Básica -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información Básica
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Nombre</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white font-semibold">{{ producto.nombre }}</dd>
              </div>
              <div v-if="producto.sku">
                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">SKU</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white font-mono">{{ producto.sku }}</dd>
              </div>
              <div v-if="producto.codigo_barras">
                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Código de Barras</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white font-mono">{{ producto.codigo_barras }}</dd>
              </div>
              <div v-if="producto.unidad_medida">
                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Unidad de Medida</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ producto.unidad_medida }}</dd>
              </div>
              <div v-if="producto.descripcion" class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Descripción</dt>
                <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ producto.descripcion }}</dd>
              </div>
            </dl>
          </div>

          <!-- Precios -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Precios
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg">
                <dt class="text-sm font-medium text-blue-600 dark:text-blue-400">Precio de Compra</dt>
                <dd class="mt-2 text-2xl font-bold text-blue-900 dark:text-blue-100">
                  ${{ parseFloat(producto.precio_compra || 0).toFixed(2) }}
                </dd>
              </div>
              <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg">
                <dt class="text-sm font-medium text-green-600 dark:text-green-400">Precio de Venta</dt>
                <dd class="mt-2 text-2xl font-bold text-green-900 dark:text-green-100">
                  ${{ parseFloat(producto.precio_venta || 0).toFixed(2) }}
                </dd>
              </div>
              <div v-if="producto.precio_mayoreo" class="bg-purple-50 dark:bg-purple-900/30 p-4 rounded-lg">
                <dt class="text-sm font-medium text-purple-600 dark:text-purple-400">Precio Mayoreo</dt>
                <dd class="mt-2 text-2xl font-bold text-purple-900 dark:text-purple-100">
                  ${{ parseFloat(producto.precio_mayoreo || 0).toFixed(2) }}
                </dd>
              </div>
            </dl>
            <div v-if="margenGanancia !== null" class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Margen de Ganancia:</span>
                <span class="text-xl font-bold" :class="margenGanancia > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ margenGanancia.toFixed(2) }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Inventario -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Inventario
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Stock Actual</dt>
                <dd class="mt-2 flex items-center gap-2">
                  <span class="text-3xl font-bold" :class="esBajoStock ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">
                    {{ producto.stock_actual || 0 }}
                  </span>
                  <span v-if="esBajoStock" class="text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                      <path d="M12 9v4"/>
                      <path d="M12 17h.01"/>
                    </svg>
                  </span>
                </dd>
              </div>
              <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Stock Mínimo</dt>
                <dd class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                  {{ producto.stock_minimo || 0 }}
                </dd>
              </div>
            </div>
          </div>

          <!-- Categorías -->
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Categorías
            </h3>
            <div v-if="producto.categorias && producto.categorias.length > 0" class="flex flex-wrap gap-2">
              <Badge
                v-for="categoria in producto.categorias"
                :key="categoria.id"
                variant="outline"
                :style="{
                  backgroundColor: `${categoria.color}20`,
                  borderColor: categoria.color,
                  color: categoria.color
                }"
                class="px-3 py-1.5"
              >
                <span class="mr-1">{{ categoria.icono || '📦' }}</span>
                {{ categoria.nombre }}
              </Badge>
            </div>
            <p v-else class="text-gray-500 dark:text-gray-400">
              Sin categorías asignadas
            </p>
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else class="text-center text-red-600 dark:text-red-400">
        No se pudo cargar el producto
      </div>
    </div>

    <!-- Modal de actualizar stock -->
    <UpdateStockModal
      :show="showStockModal"
      :producto="producto"
      @close="closeStockModal"
      @update="handleStockUpdate"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductos } from '@/composables/useProductos'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import AppLayout from '@/components/layout/AppLayout.vue'
import UpdateStockModal from '@/components/productos/UpdateStockModal.vue'

const route = useRoute()
const router = useRouter()
const { fetchProducto, updateStock, deleteProducto, loading, goToIndex, goToEdit } = useProductos()

const producto = ref(null)
const showStockModal = ref(false)

// Computed
const esBajoStock = computed(() => {
  if (!producto.value) return false
  return (producto.value.stock_actual || 0) <= (producto.value.stock_minimo || 0)
})

const margenGanancia = computed(() => {
  if (!producto.value) return null
  const compra = parseFloat(producto.value.precio_compra || 0)
  const venta = parseFloat(producto.value.precio_venta || 0)
  if (compra > 0 && venta > 0) {
    return ((venta - compra) / compra) * 100
  }
  return null
})

// Métodos
const loadProducto = async () => {
  try {
    producto.value = await fetchProducto(route.params.id)
  } catch (err) {
    // Error ya manejado en el composable
  }
}

const handleImageError = (e) => {
  e.target.src = 'https://via.placeholder.com/400?text=Imagen+No+Disponible'
}

const handleEdit = () => {
  goToEdit(route.params.id)
}

const handleBack = () => {
  goToIndex()
}

const handleDelete = async () => {
  const deleted = await deleteProducto(route.params.id)
  if (deleted) {
    goToIndex()
  }
}

const openStockModal = () => {
  showStockModal.value = true
}

const closeStockModal = () => {
  showStockModal.value = false
}

const handleStockUpdate = async (data) => {
  await updateStock(route.params.id, data.cantidad, data.tipo)
  closeStockModal()
  // Recargar producto para ver el stock actualizado
  await loadProducto()
}

// Lifecycle
onMounted(() => {
  loadProducto()
})
</script>
