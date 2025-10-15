# Frontend - BackendProfesional

Sistema ERP desarrollado con **Vue 3 + Vite + Tailwind CSS + Dark Mode**

---

## 📋 Información General

**Proyecto:** BackendProfesional - Sistema ERP
**Framework:** Vue 3 (Composition API)
**Build Tool:** Vite 7
**Estilos:** Tailwind CSS v4
**State Management:** Pinia
**Router:** Vue Router 4
**HTTP Client:** Axios
**Fecha:** 2025-10-14

---

## 🚀 Inicio Rápido

### Prerrequisitos

- Node.js v22.20.0 o superior
- NPM 10.9.3 o superior
- Backend Laravel ejecutándose en `http://localhost:8000`

### Instalación y Ejecución

```bash
# Navegar a la carpeta del frontend
cd D:\MultisolucionesLV\proyectos\BackendProfesional\frontend

# Instalar dependencias (si no están instaladas)
npm install

# Ejecutar servidor de desarrollo
npm run dev
```

La aplicación estará disponible en: **http://localhost:5173**

---

## 🔧 Comandos Disponibles

```bash
# Desarrollo - Inicia servidor con hot-reload
npm run dev

# Build - Genera versión optimizada para producción
npm run build

# Preview - Previsualiza el build de producción
npm run preview
```

---

## 🔐 Autenticación - Login Dual

El sistema soporta login con **usuario** O **email**:

**Opción 1 - Login con Usuario:**
```
Usuario: jscothserver
Password: 72900968
```

**Opción 2 - Login con Email:**
```
Email: jscothserver@gmail.com
Password: 72900968
```

### Persistencia de Sesión

- ✅ Token JWT guardado en `localStorage`
- ✅ Datos del usuario guardados en `localStorage`
- ✅ Al recargar la página, la sesión se mantiene activa
- ✅ Validación automática del token con `/api/auth/me`
- ✅ Logout automático si el token es inválido

---

## 🌓 Dark Mode

- ✅ Toggle manual con botón
- ✅ Persistencia en `localStorage`
- ✅ Todos los componentes soportan modo oscuro
- ✅ Usa VueUse `useDark` composable

---

## 📂 Estructura del Proyecto

```
frontend/
├── src/
│   ├── components/         # Componentes reutilizables
│   │   ├── auth/          # LoginForm, RegisterForm
│   │   └── layout/        # AppLayout, Navbar, Sidebar
│   ├── composables/       # useDarkMode
│   ├── router/            # Configuración de rutas
│   ├── services/          # API service con Axios
│   ├── stores/            # Pinia stores (auth, theme)
│   ├── views/             # Vistas/Páginas
│   ├── App.vue            # Componente raíz
│   └── main.js            # Punto de entrada
├── .env.development       # Variables de desarrollo
├── VERSIONS.md            # Documentación de versiones
└── README.md              # Este archivo
```

---

## 📚 Documentación Adicional

- Ver `VERSIONS.md` para información detallada de versiones y compatibilidad
- Ver `.env.example` para configuración de variables de entorno

---

## 📞 Soporte

**Usuario:** jscothserver
**Email:** jscothserver@gmail.com
**Telegram:** @Multisolucioneslv_bot

---

**Última actualización:** 2025-10-14
**Estado:** ✅ Funcional y listo para desarrollo
