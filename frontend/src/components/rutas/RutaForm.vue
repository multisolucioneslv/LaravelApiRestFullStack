<template>
  <form @submit.prevent="handleSubmit" class="space-y-8">
    <!-- Sección: Información Básica -->
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
        Información Básica
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Sistema -->
        <div>
          <label for="sistema_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Sistema *
          </label>
          <select
            id="sistema_id"
            v-model="localData.sistema_id"
            required
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="" disabled>Seleccione un sistema</option>
            <option v-for="sistema in sistemas" :key="sistema.id" :value="sistema.id">
              {{ sistema.nombre }}
            </option>
          </select>
        </div>

        <!-- Método HTTP -->
        <div>
          <label for="metodo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Método HTTP *
          </label>
          <select
            id="metodo"
            v-model="localData.metodo"
            required
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="" disabled>Seleccione un método</option>
            <option value="GET">GET</option>
            <option value="POST">POST</option>
            <option value="PUT">PUT</option>
            <option value="DELETE">DELETE</option>
            <option value="PATCH">PATCH</option>
          </select>
        </div>

        <!-- Ruta -->
        <div class="md:col-span-2">
          <label for="ruta" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Ruta API *
          </label>
          <Input
            id="ruta"
            v-model="localData.ruta"
            type="text"
            required
            placeholder="/api/ejemplo"
            class="font-mono"
          />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Ejemplo: /api/users, /api/productos/{id}
          </p>
        </div>

        <!-- Descripción -->
        <div class="md:col-span-2">
          <label for="descripcion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Descripción
          </label>
          <textarea
            id="descripcion"
            v-model="localData.descripcion"
            rows="3"
            placeholder="Descripción breve del endpoint"
            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Sección: Controlador -->
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
        Controlador y Acción
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Controlador -->
        <div>
          <label for="controlador" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Controlador *
          </label>
          <Input
            id="controlador"
            v-model="localData.controlador"
            type="text"
            required
            placeholder="UserController"
            class="font-mono"
          />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Ejemplo: App\Http\Controllers\UserController
          </p>
        </div>

        <!-- Acción -->
        <div>
          <label for="accion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
            Acción/Método
          </label>
          <Input
            id="accion"
            v-model="localData.accion"
            type="text"
            placeholder="index"
            class="font-mono"
          />
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Ejemplo: index, store, show, update, destroy
          </p>
        </div>
      </div>
    </div>

    <!-- Sección: Middleware -->
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
        Middleware
      </h3>
      <div>
        <label for="middleware" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
          Middleware (JSON Array)
        </label>
        <textarea
          id="middleware"
          v-model="localData.middleware"
          rows="4"
          placeholder='["auth:api", "verified"]'
          class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 font-mono"
          :class="{ 'border-red-500': middlewareError }"
        ></textarea>
        <p v-if="middlewareError" class="text-xs text-red-600 dark:text-red-400 mt-1">
          {{ middlewareError }}
        </p>
        <p v-else class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Ingrese un array JSON válido de middleware. Ejemplo: ["auth:api", "throttle:60,1"]
        </p>
      </div>
    </div>

    <!-- Sección: Estado -->
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
        Estado
      </h3>
      <div class="flex items-center space-x-3">
        <Checkbox
          :checked="localData.activo"
          @update:checked="localData.activo = $event"
        />
        <label class="text-sm font-medium text-gray-900 dark:text-white">
          Ruta activa
        </label>
      </div>
    </div>

    <!-- Botones -->
    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
      <Button
        type="button"
        variant="outline"
        @click="$emit('cancel')"
        :disabled="loading"
      >
        Cancelar
      </Button>
      <Button
        type="submit"
        :disabled="loading || !!middlewareError"
      >
        {{ loading ? (isEdit ? 'Actualizando...' : 'Creando...') : (isEdit ? 'Actualizar Ruta' : 'Crear Ruta') }}
      </Button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { useSistemas } from '@/composables/useSistemas'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'submit', 'cancel'])

// Obtener lista de sistemas para el select
const { sistemas, fetchSistemas } = useSistemas()

onMounted(async () => {
  await fetchSistemas()
})

// Crear copia local del modelo
const localData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Validación de middleware JSON
const middlewareError = ref('')

watch(() => localData.value.middleware, (newValue) => {
  if (!newValue || newValue.trim() === '') {
    middlewareError.value = ''
    return
  }

  try {
    const parsed = JSON.parse(newValue)
    if (!Array.isArray(parsed)) {
      middlewareError.value = 'El middleware debe ser un array JSON válido'
    } else {
      middlewareError.value = ''
    }
  } catch (e) {
    middlewareError.value = 'JSON inválido. Verifique la sintaxis.'
  }
})

const handleSubmit = () => {
  // Validar middleware antes de enviar
  if (middlewareError.value) {
    return
  }

  emit('submit')
}
</script>
