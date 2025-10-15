<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Teléfono
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica los datos del teléfono
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
      <div v-if="loadingTelefono" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <form v-else @submit.prevent="handleSubmit" class="card">
        <div class="space-y-8">
          <!-- Sección: Información del Teléfono -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información del Teléfono
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Teléfono -->
              <div>
                <label for="telefono" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Teléfono *
                </label>
                <Input
                  id="telefono"
                  v-model="form.telefono"
                  type="text"
                  required
                  maxlength="20"
                  placeholder="Ingrese el número de teléfono"
                />
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
              {{ loading ? 'Actualizando...' : 'Actualizar Teléfono' }}
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
import { useTelefonos } from '@/composables/useTelefonos'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchTelefono, updateTelefono, loading, goToIndex } = useTelefonos()

const loadingTelefono = ref(true)

const form = ref({
  telefono: '',
})

onMounted(async () => {
  try {
    // Cargar datos del teléfono
    const telefonoId = route.params.id
    const telefono = await fetchTelefono(telefonoId)

    // Llenar formulario
    form.value = {
      telefono: telefono.telefono,
    }
  } catch (err) {
    // El error se maneja en el composable
    goToIndex()
  } finally {
    loadingTelefono.value = false
  }
})

const handleSubmit = async () => {
  try {
    const telefonoId = route.params.id
    await updateTelefono(telefonoId, form.value)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
