<template>
  <Dialog :open="show" @update:open="handleClose">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>Actualizar Stock</DialogTitle>
        <DialogDescription>
          Producto: <strong>{{ producto?.nombre }}</strong>
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 py-4">
        <!-- Stock actual -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-400">Stock Actual:</span>
            <span class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ producto?.stock_actual || 0 }}
            </span>
          </div>
          <div class="flex items-center justify-between mt-2">
            <span class="text-xs text-gray-500 dark:text-gray-500">Stock Mínimo:</span>
            <span class="text-sm text-gray-600 dark:text-gray-400">
              {{ producto?.stock_minimo || 0 }}
            </span>
          </div>
        </div>

        <!-- Tipo de operación -->
        <div class="space-y-2">
          <label class="text-sm font-medium">Tipo de operación</label>
          <div class="flex gap-2">
            <button
              @click="tipo = 'aumentar'"
              :class="[
                'flex-1 py-3 px-4 rounded-lg border-2 transition-all',
                tipo === 'aumentar'
                  ? 'border-green-500 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                  : 'border-gray-200 dark:border-gray-700 hover:border-green-300'
              ]"
            >
              <div class="flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 12h14"/>
                  <path d="M12 5v14"/>
                </svg>
                <span class="font-medium">Aumentar</span>
              </div>
            </button>
            <button
              @click="tipo = 'disminuir'"
              :class="[
                'flex-1 py-3 px-4 rounded-lg border-2 transition-all',
                tipo === 'disminuir'
                  ? 'border-red-500 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400'
                  : 'border-gray-200 dark:border-gray-700 hover:border-red-300'
              ]"
            >
              <div class="flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 12h14"/>
                </svg>
                <span class="font-medium">Disminuir</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Cantidad -->
        <div class="space-y-2">
          <label class="text-sm font-medium">Cantidad</label>
          <Input
            v-model.number="cantidad"
            type="number"
            min="1"
            placeholder="Ingrese cantidad"
            class="text-center text-lg font-semibold"
          />
        </div>

        <!-- Preview del resultado -->
        <div v-if="cantidad > 0" class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4 border-2 border-blue-200 dark:border-blue-800">
          <div class="flex items-center justify-between">
            <span class="text-sm text-blue-700 dark:text-blue-300">Nuevo stock:</span>
            <div class="flex items-center gap-2">
              <span class="text-gray-400 line-through">{{ producto?.stock_actual || 0 }}</span>
              <span class="text-2xl font-bold" :class="nuevoStock < (producto?.stock_minimo || 0) ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400'">
                {{ nuevoStock }}
              </span>
            </div>
          </div>
          <div v-if="nuevoStock < (producto?.stock_minimo || 0)" class="mt-2 flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
              <path d="M12 9v4"/>
              <path d="M12 17h.01"/>
            </svg>
            Alerta: El stock quedará por debajo del mínimo
          </div>
          <div v-if="nuevoStock < 0" class="mt-2 flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="m15 9-6 6"/>
              <path d="m9 9 6 6"/>
            </svg>
            Error: El stock no puede ser negativo
          </div>
        </div>
      </div>

      <div class="flex gap-2">
        <Button variant="outline" class="flex-1" @click="handleClose">
          Cancelar
        </Button>
        <Button
          class="flex-1"
          :disabled="!cantidad || cantidad <= 0 || nuevoStock < 0"
          @click="handleSubmit"
        >
          Actualizar Stock
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  producto: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'update'])

// Estado local
const tipo = ref('aumentar')
const cantidad = ref(1)

// Computed
const nuevoStock = computed(() => {
  const stockActual = props.producto?.stock_actual || 0
  if (!cantidad.value) return stockActual

  return tipo.value === 'aumentar'
    ? stockActual + cantidad.value
    : stockActual - cantidad.value
})

// Watchers
watch(() => props.show, (newVal) => {
  if (newVal) {
    // Reset cuando se abre el modal
    tipo.value = 'aumentar'
    cantidad.value = 1
  }
})

// Métodos
const handleClose = () => {
  emit('close')
}

const handleSubmit = () => {
  if (!cantidad.value || cantidad.value <= 0 || nuevoStock.value < 0) {
    return
  }

  emit('update', {
    cantidad: cantidad.value,
    tipo: tipo.value
  })
}
</script>
