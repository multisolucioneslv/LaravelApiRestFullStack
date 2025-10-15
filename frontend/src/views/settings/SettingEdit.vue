<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Configuración
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Actualiza los datos de la configuración
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
          <!-- Sección: Información de la Configuración -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información de la Configuración
            </h3>
            <div class="grid grid-cols-1 gap-6">
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
                  placeholder="Ej: Mantenimiento programado, Notificaciones activas"
                  maxlength="255"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Nombre descriptivo de la configuración
                </p>
              </div>

              <!-- Clave -->
              <div>
                <label for="clave" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Clave (Opcional)
                </label>
                <Input
                  id="clave"
                  v-model="form.clave"
                  type="text"
                  placeholder="Ej: maintenance_mode, notifications_enabled"
                  maxlength="255"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Clave única para identificar la configuración en el código
                </p>
              </div>
            </div>
          </div>

          <!-- Sección: Estado -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Estado de la Configuración
            </h3>
            <div class="flex items-center space-x-3">
              <Checkbox
                :checked="form.accion"
                @update:checked="form.accion = $event"
              />
              <label class="text-sm font-medium text-gray-900 dark:text-white">
                Configuración activa
              </label>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              Define si esta configuración está activa o inactiva en el sistema
            </p>
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
              {{ loading ? 'Actualizando...' : 'Actualizar Configuración' }}
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
import { useSettings } from '@/composables/useSettings'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchSetting, updateSetting, loading, goToIndex } = useSettings()

const loadingData = ref(true)
const settingId = route.params.id

const form = ref({
  nombre: '',
  accion: true,
  clave: '',
})

// Cargar datos de la configuración
onMounted(async () => {
  try {
    const setting = await fetchSetting(settingId)
    form.value = {
      nombre: setting.nombre,
      accion: setting.accion,
      clave: setting.clave || '',
    }
  } catch (err) {
    // Error manejado en el composable
    goToIndex()
  } finally {
    loadingData.value = false
  }
})

const handleSubmit = async () => {
  try {
    await updateSetting(settingId, form.value)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
