<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Crear Ruta API
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Registra un nuevo endpoint en el sistema
        </p>
      </div>

      <!-- Formulario -->
      <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <RutaForm
          v-model="formData"
          :is-edit="false"
          :loading="loading"
          @submit="handleSubmit"
          @cancel="handleCancel"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRutas } from '@/composables/useRutas'
import RutaForm from '@/components/rutas/RutaForm.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  loading,
  createRuta,
  goToIndex,
} = useRutas()

const formData = ref({
  sistema_id: null,
  ruta: '',
  metodo: '',
  descripcion: '',
  controlador: '',
  accion: '',
  middleware: '[]',
  activo: true,
})

const handleSubmit = async () => {
  try {
    // Parsear middleware si es string JSON
    const dataToSend = {
      ...formData.value,
      middleware: typeof formData.value.middleware === 'string'
        ? JSON.parse(formData.value.middleware)
        : formData.value.middleware
    }

    await createRuta(dataToSend)
    goToIndex()
  } catch (error) {
    console.error('Error al crear ruta:', error)
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
