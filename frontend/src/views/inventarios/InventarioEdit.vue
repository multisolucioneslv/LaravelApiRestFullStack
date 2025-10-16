<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Inventario
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica los datos del producto en el inventario
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
        <div class="space-y-8">
          <!-- Secci�n: Informaci�n B�sica -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Informaci�n B�sica
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Nombre -->
              <div>
                <label for="nombre" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Nombre *
                </label>
                <Input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  placeholder="Ingrese el nombre del producto"
                />
              </div>

              <!-- C�digo -->
              <div>
                <label for="codigo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  C�digo *
                </label>
                <Input
                  id="codigo"
                  v-model="form.codigo"
                  type="text"
                  required
                  placeholder="SKU o c�digo del producto"
                />
              </div>

              <!-- Descripci�n -->
              <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Descripci�n
                </label>
                <textarea
                  id="descripcion"
                  v-model="form.descripcion"
                  rows="3"
                  class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  placeholder="Descripci�n detallada del producto (opcional)"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Secci�n: Ubicaci�n -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Ubicaci�n
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Bodega -->
              <div>
                <label for="bodega_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Bodega *
                </label>
                <select
                  id="bodega_id"
                  v-model="form.bodega_id"
                  required
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione...</option>
                  <option
                    v-for="bodega in bodegas"
                    :key="bodega.id"
                    :value="bodega.id"
                  >
                    {{ bodega.nombre }}
                  </option>
                </select>
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
                  <option value="">Seleccione...</option>
                  <option
                    v-for="empresa in empresas"
                    :key="empresa.id"
                    :value="empresa.id"
                  >
                    {{ empresa.nombre }}
                  </option>
                </select>
              </div>

              <!-- Galer�a -->
              <div>
                <label for="galeria_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Galer�a
                </label>
                <select
                  id="galeria_id"
                  v-model="form.galeria_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Ninguna</option>
                  <option
                    v-for="galeria in galerias"
                    :key="galeria.id"
                    :value="galeria.id"
                  >
                    {{ galeria.nombre }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Secci�n: Stock -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Control de Stock
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Cantidad -->
              <div>
                <label for="cantidad" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Cantidad
                </label>
                <Input
                  id="cantidad"
                  v-model.number="form.cantidad"
                  type="number"
                  min="0"
                  placeholder="0"
                />
              </div>

              <!-- M�nimo -->
              <div>
                <label for="minimo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Stock M�nimo
                </label>
                <Input
                  id="minimo"
                  v-model.number="form.minimo"
                  type="number"
                  min="0"
                  placeholder="0"
                />
              </div>

              <!-- M�ximo -->
              <div>
                <label for="maximo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Stock M�ximo
                </label>
                <Input
                  id="maximo"
                  v-model.number="form.maximo"
                  type="number"
                  min="0"
                  placeholder="0"
                />
              </div>
            </div>
          </div>

          <!-- Secci�n: Precios -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Precios
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Precio Compra -->
              <div>
                <label for="precio_compra" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Precio de Compra
                </label>
                <Input
                  id="precio_compra"
                  v-model.number="form.precio_compra"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                />
              </div>

              <!-- Precio Venta -->
              <div>
                <label for="precio_venta" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Precio de Venta
                </label>
                <Input
                  id="precio_venta"
                  v-model.number="form.precio_venta"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                />
              </div>
            </div>
          </div>

          <!-- Secci�n: Configuraci�n -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Configuraci�n
            </h3>
            <div class="flex items-center space-x-3">
              <Checkbox
                :checked="form.activo"
                @update:checked="form.activo = $event"
              />
              <label class="text-sm font-medium text-gray-900 dark:text-white">
                Producto activo
              </label>
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
              {{ loading ? 'Actualizando...' : 'Actualizar Inventario' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useInventarios } from '@/composables/useInventarios'
import { useEmpresas } from '@/composables/useEmpresas'
import { useBodegas } from '@/composables/useBodegas'
import { useGalerias } from '@/composables/useGalerias'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const authStore = useAuthStore()

const { fetchInventario, updateInventario, loading, goToIndex } = useInventarios()
const { empresas, fetchEmpresas } = useEmpresas()
const { bodegas, fetchBodegas } = useBodegas()
const { galerias, fetchGalerias } = useGalerias()

const loadingData = ref(true)

const form = ref({
  nombre: '',
  codigo: '',
  descripcion: '',
  galeria_id: '',
  bodega_id: '',
  empresa_id: '',
  cantidad: 0,
  minimo: 0,
  maximo: 0,
  precio_compra: 0.00,
  precio_venta: 0.00,
  activo: true,
})

// Cargar datos al montar el componente SOLO si hay sesi�n activa
onMounted(async () => {
  if (!authStore.isAuthenticated) {
    loadingData.value = false
    return
  }

  try {
    loadingData.value = true

    // Cargar cat�logos y datos del inventario en paralelo
    const [inventarioData] = await Promise.all([
      fetchInventario(route.params.id),
      fetchEmpresas(),
      fetchBodegas(),
      fetchGalerias(),
    ])

    // Llenar el formulario con los datos obtenidos
    form.value = {
      nombre: inventarioData.nombre,
      codigo: inventarioData.codigo,
      descripcion: inventarioData.descripcion || '',
      galeria_id: inventarioData.galeria_id || '',
      bodega_id: inventarioData.bodega_id,
      empresa_id: inventarioData.empresa_id,
      cantidad: inventarioData.cantidad,
      minimo: inventarioData.minimo,
      maximo: inventarioData.maximo,
      precio_compra: parseFloat(inventarioData.precio_compra),
      precio_venta: parseFloat(inventarioData.precio_venta),
      activo: inventarioData.activo,
    }
  } catch (err) {
    // Error manejado por el composable
  } finally {
    loadingData.value = false
  }
})

// Limpiar cuando se desmonta el componente
onUnmounted(() => {
  empresas.value = []
  bodegas.value = []
  galerias.value = []
})

// Detener carga si se cierra sesi�n mientras est� en la vista
watch(() => authStore.isAuthenticated, (isAuth) => {
  if (!isAuth) {
    empresas.value = []
    bodegas.value = []
    galerias.value = []
  }
})

const handleSubmit = async () => {
  try {
    const data = {
      nombre: form.value.nombre,
      codigo: form.value.codigo,
      descripcion: form.value.descripcion || null,
      galeria_id: form.value.galeria_id || null,
      bodega_id: form.value.bodega_id,
      empresa_id: form.value.empresa_id,
      cantidad: form.value.cantidad,
      minimo: form.value.minimo,
      maximo: form.value.maximo,
      precio_compra: form.value.precio_compra,
      precio_venta: form.value.precio_venta,
      activo: form.value.activo,
    }

    await updateInventario(route.params.id, data)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
