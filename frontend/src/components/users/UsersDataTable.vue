<template>
  <div class="space-y-4">
    <!-- Barra de búsqueda y acciones -->
    <div class="flex items-center justify-between gap-4">
      <Input
        v-model="searchQuery"
        placeholder="Buscar por usuario, nombre o email..."
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
          Nuevo Usuario
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
          Mostrando {{ users.length }} de {{ total }} usuarios
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
  users: {
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

const emit = defineEmits(['search', 'edit', 'delete', 'bulkDelete', 'create', 'previousPage', 'nextPage', 'openAvatarModal'])

const rowSelection = ref({})
const searchQuery = ref('')
const columnVisibility = ref({})

// Labels para las columnas
const columnLabels = {
  usuario: 'Usuario',
  name: 'Nombre',
  email: 'Email',
  sexo: 'Sexo',
  telefono: 'Teléfono',
  chatid: 'Chat ID',
  empresa: 'Empresa',
  avatar: 'Avatar',
  cuenta: 'Cuenta',
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
    accessorKey: 'usuario',
    header: 'Usuario',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('usuario')),
    enableHiding: false,
  },
  {
    accessorKey: 'name',
    header: 'Nombre',
    enableHiding: false,
  },
  {
    accessorKey: 'email',
    header: 'Email',
    enableHiding: false,
  },
  {
    accessorKey: 'sexo',
    id: 'sexo',
    header: 'Sexo',
    cell: ({ row }) => {
      const sex = row.original.sex
      return h('div', {}, sex?.name || '-')
    },
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'telefono',
    id: 'telefono',
    header: 'Teléfono',
    cell: ({ row }) => h('div', {}, row.getValue('telefono') || '-'),
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'chatid',
    id: 'chatid',
    header: 'Chat ID',
    cell: ({ row }) => h('div', {}, row.getValue('chatid') || '-'),
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'empresa',
    id: 'empresa',
    header: 'Empresa',
    cell: ({ row }) => {
      const empresa = row.original.empresa
      return h('div', {}, empresa?.nombre || '-')
    },
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'avatar',
    id: 'avatar',
    header: 'Avatar',
    cell: ({ row }) => {
      const user = row.original
      const avatarUrl = user.avatar

      const avatarElement = avatarUrl
        ? h('img', {
            key: avatarUrl, // Key única para forzar re-render
            src: avatarUrl,
            alt: user.name,
            class: 'w-10 h-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700'
          })
        : h('div', {
            key: `no-avatar-${user.id}`, // Key única para cuando no hay avatar
            class: 'w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 dark:from-blue-500 dark:to-blue-700 flex items-center justify-center text-white font-semibold text-sm'
          }, user.name?.charAt(0).toUpperCase() || '?')

      return h('button', {
        key: `avatar-btn-${user.id}-${avatarUrl || 'no-avatar'}`, // Key única para el botón
        onClick: () => emit('openAvatarModal', user),
        class: 'hover:opacity-75 transition-opacity cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-full',
        title: 'Click para editar avatar'
      }, avatarElement)
    },
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'cuenta',
    id: 'cuenta',
    header: 'Cuenta',
    cell: ({ row }) => h('div', {}, row.getValue('cuenta') || '-'),
    enableHiding: true, // Toggleable
  },
  {
    accessorKey: 'activo',
    header: 'Estado',
    cell: ({ row }) => {
      const activo = row.getValue('activo')
      return h('span', {
        class: activo
          ? 'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
          : 'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
      }, activo ? 'Activo' : 'Inactivo')
    },
    enableHiding: false,
  },
  {
    id: 'actions',
    header: 'Acciones',
    cell: ({ row }) => {
      const user = row.original
      return h('div', { class: 'flex gap-2' }, [
        h(Button, {
          variant: 'ghost',
          size: 'sm',
          onClick: () => emit('edit', user.id),
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
          onClick: () => emit('delete', user.id),
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
    return props.users
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
  const ids = selectedRows.value.map(user => user.id)
  emit('bulkDelete', ids)
  rowSelection.value = {} // Limpiar selección
}

const handleCreate = () => {
  emit('create')
}
</script>
