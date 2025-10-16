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
              <!-- Género -->
              <div>
                <label for="gender_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Género
                </label>
                <select
                  id="gender_id"
                  v-model="form.gender_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione...</option>
                  <option
                    v-for="gender in genders"
                    :key="gender.id"
                    :value="gender.id"
                  >
                    {{ gender.sexo }}
                  </option>
                </select>
              </div>

              <!-- Teléfono -->
              <div>
                <label for="phone_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Teléfono
                </label>
                <select
                  id="phone_id"
                  v-model="form.phone_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione...</option>
                  <option
                    v-for="telefono in telefonos"
                    :key="telefono.id"
                    :value="telefono.id"
                  >
                    {{ telefono.telefono }}
                  </option>
                </select>
              </div>

              <!-- Chat ID (Telegram) -->
              <div>
                <label for="chatid_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Chat ID (Telegram)
                </label>
                <select
                  id="chatid_id"
                  v-model="form.chatid_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione...</option>
                  <option
                    v-for="chatid in chatids"
                    :key="chatid.id"
                    :value="chatid.id"
                  >
                    {{ chatid.idtelegram }}
                  </option>
                </select>
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
import { useGenders } from '@/composables/useGenders'
import { useTelefonos } from '@/composables/useTelefonos'
import { useChatids } from '@/composables/useChatids'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchUser, updateUser, loading, goToIndex } = useUsers()
const { empresas, fetchEmpresas } = useEmpresas()
const { genders, fetchGenders } = useGenders()
const { telefonos, fetchTelefonos } = useTelefonos()
const { chatids, fetchChatids } = useChatids()

const loadingUser = ref(true)

const form = ref({
  usuario: '',
  name: '',
  email: '',
  gender_id: '',
  phone_id: '',
  chatid_id: '',
  empresa_id: '',
  activo: true,
})

onMounted(async () => {
  try {
    // Cargar datos en paralelo
    await Promise.all([
      fetchEmpresas(),
      fetchGenders(),
      fetchTelefonos(),
      fetchChatids()
    ])

    // Cargar datos del usuario
    const userId = route.params.id
    const user = await fetchUser(userId)

    // Llenar formulario con los IDs correctos
    form.value = {
      usuario: user.usuario,
      name: user.name,
      email: user.email,
      gender_id: user.gender_id || '',
      phone_id: user.phone_id || '',
      chatid_id: user.chatid_id || '',
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
    if (form.value.gender_id) {
      userData.gender_id = form.value.gender_id
    }
    if (form.value.phone_id) {
      userData.phone_id = form.value.phone_id
    }
    if (form.value.chatid_id) {
      userData.chatid_id = form.value.chatid_id
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
