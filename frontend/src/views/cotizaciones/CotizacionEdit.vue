<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Cotización
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica la cotización {{ form.codigo }}
          </p>
        </div>
        <Button variant="outline" @click="goToIndex">
          Volver al listado
        </Button>
      </div>

      <!-- Loading -->
      <div v-if="loadingData" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <Card v-else>
        <CardContent class="pt-6">
          <form @submit.prevent="handleSubmit" class="space-y-8">

            <!-- SECCIÓN 1: Información General -->
            <div>
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                Información General
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Código (Solo lectura) -->
                <div class="space-y-2">
                  <Label for="codigo">Código</Label>
                  <Input
                    id="codigo"
                    v-model="form.codigo"
                    disabled
                    class="bg-gray-100 dark:bg-gray-800"
                  />
                </div>

                <!-- Fecha -->
                <div class="space-y-2">
                  <Label for="fecha" class="required">Fecha</Label>
                  <Input
                    id="fecha"
                    v-model="form.fecha"
                    type="date"
                    required
                  />
                </div>

                <!-- Fecha Vencimiento -->
                <div class="space-y-2">
                  <Label for="fecha_vencimiento">Fecha Vencimiento</Label>
                  <Input
                    id="fecha_vencimiento"
                    v-model="form.fecha_vencimiento"
                    type="date"
                  />
                </div>

                <!-- Empresa -->
                <div class="space-y-2">
                  <Label for="empresa_id" class="required">Empresa</Label>
                  <Select v-model="form.empresa_id" required>
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccione empresa" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="empresa in empresas"
                        :key="empresa.id"
                        :value="empresa.id"
                      >
                        {{ empresa.nombre }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Moneda -->
                <div class="space-y-2">
                  <Label for="moneda_id">Moneda</Label>
                  <Select v-model="form.moneda_id">
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccione moneda" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="moneda in monedas"
                        :key="moneda.id"
                        :value="moneda.id"
                      >
                        {{ moneda.nombre }} ({{ moneda.simbolo }})
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Impuesto -->
                <div class="space-y-2">
                  <Label for="tax_id">Impuesto</Label>
                  <Select v-model="form.tax_id" @update:modelValue="calcularTotales">
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccione impuesto" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="tax in taxes"
                        :key="tax.id"
                        :value="tax.id"
                      >
                        {{ tax.nombre }} ({{ tax.porcentaje }}%)
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Estado -->
                <div class="space-y-2">
                  <Label for="estado">Estado</Label>
                  <Select v-model="form.estado">
                    <SelectTrigger>
                      <SelectValue placeholder="Seleccione estado" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="pendiente">Pendiente</SelectItem>
                      <SelectItem value="aprobada">Aprobada</SelectItem>
                      <SelectItem value="rechazada">Rechazada</SelectItem>
                      <SelectItem value="convertida">Convertida</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Observaciones -->
                <div class="space-y-2 md:col-span-2">
                  <Label for="observaciones">Observaciones</Label>
                  <Textarea
                    id="observaciones"
                    v-model="form.observaciones"
                    rows="3"
                    placeholder="Observaciones adicionales..."
                  />
                </div>
              </div>
            </div>

            <!-- SECCIÓN 2: Detalles/Items -->
            <div>
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Detalles de la Cotización
                </h3>
                <Button type="button" @click="agregarDetalle">
                  + Agregar Item
                </Button>
              </div>

              <!-- Tabla de detalles -->
              <div class="border rounded-lg overflow-hidden">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead class="w-[300px]">Producto</TableHead>
                      <TableHead class="w-[120px]">Cantidad</TableHead>
                      <TableHead class="w-[150px]">Precio Unit.</TableHead>
                      <TableHead class="w-[150px]">Descuento</TableHead>
                      <TableHead class="w-[150px]">Subtotal</TableHead>
                      <TableHead class="w-[100px]">Acciones</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-if="form.detalles.length === 0">
                      <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                        No hay items agregados. Haz clic en "Agregar Item" para comenzar.
                      </TableCell>
                    </TableRow>
                    <TableRow
                      v-for="(detalle, index) in form.detalles"
                      :key="index"
                    >
                      <!-- Producto -->
                      <TableCell>
                        <Select
                          v-model="detalle.inventario_id"
                          @update:modelValue="(val) => onProductoChange(index, val)"
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Seleccione producto" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem
                              v-for="inventario in inventarios"
                              :key="inventario.id"
                              :value="inventario.id"
                            >
                              {{ inventario.nombre }} ({{ inventario.codigo }})
                            </SelectItem>
                          </SelectContent>
                        </Select>
                      </TableCell>

                      <!-- Cantidad -->
                      <TableCell>
                        <Input
                          v-model.number="detalle.cantidad"
                          type="number"
                          min="1"
                          @input="calcularSubtotalLinea(index)"
                        />
                      </TableCell>

                      <!-- Precio Unitario -->
                      <TableCell>
                        <Input
                          v-model.number="detalle.precio_unitario"
                          type="number"
                          step="0.01"
                          min="0"
                          @input="calcularSubtotalLinea(index)"
                        />
                      </TableCell>

                      <!-- Descuento -->
                      <TableCell>
                        <Input
                          v-model.number="detalle.descuento"
                          type="number"
                          step="0.01"
                          min="0"
                          @input="calcularSubtotalLinea(index)"
                        />
                      </TableCell>

                      <!-- Subtotal -->
                      <TableCell>
                        <div class="font-medium">
                          ${{ detalle.subtotal.toFixed(2) }}
                        </div>
                      </TableCell>

                      <!-- Acciones -->
                      <TableCell>
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          @click="eliminarDetalle(index)"
                          class="text-red-600 hover:text-red-700"
                        >
                          Eliminar
                        </Button>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>

            <!-- SECCIÓN 3: Totales -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                Resumen de Totales
              </h3>
              <div class="space-y-2 max-w-md ml-auto">
                <div class="flex justify-between text-sm">
                  <span>Subtotal:</span>
                  <span class="font-medium">${{ totales.subtotal.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span>Descuento Total:</span>
                  <span class="font-medium text-red-600">-${{ totales.descuento.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span>Impuesto ({{ porcentajeImpuesto }}%):</span>
                  <span class="font-medium">${{ totales.impuesto.toFixed(2) }}</span>
                </div>
                <div class="border-t pt-2 flex justify-between text-lg font-bold">
                  <span>Total:</span>
                  <span>${{ totales.total.toFixed(2) }}</span>
                </div>
              </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end gap-4">
              <Button type="button" variant="outline" @click="goToIndex">
                Cancelar
              </Button>
              <Button type="submit" :disabled="loading || form.detalles.length === 0">
                <span v-if="loading">Actualizando...</span>
                <span v-else">Actualizar Cotización</span>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCotizaciones } from '@/composables/useCotizaciones'
import AppLayout from '@/components/layout/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent } from '@/components/ui/card'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
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
  empresas,
  monedas,
  taxes,
  inventarios,
  fetchCotizacion,
  updateCotizacion,
  fetchEmpresas,
  fetchMonedas,
  fetchTaxes,
  fetchInventarios,
  goToIndex,
} = useCotizaciones()

const loadingData = ref(true)

// Formulario
const form = reactive({
  codigo: '',
  fecha: '',
  fecha_vencimiento: null,
  estado: 'pendiente',
  observaciones: null,
  empresa_id: null,
  moneda_id: null,
  tax_id: null,
  detalles: [],
})

// Totales calculados
const totales = reactive({
  subtotal: 0,
  descuento: 0,
  impuesto: 0,
  total: 0,
})

// Porcentaje de impuesto seleccionado
const porcentajeImpuesto = computed(() => {
  if (!form.tax_id) return 0
  const tax = taxes.value.find(t => t.id === form.tax_id)
  return tax ? tax.porcentaje : 0
})

// Agregar nuevo detalle
const agregarDetalle = () => {
  form.detalles.push({
    inventario_id: null,
    cantidad: 1,
    precio_unitario: 0,
    descuento: 0,
    subtotal: 0,
  })
}

// Eliminar detalle
const eliminarDetalle = (index) => {
  form.detalles.splice(index, 1)
  calcularTotales()
}

// Cuando cambia el producto, autocompletar precio
const onProductoChange = (index, inventarioId) => {
  const inventario = inventarios.value.find(i => i.id === inventarioId)
  if (inventario) {
    form.detalles[index].precio_unitario = parseFloat(inventario.precio_venta || 0)
    calcularSubtotalLinea(index)
  }
}

// Calcular subtotal de una línea
const calcularSubtotalLinea = (index) => {
  const detalle = form.detalles[index]
  const cantidad = parseFloat(detalle.cantidad) || 0
  const precioUnitario = parseFloat(detalle.precio_unitario) || 0
  const descuento = parseFloat(detalle.descuento) || 0

  detalle.subtotal = (cantidad * precioUnitario) - descuento
  calcularTotales()
}

// Calcular totales generales
const calcularTotales = () => {
  let subtotal = 0
  let descuentoTotal = 0

  form.detalles.forEach(detalle => {
    const cantidad = parseFloat(detalle.cantidad) || 0
    const precioUnitario = parseFloat(detalle.precio_unitario) || 0
    const descuento = parseFloat(detalle.descuento) || 0

    subtotal += cantidad * precioUnitario
    descuentoTotal += descuento
  })

  const subtotalConDescuento = subtotal - descuentoTotal
  const impuesto = (subtotalConDescuento * porcentajeImpuesto.value) / 100
  const total = subtotalConDescuento + impuesto

  totales.subtotal = subtotal
  totales.descuento = descuentoTotal
  totales.impuesto = impuesto
  totales.total = total
}

// Cargar datos de la cotización
const cargarCotizacion = async () => {
  try {
    const cotizacion = await fetchCotizacion(cotizacionId)

    // Cargar datos generales
    form.codigo = cotizacion.codigo
    form.fecha = cotizacion.fecha
    form.fecha_vencimiento = cotizacion.fecha_vencimiento
    form.estado = cotizacion.estado
    form.observaciones = cotizacion.observaciones
    form.empresa_id = cotizacion.empresa_id
    form.moneda_id = cotizacion.moneda_id
    form.tax_id = cotizacion.tax_id

    // Cargar detalles
    form.detalles = cotizacion.detalles.map(detalle => ({
      inventario_id: detalle.inventario_id,
      cantidad: detalle.cantidad,
      precio_unitario: parseFloat(detalle.precio_unitario),
      descuento: parseFloat(detalle.descuento),
      subtotal: parseFloat(detalle.subtotal),
    }))

    // Calcular totales
    calcularTotales()
  } catch (error) {
    console.error('Error al cargar cotización:', error)
  } finally {
    loadingData.value = false
  }
}

// Enviar formulario
const handleSubmit = async () => {
  if (form.detalles.length === 0) {
    alert('Debe agregar al menos un detalle a la cotización')
    return
  }

  try {
    await updateCotizacion(cotizacionId, form)
    goToIndex()
  } catch (error) {
    console.error('Error al actualizar cotización:', error)
  }
}

// Cargar catálogos y datos al montar
onMounted(async () => {
  await Promise.all([
    fetchEmpresas(),
    fetchMonedas(),
    fetchTaxes(),
    fetchInventarios(),
  ])
  await cargarCotizacion()
})
</script>

<style scoped>
.required::after {
  content: " *";
  color: #ef4444;
}
</style>
