<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Usuario
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica los datos del usuario
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
      <div v-if="loadingUser" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <form v-else @submit.prevent="handleSubmit" class="card">
        <div class="space-y-8">
          <!-- Sección: Información Básica -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información Básica
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Usuario -->
              <div>
                <label for="usuario" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Usuario *
                </label>
                <Input
                  id="usuario"
                  v-model="form.usuario"
                  type="text"
                  required
                  placeholder="Ingrese el nombre de usuario"
                />
              </div>

              <!-- Nombre completo -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Nombre completo *
                </label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="Ingrese el nombre completo"
                />
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Email *
                </label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="usuario@ejemplo.com"
                />
              </div>
            </div>
          </div>

          <!-- Sección: Información Adicional -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información Adicional
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Sexo -->
              <div>
                <label for="sexo_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Sexo
                </label>
                <select
                  id="sexo_id"
                  v-model="form.sexo_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione...</option>
                  <option value="1">Masculino</option>
                  <option value="2">Femenino</option>
                  <option value="3">Otro</option>
                </select>
              </div>

              <!-- Teléfono -->
              <div>
                <label for="telefono" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Teléfono
                </label>
                <Input
                  id="telefono"
                  v-model="form.telefono"
                  type="tel"
                  placeholder="(000) 000-0000"
                />
              </div>

              <!-- Chat ID (Telegram) -->
              <div>
                <label for="chatid" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Chat ID (Telegram)
                </label>
                <Input
                  id="chatid"
                  v-model="form.chatid"
                  type="text"
                  placeholder="123456789"
                />
              </div>

              <!-- Empresa -->
              <div>
                <label for="empresa_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Empresa
                </label>
                <select
                  id="empresa_id"
                  v-model="form.empresa_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione...</option>
                  <option
                    v-for="empresa in empresas"
                    :key="empresa.id"
                    :value="empresa.id"
                  >
                    {{ empresa.nombre }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Sección: Configuración -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Configuración
            </h3>
            <div class="flex items-center space-x-3">
              <Checkbox
                :checked="form.activo"
                @update:checked="form.activo = $event"
              />
              <label class="text-sm font-medium text-gray-900 dark:text-white">
                Usuario activo
              </label>
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
              {{ loading ? 'Actualizando...' : 'Actualizar Usuario' }}
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
import { useUsers } from '@/composables/useUsers'
import { useEmpresas } from '@/composables/useEmpresas'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchUser, updateUser, loading, goToIndex } = useUsers()
const { empresas, fetchEmpresas } = useEmpresas()

const loadingUser = ref(true)

const form = ref({
  usuario: '',
  name: '',
  email: '',
  sexo_id: '',
  telefono: '',
  chatid: '',
  empresa_id: '',
  activo: true,
})

onMounted(async () => {
  try {
    // Cargar empresas
    await fetchEmpresas()

    // Cargar datos del usuario
    const userId = route.params.id
    const user = await fetchUser(userId)

    // Llenar formulario
    form.value = {
      usuario: user.usuario,
      name: user.name,
      email: user.email,
      sexo_id: user.sexo_id || '',
      telefono: user.telefono || '',
      chatid: user.chatid || '',
      empresa_id: user.empresa_id || '',
      activo: user.activo,
    }
  } catch (err) {
    // El error se maneja en el composable
    goToIndex()
  } finally {
    loadingUser.value = false
  }
})

const handleSubmit = async () => {
  try {
    const userId = route.params.id

    // Preparar datos para enviar (solo campos con valores)
    const userData = {
      usuario: form.value.usuario,
      name: form.value.name,
      email: form.value.email,
      activo: form.value.activo ? 1 : 0,
    }

    // Solo agregar campos opcionales si tienen valor
    if (form.value.sexo_id) {
      userData.sexo_id = form.value.sexo_id
    }
    if (form.value.telefono) {
      userData.telefono = form.value.telefono
    }
    if (form.value.chatid) {
      userData.chatid = form.value.chatid
    }
    if (form.value.empresa_id) {
      userData.empresa_id = form.value.empresa_id
    }

    await updateUser(userId, userData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
