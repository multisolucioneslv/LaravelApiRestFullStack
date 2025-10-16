<template>
  <div class="space-y-4">
    <!-- Barra de búsqueda y acciones -->
    <div class="flex items-center justify-between gap-4">
      <Input
        v-model="searchQuery"
        placeholder="Buscar por nombre o email..."
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
          Nueva Empresa
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
          Mostrando {{ empresas.length }} de {{ total }} empresas
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
  empresas: {
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
  nombre: 'Nombre',
  email: 'Email',
  telefono: 'Teléfono',
  moneda: 'Moneda',
  direccion: 'Dirección',
  logo: 'Logo',
  zona_horaria: 'Zona Horaria',
  activo: 'Estado',
}

const getColumnLabel = (columnId) => {
  return columnLabels[columnId] || columnId
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
    accessorKey: 'nombre',
    header: 'Nombre',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('nombre')),
    enableHiding: false,
  },
  {
    accessorKey: 'email',
    header: 'Email',
    cell: ({ row }) => h('div', {}, row.getValue('email') || '-'),
    enableHiding: false,
  },
  {
    accessorKey: 'telefono',
    id: 'telefono',
    header: 'Teléfono',
    cell: ({ row }) => {
      const telefono = row.original.telefono
      return h('div', {}, telefono?.telefono || '-')
    },
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'moneda',
    id: 'moneda',
    header: 'Moneda',
    cell: ({ row }) => {
      const moneda = row.original.moneda
      return h('div', {}, moneda ? `${moneda.nombre} (${moneda.simbolo})` : '-')
    },
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'direccion',
    id: 'direccion',
    header: 'Dirección',
    cell: ({ row }) => h('div', { class: 'max-w-xs truncate' }, row.getValue('direccion') || '-'),
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'logo',
    id: 'logo',
    header: 'Logo',
    cell: ({ row }) => {
      const empresa = row.original
      const logoUrl = empresa.logo

      return logoUrl
        ? h('img', {
            key: logoUrl,
            src: logoUrl,
            alt: empresa.nombre,
            class: 'w-12 h-12 object-contain'
          })
        : h('div', {
            class: 'w-12 h-12 rounded bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white font-semibold'
          }, empresa.nombre?.charAt(0).toUpperCase() || '?')
    },
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'zona_horaria',
    id: 'zona_horaria',
    header: 'Zona Horaria',
    cell: ({ row }) => h('div', { class: 'text-sm' }, row.getValue('zona_horaria') || '-'),
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'activo',
    header: 'Estado',
    cell: ({ row }) => {
      const activo = row.getValue('activo')
      return h('span', {
        class: activo
          ? 'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-green-200 text-green-800 dark:bg-green-900/30 dark:text-green-400'
          : 'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-200 text-red-800 dark:bg-red-900/30 dark:text-red-400'
      }, activo ? 'Activo' : 'Inactivo')
    },
    enableHiding: false,
  },
  {
    id: 'actions',
    header: 'Acciones',
    cell: ({ row }) => {
      const empresa = row.original
      return h('div', { class: 'flex gap-2' }, [
        h(Button, {
          size: 'sm',
          class: 'bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors shadow-sm',
          onClick: () => emit('edit', empresa.id),
          title: 'Editar empresa'
        }, () => h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '18',
          height: '18',
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
          size: 'sm',
          class: 'bg-red-600 hover:bg-red-700 text-white dark:bg-red-600 dark:hover:bg-red-700 transition-colors shadow-sm',
          onClick: () => emit('delete', empresa.id),
          title: 'Eliminar empresa'
        }, () => h('svg', {
          xmlns: 'http://www.w3.org/2000/svg',
          width: '18',
          height: '18',
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
    return props.empresas
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
  const ids = selectedRows.value.map(empresa => empresa.id)
  emit('bulkDelete', ids)
  rowSelection.value = {} // Limpiar selección
}

const handleCreate = () => {
  emit('create')
}
</script>
