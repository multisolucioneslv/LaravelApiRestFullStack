<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Crear Usuario
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Completa el formulario para crear un nuevo usuario
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

      <!-- Formulario -->
      <form @submit.prevent="handleSubmit" class="card">
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

          <!-- Sección: Credenciales -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Credenciales
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Contraseña -->
              <div>
                <label for="password" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Contraseña *
                </label>
                <div class="relative">
                  <Input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    placeholder="Mínimo 6 caracteres"
                    class="pr-10"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                  >
                    <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                  </button>
                </div>
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

          <!-- Sección: Avatar -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Imagen de Perfil
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
                @click="$refs.avatarInput.click()"
              >
                <input
                  ref="avatarInput"
                  type="file"
                  accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                  class="hidden"
                  @change="handleFileSelect"
                />

                <div v-if="!avatarPreview" class="space-y-3">
                  <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                  </svg>
                  <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold text-primary-600 dark:text-primary-400">Haz clic para subir</span>
                    o arrastra y suelta
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-500">
                    PNG, JPG, GIF o WEBP (máx. 5MB)
                  </p>
                </div>

                <!-- Vista previa de la imagen -->
                <div v-else class="relative">
                  <img :src="avatarPreview" alt="Avatar preview" class="mx-auto h-32 w-32 rounded-full object-cover" />
                  <button
                    type="button"
                    @click.stop="removeAvatar"
                    class="absolute top-0 right-1/2 translate-x-16 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 transition-colors"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    {{ avatarFileName }}
                  </p>
                </div>
              </div>

              <!-- Mensaje de error -->
              <p v-if="avatarError" class="text-sm text-red-600 dark:text-red-400">
                {{ avatarError }}
              </p>
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
              {{ loading ? 'Creando...' : 'Crear Usuario' }}
            </Button>
          </div>
        </div>
    </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useUsers } from '@/composables/useUsers'
import { useEmpresas } from '@/composables/useEmpresas'
import { useGenders } from '@/composables/useGenders'
import { useTelefonos } from '@/composables/useTelefonos'
import { useChatids } from '@/composables/useChatids'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const { createUser, loading, goToIndex } = useUsers()
const { empresas, fetchEmpresas } = useEmpresas()
const { genders, fetchGenders } = useGenders()
const { telefonos, fetchTelefonos } = useTelefonos()
const { chatids, fetchChatids } = useChatids()

const showPassword = ref(false)
const isDragging = ref(false)
const avatarPreview = ref(null)
const avatarFileName = ref('')
const avatarError = ref('')
const avatarInput = ref(null)

// Cargar datos al montar el componente
onMounted(async () => {
  // Cargar datos en paralelo
  await Promise.all([
    fetchEmpresas(),
    fetchGenders(),
    fetchTelefonos(),
    fetchChatids()
  ])
})

const form = ref({
  usuario: '',
  name: '',
  email: '',
  password: '',
  avatar: null, // Archivo de imagen
  gender_id: '',
  phone_id: '',
  chatid_id: '',
  empresa_id: '',
  activo: true,
})

// Validar archivo de imagen
const validateImage = (file) => {
  avatarError.value = ''

  // Validar tipo de archivo
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
  if (!validTypes.includes(file.type)) {
    avatarError.value = 'Formato no válido. Solo se permiten imágenes JPG, PNG, GIF o WEBP.'
    return false
  }

  // Validar tamaño (5MB máximo)
  const maxSize = 5 * 1024 * 1024 // 5MB en bytes
  if (file.size > maxSize) {
    avatarError.value = 'La imagen excede el tamaño máximo de 5MB.'
    return false
  }

  return true
}

// Manejar selección de archivo
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (validateImage(file)) {
    form.value.avatar = file
    avatarFileName.value = file.name

    // Crear vista previa
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Manejar drop de archivo
const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (!file) return

  if (validateImage(file)) {
    form.value.avatar = file
    avatarFileName.value = file.name

    // Crear vista previa
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Remover avatar
const removeAvatar = () => {
  form.value.avatar = null
  avatarPreview.value = null
  avatarFileName.value = ''
  avatarError.value = ''
  if (avatarInput.value) {
    avatarInput.value.value = ''
  }
}

const handleSubmit = async () => {
  try {
    // Crear FormData para enviar archivo
    const formData = new FormData()
    formData.append('usuario', form.value.usuario)
    formData.append('name', form.value.name)
    formData.append('email', form.value.email)
    formData.append('password', form.value.password)
    formData.append('activo', form.value.activo ? '1' : '0')

    if (form.value.avatar) {
      formData.append('avatar', form.value.avatar)
    }
    if (form.value.gender_id) {
      formData.append('gender_id', form.value.gender_id)
    }
    if (form.value.phone_id) {
      formData.append('phone_id', form.value.phone_id)
    }
    if (form.value.chatid_id) {
      formData.append('chatid_id', form.value.chatid_id)
    }
    if (form.value.empresa_id) {
      formData.append('empresa_id', form.value.empresa_id)
    }

    await createUser(formData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
