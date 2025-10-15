<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Perfil</h1>
          <p class="text-gray-600 dark:text-gray-400">Actualiza tu información personal</p>
        </div>
        <button
          @click="$router.push({ name: 'profile' })"
          class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
        >
          Cancelar
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Edit Form -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Avatar Section -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Foto de Perfil</h3>

            <!-- Avatar Preview -->
            <div class="flex flex-col items-center mb-4">
              <div v-if="avatarPreview || avatarURL" class="w-32 h-32 rounded-full overflow-hidden shadow-lg border-4 border-gray-200 dark:border-gray-600 mb-4">
                <img :src="avatarPreview || avatarURL" alt="Avatar" class="w-full h-full object-cover" />
              </div>
              <div v-else class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg mb-4">
                {{ getInitials(form.name) }}
              </div>

              <!-- Upload Button -->
              <label class="w-full cursor-pointer">
                <input
                  type="file"
                  @change="handleAvatarChange"
                  accept="image/*"
                  class="hidden"
                />
                <div class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium text-center">
                  Cambiar Foto
                </div>
              </label>

              <!-- Delete Avatar Button -->
              <button
                v-if="avatarURL"
                @click="handleDeleteAvatar"
                :disabled="updating"
                class="mt-2 w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 font-medium disabled:opacity-50"
              >
                Eliminar Foto
              </button>

              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                JPG, PNG o GIF (MAX. 2MB)
              </p>
            </div>
          </div>
        </div>

        <!-- Form Fields -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información Personal -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información Personal</h3>

            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nombre -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre Completo *
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Usuario -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Usuario *
                  </label>
                  <input
                    v-model="form.usuario"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email *
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Teléfono -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Teléfono
                  </label>
                  <input
                    v-model="form.telefono"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Chat ID -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Chat ID (Telegram)
                  </label>
                  <input
                    v-model="form.chatid"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Género -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Género
                  </label>
                  <select
                    v-model="form.gender_id"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  >
                    <option :value="null">Seleccionar...</option>
                    <option v-for="gender in genders" :key="gender.id" :value="gender.id">
                      {{ gender.sexo }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Botones de Acción -->
              <div class="flex justify-end space-x-4 pt-4">
                <button
                  type="button"
                  @click="$router.push({ name: 'profile' })"
                  :disabled="updating"
                  class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="updating"
                  class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center space-x-2"
                >
                  <span v-if="updating" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                  <span>{{ updating ? 'Guardando...' : 'Guardar Cambios' }}</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Cambiar Contraseña -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Cambiar Contraseña</h3>

            <form @submit.prevent="handlePasswordChange" class="space-y-4">
              <div class="space-y-4">
                <!-- Contraseña Actual -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Contraseña Actual
                  </label>
                  <input
                    v-model="passwordForm.currentPassword"
                    type="password"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Nueva Contraseña -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nueva Contraseña
                  </label>
                  <input
                    v-model="passwordForm.newPassword"
                    type="password"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Confirmar Nueva Contraseña
                  </label>
                  <input
                    v-model="passwordForm.confirmPassword"
                    type="password"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  />
                </div>
              </div>

              <!-- Botón Cambiar Contraseña -->
              <div class="flex justify-end pt-4">
                <button
                  type="submit"
                  :disabled="updating || !passwordForm.currentPassword || !passwordForm.newPassword || !passwordForm.confirmPassword"
                  class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center space-x-2"
                >
                  <span v-if="updating" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                  <span>{{ updating ? 'Cambiando...' : 'Cambiar Contraseña' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useProfile } from '@/composables/useProfile'
import { useGenders } from '@/composables/useGenders'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const { profile, loading, updating, fetchProfile, updateProfile, changePassword, deleteAvatar } = useProfile()
const { genders, fetchGenders } = useGenders()

const apiURL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

// Formulario de datos personales
const form = ref({
  usuario: '',
  name: '',
  email: '',
  telefono: '',
  chatid: '',
  gender_id: null,
  empresa_id: null,
  activo: true
})

// Formulario de cambio de contraseña
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Avatar
const avatarFile = ref(null)
const avatarPreview = ref(null)

const avatarURL = computed(() => {
  if (profile.value?.avatar) {
    // Usar updated_at como cache-busting para evitar cache del navegador
    const cacheBuster = profile.value?.updated_at
      ? new Date(profile.value.updated_at).getTime()
      : Date.now()
    return `${apiURL}/storage/${profile.value.avatar}?v=${cacheBuster}`
  }
  return null
})

const getInitials = (name) => {
  if (!name) return 'U'
  const nameParts = name.split(' ')
  if (nameParts.length >= 2) {
    return `${nameParts[0][0]}${nameParts[1][0]}`.toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validar tamaño (máximo 2MB)
    if (file.size > 2 * 1024 * 1024) {
      alert.error('Error', 'La imagen no puede ser mayor a 2MB')
      return
    }

    avatarFile.value = file

    // Crear preview
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target.result

      // Actualizar temporalmente el avatar en el authStore para que se refleje en el Navbar
      if (authStore.user) {
        authStore.user._tempAvatar = e.target.result
        // Actualizar también en localStorage para persistir durante la sesión
        localStorage.setItem('user', JSON.stringify(authStore.user))
      }
    }
    reader.readAsDataURL(file)
  }
}

const handleDeleteAvatar = async () => {
  await deleteAvatar()
  avatarFile.value = null
  avatarPreview.value = null

  // Limpiar el avatar temporal del authStore
  if (authStore.user) {
    delete authStore.user._tempAvatar
    localStorage.setItem('user', JSON.stringify(authStore.user))
  }
}

const handleSubmit = async () => {
  const formData = { ...form.value }

  // Si hay un nuevo avatar, agregarlo
  if (avatarFile.value) {
    formData.avatar = avatarFile.value
  }

  await updateProfile(formData)

  // Limpiar el avatar temporal después de guardar exitosamente
  if (authStore.user && authStore.user._tempAvatar) {
    delete authStore.user._tempAvatar
    localStorage.setItem('user', JSON.stringify(authStore.user))
  }

  router.push({ name: 'profile' })
}

const handlePasswordChange = async () => {
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    alert.error('Error', 'Las contraseñas no coinciden')
    return
  }

  await changePassword(passwordForm.value)

  // Limpiar formulario
  passwordForm.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
}

onMounted(async () => {
  await fetchProfile()
  await fetchGenders()

  // Llenar formulario con datos actuales
  if (profile.value) {
    form.value = {
      usuario: profile.value.usuario,
      name: profile.value.name,
      email: profile.value.email,
      telefono: profile.value.telefono || '',
      chatid: profile.value.chatid || '',
      gender_id: profile.value.gender?.id || profile.value.gender_id || null,
      empresa_id: profile.value.empresa?.id || profile.value.empresa_id || null,
      activo: profile.value.activo
    }
  }
})
</script>
