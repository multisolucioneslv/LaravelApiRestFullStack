import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'

// Crear instancia de Pinia
const pinia = createPinia()

// Crear app
const app = createApp(App)

// Usar plugins
app.use(pinia)
app.use(router)

// IMPORTANTE: Inicializar autenticación ANTES de montar
// Esto recupera el token de localStorage y valida con /api/auth/me
const authStore = useAuthStore()
authStore.initAuth().then(() => {
  // Montar la app solo después de validar la sesión
  app.mount('#app')
})
