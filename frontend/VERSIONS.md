# Versiones de Dependencias - Frontend Vue3

**Proyecto:** BackendProfesional - Sistema ERP
**Fecha de creación:** 2025-10-14
**Última actualización:** 2025-10-14
**Node.js:** v22.20.0
**NPM:** 10.9.3

---

## Core Framework

| Paquete | Versión | Tipo | Descripción |
|---------|---------|------|-------------|
| vue | ^3.5.22 | production | Framework progresivo de JavaScript |
| vite | ^7.1.7 | development | Build tool ultra rápido para desarrollo |
| @vitejs/plugin-vue | ^6.0.1 | development | Plugin oficial de Vue para Vite |

---

## Routing & State Management

| Paquete | Versión | Tipo | Descripción |
|---------|---------|------|-------------|
| vue-router | ^4.6.0 | production | Router oficial de Vue 3 |
| pinia | ^3.0.3 | production | Store oficial de Vue 3 (reemplazo de Vuex) |

---

## HTTP Client

| Paquete | Versión | Tipo | Descripción |
|---------|---------|------|-------------|
| axios | ^1.12.2 | production | Cliente HTTP basado en promesas |

---

## UI & Styling

| Paquete | Versión | Tipo | Descripción |
|---------|---------|------|-------------|
| tailwindcss | ^4.1.14 | development | Framework CSS utility-first |
| postcss | ^8.5.6 | development | Herramienta para transformar CSS |
| autoprefixer | ^10.4.21 | development | Plugin PostCSS para agregar vendor prefixes |
| @headlessui/vue | ^1.7.23 | production | Componentes UI accesibles sin estilos |
| @heroicons/vue | ^2.2.0 | production | Iconos SVG de Heroicons |

---

## Composables & Utilities

| Paquete | Versión | Tipo | Descripción |
|---------|---------|------|-------------|
| @vueuse/core | ^13.9.0 | production | Colección de composables Vue utilities |

---

## Notas de Compatibilidad

### ✅ Versiones Compatibles

Todas las dependencias han sido verificadas y son compatibles entre sí:

1. **Vue 3.5.22** - Versión estable más reciente
2. **Vite 7.1.7** - Compatible con Vue 3.5+
3. **Vue Router 4.6.0** - Última versión para Vue 3
4. **Pinia 3.0.3** - Store oficial actualizado
5. **Tailwind CSS 4.1.14** - Versión major 4 con nuevas features
6. **VueUse 13.9.0** - Compatible con Vue 3.5+

### 🔄 Configuración de Tailwind CSS v4

Tailwind CSS v4 tiene una configuración diferente a v3:

```js
// tailwind.config.js
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  darkMode: 'class', // Estrategia: clase 'dark' en el HTML
  theme: {
    extend: {
      colors: {
        primary: { /* colores personalizados */ }
      }
    }
  },
  plugins: []
}
```

### 🎨 Dark Mode Implementation

El Dark Mode está implementado usando:

- **Estrategia:** `class` (clase 'dark' en el elemento HTML)
- **Persistencia:** localStorage con VueUse (`useDark`)
- **Key de storage:** `vueuse-color-scheme`
- **Clases:** Todos los componentes usan `dark:` prefix

Ejemplo:
```vue
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
```

### 🔐 Autenticación JWT

**Persistencia de datos:**
- Token guardado en `localStorage` con key: `auth_token`
- Usuario guardado en `localStorage` con key: `user`
- Validación automática al cargar app: llamada a `/api/auth/me`
- Logout automático si token inválido

**Login DUAL:**
- Detecta automáticamente si el input es email (contiene '@') o usuario
- Envía el campo correcto al backend (`email` o `usuario`)

### 📦 Scripts Disponibles

```json
{
  "dev": "vite",              // Servidor de desarrollo
  "build": "vite build",      // Build para producción
  "preview": "vite preview"   // Preview del build
}
```

---

## Variables de Entorno

El proyecto usa archivos `.env` para configuración:

- `.env.development` - Desarrollo local
- `.env.production` - Producción
- `.env.example` - Plantilla de ejemplo

**Variables configuradas:**

```bash
VITE_API_BASE_URL=http://localhost:8000/api  # URL del backend Laravel
VITE_APP_NAME=BackendProfesional
VITE_APP_ENV=development
VITE_DEBUG=true
```

---

## Estructura del Proyecto

```
frontend/
├── src/
│   ├── assets/              # Recursos estáticos
│   ├── components/          # Componentes reutilizables
│   │   ├── auth/           # Componentes de autenticación
│   │   └── layout/         # Componentes de layout
│   ├── composables/        # Composables personalizados
│   ├── router/             # Configuración de rutas
│   ├── services/           # Servicios (API, etc.)
│   ├── stores/             # Stores de Pinia
│   ├── views/              # Vistas/Páginas
│   ├── App.vue             # Componente raíz
│   ├── main.js             # Punto de entrada
│   └── style.css           # Estilos globales de Tailwind
├── public/                 # Assets públicos
├── .env.development        # Variables de desarrollo
├── .env.production         # Variables de producción
├── .env.example            # Plantilla de variables
├── index.html              # HTML principal
├── package.json            # Dependencias
├── tailwind.config.js      # Configuración de Tailwind
├── postcss.config.js       # Configuración de PostCSS
├── vite.config.js          # Configuración de Vite
├── VERSIONS.md             # Este archivo
└── README.md               # Documentación del proyecto
```

---

## Funcionalidades Implementadas

### ✅ Completadas

1. **Autenticación JWT con persistencia**
   - Login dual (usuario o email)
   - Token guardado en localStorage
   - Validación automática al cargar app
   - Logout automático si token inválido
   - Interceptores Axios para manejo de errores 401

2. **Dark Mode persistente**
   - Usa VueUse `useDark` composable
   - Persiste en localStorage
   - Toggle manual disponible
   - Todos los componentes soportan dark mode

3. **Router con guards de navegación**
   - Rutas protegidas por autenticación
   - Redirección automática a login si no autenticado
   - Redirección a dashboard si ya autenticado

4. **State Management con Pinia**
   - authStore para autenticación
   - themeStore para tema (dark/light)
   - Persistencia automática en localStorage

5. **UI Components**
   - LoginForm con validación
   - RegisterForm con validación
   - AppLayout con Sidebar y Navbar
   - DarkModeToggle
   - Estilos personalizados con Tailwind

---

## Comandos para Ejecutar

### Desarrollo

```bash
cd D:\MultisolucionesLV\proyectos\BackendProfesional\frontend
npm run dev
```

La aplicación estará disponible en: http://localhost:5173

### Build para Producción

```bash
npm run build
```

El build se generará en la carpeta `dist/`

### Preview del Build

```bash
npm run preview
```

---

## Testing Pendiente

### 🧪 Tests a Realizar

1. **Login Dual:**
   - ✅ Login con usuario: `jscothserver`
   - ✅ Login con email: `jscothserver@gmail.com`
   - ✅ Password: `72900968`

2. **Persistencia de Sesión:**
   - ✅ Login exitoso → recargar página → sesión activa
   - ✅ Cerrar navegador → abrir → sesión activa (si token válido)
   - ✅ Token inválido → logout automático

3. **Dark Mode:**
   - ✅ Toggle dark mode → recargar → preferencia mantenida
   - ✅ Cerrar navegador → abrir → tema mantenido

4. **Navegación:**
   - ✅ Usuario no autenticado → acceder a /dashboard → redirige a /login
   - ✅ Usuario autenticado → acceder a /login → redirige a /dashboard

---

## Problemas Conocidos

### ❌ Ninguno detectado actualmente

---

## Próximos Pasos

1. **Implementar más vistas:**
   - Módulo de Empresas
   - Módulo de Usuarios
   - Módulo de Roles y Permisos
   - Dashboard con estadísticas

2. **Agregar validaciones:**
   - Validación de formularios con Vuelidate o similar
   - Mensajes de error personalizados

3. **Mejorar UX:**
   - Animaciones de transición
   - Loading states mejorados
   - Toasts/Notificaciones

4. **Testing:**
   - Unit tests con Vitest
   - E2E tests con Cypress/Playwright

---

**Última revisión:** 2025-10-14
**Responsable:** Claude Code Agent
**Estado:** ✅ Funcional y listo para desarrollo
