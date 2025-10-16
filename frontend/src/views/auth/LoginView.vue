<template>
  <div
    class="min-h-screen flex items-center justify-center px-4 py-12 relative"
    :style="backgroundStyle"
  >
    <!-- Overlay si hay fondo de login -->
    <div
      v-if="config.fondo_login"
      class="absolute inset-0 bg-black/40 dark:bg-black/60"
    ></div>

    <!-- Toggle de Dark Mode en esquina superior derecha -->
    <div class="absolute top-4 right-4 z-10">
      <DarkModeToggle />
    </div>

    <!-- Login Form con logo -->
    <div class="relative z-10 w-full">
      <LoginForm :logo="config.logo" :empresa-nombre="config.nombre" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import LoginForm from '@/components/auth/LoginForm.vue'
import DarkModeToggle from '@/components/layout/DarkModeToggle.vue'
import api from '@/services/api'

const config = ref({
  nombre: 'Sistema',
  logo: null,
  favicon: null,
  fondo_login: null
})

// Computed para el estilo de fondo
const backgroundStyle = computed(() => {
  if (config.value.fondo_login) {
    return {
      backgroundImage: `url(${config.value.fondo_login})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      backgroundRepeat: 'no-repeat'
    }
  }
  return {
    backgroundColor: '' // Usará las clases de Tailwind
  }
})

// Función para cambiar el favicon dinámicamente
const changeFavicon = (faviconUrl) => {
  if (!faviconUrl) return

  // Buscar el link del favicon existente o crear uno nuevo
  let link = document.querySelector("link[rel~='icon']")
  if (!link) {
    link = document.createElement('link')
    link.rel = 'icon'
    document.getElementsByTagName('head')[0].appendChild(link)
  }
  link.href = faviconUrl
}

// Cargar configuración pública al montar
onMounted(async () => {
  try {
    const response = await api.get('/public/login-config')
    if (response.data.success) {
      config.value = response.data.data

      // Cambiar favicon si existe
      if (config.value.favicon) {
        changeFavicon(config.value.favicon)
      }

      // Cambiar título de la página
      document.title = `Login - ${config.value.nombre}`
    }
  } catch (error) {
    console.error('Error al cargar configuración de login:', error)
    // Si falla, usar valores por defecto (ya están en config.value)
  }
})
</script>
