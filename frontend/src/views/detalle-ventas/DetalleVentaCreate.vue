<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Nuevo Detalle de Venta
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Agrega un nuevo item a una venta
          </p>
        </div>
        <Button variant="outline" @click="goToIndex">
          Volver al listado
        </Button>
      </div>

      <!-- Formulario -->
      <Card>
        <CardContent class="pt-6">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Venta -->
            <div class="space-y-2">
              <Label for="venta_id" class="required">Venta</Label>
              <Select v-model="form.venta_id" required>
                <SelectTrigger>
                  <SelectValue placeholder="Seleccione venta" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="venta in ventas"
                    :key="venta.id"
                    :value="venta.id"
                  >
                    VTA-{{ venta.codigo || venta.id }} - {{ venta.cliente?.nombre || venta.empresa?.nombre || 'N/A' }} ({{ venta.fecha }})
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Inventario/Producto -->
            <div class="space-y-2">
              <Label for="inventario_id" class="required">Producto/Inventario</Label>
              <Select
                v-model="form.inventario_id"
                required
                @update:modelValue="onProductoChange"
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
                    {{ inventario.nombre }} ({{ inventario.codigo }}) - Stock: {{ inventario.stock || 0 }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Cantidad -->
            <div class="space-y-2">
              <Label for="cantidad" class="required">Cantidad</Label>
              <Input
                id="cantidad"
                v-model.number="form.cantidad"
                type="number"
                min="1"
                step="1"
                required
                placeholder="Ingrese cantidad"
              />
            </div>

            <!-- Precio Unitario -->
            <div class="space-y-2">
              <Label for="precio_unitario" class="required">Precio Unitario</Label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                <Input
                  id="precio_unitario"
                  v-model.number="form.precio_unitario"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  placeholder="0.00"
                  class="pl-7"
                />
              </div>
            </div>

            <!-- Descuento -->
            <div class="space-y-2">
              <Label for="descuento">Descuento</Label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                <Input
                  id="descuento"
                  v-model.number="form.descuento"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                  class="pl-7"
                />
              </div>
            </div>

            <!-- Subtotal Calculado -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
              <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                  Subtotal:
                </span>
                <span class="text-2xl font-bold text-primary">
                  ${{ subtotal.toFixed(2) }}
                </span>
              </div>
              <p class="text-xs text-gray-500 mt-2">
                Cálculo: ({{ form.cantidad }} × ${{ form.precio_unitario.toFixed(2) }}) - ${{ form.descuento.toFixed(2) }}
              </p>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end gap-4">
              <Button type="button" variant="outline" @click="goToIndex">
                Cancelar
              </Button>
              <Button type="submit" :disabled="loading">
                <span v-if="loading">Creando...</span>
                <span v-else>Crear Detalle</span>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed, onMounted, watch } from 'vue'
import { useDetalleVentas } from '@/composables/useDetalleVentas'
import AppLayout from '@/components/layout/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent } from '@/components/ui/card'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const {
  loading,
  ventas,
  inventarios,
  createDetalleVenta,
  fetchVentas,
  fetchInventarios,
  goToIndex,
} = useDetalleVentas()

// Formulario
const form = reactive({
  venta_id: null,
  inventario_id: null,
  cantidad: 1,
  precio_unitario: 0,
  descuento: 0,
})

// Subtotal calculado automáticamente
const subtotal = computed(() => {
  const cantidad = parseFloat(form.cantidad) || 0
  const precioUnitario = parseFloat(form.precio_unitario) || 0
  const descuento = parseFloat(form.descuento) || 0

  return (cantidad * precioUnitario) - descuento
})

// Watch para recalcular cuando cambien los valores
watch(
  () => [form.cantidad, form.precio_unitario, form.descuento],
  () => {
    // El computed se actualiza automáticamente
  }
)

// Cuando cambia el producto, autocompletar precio
const onProductoChange = () => {
  const inventario = inventarios.value.find(i => i.id === form.inventario_id)
  if (inventario) {
    form.precio_unitario = parseFloat(inventario.precio_venta || 0)
  }
}

// Enviar formulario
const handleSubmit = async () => {
  // Validación adicional
  if (subtotal.value < 0) {
    alert('El subtotal no puede ser negativo. Verifica el descuento.')
    return
  }

  try {
    // Preparar datos con el subtotal calculado
    const dataToSend = {
      ...form,
      subtotal: subtotal.value
    }

    await createDetalleVenta(dataToSend)
    goToIndex()
  } catch (error) {
    console.error('Error al crear detalle de venta:', error)
  }
}

// Cargar catálogos al montar
onMounted(async () => {
  await Promise.all([
    fetchVentas(),
    fetchInventarios(),
  ])
})
</script>

<style scoped>
.required::after {
  content: " *";
  color: #ef4444;
}
</style>
