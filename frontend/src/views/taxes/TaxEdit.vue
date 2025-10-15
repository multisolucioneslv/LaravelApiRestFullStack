<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Impuesto
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica los datos del impuesto
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

      <!-- Loading inicial -->
      <div v-if="loadingTax" class="flex justify-center items-center h-64">
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
                  Nombre del Impuesto *
                </label>
                <Input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  placeholder="Ej: IVA, ISR, ITBIS"
                />
              </div>

              <!-- Porcentaje -->
              <div>
                <label for="porcentaje" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Porcentaje (%) *
                </label>
                <Input
                  id="porcentaje"
                  v-model="form.porcentaje"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  required
                  placeholder="Ej: 16.00"
                />
              </div>
            </div>
          </div>

          <!-- Sección: Detalles -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Detalles
            </h3>
            <div class="space-y-4">
              <!-- Descripción -->
              <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Descripción
                </label>
                <textarea
                  id="descripcion"
                  v-model="form.descripcion"
                  rows="4"
                  placeholder="Descripción del impuesto (opcional)"
                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
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
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
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
            </div>
          </div>

          <!-- Sección: Estado -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Estado
            </h3>
            <div class="flex items-center space-x-3">
              <Checkbox
                :checked="form.activo"
                @update:checked="form.activo = $event"
              />
              <label class="text-sm font-medium text-gray-900 dark:text-white">
                Impuesto activo
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
              {{ loading ? 'Actualizando...' : 'Actualizar Impuesto' }}
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
import { useTaxes } from '@/composables/useTaxes'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchTax, updateTax, loading, goToIndex, empresas, fetchEmpresas } = useTaxes()

const loadingTax = ref(true)

const form = ref({
  nombre: '',
  porcentaje: '',
  descripcion: '',
  empresa_id: '',
  activo: true,
})

onMounted(async () => {
  try {
    // Cargar empresas
    await fetchEmpresas()

    // Cargar datos del impuesto
    const taxId = route.params.id
    const tax = await fetchTax(taxId)

    // Llenar formulario
    form.value = {
      nombre: tax.nombre,
      porcentaje: tax.porcentaje,
      descripcion: tax.descripcion || '',
      empresa_id: tax.empresa_id,
      activo: tax.activo,
    }
  } catch (err) {
    // El error se maneja en el composable
    goToIndex()
  } finally {
    loadingTax.value = false
  }
})

const handleSubmit = async () => {
  try {
    const taxId = route.params.id

    // Preparar datos para enviar
    const taxData = {
      nombre: form.value.nombre,
      porcentaje: parseFloat(form.value.porcentaje),
      descripcion: form.value.descripcion,
      empresa_id: parseInt(form.value.empresa_id),
      activo: form.value.activo ? 1 : 0,
    }

    await updateTax(taxId, taxData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
