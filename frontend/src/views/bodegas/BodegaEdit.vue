<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Bodega
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica los datos de la bodega
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
          <!-- Sección: Información Básica -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información Básica
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
                  placeholder="Ingrese el nombre de la bodega"
                />
              </div>

              <!-- Código -->
              <div>
                <label for="codigo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Código *
                </label>
                <Input
                  id="codigo"
                  v-model="form.codigo"
                  type="text"
                  required
                  placeholder="Ingrese el código único"
                />
              </div>
            </div>
          </div>

          <!-- Sección: Información Adicional -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información Adicional
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Dirección -->
              <div class="md:col-span-2">
                <label for="direccion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Dirección
                </label>
                <textarea
                  id="direccion"
                  v-model="form.direccion"
                  rows="3"
                  placeholder="Ingrese la dirección de la bodega"
                  class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                ></textarea>
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
            </div>
          </div>

          <!-- Sección: Configuración -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Configuración
            </h3>
            <div class="flex items-center space-x-3">
              <Checkbox
                :checked="form.activo"
                @update:checked="form.activo = $event"
              />
              <label class="text-sm font-medium text-gray-900 dark:text-white">
                Bodega activa
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
              {{ loading ? 'Actualizando...' : 'Actualizar Bodega' }}
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
import { useBodegas } from '@/composables/useBodegas'
import { useEmpresas } from '@/composables/useEmpresas'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchBodega, updateBodega, loading, goToIndex } = useBodegas()
const { empresas, fetchEmpresas } = useEmpresas()

const loadingData = ref(true)

const form = ref({
  nombre: '',
  codigo: '',
  direccion: '',
  empresa_id: '',
  activo: true,
})

// Cargar datos al montar el componente
onMounted(async () => {
  try {
    await fetchEmpresas()
    const bodega = await fetchBodega(route.params.id)

    // Rellenar formulario con datos de la bodega
    form.value = {
      nombre: bodega.nombre,
      codigo: bodega.codigo,
      direccion: bodega.direccion || '',
      empresa_id: bodega.empresa_id,
      activo: bodega.activo,
    }
  } catch (err) {
    // Error ya manejado en el composable
    goToIndex()
  } finally {
    loadingData.value = false
  }
})

const handleSubmit = async () => {
  try {
    await updateBodega(route.params.id, form.value)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
