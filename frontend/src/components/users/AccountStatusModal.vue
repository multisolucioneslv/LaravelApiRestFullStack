<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click.self="handleClose"
  >
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black/50 transition-opacity"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div
        class="relative w-full max-w-md transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl transition-all"
        @click.stop
      >
        <!-- Header -->
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Cambiar Estado de Cuenta
          </h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Usuario: <span class="font-medium text-gray-900 dark:text-white">{{ user?.name }}</span>
          </p>
        </div>

        <!-- Body -->
        <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
          <!-- Estado actual -->
          <div class="rounded-md bg-gray-50 dark:bg-gray-900/50 p-3">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Estado actual:
              <span
                :class="getStatusClass(user?.cuenta)"
                class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
              >
                {{ formatStatus(user?.cuenta) }}
              </span>
            </p>
          </div>

          <!-- Nuevo estado -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
              Nuevo Estado *
            </label>
            <select
              v-model="form.cuenta"
              required
              class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
            >
              <option value="">Seleccione un estado...</option>
              <option value="activada">Activada</option>
              <option value="suspendida">Suspendida</option>
              <option value="cancelada">Cancelada</option>
            </select>
          </div>

          <!-- Razón (solo para suspendida o cancelada) -->
          <div v-if="requiresReason" class="space-y-2">
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Razón *
            </label>
            <textarea
              v-model="form.razon_suspendida"
              required
              rows="4"
              placeholder="Ingrese la razón de la suspensión o cancelación..."
              class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
              maxlength="500"
            ></textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ form.razon_suspendida.length }} / 500 caracteres
            </p>
          </div>

          <!-- Warning para SuperAdmin -->
          <div
            v-if="user?.roles?.some(role => role.name === 'SuperAdmin')"
            class="rounded-md bg-red-50 dark:bg-red-900/20 p-3"
          >
            <p class="text-sm text-red-800 dark:text-red-200 font-medium">
              ⚠️ No se puede cambiar el estado de cuenta de un SuperAdmin
            </p>
          </div>

          <!-- Warning para Administrador de empresa -->
          <div
            v-else-if="requiresReason && user?.roles?.some(role => role.name === 'Administrador')"
            class="rounded-md bg-orange-50 dark:bg-orange-900/20 p-3"
          >
            <p class="text-sm text-orange-800 dark:text-orange-200">
              <strong>⚠️ ADVERTENCIA IMPORTANTE:</strong> Al suspender/cancelar la cuenta de este Administrador, <strong>TODOS los usuarios de su empresa también serán suspendidos/cancelados automáticamente</strong> con una razón por defecto. Solo se reactivarán cuando la cuenta del Administrador sea reactivada.
            </p>
          </div>

          <!-- Warning general -->
          <div
            v-else-if="requiresReason"
            class="rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-3"
          >
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
              <strong>Advertencia:</strong> Si el usuario está en línea, su sesión será cerrada inmediatamente y no podrá iniciar sesión nuevamente hasta que su cuenta sea reactivada.
            </p>
          </div>
        </form>

        <!-- Footer -->
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-end space-x-3">
          <Button
            type="button"
            variant="outline"
            @click="handleClose"
            :disabled="loading"
          >
            Cancelar
          </Button>
          <Button
            type="submit"
            @click="handleSubmit"
            :disabled="loading || !form.cuenta || (requiresReason && !form.razon_suspendida) || user?.roles?.some(role => role.name === 'SuperAdmin')"
          >
            {{ loading ? 'Guardando...' : 'Guardar Cambios' }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  user: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'submit'])

const form = ref({
  cuenta: '',
  razon_suspendida: '',
})

// Computed
const requiresReason = computed(() => {
  return ['suspendida', 'cancelada'].includes(form.value.cuenta)
})

// Watchers
watch(() => props.isOpen, (newValue) => {
  if (newValue && props.user) {
    // Resetear formulario cuando se abre el modal
    form.value = {
      cuenta: '',
      razon_suspendida: '',
    }
  }
})

watch(() => form.value.cuenta, (newValue) => {
  // Limpiar razón si se selecciona 'activada'
  if (newValue === 'activada') {
    form.value.razon_suspendida = ''
  }
})

// Methods
const formatStatus = (status) => {
  const statusMap = {
    creada: 'Creada',
    activada: 'Activada',
    suspendida: 'Suspendida',
    cancelada: 'Cancelada',
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    creada: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
    activada: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    suspendida: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    cancelada: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  }
  return classMap[status] || ''
}

const handleClose = () => {
  if (!props.loading) {
    emit('close')
  }
}

const handleSubmit = () => {
  if (props.loading) return
  if (!form.value.cuenta) return
  if (requiresReason.value && !form.value.razon_suspendida) return
  if (props.user?.roles?.some(role => role.name === 'SuperAdmin')) return

  emit('submit', {
    userId: props.user.id,
    cuenta: form.value.cuenta,
    razon_suspendida: form.value.razon_suspendida || null,
  })
}
</script>
