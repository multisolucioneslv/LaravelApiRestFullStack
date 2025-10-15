<template>
  <div class="space-y-4">
    <!-- Barra de búsqueda y acciones -->
    <div class="flex items-center justify-between gap-4">
      <Input
        v-model="searchQuery"
        placeholder="Buscar por código o estado..."
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
          Nueva Cotización
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
          Mostrando {{ cotizaciones.length }} de {{ total }} cotizaciones
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
  cotizaciones: {
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

const emit = defineEmits(['search', 'show', 'edit', 'delete', 'bulkDelete', 'create', 'previousPage', 'nextPage'])

const rowSelection = ref({})
const searchQuery = ref('')
const columnVisibility = ref({})

// Labels para las columnas
const columnLabels = {
  fecha: 'Fecha',
  fecha_vencimiento: 'Vencimiento',
  empresa: 'Empresa',
  moneda: 'Moneda',
  observaciones: 'Observaciones',
}

const getColumnLabel = (columnId) => {
  return columnLabels[columnId] || columnId
}

// Función para obtener el color del badge según estado
const getEstadoBadgeClass = (estado) => {
  const classes = {
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    aprobada: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    rechazada: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    convertida: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
  }
  return classes[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getEstadoLabel = (estado) => {
  const labels = {
    pendiente: 'Pendiente',
    aprobada: 'Aprobada',
    rechazada: 'Rechazada',
    convertida: 'Convertida',
  }
  return labels[estado] || estado
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
    cell: ({ row }) => h('div', { class: 'font-mono font-medium' }, row.getValue('codigo')),
    enableHiding: false,
  },
  {
    accessorKey: 'fecha',
    id: 'fecha',
    header: 'Fecha',
    cell: ({ row }) => h('div', {}, row.getValue('fecha')),
    enableHiding: true,
  },
  {
    accessorKey: 'fecha_vencimiento',
    id: 'fecha_vencimiento',
    header: 'Vencimiento',
    cell: ({ row }) => {
      const fecha = row.getValue('fecha_vencimiento')
      return h('div', {}, fecha || '-')
    },
    enableHiding: true,
  },
  {
    accessorKey: 'estado',
    header: 'Estado',
    cell: ({ row }) => {
      const estado = row.getValue('estado')
      return h('span', {
        class: `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getEstadoBadgeClass(estado)}`
      }, getEstadoLabel(estado))
    },
    enableHiding: false,
  },
  {
    accessorKey: 'empresa',
    id: 'empresa',
    header: 'Empresa',
    cell: ({ row }) => {
      const empresa = row.original.empresa
      return h('div', {}, empresa?.nombre || '-')
    },
    enableHiding: true,
  },
  {
    accessorKey: 'moneda',
    id: 'moneda',
    header: 'Moneda',
    cell: ({ row }) => {
      const moneda = row.original.moneda
      return h('div', {}, moneda ? `${moneda.simbolo} ${moneda.codigo}` : '-')
    },
    enableHiding: true,
  },
  {
    accessorKey: 'total',
    header: 'Total',
    cell: ({ row }) => {
      const total = row.getValue('total')
      const moneda = row.original.moneda
      const simbolo = moneda?.simbolo || '$'
      return h('div', { class: 'text-right font-semibold' }, `${simbolo}${parseFloat(total).toFixed(2)}`)
    },
    enableHiding: false,
  },
  {
    accessorKey: 'observaciones',
    id: 'observaciones',
    header: 'Observaciones',
    cell: ({ row }) => {
      const obs = row.getValue('observaciones')
      return h('div', { class: 'max-w-xs truncate' }, obs || '-')
    },
    enableHiding: true,
  },
  {
    id: 'actions',
    header: 'Acciones',
    cell: ({ row }) => {
      const cotizacion = row.original
      return h('div', { class: 'flex gap-2' }, [
        h(Button, {
          variant: 'ghost',
          size: 'sm',
          onClick: () => emit('show', cotizacion.id),
          title: 'Ver'
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
          h('path', { d: 'M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z' }),
          h('circle', { cx: '12', cy: '12', r: '3' })
        ])),
        h(Button, {
          variant: 'ghost',
          size: 'sm',
          onClick: () => emit('edit', cotizacion.id),
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
          onClick: () => emit('delete', cotizacion.id),
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
    return props.cotizaciones
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
  const ids = selectedRows.value.map(cotizacion => cotizacion.id)
  emit('bulkDelete', ids)
  rowSelection.value = {} // Limpiar selección
}

const handleCreate = () => {
  emit('create')
}
</script>
