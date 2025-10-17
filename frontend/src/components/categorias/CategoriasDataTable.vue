<template>
  <div class="space-y-4">
    <!-- Barra de búsqueda y acciones -->
    <div class="flex items-center justify-between gap-4">
      <Input
        v-model="searchQuery"
        placeholder="Buscar por nombre..."
        class="max-w-sm"
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
          Nueva Categoría
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
                No hay categorías que mostrar.
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </div>

    <!-- Paginación -->
    <div class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        Mostrando {{ categorias.length }} de {{ total }} categorías
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
import { Badge } from '@/components/ui/badge'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const props = defineProps({
  categorias: {
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

const emit = defineEmits(['search', 'edit', 'delete', 'create', 'previousPage', 'nextPage'])

const searchQuery = ref('')
const columnVisibility = ref({})

// Labels para las columnas
const columnLabels = {
  slug: 'Slug',
  productos_count: 'Productos',
}

const getColumnLabel = (columnId) => {
  return columnLabels[columnId] || columnId
}

// Definir columnas de la tabla
const columns = [
  {
    accessorKey: 'icono',
    header: 'Icono',
    cell: ({ row }) => {
      const icono = row.getValue('icono')
      const color = row.original.color || '#6b7280'
      return h('div', { class: 'flex items-center justify-center' }, [
        h('div', {
          class: 'w-10 h-10 rounded-lg flex items-center justify-center text-white text-lg font-bold shadow-md',
          style: { backgroundColor: color }
        }, icono || '📦')
      ])
    },
    enableHiding: false,
  },
  {
    accessorKey: 'nombre',
    header: 'Nombre',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('nombre')),
    enableHiding: false,
  },
  {
    accessorKey: 'slug',
    id: 'slug',
    header: 'Slug',
    cell: ({ row }) => h('div', { class: 'font-mono text-sm text-gray-600 dark:text-gray-400' }, row.getValue('slug')),
    enableHiding: true,
  },
  {
    accessorKey: 'descripcion',
    header: 'Descripción',
    cell: ({ row }) => {
      const desc = row.getValue('descripcion')
      return h('div', { class: 'text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate' }, desc || '-')
    },
    enableHiding: true,
  },
  {
    accessorKey: 'productos_count',
    id: 'productos_count',
    header: 'Productos',
    cell: ({ row }) => {
      const count = row.original.productos_count || 0
      return h('div', { class: 'text-center' }, [
        h(Badge, { variant: 'secondary' }, () => count)
      ])
    },
    enableHiding: true,
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
      }, () => activo ? 'Activa' : 'Inactiva')
    },
    enableHiding: false,
  },
  {
    id: 'actions',
    header: 'Acciones',
    cell: ({ row }) => {
      const categoria = row.original
      return h('div', { class: 'flex gap-1' }, [
        h(Button, {
          size: 'sm',
          variant: 'ghost',
          class: 'h-8 w-8 p-0 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/30',
          onClick: () => emit('edit', categoria.id),
          title: 'Editar categoría'
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
          onClick: () => emit('delete', categoria.id),
          title: 'Eliminar categoría'
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
    return props.categorias
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

const handleCreate = () => {
  emit('create')
}
</script>
