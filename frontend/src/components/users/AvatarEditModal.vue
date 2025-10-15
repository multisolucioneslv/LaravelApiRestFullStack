<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>Editar Avatar - {{ user?.name }}</DialogTitle>
        <DialogDescription>
          Sube una nueva imagen para el avatar del usuario
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4">
        <!-- Avatar Actual -->
        <div class="flex justify-center">
          <div class="relative">
            <img
              v-if="previewUrl || user?.avatar"
              :src="previewUrl || user?.avatar"
              :alt="user?.name"
              class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700"
            />
            <div
              v-else
              class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-4xl font-bold"
            >
              {{ user?.name?.charAt(0).toUpperCase() }}
            </div>
          </div>
        </div>

        <!-- Drag and Drop Area -->
        <div
          @drop.prevent="handleDrop"
          @dragover.prevent="handleDragOver"
          @dragleave="handleDragLeave"
          :class="[
            'border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
            isDragging
              ? 'border-primary bg-primary/5'
              : 'border-gray-300 dark:border-gray-700 hover:border-primary'
          ]"
          @click="$refs.fileInput.click()"
        >
          <svg
            class="mx-auto h-12 w-12 text-gray-400"
            stroke="currentColor"
            fill="none"
            viewBox="0 0 48 48"
            aria-hidden="true"
          >
            <path
              d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <span class="font-semibold">Click para subir</span> o arrastra y suelta
          </p>
          <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 2MB</p>
        </div>

        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          class="hidden"
          @change="handleFileInput"
        />

        <!-- Error -->
        <p v-if="error" class="text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </p>

        <!-- Botones -->
        <div class="flex justify-end space-x-2">
          <Button
            variant="outline"
            @click="$emit('update:open', false)"
            :disabled="uploading"
          >
            Cancelar
          </Button>
          <Button
            @click="handleUpload"
            :disabled="!file || uploading"
          >
            <span v-if="uploading">Guardando...</span>
            <span v-else>Guardar Avatar</span>
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { useFileUpload } from '@/composables/useFileUpload'
import { useAlert } from '@/composables/useAlert'
import apiService from '@/services/api'

const props = defineProps({
  open: Boolean,
  user: Object,
})

const emit = defineEmits(['update:open', 'avatarUpdated'])

const alert = useAlert()
const uploading = ref(false)

const {
  isDragging,
  file,
  previewUrl,
  error,
  handleDrop,
  handleDragOver,
  handleDragLeave,
  handleFileInput,
  reset,
} = useFileUpload({
  accept: 'image/*',
  maxSize: 2 * 1024 * 1024,
  onError: (err) => {
    alert.error('Error', err)
  },
})

// Reset al cerrar el modal
watch(() => props.open, (newVal) => {
  if (!newVal) {
    reset()
  }
})

const handleUpload = async () => {
  if (!file.value || !props.user) return

  uploading.value = true

  try {
    const formData = new FormData()

    // Enviar el avatar
    formData.append('avatar', file.value)

    // Enviar campos requeridos del usuario (para que pase la validación)
    formData.append('usuario', props.user.usuario)
    formData.append('name', props.user.name)
    formData.append('email', props.user.email)
    formData.append('activo', props.user.activo ? '1' : '0')

    // Enviar campos opcionales si existen
    if (props.user.sexo_id) {
      formData.append('sexo_id', props.user.sexo_id)
    }
    if (props.user.telefono) {
      formData.append('telefono', props.user.telefono)
    }
    if (props.user.chatid) {
      formData.append('chatid', props.user.chatid)
    }
    if (props.user.empresa_id) {
      formData.append('empresa_id', props.user.empresa_id)
    }

    formData.append('_method', 'PUT')

    const response = await apiService.post(`/users/${props.user.id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    alert.success('¡Avatar actualizado!', 'El avatar se ha actualizado correctamente')
    emit('avatarUpdated', response.data.data)
    emit('update:open', false)
    reset()
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'Error al actualizar el avatar'
    alert.error('Error', errorMsg)
  } finally {
    uploading.value = false
  }
}
</script>
