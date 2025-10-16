<template>
  <div class="w-full max-w-md mx-auto">
    <div class="card">
      <!-- Logo de la Empresa -->
      <div v-if="logo" class="flex justify-center mb-6">
        <img
          :src="logo"
          :alt="empresaNombre"
          class="h-20 w-auto object-contain"
        />
      </div>

      <!-- Encabezado -->
      <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
          Iniciar Sesión
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Ingresa tus credenciales para continuar
        </p>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Usuario o Email (LOGIN DUAL) -->
        <div>
          <label
            for="loginField"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
          >
            Usuario o Correo Electrónico
          </label>
          <input
            id="loginField"
            v-model="form.loginField"
            type="text"
            required
            autocomplete="username"
            class="input-field"
            placeholder="usuario o tu@email.com"
          />
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Puedes usar tu nombre de usuario o correo electrónico
          </p>
        </div>

        <!-- Password -->
        <div>
          <label
            for="password"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
          >
            Contraseña
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="current-password"
              class="input-field pr-10"
              placeholder="••••••••"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              tabindex="-1"
            >
              <!-- Ícono de ojo (mostrar) -->
              <svg
                v-if="!showPassword"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              <!-- Ícono de ojo tachado (ocultar) -->
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
                />
              </svg>
            </button>
          </div>
        </div>

        <!-- Recordar sesión -->
        <div class="flex items-center">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500"
          />
          <label
            for="remember"
            class="ml-2 text-sm text-gray-700 dark:text-gray-300"
          >
            Recordar sesión
          </label>
        </div>

        <!-- Error message -->
        <div
          v-if="authStore.error"
          class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900"
        >
          <p class="text-sm text-red-600 dark:text-red-400">
            {{ authStore.error }}
          </p>
        </div>

        <!-- Botón de submit -->
        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!authStore.loading">Iniciar Sesión</span>
          <span v-else class="flex items-center justify-center">
            <svg
              class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
            Iniciando...
          </span>
        </button>

        <!-- Link a registro -->
        <div class="text-center text-sm">
          <span class="text-gray-600 dark:text-gray-400">
            ¿No tienes cuenta?
          </span>
          <router-link
            to="/register"
            class="ml-1 text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium"
          >
            Regístrate aquí
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

// Props
const props = defineProps({
  logo: {
    type: String,
    default: null
  },
  empresaNombre: {
    type: String,
    default: 'Sistema'
  }
})

const authStore = useAuthStore()

const form = reactive({
  loginField: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

/**
 * LOGIN DUAL: Detecta si el usuario ingresó email o usuario
 * y envía el campo correcto al backend
 */
const handleSubmit = async () => {
  authStore.clearError()

  // Preparar credenciales
  const credentials = {
    password: form.password,
  }

  // Detectar si es email o usuario
  // Si contiene '@' es email, sino es usuario
  if (form.loginField.includes('@')) {
    credentials.email = form.loginField
  } else {
    credentials.usuario = form.loginField
  }

  await authStore.login(credentials)
}

onMounted(() => {
  authStore.clearError()
})
</script>
