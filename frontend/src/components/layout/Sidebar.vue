<template>
  <aside
    :class="[
      'bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 min-h-screen transition-all duration-300',
      isCollapsed ? 'w-20' : 'w-64'
    ]"
  >
    <!-- Header con Logo/Nombre de Empresa y botón de colapsar -->
    <div class="border-b border-gray-200 dark:border-gray-700 p-4">
      <div class="flex items-center justify-between">
        <!-- SI HAY LOGO: mostrar SOLO logo -->
        <div v-if="empresaLogo" class="flex items-center flex-1 min-w-0">
          <img
            :src="empresaLogo"
            :alt="empresaNombre"
            :class="[
              'object-contain',
              isCollapsed ? 'w-10 h-10' : 'h-10 max-w-full'
            ]"
          />
        </div>

        <!-- SI NO HAY LOGO: mostrar nombre completo o iniciales -->
        <div v-else class="flex items-center flex-1 min-w-0">
          <!-- Cuando está colapsado: mostrar iniciales -->
          <div
            v-if="isCollapsed"
            class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary-600 dark:bg-primary-500 text-white font-bold text-sm"
          >
            {{ empresaIniciales }}
          </div>

          <!-- Cuando está expandido: mostrar nombre completo -->
          <h2
            v-else
            class="text-base font-semibold text-gray-900 dark:text-white truncate"
          >
            {{ empresaNombre }}
          </h2>
        </div>

        <!-- Botón de colapsar/expandir -->
        <button
          @click="toggleSidebar"
          class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex-shrink-0"
          :title="isCollapsed ? 'Expandir menú' : 'Colapsar menú'"
        >
          <Bars3Icon v-if="isCollapsed" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
          <ChevronLeftIcon v-else class="w-5 h-5 text-gray-600 dark:text-gray-300" />
        </button>
      </div>
    </div>

    <!-- Contenido del menú -->
    <div :class="isCollapsed ? 'p-2' : 'p-6'">

      <!-- Lista de navegación -->
      <nav class="space-y-1">
        <!-- Dashboard (sin dropdown) -->
        <router-link
          to="/dashboard"
          :class="[
            'flex items-center rounded-lg transition-colors duration-200',
            isCollapsed ? 'justify-center p-3' : 'space-x-3 px-4 py-3',
            isActive('/dashboard')
              ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          :title="isCollapsed ? 'Dashboard' : ''"
        >
          <HomeIcon class="w-5 h-5 flex-shrink-0" />
          <span v-if="!isCollapsed" class="font-medium">Dashboard</span>
        </router-link>

        <!-- Documentación (sin dropdown) -->
        <router-link
          to="/docs"
          :class="[
            'flex items-center rounded-lg transition-colors duration-200',
            isCollapsed ? 'justify-center p-3' : 'space-x-3 px-4 py-3',
            isActive('/docs')
              ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
          :title="isCollapsed ? 'Documentación' : ''"
        >
          <BookOpenIcon class="w-5 h-5 flex-shrink-0" />
          <span v-if="!isCollapsed" class="font-medium">Documentación</span>
        </router-link>

        <!-- Grupos con dropdown -->
        <div v-for="group in menuGroups" :key="group.name" class="space-y-1">
          <!-- Encabezado del grupo -->
          <button
            v-if="!isCollapsed"
            @click="toggleGroup(group.name)"
            class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition-colors duration-200 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <div class="flex items-center space-x-3">
              <component :is="group.icon" class="w-5 h-5 flex-shrink-0" />
              <span class="font-medium">{{ group.label }}</span>
            </div>
            <ChevronDownIcon
              class="w-4 h-4 transition-transform duration-200 flex-shrink-0"
              :class="{ 'transform rotate-180': openGroups[group.name] }"
            />
          </button>

          <!-- Cuando está colapsado, mostrar solo el ícono del grupo como indicador -->
          <div
            v-else
            :title="group.label"
            class="flex items-center justify-center p-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
          >
            <component :is="group.icon" class="w-5 h-5" />
          </div>

          <!-- Items del grupo (colapsable) - ocultos cuando sidebar está colapsado -->
          <div
            v-if="!isCollapsed"
            v-show="openGroups[group.name]"
            class="ml-4 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-2"
          >
            <router-link
              v-for="item in group.items"
              :key="item.name"
              :to="item.to"
              class="flex items-center space-x-3 px-4 py-2 rounded-lg transition-colors duration-200 text-sm"
              :class="[
                isActive(item.to)
                  ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400'
                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
              ]"
            >
              <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </div>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useSidebar } from '@/composables/useSidebar'
import {
  HomeIcon,
  UsersIcon,
  Cog6ToothIcon,
  ChartBarIcon,
  PhoneIcon,
  ChatBubbleLeftRightIcon,
  CurrencyDollarIcon,
  BuildingStorefrontIcon,
  ArchiveBoxIcon,
  BuildingOfficeIcon,
  ReceiptPercentIcon,
  PhotoIcon,
  DocumentTextIcon,
  ShoppingCartIcon,
  ClipboardDocumentListIcon,
  ArrowPathIcon,
  WrenchScrewdriverIcon,
  AdjustmentsHorizontalIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  Bars3Icon,
  TagIcon,
  BriefcaseIcon,
  ShieldCheckIcon,
  BookOpenIcon,
  CubeIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()
const { isCollapsed, toggle: toggleSidebar } = useSidebar()

// Datos de la empresa
const empresaLogo = computed(() => {
  const logo = authStore.user?.empresa?.logo
  // Si el logo existe y no está vacío, devolverlo
  if (logo && logo.trim() !== '') {
    // Si el logo es una URL completa, usarla directamente
    if (logo.startsWith('http')) {
      return logo
    }
    // Si es una ruta relativa, construir la URL completa
    return `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}${logo}`
  }
  return null
})

const empresaNombre = computed(() => {
  return authStore.user?.empresa?.nombre || 'Mi Empresa'
})

const empresaIniciales = computed(() => {
  const nombre = empresaNombre.value
  // Obtener las primeras 2 letras de las primeras 2 palabras
  const palabras = nombre.split(' ')
  if (palabras.length >= 2) {
    return (palabras[0][0] + palabras[1][0]).toUpperCase()
  }
  // Si solo hay una palabra, tomar las primeras 2 letras
  return nombre.substring(0, 2).toUpperCase()
})

// Estado de grupos abiertos/cerrados (persistente en localStorage)
const openGroups = reactive({
  operaciones: false,
  inventario: false,
  productos: false,
  empresas: false,
  usuarios: false,
  catalogos: false,
  administracion: false,
  reportes: false,
})

// Grupos del menú
const menuGroups = [
  {
    name: 'operaciones',
    label: 'Operaciones',
    icon: BriefcaseIcon,
    items: [
      {
        name: 'cotizaciones',
        label: 'Cotizaciones',
        to: '/cotizaciones',
        icon: DocumentTextIcon,
      },
      {
        name: 'ventas',
        label: 'Ventas',
        to: '/ventas',
        icon: ShoppingCartIcon,
      },
      {
        name: 'pedidos',
        label: 'Pedidos',
        to: '/pedidos',
        icon: ClipboardDocumentListIcon,
      },
    ],
  },
  {
    name: 'inventario',
    label: 'Inventario',
    icon: ArchiveBoxIcon,
    items: [
      {
        name: 'bodegas',
        label: 'Bodegas',
        to: '/bodegas',
        icon: BuildingStorefrontIcon,
      },
      {
        name: 'inventarios',
        label: 'Inventarios',
        to: '/inventarios',
        icon: ArchiveBoxIcon,
      },
      {
        name: 'galerias',
        label: 'Galerías',
        to: '/galerias',
        icon: PhotoIcon,
      },
    ],
  },
  {
    name: 'productos',
    label: 'Productos',
    icon: CubeIcon,
    items: [
      {
        name: 'productos',
        label: 'Productos',
        to: '/productos',
        icon: CubeIcon,
      },
      {
        name: 'categorias',
        label: 'Categorías',
        to: '/categorias',
        icon: TagIcon,
      },
    ],
  },
  {
    name: 'empresas',
    label: 'Empresas',
    icon: BuildingOfficeIcon,
    items: [
      {
        name: 'empresas',
        label: 'Gestión de Empresas',
        to: '/empresas',
        icon: BuildingOfficeIcon,
      },
      {
        name: 'empresa-config',
        label: 'Mi Empresa',
        to: '/empresa/configuracion',
        icon: WrenchScrewdriverIcon,
      },
    ],
  },
  {
    name: 'usuarios',
    label: 'Usuarios',
    icon: UsersIcon,
    items: [
      {
        name: 'users',
        label: 'Gestión de Usuarios',
        to: '/users',
        icon: UsersIcon,
      },
      {
        name: 'roles',
        label: 'Roles y Permisos',
        to: '/roles',
        icon: ShieldCheckIcon,
      },
    ],
  },
  {
    name: 'catalogos',
    label: 'Catálogos',
    icon: TagIcon,
    items: [
      {
        name: 'monedas',
        label: 'Monedas',
        to: '/monedas',
        icon: CurrencyDollarIcon,
      },
      {
        name: 'taxes',
        label: 'Impuestos',
        to: '/taxes',
        icon: ReceiptPercentIcon,
      },
      {
        name: 'sexes',
        label: 'Sexos',
        to: '/sexes',
        icon: UsersIcon,
      },
      {
        name: 'telefonos',
        label: 'Teléfonos',
        to: '/telefonos',
        icon: PhoneIcon,
      },
      {
        name: 'chatids',
        label: 'Chat IDs',
        to: '/chatids',
        icon: ChatBubbleLeftRightIcon,
      },
    ],
  },
  {
    name: 'administracion',
    label: 'Administración',
    icon: Cog6ToothIcon,
    items: [
      {
        name: 'sistemas',
        label: 'Sistemas',
        to: '/sistemas',
        icon: Cog6ToothIcon,
      },
      {
        name: 'settings',
        label: 'Configuraciones',
        to: '/settings',
        icon: AdjustmentsHorizontalIcon,
      },
      {
        name: 'rutas',
        label: 'Rutas API',
        to: '/rutas',
        icon: ArrowPathIcon,
      },
    ],
  },
  {
    name: 'reportes',
    label: 'Reportes',
    icon: ChartBarIcon,
    items: [
      {
        name: 'reports',
        label: 'Reportes',
        to: '/reports',
        icon: ChartBarIcon,
      },
    ],
  },
]

// Verificar si una ruta está activa
const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}

// Alternar grupo abierto/cerrado (comportamiento accordion - solo uno abierto a la vez)
const toggleGroup = (groupName) => {
  const wasOpen = openGroups[groupName]

  // Cerrar todos los grupos
  Object.keys(openGroups).forEach(key => {
    openGroups[key] = false
  })

  // Si el grupo estaba cerrado, abrirlo
  if (!wasOpen) {
    openGroups[groupName] = true
  }

  // Guardar estado en localStorage
  localStorage.setItem('sidebarOpenGroups', JSON.stringify(openGroups))
}

// Detectar qué grupo contiene la ruta actual
const detectActiveGroup = () => {
  const currentPath = route.path

  // Buscar el grupo que contiene un item con la ruta actual
  for (const group of menuGroups) {
    const hasActiveItem = group.items.some(item =>
      currentPath === item.to || currentPath.startsWith(item.to + '/')
    )

    if (hasActiveItem) {
      return group.name
    }
  }

  return null
}

// Actualizar grupos cuando cambia la ruta
const updateGroupsBasedOnRoute = () => {
  // Detectar grupo activo basado en la ruta actual
  const activeGroup = detectActiveGroup()

  // Cerrar todos los grupos primero
  Object.keys(openGroups).forEach(key => {
    openGroups[key] = false
  })

  // Si hay un grupo activo, abrirlo
  if (activeGroup) {
    openGroups[activeGroup] = true
  }
  // Si no hay grupo activo (estamos en dashboard u otra ruta independiente),
  // todos quedan cerrados

  // Guardar estado en localStorage
  localStorage.setItem('sidebarOpenGroups', JSON.stringify(openGroups))
}

// Cargar estado de grupos al montar
onMounted(() => {
  updateGroupsBasedOnRoute()
})

// Actualizar cuando cambia la ruta
watch(() => route.path, () => {
  updateGroupsBasedOnRoute()
})
</script>
