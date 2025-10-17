<template>
  <div class="space-y-4">
    <!-- Barra de búsqueda, filtros y acciones -->
    <div class="flex flex-col gap-4">
      <div class="flex items-center justify-between gap-4">
        <Input
          v-model="searchQuery"
          placeholder="Buscar por nombre, SKU o código de barras..."
          class="max-w-md"
          @input="handleSearch"
        />
        <div class="flex items-center gap-2">
          <!-- Botón de columnas toggleables -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" class="ml-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M3 3h18v18H3z"/><path d="M21 9H3"/><path d="M21 15H3"/></svg>
                Columnas
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuLabel>Columnas visibles</DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuCheckboxItem
                v-for="column in table.getAllColumns().filter(col => col.getCanHide())"
                :key="column.id"
                :checked="column.getIsVisible()"
                @update:checked="(value) => column.toggleVisibility(!!value)"
              >
                {{ getColumnLabel(column.id) }}
              </DropdownMenuCheckboxItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <Button @click="handleCreate">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Nuevo Producto
          </Button>
        </div>
      </div>

      <!-- Filtros -->
      <div class="flex items-center gap-2">
        <select
          v-model="filterCategoria"
          @change="handleFilterChange"
          class="rounded-md border border-input bg-background px-3 py-2 text-sm"
        >
          <option value="">Todas las categorías</option>
          <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
            {{ cat.nombre }}
          </option>
        </select>

        <select
          v-model="filterActivo"
          @change="handleFilterChange"
          class="rounded-md border border-input bg-background px-3 py-2 text-sm"
        >
          <option value="">Todos los estados</option>
          <option value="1">Activos</option>
          <option value="0">Inactivos</option>
        </select>

        <Button
          variant="outline"
          size="sm"
          @click="handleToggleBajoStock"
          :class="{ 'bg-red-100 dark:bg-red-900/30 border-red-500': showBajoStock }"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          {{ showBajoStock ? 'Mostrando bajo stock' : 'Filtrar bajo stock' }}
        </Button>

        <Button
          v-if="filterCategoria || filterActivo !== '' || showBajoStock || searchQuery"
          variant="ghost"
          size="sm"
          @click="handleClearFilters"
        >
          Limpiar filtros
        </Button>
      </div>
    </div>

    <!-- Tabla -->
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow
            v-for="headerGroup in table.getHeaderGroups()"
            :key="headerGroup.id"
          >
            <TableHead
              v-for="header in headerGroup.headers"
              :key="header.id"
            >
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() ? 'selected' : undefined"
            >
              <TableCell
                v-for="cell in row.getVisibleCells()"
                :key="cell.id"
              >
                <FlexRender
                  :render="cell.column.columnDef.cell"
                  :props="cell.getContext()"
                />
              </TableCell>
            </TableRow>
          </template>
          <template v-else>
            <TableRow>
              <TableCell :colspan="columns.length" class="h-24 text-center">
                No hay productos que mostrar.
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </div>

    <!-- Paginación -->
    <div class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        Mostrando {{ productos.length }} de {{ total }} productos
      </div>
      <div class="flex items-center space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="!hasPrevPage"
          @click="$emit('previousPage')"
        >
          Anterior
        </Button>
        <div class="text-sm text-muted-foreground">
          Página {{ currentPage }} de {{ lastPage }}
        </div>
        <Button
          variant="outline"
          size="sm"
          :disabled="!hasNextPage"
          @click="$emit('nextPage')"
        >
          Siguiente
        </Button>
      </div>
    </div>

    <!-- Modal de actualizar stock -->
    <UpdateStockModal
      :show="showStockModal"
      :producto="selectedProducto"
      @close="closeStockModal"
      @update="handleStockUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import {
  FlexRender,
  getCoreRowModel,
  useVueTable,
} from '@tanstack/vue-table'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import UpdateStockModal from '@/components/productos/UpdateStockModal.vue'

const props = defineProps({
  productos: {
    type: Array,
    required: true,
  },
  categorias: {
    type: Array,
    default: () => []
  },
  currentPage: {
    type: Number,
    required: true,
  },
  lastPage: {
    type: Number,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
  hasPrevPage: {
    type: Boolean,
    required: true,
  },
  hasNextPage: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits(['search', 'filter', 'view', 'edit', 'delete', 'updateStock', 'create', 'previousPage', 'nextPage'])

const searchQuery = ref('')
const filterCategoria = ref('')
const filterActivo = ref('')
const showBajoStock = ref(false)
const columnVisibility = ref({})

// Modal de stock
const showStockModal = ref(false)
const selectedProducto = ref(null)

// Labels para las columnas
const columnLabels = {
  sku: 'SKU',
  codigo_barras: 'Código de Barras',
  categorias: 'Categorías',
  precio_compra: 'Precio Compra',
  precio_venta: 'Precio Venta',
  stock: 'Stock',
}

const getColumnLabel = (columnId) => {
  return columnLabels[columnId] || columnId
}

// Definir columnas de la tabla
const columns = [
  {
    accessorKey: 'imagen',
    header: 'Imagen',
    cell: ({ row }) => {
      const imagen = row.getValue('imagen')
      return h('div', { class: 'flex items-center justify-center' }, [
        imagen
          ? h('img', {
              src: imagen,
              alt: row.original.nombre,
              class: 'h-12 w-12 rounded object-cover',
              onerror: (e) => { e.target.src = 'https://via.placeholder.com/48?text=No+Img' }
            })
          : h('div', {
              class: 'h-12 w-12 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400 text-xs'
            }, 'Sin imagen')
      ])
    },
    enableHiding: false,
  },
  {
    accessorKey: 'nombre',
    header: 'Nombre',
    cell: ({ row }) => h('div', { class: 'font-medium min-w-[150px]' }, row.getValue('nombre')),
    enableHiding: false,
  },
  {
    accessorKey: 'sku',
    id: 'sku',
    header: 'SKU',
    cell: ({ row }) => h('div', { class: 'font-mono text-sm' }, row.getValue('sku') || '-'),
    enableHiding: true,
  },
  {
    accessorKey: 'codigo_barras',
    id: 'codigo_barras',
    header: 'Código Barras',
    cell: ({ row }) => h('div', { class: 'font-mono text-sm' }, row.getValue('codigo_barras') || '-'),
    enableHiding: true,
  },
  {
    accessorKey: 'categorias',
    id: 'categorias',
    header: 'Categorías',
    cell: ({ row }) => {
      const categorias = row.original.categorias || []
      if (categorias.length === 0) {
        return h('span', { class: 'text-gray-400' }, 'Sin categoría')
      }
      return h('div', { class: 'flex flex-wrap gap-1' }, categorias.slice(0, 2).map(cat =>
        h(Badge, {
          variant: 'outline',
          style: {
            backgroundColor: `${cat.color}20`,
            borderColor: cat.color,
            color: cat.color
          }
        }, () => cat.nombre)
      ).concat(
        categorias.length > 2
          ? h('span', { class: 'text-xs text-gray-500' }, `+${categorias.length - 2}`)
          : []
      ))
    },
    enableHiding: true,
  },
  {
    accessorKey: 'precio_compra',
    id: 'precio_compra',
    header: 'Precio Compra',
    cell: ({ row }) => h('div', { class: 'text-right' }, `$${parseFloat(row.getValue('precio_compra') || 0).toFixed(2)}`),
    enableHiding: true,
  },
  {
    accessorKey: 'precio_venta',
    id: 'precio_venta',
    header: 'Precio Venta',
    cell: ({ row }) => h('div', { class: 'text-right font-medium' }, `$${parseFloat(row.getValue('precio_venta') || 0).toFixed(2)}`),
    enableHiding: false,
  },
  {
    accessorKey: 'stock_actual',
    id: 'stock',
    header: 'Stock',
    cell: ({ row }) => {
      const stockActual = row.original.stock_actual || 0
      const stockMinimo = row.original.stock_minimo || 0
      const bajoStock = stockActual <= stockMinimo

      return h('div', { class: 'flex items-center gap-2' }, [
        h('span', {
          class: bajoStock
            ? 'text-red-600 dark:text-red-400 font-bold'
            : 'text-gray-900 dark:text-gray-100'
        }, `${stockActual} / ${stockMinimo}`),
        bajoStock && h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '16',
          height: '16',
          viewBox: '0 0 24 24',
          fill: 'none',
          stroke: 'currentColor',
          'stroke-width': '2',
          class: 'text-red-500'
        }, [
          h('path', { d: 'm21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z' }),
          h('path', { d: 'M12 9v4' }),
          h('path', { d: 'M12 17h.01' })
        ])
      ])
    },
    enableHiding: false,
  },
  {
    accessorKey: 'activo',
    header: 'Estado',
    cell: ({ row }) => {
      const activo = row.getValue('activo')
      return h(Badge, {
        variant: activo ? 'default' : 'secondary',
        class: activo
          ? 'bg-green-600 hover:bg-green-700 dark:bg-green-600'
          : 'bg-gray-400 hover:bg-gray-500'
      }, () => activo ? 'Activo' : 'Inactivo')
    },
    enableHiding: false,
  },
  {
    id: 'actions',
    header: 'Acciones',
    cell: ({ row }) => {
      const producto = row.original
      return h('div', { class: 'flex gap-1' }, [
        h(Button, {
          size: 'sm',
          variant: 'ghost',
          class: 'h-8 w-8 p-0 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30',
          onClick: () => emit('view', producto.id),
          title: 'Ver detalles'
        }, () => h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '18',
          height: '18',
          viewBox: '0 0 24 24',
          fill: 'none',
          stroke: 'currentColor',
          'stroke-width': '2'
        }, [
          h('path', { d: 'M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z' }),
          h('circle', { cx: '12', cy: '12', r: '3' })
        ])),
        h(Button, {
          size: 'sm',
          variant: 'ghost',
          class: 'h-8 w-8 p-0 text-orange-600 hover:bg-orange-100 dark:hover:bg-orange-900/30',
          onClick: () => openStockModal(producto),
          title: 'Actualizar stock'
        }, () => h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '18',
          height: '18',
          viewBox: '0 0 24 24',
          fill: 'none',
          stroke: 'currentColor',
          'stroke-width': '2'
        }, [
          h('path', { d: 'M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z' }),
          h('polyline', { points: '7.5 4.21 12 6.81 16.5 4.21' }),
          h('polyline', { points: '7.5 19.79 7.5 14.6 3 12' }),
          h('polyline', { points: '21 12 16.5 14.6 16.5 19.79' }),
          h('polyline', { points: '3.27 6.96 12 12.01 20.73 6.96' }),
          h('line', { x1: '12', y1: '22.08', x2: '12', y2: '12' })
        ])),
        h(Button, {
          size: 'sm',
          variant: 'ghost',
          class: 'h-8 w-8 p-0 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30',
          onClick: () => emit('edit', producto.id),
          title: 'Editar producto'
        }, () => h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '18',
          height: '18',
          viewBox: '0 0 24 24',
          fill: 'none',
          stroke: 'currentColor',
          'stroke-width': '2'
        }, [
          h('path', { d: 'M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z' }),
          h('path', { d: 'm15 5 4 4' })
        ])),
        h(Button, {
          size: 'sm',
          variant: 'ghost',
          class: 'h-8 w-8 p-0 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30',
          onClick: () => emit('delete', producto.id),
          title: 'Eliminar producto'
        }, () => h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '18',
          height: '18',
          viewBox: '0 0 24 24',
          fill: 'none',
          stroke: 'currentColor',
          'stroke-width': '2'
        }, [
          h('path', { d: 'M3 6h18' }),
          h('path', { d: 'M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6' }),
          h('path', { d: 'M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2' })
        ])),
      ])
    },
    enableHiding: false,
  },
]

// Configurar tabla
const table = useVueTable({
  get data() {
    return props.productos
  },
  columns,
  getCoreRowModel: getCoreRowModel(),
  state: {
    get columnVisibility() {
      return columnVisibility.value
    },
  },
  onColumnVisibilityChange: updaterOrValue => {
    columnVisibility.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(columnVisibility.value)
      : updaterOrValue
  },
})

// Manejadores
const handleSearch = () => {
  emit('search', searchQuery.value)
}

const handleFilterChange = () => {
  const filters = {}
  if (filterCategoria.value) filters.categoria_id = filterCategoria.value
  if (filterActivo.value !== '') filters.activo = filterActivo.value === '1'
  if (showBajoStock.value) filters.bajo_stock = true

  emit('filter', filters)
}

const handleToggleBajoStock = () => {
  showBajoStock.value = !showBajoStock.value
  handleFilterChange()
}

const handleClearFilters = () => {
  searchQuery.value = ''
  filterCategoria.value = ''
  filterActivo.value = ''
  showBajoStock.value = false
  emit('filter', {})
  emit('search', '')
}

const handleCreate = () => {
  emit('create')
}

// Modal de stock
const openStockModal = (producto) => {
  selectedProducto.value = producto
  showStockModal.value = true
}

const closeStockModal = () => {
  showStockModal.value = false
  selectedProducto.value = null
}

const handleStockUpdate = (data) => {
  emit('updateStock', selectedProducto.value.id, data.cantidad, data.tipo)
  closeStockModal()
}
</script>
