<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Crear Sexo
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Completa el formulario para crear un nuevo sexo
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
        <div class="space-y-8">
          <!-- Sección: Información del Sexo -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información del Sexo
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Sexo -->
              <div>
                <label for="sexo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Sexo *
                </label>
                <Input
                  id="sexo"
                  v-model="form.sexo"
                  type="text"
                  required
                  placeholder="Ej: Masculino, Femenino"
                  maxlength="50"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Nombre del sexo (máx. 50 caracteres)
                </p>
              </div>

              <!-- Inicial -->
              <div>
                <label for="inicial" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Inicial *
                </label>
                <Input
                  id="inicial"
                  v-model="form.inicial"
                  type="text"
                  required
                  placeholder="Ej: M, F"
                  maxlength="1"
                  @input="form.inicial = form.inicial.toUpperCase()"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Inicial que representa el sexo (1 carácter)
                </p>
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
              {{ loading ? 'Creando...' : 'Crear Sexo' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useGenders } from '@/composables/useGenders'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import AppLayout from '@/components/layout/AppLayout.vue'

const { createGender, loading, goToIndex } = useGenders()

const form = ref({
  sexo: '',
  inicial: '',
})

const handleSubmit = async () => {
  try {
    await createGender(form.value)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
