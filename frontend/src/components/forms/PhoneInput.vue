<template>
  <div class="space-y-3">
    <label class="block text-sm font-medium text-gray-900 dark:text-white">
      Teléfonos
    </label>

    <!-- Lista de teléfonos -->
    <div v-for="(phone, index) in localPhones" :key="index" class="flex gap-2">
      <Input
        v-model="phone.telefono"
        type="text"
        placeholder="(702) 337-9581"
        class="flex-1"
        @input="emitUpdate"
      />
      <Button
        type="button"
        variant="outline"
        size="icon"
        @click="removePhone(index)"
        :disabled="localPhones.length === 1"
        class="h-10 w-10"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </Button>
    </div>

    <!-- Botón para agregar más teléfonos -->
    <Button
      type="button"
      variant="outline"
      @click="addPhone"
      class="w-full"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
      </svg>
      Agregar teléfono
    </Button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [{ telefono: '' }]
  }
})

const emit = defineEmits(['update:modelValue'])

const localPhones = ref([...props.modelValue])

// Asegurar que siempre hay al menos un campo vacío
if (localPhones.value.length === 0) {
  localPhones.value.push({ telefono: '' })
}

watch(() => props.modelValue, (newValue) => {
  localPhones.value = [...newValue]
  if (localPhones.value.length === 0) {
    localPhones.value.push({ telefono: '' })
  }
}, { deep: true })

const addPhone = () => {
  localPhones.value.push({ telefono: '' })
  emitUpdate()
}

const removePhone = (index) => {
  if (localPhones.value.length > 1) {
    localPhones.value.splice(index, 1)
    emitUpdate()
  }
}

const emitUpdate = () => {
  // Emitir todos los teléfonos sin filtrar (el filtrado se hace al enviar el formulario)
  emit('update:modelValue', localPhones.value)
}
</script>
