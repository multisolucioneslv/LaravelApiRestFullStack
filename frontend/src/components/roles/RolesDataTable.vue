<template>
  <div class="space-y-4">
    <!-- Barra de búsqueda y acciones -->
    <div class="flex items-center justify-between gap-4">
      <Input
        v-model="searchQuery"
        placeholder="Buscar por nombre de rol..."
        class="max-w-sm"
        @input="handleSearch"
      />
      <div class="flex items-center gap-2">
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
          Nuevo Rol
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
        Mostrando {{ roles.length }} de {{ total }} roles
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
import { ref, h } from 'vue'
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
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Badge } from '@/components/ui/badge'

const props = defineProps({
  roles: {
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
  name: 'Nombre del Rol',
  permissions: 'Permisos',
  created_at: 'Fecha de Creación',
}

const getColumnLabel = (columnId) => {
  return columnLabels[columnId] || columnId
}

// Definir columnas de la tabla
const columns = [
  {
    accessorKey: 'id',
    header: 'ID',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('id')),
    enableHiding: true,
  },
  {
    accessorKey: 'name',
    header: 'Nombre del Rol',
    cell: ({ row }) => h('div', { class: 'font-semibold text-primary' }, row.getValue('name')),
    enableHiding: false,
  },
  {
    accessorKey: 'permissions',
    id: 'permissions',
    header: 'Permisos',
    cell: ({ row }) => {
      const permissions = row.original.permissions || []
      const count = permissions.length

      if (count === 0) {
        return h('span', { class: 'text-gray-400' }, 'Sin permisos')
      }

      // Crear badge con el conteo
      const badge = h(Badge, {
        variant: 'secondary',
        class: 'cursor-help',
        title: permissions.map(p => p.name).join('\n')
      }, () => `${count} permiso${count !== 1 ? 's' : ''}`)

      return h('div', { class: 'flex gap-1' }, [badge])
    },
    enableHiding: false,
  },
  {
    accessorKey: 'created_at',
    id: 'created_at',
    header: 'Fecha de Creación',
    cell: ({ row }) => {
      const date = row.getValue('created_at')
      if (!date) return '-'
      return h('div', { class: 'text-sm text-muted-foreground' },
        new Date(date).toLocaleDateString('es-ES', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        })
      )
    },
    enableHiding: true,
  },
  {
    id: 'actions',
    header: 'Acciones',
    cell: ({ row }) => {
      const role = row.original
      const isSystemRole = ['SuperAdmin', 'Administrador', 'Supervisor', 'Vendedor', 'Usuario', 'Contabilidad'].includes(role.name)

      return h('div', { class: 'flex gap-2' }, [
        h(Button, {
          size: 'sm',
          class: 'bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors shadow-sm',
          onClick: () => emit('edit', role.id),
          title: 'Editar rol'
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
        ...(isSystemRole ? [] : [
          h(Button, {
            size: 'sm',
            class: 'bg-red-600 hover:bg-red-700 text-white dark:bg-red-600 dark:hover:bg-red-700 transition-colors shadow-sm',
            onClick: () => emit('delete', role.id),
            title: 'Eliminar rol'
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
          ]))
        ])
      ])
    },
    enableHiding: false,
  },
]

// Configurar tabla
const table = useVueTable({
  get data() {
    return props.roles
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
