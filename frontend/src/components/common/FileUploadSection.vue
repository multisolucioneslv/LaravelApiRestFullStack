<template>
  <div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
      {{ title }}
    </h3>
    <div class="space-y-4">
      <!-- Zona de Drag & Drop -->
      <div
        @drop.prevent="handleDrop"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        :class="[
          'border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
          isDragging
            ? 'border-primary-500 bg-primary-50 dark:bg-primary-950'
            : 'border-gray-300 dark:border-gray-600 hover:border-primary-400'
        ]"
        @click="$refs.fileInput.click()"
      >
        <input
          ref="fileInput"
          type="file"
          :accept="accept"
          class="hidden"
          @change="handleFileSelect"
        />

        <!-- Sin archivo -->
        <div v-if="!localPreview && !currentFileUrl" class="space-y-3">
          <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
          </svg>
          <div class="text-sm text-gray-600 dark:text-gray-400">
            <span class="font-semibold text-primary-600 dark:text-primary-400">Haz clic para subir</span>
            o arrastra y suelta
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-500">
            {{ description }}
          </p>
        </div>

        <!-- Vista previa de la imagen actual (si no hay nueva) -->
        <div v-else-if="!localPreview && currentFileUrl" class="space-y-3">
          <img :src="currentFileUrl" alt="Imagen actual" class="mx-auto h-32 object-contain" />
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Imagen actual - Haz clic para cambiar
          </p>
        </div>

        <!-- Vista previa de la nueva imagen -->
        <div v-else class="relative">
          <img :src="localPreview" alt="Vista previa" class="mx-auto h-32 object-contain" />
          <button
            type="button"
            @click.stop="removeFile"
            class="absolute top-0 right-1/2 translate-x-16 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            {{ localFilename }}
          </p>
        </div>
      </div>

      <!-- Mensaje de error -->
      <p v-if="localError" class="text-sm text-red-600 dark:text-red-400">
        {{ localError }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  file: {
    type: File,
    default: null
  },
  preview: {
    type: String,
    default: null
  },
  filename: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  maxSize: {
    type: Number,
    default: 5 // MB
  },
  accept: {
    type: String,
    default: 'image/jpeg,image/jpg,image/png,image/gif,image/webp'
  },
  currentFileUrl: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:file', 'update:preview', 'update:filename', 'update:error'])

const isDragging = ref(false)
const fileInput = ref(null)

// Estados locales sincronizados con props
const localPreview = ref(props.preview)
const localFilename = ref(props.filename)
const localError = ref(props.error)

// Watch para sincronizar cambios externos
watch(() => props.preview, (newVal) => {
  localPreview.value = newVal
})

watch(() => props.filename, (newVal) => {
  localFilename.value = newVal
})

watch(() => props.error, (newVal) => {
  localError.value = newVal
})

// Validar archivo
const validateFile = (file) => {
  localError.value = ''
  emit('update:error', '')

  // Validar tipo de archivo
  const validTypes = props.accept.split(',')
  if (!validTypes.includes(file.type)) {
    const errorMsg = 'Formato no válido. Verifica los tipos permitidos.'
    localError.value = errorMsg
    emit('update:error', errorMsg)
    return false
  }

  // Validar tamaño
  const maxSizeBytes = props.maxSize * 1024 * 1024
  if (file.size > maxSizeBytes) {
    const errorMsg = `El archivo excede el tamaño máximo de ${props.maxSize}MB.`
    localError.value = errorMsg
    emit('update:error', errorMsg)
    return false
  }

  return true
}

// Manejar selección de archivo
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (validateFile(file)) {
    emit('update:file', file)
    localFilename.value = file.name
    emit('update:filename', file.name)

    // Crear vista previa
    const reader = new FileReader()
    reader.onload = (e) => {
      localPreview.value = e.target.result
      emit('update:preview', e.target.result)
    }
    reader.readAsDataURL(file)
  }
}

// Manejar drop de archivo
const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (!file) return

  if (validateFile(file)) {
    emit('update:file', file)
    localFilename.value = file.name
    emit('update:filename', file.name)

    // Crear vista previa
    const reader = new FileReader()
    reader.onload = (e) => {
      localPreview.value = e.target.result
      emit('update:preview', e.target.result)
    }
    reader.readAsDataURL(file)
  }
}

// Remover archivo
const removeFile = () => {
  emit('update:file', null)
  localPreview.value = null
  emit('update:preview', null)
  localFilename.value = ''
  emit('update:filename', '')
  localError.value = ''
  emit('update:error', '')
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}
</script>
