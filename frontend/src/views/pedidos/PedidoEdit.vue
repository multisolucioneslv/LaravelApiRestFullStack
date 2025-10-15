<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Pedido
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Actualiza la información del pedido
          </p>
        </div>
        <Button
          type="button"
          variant="outline"
          @click="handleCancel"
        >
          Volver
        </Button>
      </div>

      <!-- Loading -->
      <div v-if="loadingData" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <form v-else @submit.prevent="handleSubmit" class="card">
        <div class="space-y-6">
          <!-- Sección: Información del Pedido -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información del Pedido
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Código (readonly) -->
              <div>
                <label for="codigo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Código
                </label>
                <Input
                  id="codigo"
                  v-model="form.codigo"
                  type="text"
                  readonly
                  class="bg-gray-100 dark:bg-gray-800 cursor-not-allowed"
                />
              </div>

              <!-- Fecha -->
              <div>
                <label for="fecha" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Fecha *
                </label>
                <Input
                  id="fecha"
                  v-model="form.fecha"
                  type="date"
                  required
                />
              </div>

              <!-- Fecha de Entrega -->
              <div>
                <label for="fecha_entrega" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Fecha de Entrega
                </label>
                <Input
                  id="fecha_entrega"
                  v-model="form.fecha_entrega"
                  type="date"
                />
              </div>

              <!-- Empresa -->
              <div>
                <label for="empresa_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Empresa *
                </label>
                <select
                  id="empresa_id"
                  v-model="form.empresa_id"
                  required
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione una empresa</option>
                  <option
                    v-for="empresa in empresas"
                    :key="empresa.id"
                    :value="empresa.id"
                  >
                    {{ empresa.nombre }}
                  </option>
                </select>
              </div>

              <!-- Tipo -->
              <div>
                <label for="tipo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Tipo *
                </label>
                <select
                  id="tipo"
                  v-model="form.tipo"
                  required
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione un tipo</option>
                  <option value="compra">Compra</option>
                  <option value="venta">Venta</option>
                  <option value="servicio">Servicio</option>
                </select>
              </div>

              <!-- Estado -->
              <div>
                <label for="estado" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Estado *
                </label>
                <select
                  id="estado"
                  v-model="form.estado"
                  required
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="pendiente">Pendiente</option>
                  <option value="proceso">En Proceso</option>
                  <option value="completado">Completado</option>
                  <option value="cancelado">Cancelado</option>
                </select>
              </div>

              <!-- Total -->
              <div>
                <label for="total" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Total *
                </label>
                <Input
                  id="total"
                  v-model="form.total"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  placeholder="0.00"
                />
              </div>

              <!-- Observaciones -->
              <div class="md:col-span-2">
                <label for="observaciones" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Observaciones
                </label>
                <textarea
                  id="observaciones"
                  v-model="form.observaciones"
                  rows="3"
                  placeholder="Notas adicionales del pedido..."
                  class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <Button
              type="button"
              variant="outline"
              @click="handleCancel"
              :disabled="loading"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              :disabled="loading"
            >
              {{ loading ? 'Guardando...' : 'Guardar Cambios' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { usePedidos } from '@/composables/usePedidos'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchPedido, updatePedido, loading, goToIndex, empresas, fetchEmpresas } = usePedidos()

const loadingData = ref(true)

const form = ref({
  codigo: '',
  fecha: '',
  fecha_entrega: '',
  empresa_id: '',
  tipo: '',
  estado: 'pendiente',
  total: '',
  observaciones: '',
})

// Cargar datos al montar
onMounted(async () => {
  loadingData.value = true

  // Cargar empresas
  await fetchEmpresas()

  // Cargar datos del pedido
  const pedidoId = route.params.id
  const pedidoData = await fetchPedido(pedidoId)

  if (pedidoData) {
    // Formatear fechas para inputs tipo date
    const formatDateForInput = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    }

    form.value = {
      codigo: pedidoData.codigo || '',
      fecha: formatDateForInput(pedidoData.fecha),
      fecha_entrega: formatDateForInput(pedidoData.fecha_entrega),
      empresa_id: pedidoData.empresa_id || '',
      tipo: pedidoData.tipo || '',
      estado: pedidoData.estado || 'pendiente',
      total: pedidoData.total || '',
      observaciones: pedidoData.observaciones || '',
    }
  }

  loadingData.value = false
})

const handleSubmit = async () => {
  const pedidoId = route.params.id
  const success = await updatePedido(pedidoId, form.value)
  if (success) {
    goToIndex()
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
