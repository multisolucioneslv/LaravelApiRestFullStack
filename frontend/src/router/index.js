import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

/**
 * Definición de rutas de la aplicación
 */
const routes = [
  // Ruta raíz - redirige según autenticación
  {
    path: '/',
    redirect: () => {
      const authStore = useAuthStore()
      return authStore.isAuthenticated ? '/dashboard' : '/login'
    }
  },

  // Rutas públicas (Auth)
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: {
      requiresAuth: false,
      title: 'Iniciar Sesión'
    }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: {
      requiresAuth: false,
      title: 'Registrarse'
    }
  },

  // Rutas protegidas (requieren autenticación)
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: {
      requiresAuth: true,
      title: 'Dashboard'
    }
  },

  // Rutas de Perfil del Usuario
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/profile/ProfileView.vue'),
    meta: {
      requiresAuth: true,
      title: 'Mi Perfil'
    }
  },
  {
    path: '/profile/edit',
    name: 'profile.edit',
    component: () => import('@/views/profile/ProfileEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Perfil'
    }
  },

  // Rutas de Usuarios
  {
    path: '/users',
    name: 'users.index',
    component: () => import('@/views/users/UsersIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Usuarios'
    }
  },
  {
    path: '/users/create',
    name: 'users.create',
    component: () => import('@/views/users/UserCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Usuario'
    }
  },
  {
    path: '/users/:id/edit',
    name: 'users.edit',
    component: () => import('@/views/users/UserEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Usuario'
    }
  },

  // Rutas de Sistemas
  {
    path: '/sistemas',
    name: 'sistemas.index',
    component: () => import('@/views/sistemas/SistemasIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Sistemas'
    }
  },
  {
    path: '/sistemas/create',
    name: 'sistemas.create',
    component: () => import('@/views/sistemas/SistemaCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Sistema'
    }
  },
  {
    path: '/sistemas/:id/edit',
    name: 'sistemas.edit',
    component: () => import('@/views/sistemas/SistemaEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Sistema'
    }
  },

  // Rutas de Sexes
  {
    path: '/sexes',
    name: 'sexes.index',
    component: () => import('@/views/sexes/SexesIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Sexos'
    }
  },
  {
    path: '/sexes/create',
    name: 'sexes.create',
    component: () => import('@/views/sexes/SexCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Sexo'
    }
  },
  {
    path: '/sexes/:id/edit',
    name: 'sexes.edit',
    component: () => import('@/views/sexes/SexEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Sexo'
    }
  },

  // Rutas de Monedas
  {
    path: '/monedas',
    name: 'monedas.index',
    component: () => import('@/views/monedas/MonedasIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Monedas'
    }
  },
  {
    path: '/monedas/create',
    name: 'monedas.create',
    component: () => import('@/views/monedas/MonedaCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Moneda'
    }
  },
  {
    path: '/monedas/:id/edit',
    name: 'monedas.edit',
    component: () => import('@/views/monedas/MonedaEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Moneda'
    }
  },

  // Rutas de Teléfonos
  {
    path: '/telefonos',
    name: 'telefonos.index',
    component: () => import('@/views/telefonos/TelefonosIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Teléfonos'
    }
  },
  {
    path: '/telefonos/create',
    name: 'telefonos.create',
    component: () => import('@/views/telefonos/TelefonoCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Teléfono'
    }
  },
  {
    path: '/telefonos/:id/edit',
    name: 'telefonos.edit',
    component: () => import('@/views/telefonos/TelefonoEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Teléfono'
    }
  },

  // Rutas de Chat IDs
  {
    path: '/chatids',
    name: 'chatids.index',
    component: () => import('@/views/chatids/ChatidsIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Chat IDs'
    }
  },
  {
    path: '/chatids/create',
    name: 'chatids.create',
    component: () => import('@/views/chatids/ChatidCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Chat ID'
    }
  },
  {
    path: '/chatids/:id/edit',
    name: 'chatids.edit',
    component: () => import('@/views/chatids/ChatidEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Chat ID'
    }
  },

  // Rutas de Bodegas
  {
    path: '/bodegas',
    name: 'bodegas.index',
    component: () => import('@/views/bodegas/BodegasIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Bodegas'
    }
  },
  {
    path: '/bodegas/create',
    name: 'bodegas.create',
    component: () => import('@/views/bodegas/BodegaCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Bodega'
    }
  },
  {
    path: '/bodegas/:id/edit',
    name: 'bodegas.edit',
    component: () => import('@/views/bodegas/BodegaEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Bodega'
    }
  },

  // Rutas de Inventarios
  {
    path: '/inventarios',
    name: 'inventarios.index',
    component: () => import('@/views/inventarios/InventariosIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Inventarios'
    }
  },
  {
    path: '/inventarios/create',
    name: 'inventarios.create',
    component: () => import('@/views/inventarios/InventarioCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Inventario'
    }
  },
  {
    path: '/inventarios/:id/edit',
    name: 'inventarios.edit',
    component: () => import('@/views/inventarios/InventarioEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Inventario'
    }
  },

  // Rutas de Empresas
  {
    path: '/empresas',
    name: 'empresas.index',
    component: () => import('@/views/empresas/EmpresasIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Empresas'
    }
  },
  {
    path: '/empresas/create',
    name: 'empresas.create',
    component: () => import('@/views/empresas/EmpresaCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Empresa'
    }
  },
  {
    path: '/empresas/:id/edit',
    name: 'empresas.edit',
    component: () => import('@/views/empresas/EmpresaEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Empresa'
    }
  },

  // Configuración de Empresa (para Admin - solo SU empresa)
  {
    path: '/empresa/configuracion',
    name: 'empresa.config',
    component: () => import('@/views/empresa/EmpresaConfigView.vue'),
    meta: {
      requiresAuth: true,
      title: 'Configuración de Empresa'
    }
  },

  // Rutas de Impuestos (Taxes)
  {
    path: '/taxes',
    name: 'taxes.index',
    component: () => import('@/views/taxes/TaxesIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Impuestos'
    }
  },
  {
    path: '/taxes/create',
    name: 'taxes.create',
    component: () => import('@/views/taxes/TaxCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Impuesto'
    }
  },
  {
    path: '/taxes/:id/edit',
    name: 'taxes.edit',
    component: () => import('@/views/taxes/TaxEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Impuesto'
    }
  },

  // Rutas de Galerías
  {
    path: '/galerias',
    name: 'galerias.index',
    component: () => import('@/views/galerias/GaleriasIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Galerías'
    }
  },
  {
    path: '/galerias/create',
    name: 'galerias.create',
    component: () => import('@/views/galerias/GaleriaCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Galería'
    }
  },
  {
    path: '/galerias/:id/edit',
    name: 'galerias.edit',
    component: () => import('@/views/galerias/GaleriaEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Galería'
    }
  },

  // Rutas de Cotizaciones
  {
    path: '/cotizaciones',
    name: 'cotizaciones.index',
    component: () => import('@/views/cotizaciones/CotizacionesIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Cotizaciones'
    }
  },
  {
    path: '/cotizaciones/create',
    name: 'cotizaciones.create',
    component: () => import('@/views/cotizaciones/CotizacionCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Cotización'
    }
  },
  {
    path: '/cotizaciones/:id',
    name: 'cotizaciones.show',
    component: () => import('@/views/cotizaciones/CotizacionShow.vue'),
    meta: {
      requiresAuth: true,
      title: 'Ver Cotización'
    }
  },
  {
    path: '/cotizaciones/:id/edit',
    name: 'cotizaciones.edit',
    component: () => import('@/views/cotizaciones/CotizacionEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Cotización'
    }
  },

  // Rutas de Ventas
  {
    path: '/ventas',
    name: 'ventas.index',
    component: () => import('@/views/ventas/VentasIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Ventas'
    }
  },
  {
    path: '/ventas/create',
    name: 'ventas.create',
    component: () => import('@/views/ventas/VentaCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Venta'
    }
  },
  {
    path: '/ventas/:id',
    name: 'ventas.show',
    component: () => import('@/views/ventas/VentaShow.vue'),
    meta: {
      requiresAuth: true,
      title: 'Ver Venta'
    }
  },
  {
    path: '/ventas/:id/edit',
    name: 'ventas.edit',
    component: () => import('@/views/ventas/VentaEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Venta'
    }
  },

  // Rutas de Pedidos
  {
    path: '/pedidos',
    name: 'pedidos.index',
    component: () => import('@/views/pedidos/PedidosIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Pedidos'
    }
  },
  {
    path: '/pedidos/create',
    name: 'pedidos.create',
    component: () => import('@/views/pedidos/PedidoCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Pedido'
    }
  },
  {
    path: '/pedidos/:id/edit',
    name: 'pedidos.edit',
    component: () => import('@/views/pedidos/PedidoEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Pedido'
    }
  },

  // Rutas de Configuraciones (Settings)
  {
    path: '/settings',
    name: 'settings.index',
    component: () => import('@/views/settings/SettingsIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Configuraciones'
    }
  },
  {
    path: '/settings/create',
    name: 'settings.create',
    component: () => import('@/views/settings/SettingCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Configuración'
    }
  },
  {
    path: '/settings/:id/edit',
    name: 'settings.edit',
    component: () => import('@/views/settings/SettingEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Configuración'
    }
  },

  // Rutas de Rutas API del Sistema
  {
    path: '/rutas',
    name: 'rutas.index',
    component: () => import('@/views/rutas/RutasIndex.vue'),
    meta: {
      requiresAuth: true,
      title: 'Rutas API'
    }
  },
  {
    path: '/rutas/create',
    name: 'rutas.create',
    component: () => import('@/views/rutas/RutaCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Crear Ruta API'
    }
  },
  {
    path: '/rutas/:id/edit',
    name: 'rutas.edit',
    component: () => import('@/views/rutas/RutaEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Editar Ruta API'
    }
  },

  // Ruta 404 - Not Found
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue'),
    meta: {
      title: 'Página no encontrada'
    }
  }
]

/**
 * Crear instancia del router
 */
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

/**
 * Guard de navegación global
 * Verifica autenticación antes de cada ruta
 */
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const requiresAuth = to.meta.requiresAuth

  // Actualizar título de la página
  document.title = to.meta.title
    ? `${to.meta.title} - BackendProfesional`
    : 'BackendProfesional'

  // Si la ruta requiere autenticación
  if (requiresAuth && !authStore.isAuthenticated) {
    // Redirigir al login guardando la ruta destino
    next({
      name: 'login',
      query: { redirect: to.fullPath }
    })
  }
  // Si ya está autenticado e intenta ir a login/register
  else if (!requiresAuth && authStore.isAuthenticated && (to.name === 'login' || to.name === 'register')) {
    // Redirigir al dashboard
    next({ name: 'dashboard' })
  }
  // Permitir navegación
  else {
    next()
  }
})

export default router
