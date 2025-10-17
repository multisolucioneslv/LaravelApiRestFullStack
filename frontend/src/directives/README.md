# Directivas y Composables de Permisos

## Instalación

### 1. Registrar la directiva v-can en main.js

```javascript
import { createApp } from 'vue';
import App from './App.vue';
import canDirective from './directives/can';

const app = createApp(App);

// Registrar directiva global v-can
app.directive('can', canDirective);

app.mount('#app');
```

### 2. Asegurar que el authStore tenga permisos y roles

Tu `stores/auth.js` debe retornar el usuario con esta estructura:

```javascript
// stores/auth.js
import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(credentials) {
      const response = await axios.post('/api/auth/login', credentials);

      this.user = response.data.user; // Debe incluir roles y permissions
      this.token = response.data.token;

      // Guardar en localStorage
      localStorage.setItem('token', this.token);
      localStorage.setItem('user', JSON.stringify(this.user));
    },

    async fetchUser() {
      const response = await axios.get('/api/auth/me');
      this.user = response.data.data; // Usuario con roles y permissions
    },

    logout() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    },
  },
});
```

**Estructura esperada del usuario:**

```json
{
  "id": 1,
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "roles": [
    { "id": 1, "name": "Administrador" }
  ],
  "permissions": [
    { "id": 1, "name": "productos.index" },
    { "id": 2, "name": "productos.store" },
    { "id": 3, "name": "productos.update" },
    { "id": 4, "name": "productos.destroy" }
  ]
}
```

---

## Uso

### 1. Usando la directiva v-can

```vue
<template>
  <!-- Mostrar solo si el usuario tiene el permiso -->
  <button v-can="'productos.store'" @click="crearProducto">
    Crear Producto
  </button>

  <button v-can="'productos.update'" @click="editarProducto">
    Editar
  </button>

  <button v-can="'productos.destroy'" @click="eliminarProducto">
    Eliminar
  </button>
</template>

<script setup>
const crearProducto = () => {
  console.log('Creando producto...');
};
</script>
```

---

### 2. Usando el composable usePermissions (RECOMENDADO)

```vue
<template>
  <div class="productos-page">
    <!-- Verificar permisos con v-if -->
    <button v-if="can('productos.store')" @click="crearProducto">
      Crear Producto
    </button>

    <button v-if="can('productos.update')" @click="editarProducto">
      Editar
    </button>

    <button v-if="can('productos.destroy')" @click="eliminarProducto">
      Eliminar
    </button>

    <!-- Mostrar panel solo si es Administrador -->
    <div v-if="hasRole('Administrador')" class="admin-panel">
      Panel de Administrador
    </div>

    <!-- Mostrar si es SuperAdmin -->
    <div v-if="isSuperAdmin" class="superadmin-panel">
      Panel de SuperAdmin
    </div>

    <!-- Mostrar si tiene cualquiera de estos permisos -->
    <button v-if="canAny(['productos.store', 'productos.update'])">
      Crear o Editar
    </button>

    <!-- Mostrar solo si tiene ambos permisos -->
    <button v-if="canAll(['productos.store', 'productos.destroy'])">
      Crear y Eliminar
    </button>
  </div>
</template>

<script setup>
import { usePermissions } from '@/composables/usePermissions';

const {
  can,
  canAny,
  canAll,
  hasRole,
  hasAnyRole,
  isSuperAdmin,
  isAdmin,
  allPermissions,
  allRoles,
} = usePermissions();

const crearProducto = () => {
  // Verificar permiso programáticamente
  if (can('productos.store')) {
    console.log('Creando producto...');
  }
};

const editarProducto = () => {
  console.log('Editando producto...');
};

const eliminarProducto = () => {
  console.log('Eliminando producto...');
};

// Mostrar todos los permisos del usuario en consola
console.log('Permisos del usuario:', allPermissions.value);
console.log('Roles del usuario:', allRoles.value);
</script>
```

---

### 3. Verificar permisos en guards de Vue Router

```javascript
// router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/productos',
    name: 'ProductosList',
    component: () => import('@/views/Productos/Index.vue'),
    meta: { requiresAuth: true, permission: 'productos.index' },
  },
  {
    path: '/productos/crear',
    name: 'ProductosCreate',
    component: () => import('@/views/Productos/Create.vue'),
    meta: { requiresAuth: true, permission: 'productos.store' },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Guard global
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'Login' });
    return;
  }

  // Verificar permiso
  if (to.meta.permission) {
    const user = authStore.user;

    // SuperAdmin pasa siempre
    if (user.roles.some((role) => role.name === 'SuperAdmin')) {
      next();
      return;
    }

    // Verificar permiso
    const hasPermission = user.permissions.some(
      (perm) => perm.name === to.meta.permission
    );

    if (!hasPermission) {
      next({ name: 'NotAuthorized' }); // Ruta 403
      return;
    }
  }

  next();
});

export default router;
```

---

## API - Métodos Disponibles

### usePermissions()

| Método | Parámetro | Retorna | Descripción |
|--------|-----------|---------|-------------|
| `can(permission)` | String | Boolean | Verifica si tiene un permiso |
| `canAny(permissions)` | Array | Boolean | Verifica si tiene cualquier permiso |
| `canAll(permissions)` | Array | Boolean | Verifica si tiene todos los permisos |
| `hasRole(role)` | String | Boolean | Verifica si tiene un rol |
| `hasAnyRole(roles)` | Array | Boolean | Verifica si tiene cualquier rol |
| `hasAllRoles(roles)` | Array | Boolean | Verifica si tiene todos los roles |
| `isSuperAdmin` | - | Boolean | Es SuperAdmin |
| `isAdmin` | - | Boolean | Es Administrador |
| `allPermissions` | - | Array | Todos los permisos |
| `allRoles` | - | Array | Todos los roles |

---

## Permisos Disponibles para Productos

- `productos.index` - Ver lista de productos
- `productos.show` - Ver detalle de un producto
- `productos.store` - Crear producto
- `productos.update` - Editar producto
- `productos.destroy` - Eliminar producto
- `productos.restore` - Restaurar producto eliminado

## Permisos Disponibles para Categorías

- `categorias.index` - Ver lista de categorías
- `categorias.show` - Ver detalle de una categoría
- `categorias.store` - Crear categoría
- `categorias.update` - Editar categoría
- `categorias.destroy` - Eliminar categoría
- `categorias.restore` - Restaurar categoría eliminada

---

## Roles Predeterminados

1. **SuperAdmin** - Todos los permisos
2. **Administrador** - Todos los permisos de productos y categorías
3. **Supervisor** - Ver, crear, editar (NO eliminar)
4. **Vendedor** - Solo lectura (index, show)
5. **Usuario** - Solo ver lista (index)
6. **Contabilidad** - Ver lista y detalle (index, show)
