<template>
  <AppLayout>
    <!-- Header del Dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Dashboard Analítico
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Resumen completo de estadísticas y métricas del negocio
        </p>
      </div>
      <button
        @click="fetchStatistics"
        :disabled="loading"
        class="btn-primary mt-4 sm:mt-0 flex items-center space-x-2"
      >
        <ArrowPathIcon
          class="w-5 h-5"
          :class="{ 'animate-spin': loading }"
        />
        <span>Actualizar</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !statistics" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="i in 4"
          :key="`skeleton-${i}`"
          class="card animate-pulse"
        >
          <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="card bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800"
    >
      <div class="flex items-center space-x-3">
        <ExclamationTriangleIcon class="w-8 h-8 text-red-600 dark:text-red-400" />
        <div>
          <h3 class="text-lg font-semibold text-red-900 dark:text-red-200">
            Error al cargar estadísticas
          </h3>
          <p class="text-sm text-red-700 dark:text-red-300">
            {{ error }}
          </p>
          <button
            @click="fetchStatistics"
            class="mt-2 text-sm text-red-600 dark:text-red-400 hover:underline"
          >
            Intentar nuevamente
          </button>
        </div>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="statistics" class="space-y-6">
      <!-- SECCIÓN 1: Resumen General (Grid de 4 columnas) -->
      <section>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
          Resumen General
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Ventas -->
          <StatCard
            title="Total Ventas"
            :value="statistics.ventas?.total_dinero || 0"
            value-type="currency"
            :secondary-value="`${statistics.ventas?.total_ventas || 0} ventas realizadas`"
            :icon="CurrencyDollarIcon"
            color="green"
          />

          <!-- Total Productos -->
          <StatCard
            title="Total Productos"
            :value="statistics.productos?.total_productos || 0"
            value-type="number"
            :secondary-value="`${statistics.productos?.productos_bajo_stock || 0} bajo stock`"
            :icon="CubeIcon"
            color="purple"
          />

          <!-- Total Cotizaciones -->
          <StatCard
            title="Total Cotizaciones"
            :value="statistics.cotizaciones?.total_cotizaciones || 0"
            value-type="number"
            :secondary-value="`${statistics.cotizaciones?.tasa_conversion || 0}% tasa conversión`"
            :icon="DocumentTextIcon"
            color="blue"
          />

          <!-- Total Pedidos -->
          <StatCard
            title="Total Pedidos"
            :value="statistics.pedidos?.total_pedidos || 0"
            value-type="number"
            :secondary-value="`${statistics.pedidos?.pedidos_pendientes || 0} pendientes`"
            :icon="TruckIcon"
            color="yellow"
          />
        </div>
      </section>

      <!-- SECCIÓN 2: Ventas (2 columnas) -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ventas del Mes -->
        <ChartCard title="Ventas del Mes">
          <SimpleBarChart
            :data="ventasDelMesData"
            value-type="currency"
            color="green"
          />
        </ChartCard>

        <!-- Top 5 Ventas Más Grandes -->
        <ChartCard title="Top 5 Ventas Más Grandes">
          <div class="space-y-3">
            <div
              v-for="(venta, index) in statistics.ventas?.top_ventas || []"
              :key="index"
              class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
            >
              <div class="flex items-center space-x-3">
                <div
                  class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full font-bold text-sm"
                >
                  #{{ index + 1 }}
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    Venta #{{ venta.id }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDate(venta.fecha) }}
                  </p>
                </div>
              </div>
              <p class="text-lg font-bold text-green-600 dark:text-green-400">
                ${{ Number(venta.total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
              </p>
            </div>
          </div>
          <div
            v-if="!statistics.ventas?.top_ventas?.length"
            class="text-center py-8 text-gray-500 dark:text-gray-400"
          >
            No hay ventas registradas
          </div>
        </ChartCard>
      </section>

      <!-- SECCIÓN 3: Productos (2 columnas) -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 10 Productos Más Vendidos -->
        <ChartCard title="Top 10 Productos Más Vendidos">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                    Producto
                  </th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                    Cantidad
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="(producto, index) in statistics.productos?.top_productos || []"
                  :key="index"
                  class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                >
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                    {{ producto.nombre }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">
                    {{ producto.total_vendido }}
                  </td>
                </tr>
              </tbody>
            </table>
            <div
              v-if="!statistics.productos?.top_productos?.length"
              class="text-center py-8 text-gray-500 dark:text-gray-400"
            >
              No hay productos vendidos
            </div>
          </div>
        </ChartCard>

        <!-- Estado de Productos -->
        <ChartCard title="Estado de Productos">
          <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <p class="text-sm text-green-600 dark:text-green-400">
                Activos
              </p>
              <p class="text-2xl font-bold text-green-700 dark:text-green-300">
                {{ statistics.productos?.productos_activos || 0 }}
              </p>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Inactivos
              </p>
              <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                {{ statistics.productos?.productos_inactivos || 0 }}
              </p>
            </div>
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
              <p class="text-sm text-yellow-600 dark:text-yellow-400">
                Bajo Stock
              </p>
              <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">
                {{ statistics.productos?.productos_bajo_stock || 0 }}
              </p>
            </div>
            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
              <p class="text-sm text-red-600 dark:text-red-400">
                Sin Stock
              </p>
              <p class="text-2xl font-bold text-red-700 dark:text-red-300">
                {{ statistics.productos?.productos_sin_stock || 0 }}
              </p>
            </div>
          </div>
        </ChartCard>
      </section>

      <!-- SECCIÓN 4: Usuarios y Pedidos (2 columnas) -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Usuarios -->
        <ChartCard title="Estadísticas de Usuarios">
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <div class="flex items-center space-x-3">
                <UserGroupIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Usuarios</span>
              </div>
              <span class="text-xl font-bold text-gray-900 dark:text-white">
                {{ statistics.usuarios?.total_usuarios || 0 }}
              </span>
            </div>
            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
              <div class="flex items-center space-x-3">
                <UserGroupIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
                <span class="text-sm text-green-600 dark:text-green-400">Usuarios Activos</span>
              </div>
              <span class="text-xl font-bold text-green-700 dark:text-green-300">
                {{ statistics.usuarios?.usuarios_activos || 0 }}
              </span>
            </div>
            <div class="flex items-center justify-between p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
              <div class="flex items-center space-x-3">
                <UserGroupIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                <span class="text-sm text-purple-600 dark:text-purple-400">Con Compras</span>
              </div>
              <span class="text-xl font-bold text-purple-700 dark:text-purple-300">
                {{ statistics.usuarios?.usuarios_con_compras || 0 }}
              </span>
            </div>
            <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <div class="flex items-center space-x-3">
                <UserGroupIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                <span class="text-sm text-blue-600 dark:text-blue-400">Nuevos (30 días)</span>
              </div>
              <span class="text-xl font-bold text-blue-700 dark:text-blue-300">
                {{ statistics.usuarios?.usuarios_nuevos || 0 }}
              </span>
            </div>
          </div>
        </ChartCard>

        <!-- Pedidos por Estado -->
        <ChartCard title="Pedidos por Estado">
          <DonutChart :data="pedidosPorEstadoData" />
        </ChartCard>
      </section>

      <!-- SECCIÓN 5: Inventario por Bodegas -->
      <section v-if="statistics.bodegas?.length">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
          Inventario por Bodegas
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(bodega, index) in statistics.bodegas"
            :key="index"
            class="card hover:shadow-lg transition-shadow"
          >
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                  <BuildingStorefrontIcon class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 dark:text-white">
                    {{ bodega.nombre }}
                  </h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Bodega
                  </p>
                </div>
              </div>
            </div>
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Valor Total</span>
                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                  ${{ Number(bodega.valor_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Productos</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ bodega.total_productos || 0 }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="card text-center py-12"
    >
      <ChartBarIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        No hay datos disponibles
      </h3>
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        No se encontraron estadísticas para mostrar
      </p>
      <button @click="fetchStatistics" class="btn-primary">
        Cargar estadísticas
      </button>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import ChartCard from '@/components/dashboard/ChartCard.vue'
import SimpleBarChart from '@/components/dashboard/SimpleBarChart.vue'
import DonutChart from '@/components/dashboard/DonutChart.vue'
import { useDashboard } from '@/composables/useDashboard'

import {
  CurrencyDollarIcon,
  CubeIcon,
  DocumentTextIcon,
  TruckIcon,
  UserGroupIcon,
  BuildingStorefrontIcon,
  ChartBarIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

// Usar composable de dashboard
const { statistics, loading, error, fetchStatistics } = useDashboard()

/**
 * Formatea una fecha a formato legible
 */
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

/**
 * Datos para gráfica de ventas del mes
 */
const ventasDelMesData = computed(() => {
  if (!statistics.value?.ventas?.ventas_por_dia) return []

  return statistics.value.ventas.ventas_por_dia.map((item) => ({
    label: formatDate(item.fecha),
    value: item.total,
  }))
})

/**
 * Datos para gráfica de pedidos por estado
 */
const pedidosPorEstadoData = computed(() => {
  if (!statistics.value?.pedidos?.pedidos_por_estado) return []

  return statistics.value.pedidos.pedidos_por_estado.map((item) => ({
    label: item.estado,
    value: item.total,
  }))
})
</script>
