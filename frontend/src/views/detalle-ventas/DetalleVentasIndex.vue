<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Detalles de Ventas
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra los detalles de las ventas
        </p>
      </div>

      <!-- Filtros y Acciones -->
      <Card>
        <CardContent class="pt-6">
          <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
            <!-- Búsqueda -->
            <div class="w-full md:w-96">
              <Input
                v-model="searchTerm"
                placeholder="Buscar por venta o producto..."
                @input="handleSearchInput"
              />
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-2">
              <Button
                v-if="selectedIds.length > 0"
                variant="destructive"
                @click="handleBulkDelete"
              >
                Eliminar seleccionados ({{ selectedIds.length }})
              </Button>
              <Button @click="handleCreate">
                + Crear Detalle
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Tabla de Detalles -->
      <Card v-else>
        <CardContent class="pt-6">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[50px]">
                    <Checkbox
                      :checked="selectAll"
                      @update:checked="toggleSelectAll"
                    />
                  </TableHead>
                  <TableHead class="w-[80px]">ID</TableHead>
                  <TableHead>Venta</TableHead>
                  <TableHead>Producto/Inventario</TableHead>
                  <TableHead class="text-right">Cantidad</TableHead>
                  <TableHead class="text-right">Precio Unit.</TableHead>
                  <TableHead class="text-right">Descuento</TableHead>
                  <TableHead class="text-right">Subtotal</TableHead>
                  <TableHead class="text-center">Acciones</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <!-- Sin registros -->
                <TableRow v-if="!hasDetalleVentas">
                  <TableCell colspan="9" class="text-center text-muted-foreground py-8">
                    No hay detalles de ventas registrados
                  </TableCell>
                </TableRow>

                <!-- Listado de detalles -->
                <TableRow
                  v-for="detalle in detalleVentas"
                  :key="detalle.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                  <!-- Checkbox -->
                  <TableCell>
                    <Checkbox
                      :checked="selectedIds.includes(detalle.id)"
                      @update:checked="(checked) => toggleSelect(detalle.id, checked)"
                    />
                  </TableCell>

                  <!-- ID -->
                  <TableCell class="font-medium">
                    {{ detalle.id }}
                  </TableCell>

                  <!-- Venta -->
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium">VTA-{{ detalle.venta?.codigo || detalle.venta?.id }}</span>
                      <span class="text-xs text-gray-500">
                        {{ detalle.venta?.fecha || 'N/A' }}
                      </span>
                    </div>
                  </TableCell>

                  <!-- Producto/Inventario -->
                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium">{{ detalle.inventario?.nombre || 'N/A' }}</span>
                      <span class="text-xs text-gray-500">
                        {{ detalle.inventario?.codigo || '' }}
                      </span>
                    </div>
                  </TableCell>

                  <!-- Cantidad -->
                  <TableCell class="text-right">
                    {{ detalle.cantidad }}
                  </TableCell>

                  <!-- Precio Unitario -->
                  <TableCell class="text-right">
                    ${{ parseFloat(detalle.precio_unitario).toFixed(2) }}
                  </TableCell>

                  <!-- Descuento -->
                  <TableCell class="text-right">
                    <span class="text-red-600">
                      -${{ parseFloat(detalle.descuento || 0).toFixed(2) }}
                    </span>
                  </TableCell>

                  <!-- Subtotal -->
                  <TableCell class="text-right font-semibold">
                    ${{ parseFloat(detalle.subtotal).toFixed(2) }}
                  </TableCell>

                  <!-- Acciones -->
                  <TableCell>
                    <div class="flex justify-center gap-2">
                      <Button
                        size="sm"
                        variant="ghost"
                        @click="handleEdit(detalle.id)"
                      >
                        Editar
                      </Button>
                      <Button
                        size="sm"
                        variant="ghost"
                        class="text-red-600 hover:text-red-700"
                        @click="handleDelete(detalle.id)"
                      >
                        Eliminar
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Paginación -->
          <div
            v-if="hasDetalleVentas"
            class="flex items-center justify-between mt-6 pt-6 border-t"
          >
            <div class="text-sm text-gray-600 dark:text-gray-400">
              Mostrando {{ detalleVentas.length }} de {{ total }} registros
            </div>

            <div class="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                :disabled="!hasPrevPage"
                @click="handlePreviousPage"
              >
                Anterior
              </Button>
              <Button
                variant="outline"
                size="sm"
                :disabled="!hasNextPage"
                @click="handleNextPage"
              >
                Siguiente
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useDetalleVentas } from '@/composables/useDetalleVentas'
import AppLayout from '@/components/layout/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import { Card, CardContent } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const route = useRoute()

const {
  detalleVentas,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  hasDetalleVentas,
  fetchDetalleVentas,
  deleteDetalleVenta,
  deleteDetalleVentasBulk,
  searchDetalleVentas,
  changePage,
  goToCreate,
  goToEdit,
} = useDetalleVentas()

// Búsqueda
const searchTerm = ref('')
let searchTimeout = null

const handleSearchInput = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchDetalleVentas(searchTerm.value)
  }, 500)
}

// Selección múltiple
const selectedIds = ref([])

const selectAll = computed(() => {
  return detalleVentas.value.length > 0 &&
    selectedIds.value.length === detalleVentas.value.length
})

const toggleSelectAll = (checked) => {
  if (checked) {
    selectedIds.value = detalleVentas.value.map(d => d.id)
  } else {
    selectedIds.value = []
  }
}

const toggleSelect = (id, checked) => {
  if (checked) {
    selectedIds.value.push(id)
  } else {
    selectedIds.value = selectedIds.value.filter(selectedId => selectedId !== id)
  }
}

// Cargar detalles al montar
onMounted(() => {
  // Si hay un venta_id en query params, filtrar por esa venta
  const ventaId = route.query.venta_id
  fetchDetalleVentas(1, ventaId)
})

// Manejadores
const handleCreate = () => {
  goToCreate()
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteDetalleVenta(id)
  if (deleted) {
    // Limpiar selección si el item eliminado estaba seleccionado
    selectedIds.value = selectedIds.value.filter(selectedId => selectedId !== id)
  }
}

const handleBulkDelete = async () => {
  const deleted = await deleteDetalleVentasBulk(selectedIds.value)
  if (deleted) {
    selectedIds.value = []
  }
}

const handlePreviousPage = () => {
  changePage(currentPage.value - 1)
  selectedIds.value = [] // Limpiar selección al cambiar de página
}

const handleNextPage = () => {
  changePage(currentPage.value + 1)
  selectedIds.value = [] // Limpiar selección al cambiar de página
}
</script>
