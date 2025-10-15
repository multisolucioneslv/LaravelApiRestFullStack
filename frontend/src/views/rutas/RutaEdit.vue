<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Editar Ruta API
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Modifica los datos del endpoint
        </p>
      </div>

      <!-- Loading inicial -->
      <div v-if="loadingData" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <div v-else class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <RutaForm
          v-model="formData"
          :is-edit="true"
          :loading="loading"
          @submit="handleSubmit"
          @cancel="handleCancel"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useRutas } from '@/composables/useRutas'
import RutaForm from '@/components/rutas/RutaForm.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()

const {
  loading,
  fetchRuta,
  updateRuta,
  goToIndex,
} = useRutas()

const loadingData = ref(true)
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

onMounted(async () => {
  try {
    const ruta = await fetchRuta(route.params.id)

    // Convertir middleware array a string JSON para el formulario
    formData.value = {
      ...ruta,
      middleware: Array.isArray(ruta.middleware)
        ? JSON.stringify(ruta.middleware)
        : ruta.middleware
    }
  } catch (error) {
    console.error('Error al cargar ruta:', error)
    goToIndex()
  } finally {
    loadingData.value = false
  }
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

    await updateRuta(route.params.id, dataToSend)
    goToIndex()
  } catch (error) {
    console.error('Error al actualizar ruta:', error)
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
