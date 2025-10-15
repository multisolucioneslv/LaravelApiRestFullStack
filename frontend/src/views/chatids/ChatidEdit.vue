<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Chat ID
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica los datos del ID de chat de Telegram
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
      <div v-if="loadingChatid" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <form v-else @submit.prevent="handleSubmit" class="card">
        <div class="space-y-8">
          <!-- Sección: Información del Chat ID -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información del Chat ID
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- ID de Telegram -->
              <div>
                <label for="idtelegram" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  ID de Telegram *
                </label>
                <Input
                  id="idtelegram"
                  v-model="form.idtelegram"
                  type="text"
                  required
                  maxlength="50"
                  placeholder="Ingrese el ID de Telegram"
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
              {{ loading ? 'Actualizando...' : 'Actualizar Chat ID' }}
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
import { useChatids } from '@/composables/useChatids'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchChatid, updateChatid, loading, goToIndex } = useChatids()

const loadingChatid = ref(true)

const form = ref({
  idtelegram: '',
})

onMounted(async () => {
  try {
    // Cargar datos del chat ID
    const chatidId = route.params.id
    const chatid = await fetchChatid(chatidId)

    // Llenar formulario
    form.value = {
      idtelegram: chatid.idtelegram,
    }
  } catch (err) {
    // El error se maneja en el composable
    goToIndex()
  } finally {
    loadingChatid.value = false
  }
})

const handleSubmit = async () => {
  try {
    const chatidId = route.params.id
    await updateChatid(chatidId, form.value)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
