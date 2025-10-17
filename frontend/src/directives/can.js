/**
 * Directiva v-can para Vue 3
 *
 * Uso:
 * <button v-can="'productos.store'">Crear Producto</button>
 * <div v-can="'productos.update'">Editar</div>
 *
 * Funcionalidad:
 * - Oculta el elemento si el usuario NO tiene el permiso
 * - Verifica los permisos desde el store de Pinia (authStore)
 */

export default {
  mounted(el, binding) {
    const permission = binding.value;

    // Importar el store de autenticación (debe ajustarse según tu implementación)
    // Asumiendo que tienes un authStore con el usuario autenticado
    import('@/stores/auth').then((authModule) => {
      const authStore = authModule.useAuthStore();
      const user = authStore.user;

      if (!user) {
        // Si no hay usuario autenticado, ocultar el elemento
        el.style.display = 'none';
        return;
      }

      // Verificar si el usuario tiene el permiso
      const hasPermission = checkPermission(user, permission);

      if (!hasPermission) {
        // Si no tiene permiso, ocultar el elemento
        el.style.display = 'none';
      }
    });
  },

  updated(el, binding) {
    const permission = binding.value;

    import('@/stores/auth').then((authModule) => {
      const authStore = authModule.useAuthStore();
      const user = authStore.user;

      if (!user) {
        el.style.display = 'none';
        return;
      }

      const hasPermission = checkPermission(user, permission);

      if (!hasPermission) {
        el.style.display = 'none';
      } else {
        el.style.display = '';
      }
    });
  },
};

/**
 * Función auxiliar para verificar si el usuario tiene un permiso
 *
 * @param {Object} user - Usuario autenticado con sus permisos
 * @param {String} permission - Nombre del permiso a verificar
 * @returns {Boolean}
 */
function checkPermission(user, permission) {
  if (!user) return false;

  // SuperAdmin tiene todos los permisos
  if (user.roles && user.roles.some((role) => role.name === 'SuperAdmin')) {
    return true;
  }

  // Verificar si el usuario tiene el permiso específico
  if (user.permissions && Array.isArray(user.permissions)) {
    return user.permissions.some((perm) => perm.name === permission);
  }

  // Si el backend retorna permisos como array de strings
  if (Array.isArray(user.permissions)) {
    return user.permissions.includes(permission);
  }

  return false;
}
