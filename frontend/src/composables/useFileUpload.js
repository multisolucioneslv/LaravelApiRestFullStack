import { ref } from 'vue'

export function useFileUpload(options = {}) {
  const {
    accept = 'image/*',
    maxSize = 2 * 1024 * 1024, // 2MB por defecto
    onError = null,
  } = options

  const isDragging = ref(false)
  const file = ref(null)
  const previewUrl = ref(null)
  const error = ref(null)

  const validateFile = (uploadedFile) => {
    error.value = null

    // Validar tipo de archivo
    if (accept && !uploadedFile.type.match(accept.replace('*', '.*'))) {
      error.value = 'Tipo de archivo no permitido'
      if (onError) onError(error.value)
      return false
    }

    // Validar tamaño
    if (maxSize && uploadedFile.size > maxSize) {
      const maxSizeMB = (maxSize / (1024 * 1024)).toFixed(2)
      error.value = `El archivo excede el tamaño máximo de ${maxSizeMB}MB`
      if (onError) onError(error.value)
      return false
    }

    return true
  }

  const createPreview = (uploadedFile) => {
    if (uploadedFile.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        previewUrl.value = e.target.result
      }
      reader.readAsDataURL(uploadedFile)
    }
  }

  const handleFile = (uploadedFile) => {
    if (!validateFile(uploadedFile)) {
      return false
    }

    file.value = uploadedFile
    createPreview(uploadedFile)
    return true
  }

  const handleDrop = (e) => {
    isDragging.value = false
    const droppedFile = e.dataTransfer.files[0]
    if (droppedFile) {
      handleFile(droppedFile)
    }
  }

  const handleDragOver = (e) => {
    e.preventDefault()
    isDragging.value = true
  }

  const handleDragLeave = () => {
    isDragging.value = false
  }

  const handleFileInput = (e) => {
    const selectedFile = e.target.files[0]
    if (selectedFile) {
      handleFile(selectedFile)
    }
  }

  const reset = () => {
    file.value = null
    previewUrl.value = null
    error.value = null
    isDragging.value = false
  }

  return {
    // Estado
    isDragging,
    file,
    previewUrl,
    error,

    // Métodos
    handleDrop,
    handleDragOver,
    handleDragLeave,
    handleFileInput,
    reset,
  }
}
