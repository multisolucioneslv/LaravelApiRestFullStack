<template>
  <div class="space-y-4">
    <!-- Barra de búsqueda y acciones -->
    <div class="flex items-center justify-between gap-4">
      <Input
        v-model="searchQuery"
        placeholder="Buscar por código o empresa..."
        class="max-w-sm"
        @input="handleSearch"
      />
      <div class="flex items-center gap-2">
        <Button
          v-if="selectedRows.length > 0"
          variant="destructive"
          @click="handleBulkDelete"
        >
          Eliminar {{ selectedRows.length }} seleccionados
        </Button>

        <!-- Botón de columnas toggleables -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" class="ml-auto">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M3 3h18v18H3z"/><path d="M21 9H3"/><path d="M21 15H3"/></svg>
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
          Nuevo Pedido
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
                No hay resultados.
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </div>

    <!-- Paginación -->
    <div class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        <span v-if="selectedRows.length > 0">
          {{ selectedRows.length }} de {{ table.getFilteredRowModel().rows.length }} fila(s) seleccionadas
        </span>
        <span v-else>
          Mostrando {{ pedidos.length }} de {{ total }} pedidos
        </span>
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
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const props = defineProps({
  pedidos: {
    type: Array,
    required: true,
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

const emit = defineEmits(['search', 'edit', 'delete', 'bulkDelete', 'create', 'previousPage', 'nextPage'])

const rowSelection = ref({})
const searchQuery = ref('')
const columnVisibility = ref({})

// Labels para las columnas
const columnLabels = {
  codigo: 'Código',
  fecha: 'Fecha',
  fecha_entrega: 'Fecha Entrega',
  empresa: 'Empresa',
  tipo: 'Tipo',
  estado: 'Estado',
  total: 'Total',
  observaciones: 'Observaciones',
}

const getColumnLabel = (columnId) => {
  return columnLabels[columnId] || columnId
}

// Helper para formatear fecha
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

// Helper para formatear dinero
const formatMoney = (amount) => {
  if (!amount) return '$0.00'
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

// Helper para obtener color del badge de tipo
const getTipoBadgeClass = (tipo) => {
  const classes = {
    compra: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    venta: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    servicio: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
  }
  return classes[tipo] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

// Helper para obtener color del badge de estado
const getEstadoBadgeClass = (estado) => {
  const classes = {
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    proceso: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    completado: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    cancelado: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
  }
  return classes[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

// Helper para capitalizar primera letra
const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

// Definir columnas de la tabla
const columns = [
  {
    id: 'select',
    header: ({ table }) => h(Checkbox, {
      checked: table.getIsAllPageRowsSelected(),
      indeterminate: table.getIsSomePageRowsSelected(),
      'onUpdate:checked': value => table.toggleAllPageRowsSelected(!!value),
    }),
    cell: ({ row }) => h(Checkbox, {
      checked: row.getIsSelected(),
      disabled: !row.getCanSelect(),
      'onUpdate:checked': value => row.toggleSelected(!!value),
    }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'codigo',
    header: 'Código',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('codigo')),
    enableHiding: false,
  },
  {
    accessorKey: 'fecha',
    header: 'Fecha',
    cell: ({ row }) => h('div', {}, formatDate(row.getValue('fecha'))),
    enableHiding: false,
  },
  {
    accessorKey: 'fecha_entrega',
    id: 'fecha_entrega',
    header: 'Fecha Entrega',
    cell: ({ row }) => h('div', {}, formatDate(row.getValue('fecha_entrega'))),
    enableHiding: true,
  },
  {
    accessorKey: 'empresa',
    id: 'empresa',
    header: 'Empresa',
    cell: ({ row }) => {
      const empresa = row.original.empresa
      return h('div', {}, empresa?.nombre || '-')
    },
    enableHiding: false,
  },
  {
    accessorKey: 'tipo',
    header: 'Tipo',
    cell: ({ row }) => {
      const tipo = row.getValue('tipo')
      return h('span', {
        class: `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getTipoBadgeClass(tipo)}`
      }, capitalize(tipo))
    },
    enableHiding: false,
  },
  {
    accessorKey: 'estado',
    header: 'Estado',
    cell: ({ row }) => {
      const estado = row.getValue('estado')
      return h('span', {
        class: `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getEstadoBadgeClass(estado)}`
      }, capitalize(estado))
    },
    enableHiding: false,
  },
  {
    accessorKey: 'total',
    header: 'Total',
    cell: ({ row }) => h('div', { class: 'font-semibold text-right' }, formatMoney(row.getValue('total'))),
    enableHiding: false,
  },
  {
    accessorKey: 'observaciones',
    id: 'observaciones',
    header: 'Observaciones',
    cell: ({ row }) => h('div', { class: 'max-w-xs truncate' }, row.getValue('observaciones') || '-'),
    enableHiding: true,
  },
  {
    id: 'actions',
    header: 'Acciones',
    cell: ({ row }) => {
      const pedido = row.original
      return h('div', { class: 'flex gap-2' }, [
        h(Button, {
          variant: 'ghost',
          size: 'sm',
          onClick: () => emit('edit', pedido.id),
          title: 'Editar'
        }, () => h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '16',
          height: '16',
          viewBox: '0 0 24 24',
          fill: 'none',
          stroke: 'currentColor',
          'stroke-width': '2',
          'stroke-linecap': 'round',
          'stroke-linejoin': 'round'
        }, [
          h('path', { d: 'M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z' }),
          h('path', { d: 'm15 5 4 4' })
        ])),
        h(Button, {
          variant: 'ghost',
          size: 'sm',
          onClick: () => emit('delete', pedido.id),
          class: 'text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20',
          title: 'Eliminar'
        }, () => h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '16',
          height: '16',
          viewBox: '0 0 24 24',
          fill: 'none',
          stroke: 'currentColor',
          'stroke-width': '2',
          'stroke-linecap': 'round',
          'stroke-linejoin': 'round'
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
    return props.pedidos
  },
  columns,
  getCoreRowModel: getCoreRowModel(),
  state: {
    get rowSelection() {
      return rowSelection.value
    },
    get columnVisibility() {
      return columnVisibility.value
    },
  },
  enableRowSelection: true,
  onRowSelectionChange: updaterOrValue => {
    rowSelection.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(rowSelection.value)
      : updaterOrValue
  },
  onColumnVisibilityChange: updaterOrValue => {
    columnVisibility.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(columnVisibility.value)
      : updaterOrValue
  },
})

// Filas seleccionadas
const selectedRows = computed(() => {
  return table.getFilteredSelectedRowModel().rows.map(row => row.original)
})

// Manejadores
const handleSearch = () => {
  emit('search', searchQuery.value)
}

const handleBulkDelete = () => {
  const ids = selectedRows.value.map(pedido => pedido.id)
  emit('bulkDelete', ids)
  rowSelection.value = {} // Limpiar selección
}

const handleCreate = () => {
  emit('create')
}
</script>
