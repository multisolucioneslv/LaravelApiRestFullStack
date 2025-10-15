<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Detalle de Venta
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Visualización de venta {{ venta?.codigo }}
          </p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="goToEdit(ventaId)">
            Editar
          </Button>
          <Button variant="outline" @click="goToIndex">
            Volver al listado
          </Button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Contenido -->
      <div v-else-if="venta" class="space-y-6">
        <!-- Información General -->
        <Card>
          <CardHeader>
            <CardTitle>Información General</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div>
                <Label class="text-muted-foreground">Código</Label>
                <p class="font-mono font-semibold text-lg">{{ venta.codigo }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Fecha</Label>
                <p class="font-medium">{{ venta.fecha }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Estado</Label>
                <div>
                  <span :class="getEstadoBadgeClass(venta.estado)" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium">
                    {{ getEstadoLabel(venta.estado) }}
                  </span>
                </div>
              </div>
              <div>
                <Label class="text-muted-foreground">Tipo de Pago</Label>
                <div>
                  <span :class="getTipoPagoBadgeClass(venta.tipo_pago)" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium">
                    {{ getTipoPagoLabel(venta.tipo_pago) }}
                  </span>
                </div>
              </div>
              <div>
                <Label class="text-muted-foreground">Empresa</Label>
                <p class="font-medium">{{ venta.empresa?.nombre || '-' }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Moneda</Label>
                <p class="font-medium">
                  {{ venta.moneda ? `${venta.moneda.nombre} (${venta.moneda.simbolo})` : '-' }}
                </p>
              </div>
              <div>
                <Label class="text-muted-foreground">Impuesto</Label>
                <p class="font-medium">
                  {{ venta.tax ? `${venta.tax.nombre} (${venta.tax.porcentaje}%)` : '-' }}
                </p>
              </div>
              <div>
                <Label class="text-muted-foreground">Usuario</Label>
                <p class="font-medium">{{ venta.user?.name || '-' }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Cotización Vinculada</Label>
                <p class="font-medium">
                  {{ venta.cotizacion ? venta.cotizacion.codigo : 'Sin cotización' }}
                </p>
              </div>
              <div>
                <Label class="text-muted-foreground">Fecha Creación</Label>
                <p class="font-medium">{{ formatDateTime(venta.created_at) }}</p>
              </div>
              <div v-if="venta.observaciones" class="md:col-span-2 lg:col-span-3">
                <Label class="text-muted-foreground">Observaciones</Label>
                <p class="font-medium">{{ venta.observaciones }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Detalles de la Venta -->
        <Card>
          <CardHeader>
            <CardTitle>Detalles de Productos</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="border rounded-lg overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>#</TableHead>
                    <TableHead>Producto</TableHead>
                    <TableHead class="text-center">Cantidad</TableHead>
                    <TableHead class="text-right">Precio Unit.</TableHead>
                    <TableHead class="text-right">Descuento</TableHead>
                    <TableHead class="text-right">Subtotal</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="(detalle, index) in venta.detalles"
                    :key="detalle.id"
                  >
                    <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                    <TableCell>
                      <div class="font-medium">{{ detalle.inventario?.nombre || '-' }}</div>
                      <div class="text-sm text-muted-foreground">
                        Código: {{ detalle.inventario?.codigo || '-' }}
                      </div>
                    </TableCell>
                    <TableCell class="text-center">{{ detalle.cantidad }}</TableCell>
                    <TableCell class="text-right">
                      {{ simboloMoneda }}{{ parseFloat(detalle.precio_unitario).toFixed(2) }}
                    </TableCell>
                    <TableCell class="text-right text-red-600">
                      -{{ simboloMoneda }}{{ parseFloat(detalle.descuento).toFixed(2) }}
                    </TableCell>
                    <TableCell class="text-right font-semibold">
                      {{ simboloMoneda }}{{ parseFloat(detalle.subtotal).toFixed(2) }}
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>

        <!-- Totales -->
        <Card>
          <CardHeader>
            <CardTitle>Resumen de Totales</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3 max-w-md ml-auto">
              <div class="flex justify-between text-base">
                <span class="text-muted-foreground">Subtotal:</span>
                <span class="font-medium">{{ simboloMoneda }}{{ parseFloat(venta.subtotal).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-base">
                <span class="text-muted-foreground">Descuento Total:</span>
                <span class="font-medium text-red-600">-{{ simboloMoneda }}{{ parseFloat(venta.descuento).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-base">
                <span class="text-muted-foreground">Impuesto:</span>
                <span class="font-medium">{{ simboloMoneda }}{{ parseFloat(venta.impuesto).toFixed(2) }}</span>
              </div>
              <div class="border-t pt-3 flex justify-between text-2xl font-bold">
                <span>Total:</span>
                <span class="text-primary">{{ simboloMoneda }}{{ parseFloat(venta.total).toFixed(2) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useVentas } from '@/composables/useVentas'
import AppLayout from '@/components/layout/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const route = useRoute()
const ventaId = route.params.id

const {
  loading,
  fetchVenta,
  goToIndex,
  goToEdit,
} = useVentas()

const venta = ref(null)

// Símbolo de moneda
const simboloMoneda = computed(() => {
  return venta.value?.moneda?.simbolo || '$'
})

// Función para formatear fecha y hora
const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  const date = new Date(datetime)
  return date.toLocaleString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Función para obtener el color del badge según estado
const getEstadoBadgeClass = (estado) => {
  const classes = {
    completada: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    cancelada: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
  }
  return classes[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getEstadoLabel = (estado) => {
  const labels = {
    completada: 'Completada',
    cancelada: 'Cancelada',
    pendiente: 'Pendiente',
  }
  return labels[estado] || estado
}

// Función para obtener el color del badge según tipo de pago
const getTipoPagoBadgeClass = (tipoPago) => {
  const classes = {
    efectivo: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    tarjeta: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
    transferencia: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400',
    credito: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
  }
  return classes[tipoPago] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getTipoPagoLabel = (tipoPago) => {
  const labels = {
    efectivo: 'Efectivo',
    tarjeta: 'Tarjeta',
    transferencia: 'Transferencia',
    credito: 'Crédito',
  }
  return labels[tipoPago] || tipoPago
}

// Cargar datos de la venta
const cargarVenta = async () => {
  try {
    venta.value = await fetchVenta(ventaId)
  } catch (error) {
    console.error('Error al cargar venta:', error)
  }
}

// Cargar al montar
onMounted(() => {
  cargarVenta()
})
</script>
