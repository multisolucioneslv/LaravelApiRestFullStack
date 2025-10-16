<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Detalle de Cotización
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Visualización de cotización {{ cotizacion?.codigo }}
          </p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="goToEdit(cotizacionId)">
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
      <div v-else-if="cotizacion" class="space-y-6">
        <!-- Información General -->
        <Card>
          <CardHeader>
            <CardTitle>Información General</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div>
                <Label class="text-muted-foreground">Código</Label>
                <p class="font-mono font-semibold text-lg">{{ cotizacion.codigo }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Fecha</Label>
                <p class="font-medium">{{ cotizacion.fecha }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Fecha Vencimiento</Label>
                <p class="font-medium">{{ cotizacion.fecha_vencimiento || '-' }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Estado</Label>
                <div>
                  <span :class="getEstadoBadgeClass(cotizacion.estado)" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium">
                    {{ getEstadoLabel(cotizacion.estado) }}
                  </span>
                </div>
              </div>
              <div>
                <Label class="text-muted-foreground">Empresa</Label>
                <p class="font-medium">{{ cotizacion.empresa?.nombre || '-' }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Moneda</Label>
                <p class="font-medium">
                  {{ cotizacion.moneda ? `${cotizacion.moneda.nombre} (${cotizacion.moneda.simbolo})` : '-' }}
                </p>
              </div>
              <div>
                <Label class="text-muted-foreground">Impuesto</Label>
                <p class="font-medium">
                  {{ cotizacion.tax ? `${cotizacion.tax.nombre} (${cotizacion.tax.porcentaje}%)` : '-' }}
                </p>
              </div>
              <div>
                <Label class="text-muted-foreground">Usuario</Label>
                <p class="font-medium">{{ cotizacion.user?.name || '-' }}</p>
              </div>
              <div>
                <Label class="text-muted-foreground">Fecha Creación</Label>
                <p class="font-medium">{{ formatDateTime(cotizacion.created_at) }}</p>
              </div>
              <div v-if="cotizacion.observaciones" class="md:col-span-2 lg:col-span-3">
                <Label class="text-muted-foreground">Observaciones</Label>
                <p class="font-medium">{{ cotizacion.observaciones }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Detalles de la Cotización -->
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
                    v-for="(detalle, index) in cotizacion.detalles"
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
                <span class="font-medium">{{ simboloMoneda }}{{ parseFloat(cotizacion.subtotal).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-base">
                <span class="text-muted-foreground">Descuento Total:</span>
                <span class="font-medium text-red-600">-{{ simboloMoneda }}{{ parseFloat(cotizacion.descuento).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-base">
                <span class="text-muted-foreground">Impuesto:</span>
                <span class="font-medium">{{ simboloMoneda }}{{ parseFloat(cotizacion.impuesto).toFixed(2) }}</span>
              </div>
              <div class="border-t pt-3 flex justify-between text-2xl font-bold">
                <span>Total:</span>
                <span class="text-primary">{{ simboloMoneda }}{{ parseFloat(cotizacion.total).toFixed(2) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCotizaciones } from '@/composables/useCotizaciones'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/layout/AppLayout.vue'

const router = useRouter()
const authStore = useAuthStore()
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
const cotizacionId = route.params.id

const {
  loading,
  fetchCotizacion,
  goToIndex,
  goToEdit,
} = useCotizaciones()

const cotizacion = ref(null)

// Símbolo de moneda
const simboloMoneda = computed(() => {
  return cotizacion.value?.moneda?.simbolo || '$'
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
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    aprobada: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    rechazada: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    convertida: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
  }
  return classes[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getEstadoLabel = (estado) => {
  const labels = {
    pendiente: 'Pendiente',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
    convertida: 'Convertida',
  }
  return labels[estado] || estado
}

// Cargar datos de la cotización
const cargarCotizacion = async () => {
  try {
    cotizacion.value = await fetchCotizacion(cotizacionId)
  } catch (error) {
    console.error('Error al cargar cotización:', error)
  }
}

// Verificación de sesión y carga de datos
let sessionCheckInterval = null

onMounted(() => {
  // Verificar sesión antes de cargar datos
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }

  cargarCotizacion()

  // Configurar verificación periódica de sesión (cada 30 segundos)
  sessionCheckInterval = setInterval(() => {
    if (!authStore.isAuthenticated) {
      router.push('/login')
    }
  }, 30000)
})

// Limpiar intervalo al desmontar
onUnmounted(() => {
  if (sessionCheckInterval) {
    clearInterval(sessionCheckInterval)
  }
})

// Watch para cambios en autenticación
watch(
  () => authStore.isAuthenticated,
  (newValue) => {
    if (!newValue) {
      router.push('/login')
    }
  }
)
</script>
