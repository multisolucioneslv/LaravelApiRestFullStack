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

## 🛍️ Módulo de Productos y Categorías

Sistema completo de gestión de inventario implementado con Vue 3 Composition API.

### Componentes Implementados

**Componentes de Productos:**
- `ProductosList.vue` - Vista completa con grid/table toggle
- `ProductosTable.vue` - Tabla responsive con acciones inline
- `ProductoCard.vue` - Card individual para vista en cuadrícula
- `ProductoFilters.vue` - Panel de filtros avanzados
- `StockBadge.vue` - Indicador visual de estado de stock

**Ubicación:** `src/components/productos/`

### Stores (Pinia)

**useProductosStore:**
```javascript
// State
productos: []        // Array de productos
loading: false       // Estado de carga
pagination: {}       // Datos de paginación
filters: {}          // Filtros aplicados

// Actions
fetchProductos(page)      // Listar con paginación
fetchProducto(id)         // Ver detalle
createProducto(data)      // Crear (FormData)
updateProducto(id, data)  // Actualizar (FormData)
deleteProducto(id)        // Eliminar (soft delete)
restoreProducto(id)       // Restaurar
updateStock(id, cantidad, tipo) // Ajustar stock
```

**useCategoriasStore:**
```javascript
// State
categorias: []            // Array de categorías
categoriasOptions: []     // Para selects

// Actions
fetchAllCategorias()      // Todas activas
fetchCategorias(page)     // Con paginación
```

### Características Principales

✅ **Vista Dual:** Toggle entre Grid (cards) y Table (tabla)
✅ **Búsqueda Avanzada:** Por nombre, SKU, código de barras (con debounce)
✅ **Filtros:**
  - Por categoría
  - Por rango de precio (mín/máx)
  - Por estado (activo/inactivo)
  - Por stock mínimo

✅ **Gestión de Stock:**
  - Badge visual con 4 estados (sin stock, bajo, medio, alto)
  - Actualización rápida desde tabla
  - Alertas visuales de stock bajo

✅ **Imágenes:**
  - Upload con preview
  - Placeholder si no hay imagen
  - Manejo de errores de carga

✅ **Responsive:**
  - Mobile-first design
  - Grid adaptativo (1-4 columnas según viewport)
  - Tabla con scroll horizontal en móviles

### Rutas

```javascript
{
  path: '/productos',
  component: ProductosView,
  children: [
    { path: '', name: 'productos.index' },       // Listado
    { path: 'create', name: 'productos.create' }, // Crear
    { path: ':id', name: 'productos.detail' },    // Ver
    { path: ':id/edit', name: 'productos.edit' }  // Editar
  ]
}
```

### Permisos Requeridos

- `productos.index` - Ver listado
- `productos.show` - Ver detalles
- `productos.store` - Crear productos
- `productos.update` - Actualizar productos
- `productos.destroy` - Eliminar productos
- `productos.restore` - Restaurar productos
- `productos.stock` - Gestionar stock

### Ejemplo de Uso

```vue
<template>
  <div class="container mx-auto p-6">
    <!-- Lista completa con filtros, paginación y acciones -->
    <ProductosList />
  </div>
</template>

<script setup>
import ProductosList from '@/components/productos/ProductosList.vue'
</script>
```

### Documentación Detallada

- **Componentes:** `frontend/docs/PRODUCTOS_COMPONENTES.md`
- **API:** `backend/docs/api/PRODUCTOS_API.md`
- **Guía de Usuario:** `docs/PRODUCTOS_GUIA_USUARIO.md`

---

## 📞 Soporte

**Usuario:** jscothserver
**Email:** jscothserver@gmail.com
**Telegram:** @Multisolucioneslv_bot

---

**Última actualización:** 2025-10-16
**Estado:** ✅ Funcional y listo para desarrollo
