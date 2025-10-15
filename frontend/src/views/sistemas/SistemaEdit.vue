<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Sistema
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica los datos del sistema
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
      <div v-if="loadingSistema" class="flex justify-center items-center h-64">
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
                  Nombre del Sistema *
                </label>
                <Input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  placeholder="Ingrese el nombre del sistema"
                />
              </div>

              <!-- Versión -->
              <div>
                <label for="version" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Versión
                </label>
                <Input
                  id="version"
                  v-model="form.version"
                  type="text"
                  placeholder="1.0.0"
                />
              </div>
            </div>
          </div>

          <!-- Sección: Configuración -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Configuración
            </h3>
            <div class="space-y-4">
              <!-- Configuración JSON -->
              <div>
                <label for="configuracion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Configuración (JSON)
                </label>
                <textarea
                  id="configuracion"
                  v-model="form.configuracion"
                  rows="6"
                  placeholder='{"key": "value"}'
                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                ></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Ingrese un objeto JSON válido (opcional)
                </p>
              </div>
            </div>
          </div>

          <!-- Sección: Logo Actual -->
          <div v-if="currentLogo">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Logo Actual
            </h3>
            <div class="flex items-center gap-4">
              <img :src="currentLogo" alt="Logo actual" class="h-24 w-24 rounded-lg object-cover border-2 border-gray-200 dark:border-gray-700" />
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Para cambiar el logo, usa el modal desde la vista de gestión de sistemas
              </p>
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
                Sistema activo
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
              {{ loading ? 'Actualizando...' : 'Actualizar Sistema' }}
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
import { useSistemas } from '@/composables/useSistemas'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchSistema, updateSistema, loading, goToIndex } = useSistemas()

const loadingSistema = ref(true)
const currentLogo = ref(null)

const form = ref({
  nombre: '',
  version: '',
  configuracion: '',
  activo: true,
})

onMounted(async () => {
  try {
    // Cargar datos del sistema
    const sistemaId = route.params.id
    const sistema = await fetchSistema(sistemaId)

    // Llenar formulario
    form.value = {
      nombre: sistema.nombre,
      version: sistema.version || '',
      configuracion: sistema.configuracion || '',
      activo: sistema.activo,
    }

    // Guardar logo actual
    currentLogo.value = sistema.logo
  } catch (err) {
    // El error se maneja en el composable
    goToIndex()
  } finally {
    loadingSistema.value = false
  }
})

const handleSubmit = async () => {
  try {
    const sistemaId = route.params.id

    // Preparar datos para enviar
    const sistemaData = {
      nombre: form.value.nombre,
      activo: form.value.activo ? 1 : 0,
    }

    // Solo agregar campos opcionales si tienen valor
    if (form.value.version) {
      sistemaData.version = form.value.version
    }
    if (form.value.configuracion) {
      sistemaData.configuracion = form.value.configuracion
    }

    await updateSistema(sistemaId, sistemaData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
