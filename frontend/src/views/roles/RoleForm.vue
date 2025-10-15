<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ isEdit ? 'Editar Rol' : 'Crear Rol' }}
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            {{ isEdit ? 'Actualiza la información del rol' : 'Completa el formulario para crear un nuevo rol' }}
          </p>
        </div>
        <Button
          type="button"
          variant="outline"
          @click="handleCancel"
        >
          Volver
        </Button>
      </div>

      <!-- Loading -->
      <div v-if="loading && isEdit" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <form v-else @submit.prevent="handleSubmit" class="card">
        <div class="space-y-8">
          <!-- Sección: Información Básica -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información Básica
            </h3>
            <div class="max-w-md">
              <!-- Nombre del Rol -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Nombre del Rol *
                </label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="Ej: Gerente, Almacenista, etc."
                  :disabled="isSuperAdmin"
                />
                <p v-if="isSuperAdmin" class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                  El rol SuperAdmin no puede ser modificado
                </p>
              </div>
            </div>
          </div>

          <!-- Sección: Permisos -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Permisos del Rol
            </h3>

            <!-- Loading permisos -->
            <div v-if="loadingPermissions" class="flex justify-center items-center h-32">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>

            <!-- Grid de permisos agrupados por módulo -->
            <div v-else class="space-y-4">
              <!-- Selector global -->
              <div class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <Checkbox
                  :checked="allPermissionsSelected"
                  :indeterminate="somePermissionsSelected && !allPermissionsSelected"
                  @update:checked="toggleAllPermissions"
                />
                <label class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ allPermissionsSelected ? 'Desmarcar todos' : 'Seleccionar todos' }}
                  ({{ selectedPermissionsCount }} de {{ totalPermissionsCount }})
                </label>
              </div>

              <!-- Módulos de permisos -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="(modulePermissions, moduleName) in allPermissions"
                  :key="moduleName"
                  class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg space-y-3"
                >
                  <!-- Encabezado del módulo -->
                  <div class="flex items-center space-x-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                    <Checkbox
                      :checked="isModuleFullySelected(moduleName)"
                      :indeterminate="isModulePartiallySelected(moduleName)"
                      @update:checked="toggleModulePermissions(moduleName)"
                    />
                    <label class="text-sm font-semibold text-gray-900 dark:text-white capitalize">
                      {{ getModuleLabel(moduleName) }}
                    </label>
                  </div>

                  <!-- Permisos individuales -->
                  <div class="space-y-2">
                    <div
                      v-for="permission in modulePermissions"
                      :key="permission.id"
                      class="flex items-center space-x-2"
                    >
                      <Checkbox
                        :checked="form.permissions.includes(permission.name)"
                        @update:checked="togglePermission(permission.name)"
                      />
                      <label class="text-sm text-gray-700 dark:text-gray-300">
                        {{ getPermissionLabel(permission.name) }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <Button
              type="button"
              variant="outline"
              @click="handleCancel"
              :disabled="submitting"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              :disabled="submitting || loadingPermissions || isSuperAdmin"
            >
              {{ submitting ? (isEdit ? 'Actualizando...' : 'Creando...') : (isEdit ? 'Actualizar Rol' : 'Crear Rol') }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useRoles } from '@/composables/useRoles'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const {
  loading,
  fetchRole,
  fetchAllPermissions,
  createRole,
  updateRole,
  goToIndex,
} = useRoles()

const loadingPermissions = ref(false)
const submitting = ref(false)
const allPermissions = ref({})
const isEdit = computed(() => !!route.params.id)
const isSuperAdmin = computed(() => form.value.name === 'SuperAdmin')

const form = ref({
  name: '',
  permissions: [],
})

// Cargar permisos y rol (si es edición)
onMounted(async () => {
  // Cargar todos los permisos disponibles
  loadingPermissions.value = true
  try {
    allPermissions.value = await fetchAllPermissions()
  } catch (err) {
    console.error('Error al cargar permisos:', err)
  } finally {
    loadingPermissions.value = false
  }

  // Si es edición, cargar el rol
  if (isEdit.value) {
    try {
      const role = await fetchRole(route.params.id)
      form.value.name = role.name
      form.value.permissions = role.permissions.map(p => p.name)
    } catch (err) {
      console.error('Error al cargar rol:', err)
      goToIndex()
    }
  }
})

// Computed
const totalPermissionsCount = computed(() => {
  return Object.values(allPermissions.value).reduce((total, modulePerms) => {
    return total + modulePerms.length
  }, 0)
})

const selectedPermissionsCount = computed(() => {
  return form.value.permissions.length
})

const allPermissionsSelected = computed(() => {
  return selectedPermissionsCount.value === totalPermissionsCount.value
})

const somePermissionsSelected = computed(() => {
  return selectedPermissionsCount.value > 0 && !allPermissionsSelected.value
})

// Verificar si un módulo está completamente seleccionado
const isModuleFullySelected = (moduleName) => {
  const modulePerms = allPermissions.value[moduleName] || []
  return modulePerms.every(p => form.value.permissions.includes(p.name))
}

// Verificar si un módulo está parcialmente seleccionado
const isModulePartiallySelected = (moduleName) => {
  const modulePerms = allPermissions.value[moduleName] || []
  const selectedCount = modulePerms.filter(p => form.value.permissions.includes(p.name)).length
  return selectedCount > 0 && selectedCount < modulePerms.length
}

// Toggle de un permiso individual
const togglePermission = (permissionName) => {
  const index = form.value.permissions.indexOf(permissionName)
  if (index > -1) {
    form.value.permissions.splice(index, 1)
  } else {
    form.value.permissions.push(permissionName)
  }
}

// Toggle de todos los permisos de un módulo
const toggleModulePermissions = (moduleName) => {
  const modulePerms = allPermissions.value[moduleName] || []
  const isFullySelected = isModuleFullySelected(moduleName)

  if (isFullySelected) {
    // Desmarcar todos los permisos del módulo
    form.value.permissions = form.value.permissions.filter(p => {
      return !modulePerms.some(mp => mp.name === p)
    })
  } else {
    // Marcar todos los permisos del módulo
    const permissionNames = modulePerms.map(p => p.name)
    form.value.permissions = [
      ...new Set([...form.value.permissions, ...permissionNames])
    ]
  }
}

// Toggle de todos los permisos
const toggleAllPermissions = (checked) => {
  if (checked) {
    // Seleccionar todos
    const allPermNames = Object.values(allPermissions.value).flat().map(p => p.name)
    form.value.permissions = allPermNames
  } else {
    // Deseleccionar todos
    form.value.permissions = []
  }
}

// Labels humanizados
const getModuleLabel = (moduleName) => {
  const labels = {
    users: 'Usuarios',
    roles: 'Roles',
    permissions: 'Permisos',
    empresas: 'Empresas',
    sistemas: 'Sistemas',
    bodegas: 'Bodegas',
    inventarios: 'Inventarios',
    monedas: 'Monedas',
    taxes: 'Impuestos',
    galerias: 'Galerías',
    cotizaciones: 'Cotizaciones',
    ventas: 'Ventas',
    pedidos: 'Pedidos',
    sexes: 'Sexos',
    telefonos: 'Teléfonos',
    chatids: 'Chat IDs',
    rutas: 'Rutas API',
    settings: 'Configuraciones',
    reports: 'Reportes',
    empresa: 'Configuración Empresa',
    dashboard: 'Dashboard',
    profile: 'Perfil',
  }
  return labels[moduleName] || moduleName
}

const getPermissionLabel = (permissionName) => {
  const parts = permissionName.split('.')
  const action = parts[parts.length - 1]

  const labels = {
    index: 'Listar',
    show: 'Ver',
    store: 'Crear',
    update: 'Actualizar',
    destroy: 'Eliminar',
    view: 'Ver',
    edit: 'Editar',
  }

  return labels[action] || action
}

// Handlers
const handleSubmit = async () => {
  if (isSuperAdmin.value) return

  submitting.value = true
  try {
    const roleData = {
      name: form.value.name,
      permissions: form.value.permissions,
    }

    if (isEdit.value) {
      await updateRole(route.params.id, roleData)
    } else {
      await createRole(roleData)
    }

    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable
  } finally {
    submitting.value = false
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
