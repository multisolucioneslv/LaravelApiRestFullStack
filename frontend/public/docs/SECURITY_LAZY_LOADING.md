# 🔒 Estrategia de Seguridad: Carga Lazy de Datos por Vista

## ⚠️ Problema Identificado

**Fecha:** 2025-10-16
**Severidad:** ALTA
**Tipo:** Exposición innecesaria de datos y vulnerabilidad de seguridad

### Descripción del Problema

Actualmente, la aplicación carga datos de TODAS las áreas en componentes globales (como AppLayout, Navbar, etc.), incluso cuando el usuario no está visualizando esas secciones. Esto crea varios problemas:

1. **Exposición innecesaria de datos:**
   - Si el usuario está en "Lista de Usuarios", ¿por qué cargar datos de inventario, ventas, chat, etc.?
   - Los datos quedan expuestos en la memoria del navegador y son accesibles desde DevTools
   - Un atacante podría acceder a datos sensibles sin estar en la vista correspondiente

2. **Carga de recursos innecesaria:**
   - Requests HTTP adicionales que no se están usando
   - Consumo de memoria del navegador
   - Peor rendimiento general de la aplicación

3. **Vulnerabilidad de seguridad:**
   - Mayor superficie de ataque
   - Datos sensibles en memoria cuando no se necesitan
   - Potencial exposición de datos si hay XSS

## ✅ Solución Implementada: Carga Lazy por Vista

### Principios de la Solución

1. **Solo cargar datos cuando se necesitan**
2. **Limpiar datos cuando se sale de la vista**
3. **Verificar autenticación antes de cada carga**
4. **Detener polling cuando se cambia de vista**

### Implementación Técnica

#### 1. Composables Lazy (No Auto-Inicializados)

**ANTES (❌ INSEGURO):**
```javascript
// useOnlineUsers.js
export function useOnlineUsers() {
  // ...

  onMounted(async () => {
    // ❌ Se ejecuta SIEMPRE, incluso sin sesión
    await markOnline()
    startHeartbeat()
    startPolling()
  })
}
```

**DESPUÉS (✅ SEGURO):**
```javascript
// useOnlineUsers.js
export function useOnlineUsers() {
  // ...

  const initialize = async () => {
    // ✅ Verificar token ANTES de iniciar
    const token = localStorage.getItem('auth_token')
    if (!token) {
      console.warn('No se puede inicializar: sin token')
      return
    }

    await markOnline()
    startHeartbeat()
    startPolling()
  }

  // NO hay onMounted automático

  return {
    initialize, // ✅ Se llama manualmente
    // ...
  }
}
```

#### 2. Inicialización Condicional en Componentes

**ANTES (❌ INSEGURO):**
```vue
<script setup>
// ❌ Se ejecuta siempre
const { data } = useSomeData()
</script>
```

**DESPUÉS (✅ SEGURO):**
```vue
<script setup>
import { onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const someData = useSomeData()

// ✅ Solo inicializar si hay sesión activa
onMounted(() => {
  if (authStore.isAuthenticated) {
    someData.initialize()
  }
})

// ✅ Detener cuando se cierra sesión
watch(() => authStore.isAuthenticated, (isAuth) => {
  if (!isAuth) {
    someData.stopPolling()
  } else {
    someData.initialize()
  }
})
</script>
```

#### 3. Interceptor de Seguridad en Axios

```javascript
// services/api.js
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      console.warn('🔒 [SECURITY] Error 401 - Deteniendo polling')

      // ✅ CRÍTICO: Detener TODOS los intervalos
      const highestId = window.setTimeout(() => {}, 0)
      for (let i = 0; i < highestId; i++) {
        window.clearInterval(i)
        window.clearTimeout(i)
      }

      console.log('✅ [SECURITY] Todos los intervalos detenidos')

      // Limpiar y redirigir al login
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      router.push({ name: 'login' })
    }

    return Promise.reject(error)
  }
)
```

### 4. Carga Lazy por Ruta (Vue Router)

**Estrategia recomendada para vistas específicas:**

```javascript
// router/index.js
const routes = [
  {
    path: '/usuarios',
    name: 'users',
    component: () => import('@/views/Users.vue'),
    meta: {
      requiresAuth: true,
      // ✅ Especificar qué composables cargar
      composables: ['useUsers']
    }
  },
  {
    path: '/inventario',
    name: 'inventory',
    component: () => import('@/views/Inventory.vue'),
    meta: {
      requiresAuth: true,
      // ✅ Solo cargar datos de inventario
      composables: ['useInventory']
    }
  }
]

// Guard global para inicializar composables por ruta
router.beforeEach((to, from, next) => {
  // Limpiar composables de la ruta anterior
  if (from.meta.composables) {
    from.meta.composables.forEach(name => {
      // Detener polling de cada composable
      // Implementar según necesidad
    })
  }

  next()
})
```

### 5. Ejemplo Práctico: Vista de Chat

**Vista ChatView.vue:**
```vue
<template>
  <div>
    <h1>Chat</h1>
    <!-- Contenido del chat -->
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useChat } from '@/composables/useChat'
import { useOnlineUsers } from '@/composables/useOnlineUsers'

const chat = useChat()
const onlineUsers = useOnlineUsers()

// ✅ Cargar datos SOLO cuando se entra a esta vista
onMounted(() => {
  chat.initialize()
  onlineUsers.initialize()
})

// ✅ Limpiar datos cuando se sale de esta vista
onUnmounted(() => {
  chat.stopPolling()
  onlineUsers.stopPolling()
  onlineUsers.stopHeartbeat()
})
</script>
```

## 📋 Checklist de Seguridad para Nuevos Composables

Cuando crees un nuevo composable, asegúrate de:

- [ ] **NO usar `onMounted` para iniciar automáticamente**
- [ ] **Crear método `initialize()` manual**
- [ ] **Verificar token JWT antes de inicializar**
- [ ] **Crear métodos `stop*()` para detener polling/timers**
- [ ] **Exportar todos los métodos de control (initialize, stop, etc.)**
- [ ] **Documentar en qué vistas se debe usar**
- [ ] **Limpiar datos cuando se desmonta el componente**

## 🎯 Beneficios de Seguridad

1. ✅ **Menor superficie de ataque:** Solo se cargan datos cuando se necesitan
2. ✅ **Protección contra XSS:** Menos datos en memoria = menos exposición
3. ✅ **Prevención de DDoS:** No hay polling infinito sin sesión
4. ✅ **Mejor rendimiento:** Menos requests HTTP innecesarios
5. ✅ **Cumplimiento de privacidad:** No se exponen datos de áreas no visitadas

## 🚨 Vulnerabilidades Corregidas

### 1. Polling infinito sin sesión activa
- **Estado:** ✅ CORREGIDO
- **Solución:** Verificación de token antes de `initialize()`
- **Commit:** [Fecha: 2025-10-16]

### 2. Exposición de datos en vistas no relacionadas
- **Estado:** ✅ DOCUMENTADO (pendiente implementar en todas las vistas)
- **Solución:** Carga lazy por ruta con Vue Router guards
- **Próximos pasos:** Implementar en vistas de Users, Inventory, Ventas, etc.

### 3. Intervalos no detenidos en error 401
- **Estado:** ✅ CORREGIDO
- **Solución:** Interceptor que limpia TODOS los intervalos
- **Commit:** [Fecha: 2025-10-16]

## 📝 Próximos Pasos (Recomendaciones)

1. **Implementar carga lazy en TODAS las vistas:**
   - Vista de Usuarios
   - Vista de Inventario
   - Vista de Ventas
   - Vista de Reportes
   - Vista de Configuración

2. **Crear composable de seguridad global:**
   ```javascript
   // composables/useSecurity.js
   export function useSecurity() {
     const stopAllPolling = () => {
       // Detener TODOS los composables activos
     }

     const verifySession = () => {
       // Verificar sesión activa
     }

     return { stopAllPolling, verifySession }
   }
   ```

3. **Auditoría de seguridad:**
   - Revisar TODOS los composables existentes
   - Identificar cuáles cargan datos automáticamente
   - Refactorizar para usar patrón lazy

4. **Testing de seguridad:**
   - Verificar que NO hay requests sin token
   - Verificar que polling se detiene al cerrar sesión
   - Verificar que datos se limpian al cambiar de vista

## 👤 Responsable

**Agente de Seguridad QA**
**Fecha de creación:** 2025-10-16
**Última actualización:** 2025-10-16

---

## 📚 Referencias

- [Vue Router Guards](https://router.vuejs.org/guide/advanced/navigation-guards.html)
- [Vue Composables Best Practices](https://vuejs.org/guide/reusability/composables.html)
- [OWASP - Sensitive Data Exposure](https://owasp.org/www-project-top-ten/)
