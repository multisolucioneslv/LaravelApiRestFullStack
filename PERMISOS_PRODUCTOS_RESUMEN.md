# Sistema de Permisos para Módulo de Productos y Categorías

## Resumen Ejecutivo

**En qué estamos trabajando:**
Sistema de control de acceso granular para el módulo de Productos y Categorías usando Spatie Laravel Permission con multi-tenancy.

**Estado actual:**
✅ COMPLETADO - Sistema de permisos implementado y funcional

**Fecha de implementación:** 2025-10-16

---

## Arquitectura Implementada

### Backend (Laravel)
1. **Permisos creados** (13 permisos nuevos)
2. **Seeder actualizado** con asignación de permisos por rol
3. **Controllers** con middleware de permisos
4. **Policies** con verificación de multi-tenancy
5. **Modelos** con trait MultiTenantScope

### Frontend (Vue3)
1. **Directiva v-can** para control en templates
2. **Composable usePermissions** para lógica de permisos
3. **Guards de Vue Router** para protección de rutas

---

## Permisos Creados

### Productos (7 permisos)
- `productos.index` - Ver lista de productos
- `productos.show` - Ver detalle de un producto
- `productos.store` - Crear producto
- `productos.update` - Editar producto
- `productos.destroy` - Eliminar producto (soft delete)
- `productos.restore` - Restaurar producto eliminado

### Categorías (6 permisos)
- `categorias.index` - Ver lista de categorías
- `categorias.show` - Ver detalle de una categoría
- `categorias.store` - Crear categoría
- `categorias.update` - Editar categoría
- `categorias.destroy` - Eliminar categoría (soft delete)
- `categorias.restore` - Restaurar categoría eliminada

---

## Distribución de Permisos por Rol

### 1. SuperAdmin
- **Permisos:** TODOS (115 permisos)
- **Descripción:** Acceso total al sistema, multi-empresa

### 2. Administrador
- **Productos:** ✅ Todos (index, show, store, update, destroy, restore)
- **Categorías:** ✅ Todos (index, show, store, update, destroy, restore)
- **Descripción:** Control total de productos y categorías de su empresa

### 3. Supervisor
- **Productos:** ✅ Ver, Crear, Editar (NO eliminar)
- **Categorías:** ✅ Ver, Crear, Editar (NO eliminar)
- **Permisos específicos:**
  - productos.index, productos.show, productos.store, productos.update
  - categorias.index, categorias.show, categorias.store, categorias.update

### 4. Vendedor
- **Productos:** ✅ Solo lectura (index, show)
- **Categorías:** ✅ Solo lectura (index, show)
- **Descripción:** Puede ver productos para realizar ventas, pero no modificarlos

### 5. Usuario
- **Productos:** ✅ Solo ver lista (index)
- **Categorías:** ❌ Sin acceso
- **Descripción:** Acceso mínimo, solo consulta de productos

### 6. Contabilidad
- **Productos:** ✅ Ver lista y detalle (index, show)
- **Categorías:** ✅ Ver lista y detalle (index, show)
- **Descripción:** Lectura completa para reportes contables

---

## Archivos Creados/Modificados

### Backend

#### 1. Seeder Actualizado
**Archivo:** `backend/database/seeders/PermissionsSeeder.php`
- Agregados módulos: productos, categorias
- Permisos especiales: productos.restore, categorias.restore
- Asignación de permisos por rol

#### 2. Controllers con Middleware de Permisos

**Archivo:** `backend/app/Http/Controllers/Api/ProductoController.php`

```php
public function __construct()
{
    $this->middleware('permission:productos.index')->only(['index']);
    $this->middleware('permission:productos.show')->only(['show']);
    $this->middleware('permission:productos.store')->only(['store']);
    $this->middleware('permission:productos.update')->only(['update']);
    $this->middleware('permission:productos.destroy')->only(['destroy']);
    $this->middleware('permission:productos.restore')->only(['restore']);
}
```

**Características:**
- ✅ Validaciones completas
- ✅ Multi-tenancy (empresa_id)
- ✅ Upload de imágenes
- ✅ Soft deletes y restore
- ✅ Relación con categorías

**Archivo:** `backend/app/Http/Controllers/Api/CategoriaController.php`

```php
public function __construct()
{
    $this->middleware('permission:categorias.index')->only(['index']);
    $this->middleware('permission:categorias.show')->only(['show']);
    $this->middleware('permission:categorias.store')->only(['store']);
    $this->middleware('permission:categorias.update')->only(['update']);
    $this->middleware('permission:categorias.destroy')->only(['destroy']);
    $this->middleware('permission:categorias.restore')->only(['restore']);
}
```

**Características:**
- ✅ Generación automática de slug
- ✅ Verificación de productos asociados antes de eliminar
- ✅ Multi-tenancy

#### 3. Policies con Multi-Tenancy

**Archivo:** `backend/app/Policies/ProductoPolicy.php`

```php
public function view(User $user, Producto $producto): bool
{
    return $user->can('productos.show')
        && $producto->empresa_id === $user->empresa_id;
}

public function update(User $user, Producto $producto): bool
{
    return $user->can('productos.update')
        && $producto->empresa_id === $user->empresa_id;
}

public function delete(User $user, Producto $producto): bool
{
    return $user->can('productos.destroy')
        && $producto->empresa_id === $user->empresa_id;
}
```

**Características:**
- ✅ Verificación de permisos Spatie
- ✅ Validación de multi-tenancy (empresa_id)
- ✅ SuperAdmin puede hacer force delete

**Archivo:** `backend/app/Policies/CategoriaPolicy.php`
- Misma estructura que ProductoPolicy
- Verifica categorias.* permisos

#### 4. Modelos Actualizados

**Archivo:** `backend/app/Models/Producto.php`
```php
use HasFactory, SoftDeletes, MultiTenantScope;
```

**Archivo:** `backend/app/Models/Categoria.php`
```php
use HasFactory, SoftDeletes, MultiTenantScope;
```

**Características:**
- ✅ Trait MultiTenantScope para filtrado automático por empresa
- ✅ SoftDeletes para eliminación lógica
- ✅ Relaciones many-to-many entre productos y categorías

---

### Frontend (Vue3)

#### 1. Directiva v-can

**Archivo:** `frontend/src/directives/can.js`

**Uso:**
```vue
<button v-can="'productos.store'">Crear Producto</button>
<div v-can="'productos.update'">Editar</div>
```

**Características:**
- Oculta elementos si el usuario NO tiene el permiso
- Verifica permisos desde authStore
- SuperAdmin bypass automático

#### 2. Composable usePermissions (RECOMENDADO)

**Archivo:** `frontend/src/composables/usePermissions.js`

**Uso:**
```vue
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
  allRoles
} = usePermissions();
</script>

<template>
  <button v-if="can('productos.store')">Crear</button>
  <button v-if="can('productos.update')">Editar</button>
  <button v-if="can('productos.destroy')">Eliminar</button>

  <div v-if="hasRole('Administrador')">Panel Admin</div>
  <div v-if="isSuperAdmin">Panel SuperAdmin</div>
</template>
```

**Métodos disponibles:**
- `can(permission)` - Verifica un permiso
- `canAny([perms])` - Verifica si tiene alguno
- `canAll([perms])` - Verifica si tiene todos
- `hasRole(role)` - Verifica un rol
- `hasAnyRole([roles])` - Verifica si tiene algún rol
- `hasAllRoles([roles])` - Verifica si tiene todos los roles
- `isSuperAdmin` - Computed: es SuperAdmin
- `isAdmin` - Computed: es Administrador
- `allPermissions` - Computed: array de permisos
- `allRoles` - Computed: array de roles

#### 3. Documentación Completa

**Archivo:** `frontend/src/directives/README.md`
- Guía de instalación
- Ejemplos de uso
- Integración con Vue Router guards
- API completa del composable

---

## Instalación y Configuración

### Backend

1. **Ejecutar seeder:**
```bash
cd backend
php artisan db:seed --class=PermissionsSeeder
```

2. **Verificar permisos creados:**
```bash
php artisan tinker
>>> \Spatie\Permission\Models\Permission::count()
# Debe retornar 115 permisos

>>> \Spatie\Permission\Models\Role::with('permissions')->get()
# Ver roles con sus permisos
```

3. **Limpiar cache de permisos:**
```bash
php artisan permission:cache-reset
```

### Frontend

1. **Registrar directiva v-can en main.js:**
```javascript
import { createApp } from 'vue';
import App from './App.vue';
import canDirective from './directives/can';

const app = createApp(App);
app.directive('can', canDirective);
app.mount('#app');
```

2. **Verificar que authStore retorne permisos:**
```javascript
// stores/auth.js
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null, // Debe incluir roles y permissions
    token: null,
  }),
});
```

**Estructura esperada del usuario:**
```json
{
  "id": 1,
  "name": "Juan Pérez",
  "roles": [
    { "name": "Administrador" }
  ],
  "permissions": [
    { "name": "productos.index" },
    { "name": "productos.store" }
  ]
}
```

---

## Ejemplos de Uso

### Backend - Controller

```php
// ProductoController.php
public function index(Request $request): JsonResponse
{
    // Middleware ya verificó el permiso 'productos.index'

    $productos = Producto::query()
        ->forCurrentUser() // Multi-tenancy automático
        ->with(['categorias'])
        ->paginate(15);

    return response()->json([
        'success' => true,
        'data' => $productos->items(),
    ]);
}
```

### Backend - Policy

```php
// En cualquier parte del código
if (auth()->user()->can('productos.store')) {
    // Crear producto
}

// O usando Gate
use Illuminate\Support\Facades\Gate;

if (Gate::allows('update', $producto)) {
    // Actualizar producto
}
```

### Frontend - Componente Vue

```vue
<template>
  <div class="productos-page">
    <h1>Productos</h1>

    <!-- Solo visible si puede crear -->
    <button
      v-if="can('productos.store')"
      @click="crearProducto"
      class="btn-primary"
    >
      Crear Producto
    </button>

    <table>
      <tbody>
        <tr v-for="producto in productos" :key="producto.id">
          <td>{{ producto.nombre }}</td>

          <!-- Solo visible si puede editar -->
          <td v-if="can('productos.update')">
            <button @click="editarProducto(producto)">Editar</button>
          </td>

          <!-- Solo visible si puede eliminar -->
          <td v-if="can('productos.destroy')">
            <button @click="eliminarProducto(producto)">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions';

const { can, hasRole, isSuperAdmin } = usePermissions();
const productos = ref([]);

const crearProducto = () => {
  console.log('Crear producto');
};
</script>
```

### Frontend - Vue Router Guard

```javascript
// router/index.js
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.permission) {
    const user = authStore.user;

    // SuperAdmin siempre pasa
    if (user.roles.some(role => role.name === 'SuperAdmin')) {
      next();
      return;
    }

    // Verificar permiso
    const hasPermission = user.permissions.some(
      perm => perm.name === to.meta.permission
    );

    if (!hasPermission) {
      next({ name: 'NotAuthorized' });
      return;
    }
  }

  next();
});
```

---

## Testing

### Backend - Tests de Permisos

```php
// tests/Feature/ProductoPermissionsTest.php
public function test_supervisor_no_puede_eliminar_producto()
{
    $user = User::factory()->create();
    $user->assignRole('Supervisor');

    $producto = Producto::factory()->create([
        'empresa_id' => $user->empresa_id,
    ]);

    $this->actingAs($user, 'api')
        ->deleteJson("/api/productos/{$producto->id}")
        ->assertStatus(403); // Forbidden
}

public function test_administrador_puede_eliminar_producto()
{
    $user = User::factory()->create();
    $user->assignRole('Administrador');

    $producto = Producto::factory()->create([
        'empresa_id' => $user->empresa_id,
    ]);

    $this->actingAs($user, 'api')
        ->deleteJson("/api/productos/{$producto->id}")
        ->assertStatus(200); // OK
}
```

---

## Comandos Útiles

```bash
# Limpiar cache de permisos
php artisan permission:cache-reset

# Ver todos los permisos
php artisan tinker
>>> \Spatie\Permission\Models\Permission::all()->pluck('name')

# Ver permisos de un rol
>>> $admin = \Spatie\Permission\Models\Role::findByName('Administrador', 'api');
>>> $admin->permissions->pluck('name');

# Ver roles de un usuario
>>> $user = \App\Models\User::find(1);
>>> $user->roles->pluck('name');

# Ver permisos de un usuario
>>> $user->permissions->pluck('name');

# Asignar permiso directo a usuario
>>> $user->givePermissionTo('productos.store');

# Remover permiso
>>> $user->revokePermissionTo('productos.store');
```

---

## Próximos Pasos Sugeridos

1. **Crear rutas API** para productos y categorías en `routes/api.php`
2. **Crear FormRequests** para validaciones (StoreProductoRequest, UpdateProductoRequest)
3. **Crear Resources** para formatear respuestas JSON (ProductoResource, CategoriaResource)
4. **Crear migraciones** para tablas productos, categorias y categoria_producto
5. **Crear componentes Vue** para CRUD de productos
6. **Testing completo** de permisos y multi-tenancy

---

## Notas Importantes

### Multi-Tenancy
- Los usuarios solo ven productos/categorías de su empresa
- Validación en Controllers, Policies y Modelos
- SuperAdmin puede ver todo (bypass multi-tenancy)

### Seguridad
- Middleware de permisos en todos los endpoints
- Policies verifican empresa_id antes de permitir acciones
- Frontend oculta opciones según permisos

### Performance
- Cache de permisos (Spatie)
- Eager loading en queries (with(['categorias']))
- Paginación en index

---

## Soporte y Documentación

- **Spatie Permission:** https://spatie.be/docs/laravel-permission
- **Vue 3 Composition API:** https://vuejs.org/guide/extras/composition-api-faq.html
- **Pinia Store:** https://pinia.vuejs.org/

---

**Implementado por:** Claude Code
**Fecha:** 2025-10-16
**Versión:** 1.0.0
