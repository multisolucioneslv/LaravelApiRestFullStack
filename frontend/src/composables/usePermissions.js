/**
 * Composable usePermissions para Vue 3 Composition API
 *
 * Uso en componentes:
 *
 * <script setup>
 * import { usePermissions } from '@/composables/usePermissions';
 *
 * const { can, hasRole, hasAnyRole, hasAllRoles } = usePermissions();
 * </script>
 *
 * <template>
 *   <button v-if="can('productos.store')">Crear Producto</button>
 *   <div v-if="hasRole('Administrador')">Panel Admin</div>
 * </template>
 */

import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export function usePermissions() {
  const authStore = useAuthStore();

  /**
   * Usuario autenticado desde el store
   */
  const user = computed(() => authStore.user);

  /**
   * Verificar si el usuario tiene un permiso específico
   *
   * @param {String} permission - Nombre del permiso (ej: 'productos.store')
   * @returns {Boolean}
   */
  const can = (permission) => {
    if (!user.value) return false;

    // SuperAdmin tiene todos los permisos
    if (hasRole('SuperAdmin')) {
      return true;
    }

    // Verificar si el usuario tiene el permiso
    if (user.value.permissions && Array.isArray(user.value.permissions)) {
      // Si permissions es array de objetos: [{ name: 'productos.store' }]
      if (typeof user.value.permissions[0] === 'object') {
        return user.value.permissions.some((perm) => perm.name === permission);
      }

      // Si permissions es array de strings: ['productos.store', 'productos.index']
      return user.value.permissions.includes(permission);
    }

    return false;
  };

  /**
   * Verificar si el usuario tiene cualquiera de los permisos
   *
   * @param {Array<String>} permissions - Array de permisos
   * @returns {Boolean}
   */
  const canAny = (permissions) => {
    if (!Array.isArray(permissions)) return false;

    return permissions.some((permission) => can(permission));
  };

  /**
   * Verificar si el usuario tiene todos los permisos
   *
   * @param {Array<String>} permissions - Array de permisos
   * @returns {Boolean}
   */
  const canAll = (permissions) => {
    if (!Array.isArray(permissions)) return false;

    return permissions.every((permission) => can(permission));
  };

  /**
   * Verificar si el usuario tiene un rol específico
   *
   * @param {String} roleName - Nombre del rol (ej: 'Administrador')
   * @returns {Boolean}
   */
  const hasRole = (roleName) => {
    if (!user.value) return false;

    if (user.value.roles && Array.isArray(user.value.roles)) {
      // Si roles es array de objetos: [{ name: 'Administrador' }]
      if (typeof user.value.roles[0] === 'object') {
        return user.value.roles.some((role) => role.name === roleName);
      }

      // Si roles es array de strings: ['Administrador', 'Vendedor']
      return user.value.roles.includes(roleName);
    }

    return false;
  };

  /**
   * Verificar si el usuario tiene cualquiera de los roles
   *
   * @param {Array<String>} roles - Array de roles
   * @returns {Boolean}
   */
  const hasAnyRole = (roles) => {
    if (!Array.isArray(roles)) return false;

    return roles.some((role) => hasRole(role));
  };

  /**
   * Verificar si el usuario tiene todos los roles
   *
   * @param {Array<String>} roles - Array de roles
   * @returns {Boolean}
   */
  const hasAllRoles = (roles) => {
    if (!Array.isArray(roles)) return false;

    return roles.every((role) => hasRole(role));
  };

  /**
   * Verificar si el usuario es SuperAdmin
   *
   * @returns {Boolean}
   */
  const isSuperAdmin = computed(() => hasRole('SuperAdmin'));

  /**
   * Verificar si el usuario es Administrador
   *
   * @returns {Boolean}
   */
  const isAdmin = computed(() => hasRole('Administrador'));

  /**
   * Obtener todos los permisos del usuario
   *
   * @returns {Array<String>}
   */
  const allPermissions = computed(() => {
    if (!user.value || !user.value.permissions) return [];

    if (typeof user.value.permissions[0] === 'object') {
      return user.value.permissions.map((perm) => perm.name);
    }

    return user.value.permissions;
  });

  /**
   * Obtener todos los roles del usuario
   *
   * @returns {Array<String>}
   */
  const allRoles = computed(() => {
    if (!user.value || !user.value.roles) return [];

    if (typeof user.value.roles[0] === 'object') {
      return user.value.roles.map((role) => role.name);
    }

    return user.value.roles;
  });

  return {
    // Métodos de permisos
    can,
    canAny,
    canAll,

    // Métodos de roles
    hasRole,
    hasAnyRole,
    hasAllRoles,

    // Helpers
    isSuperAdmin,
    isAdmin,

    // Datos computados
    allPermissions,
    allRoles,
  };
}
