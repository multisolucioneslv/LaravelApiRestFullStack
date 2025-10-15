<template>
  <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 min-h-screen">
    <div class="p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Menú
      </h2>

      <!-- Lista de navegación -->
      <nav class="space-y-2">
        <router-link
          v-for="item in menuItems"
          :key="item.name"
          :to="item.to"
          class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200"
          :class="[
            isActive(item.to)
              ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          <component :is="item.icon" class="w-5 h-5" />
          <span class="font-medium">{{ item.label }}</span>
        </router-link>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import {
  HomeIcon,
  UsersIcon,
  Cog6ToothIcon,
  ChartBarIcon,
  CubeIcon,
  PhoneIcon,
  ChatBubbleLeftRightIcon,
  CurrencyDollarIcon,
  BuildingStorefrontIcon,
  ArchiveBoxIcon,
  BuildingOfficeIcon,
  ReceiptPercentIcon,
  PhotoIcon,
  DocumentTextIcon,
  AdjustmentsHorizontalIcon,
  ShoppingCartIcon,
  ClipboardDocumentListIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()

// Elementos del menú
const menuItems = [
  {
    name: 'dashboard',
    label: 'Dashboard',
    to: '/dashboard',
    icon: HomeIcon,
  },
  {
    name: 'users',
    label: 'Usuarios',
    to: '/users',
    icon: UsersIcon,
  },
  {
    name: 'sistemas',
    label: 'Sistemas',
    to: '/sistemas',
    icon: Cog6ToothIcon,
  },
  {
    name: 'rutas',
    label: 'Rutas API',
    to: '/rutas',
    icon: ArrowPathIcon,
  },
  {
    name: 'settings',
    label: 'Configuraciones',
    to: '/settings',
    icon: AdjustmentsHorizontalIcon,
  },
  {
    name: 'empresas',
    label: 'Empresas',
    to: '/empresas',
    icon: BuildingOfficeIcon,
  },
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
    name: 'galerias',
    label: 'Galerías',
    to: '/galerias',
    icon: PhotoIcon,
  },
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
  {
    name: 'reports',
    label: 'Reportes',
    to: '/reports',
    icon: ChartBarIcon,
  },
]

// Verificar si una ruta está activa
const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}
</script>
