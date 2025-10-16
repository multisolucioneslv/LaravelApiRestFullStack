<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Crear Pedido
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Completa el formulario para crear un nuevo pedido
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

      <!-- Formulario -->
      <form @submit.prevent="handleSubmit" class="card">
        <div class="space-y-6">
          <!-- Sección: Información del Pedido -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información del Pedido
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
              {{ loading ? 'Guardando...' : 'Guardar Pedido' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { usePedidos } from '@/composables/usePedidos'
import { useAuthStore } from '@/stores/auth'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()

const { createPedido, loading, goToIndex, empresas, fetchEmpresas } = usePedidos()

// Obtener fecha de hoy en formato YYYY-MM-DD
const getTodayDate = () => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const form = ref({
  fecha: getTodayDate(),
  fecha_entrega: '',
  empresa_id: '',
  tipo: '',
  estado: 'pendiente',
  total: '',
  observaciones: '',
})

// ==========================================
// SEGURIDAD: CARGA LAZY
// ==========================================

// Cargar empresas SOLO si hay sesión activa
onMounted(async () => {
  if (authStore.isAuthenticated) {
    console.log('[SECURITY] PedidoCreate: Inicializando con sesión activa')
    await fetchEmpresas()
  } else {
    console.warn('[SECURITY] PedidoCreate: No se puede cargar sin sesión')
  }
})

// Limpiar datos cuando se sale de la vista
onUnmounted(() => {
  console.log('[SECURITY] PedidoCreate: Limpiando datos al desmontar componente')
  empresas.value = []
  form.value = {
    fecha: getTodayDate(),
    fecha_entrega: '',
    empresa_id: '',
    tipo: '',
    estado: 'pendiente',
    total: '',
    observaciones: '',
  }
})

// Vigilar cambios en autenticación
watch(() => authStore.isAuthenticated, async (isAuth) => {
  if (!isAuth) {
    console.warn('[SECURITY] PedidoCreate: Sesión cerrada, limpiando datos')
    empresas.value = []
    form.value = {
      fecha: getTodayDate(),
      fecha_entrega: '',
      empresa_id: '',
      tipo: '',
      estado: 'pendiente',
      total: '',
      observaciones: '',
    }
  } else {
    console.log('[SECURITY] PedidoCreate: Sesión iniciada, cargando datos')
    await fetchEmpresas()
  }
})

const handleSubmit = async () => {
  const success = await createPedido(form.value)
  if (success) {
    goToIndex()
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
