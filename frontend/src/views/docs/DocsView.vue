<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          📖 Documentación del Sistema
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Guía completa de uso y funcionalidades de BackendProfesional
        </p>
      </div>

      <!-- Tabs para seleccionar documento -->
      <div class="card">
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex space-x-4" aria-label="Tabs">
            <button
              v-for="doc in documentos"
              :key="doc.id"
              @click="cambiarDocumento(doc.id)"
              :class="[
                'px-4 py-3 text-sm font-medium border-b-2 transition-colors',
                documentoActual === doc.id
                  ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'
              ]"
            >
              <div class="flex items-center space-x-2">
                <component :is="doc.icon" class="w-5 h-5" />
                <span>{{ doc.nombre }}</span>
              </div>
            </button>
          </nav>
        </div>

        <!-- Búsqueda -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <MagnifyingGlassIcon
              class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
            />
            <input
              v-model="busqueda"
              type="text"
              placeholder="Buscar en la documentación..."
              class="input pl-10 w-full"
            />
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Tabla de Contenidos (sidebar) -->
        <aside class="lg:col-span-3">
          <div class="card sticky top-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Contenido
            </h3>
            <nav v-if="tableOfContents.length > 0" class="space-y-1">
              <a
                v-for="heading in tableOfContents"
                :key="heading.id"
                @click.prevent="scrollToHeading(heading.id)"
                :class="[
                  'block text-sm py-2 px-3 rounded-lg transition-colors cursor-pointer',
                  heading.level === 2 ? 'pl-3 font-medium' : 'pl-6 text-gray-600 dark:text-gray-400',
                  'hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                {{ heading.text }}
              </a>
            </nav>
            <div v-else class="text-sm text-gray-500 dark:text-gray-400">
              Cargando índice...
            </div>
          </div>
        </aside>

        <!-- Contenido del documento -->
        <main class="lg:col-span-9">
          <div class="card">
            <!-- Loading -->
            <div v-if="cargando" class="flex items-center justify-center py-12">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="text-center py-12">
              <ExclamationCircleIcon class="w-16 h-16 text-red-500 mx-auto mb-4" />
              <p class="text-red-600 dark:text-red-400 font-medium">{{ error }}</p>
            </div>

            <!-- Contenido Markdown -->
            <article
              v-else
              class="prose prose-gray dark:prose-invert max-w-none
                     prose-headings:text-gray-900 dark:prose-headings:text-white
                     prose-p:text-gray-700 dark:prose-p:text-gray-300
                     prose-strong:text-gray-900 dark:prose-strong:text-white
                     prose-code:text-primary-600 dark:prose-code:text-primary-400
                     prose-code:bg-gray-100 dark:prose-code:bg-gray-800
                     prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded
                     prose-pre:bg-gray-900 dark:prose-pre:bg-gray-950
                     prose-a:text-primary-600 dark:prose-a:text-primary-400
                     prose-a:no-underline hover:prose-a:underline
                     prose-li:text-gray-700 dark:prose-li:text-gray-300
                     prose-th:text-gray-900 dark:prose-th:text-white
                     prose-td:text-gray-700 dark:prose-td:text-gray-300
                     prose-img:rounded-lg prose-img:shadow-lg"
              v-html="contenidoHTML"
            ></article>

            <!-- Sin resultados de búsqueda -->
            <div v-if="busqueda && !contenidoHTML" class="text-center py-12">
              <MagnifyingGlassIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-600 dark:text-gray-400">
                No se encontraron resultados para "{{ busqueda }}"
              </p>
            </div>
          </div>
        </main>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { marked } from 'marked'
import AppLayout from '@/components/layout/AppLayout.vue'
import {
  BookOpenIcon,
  DocumentTextIcon,
  CpuChipIcon,
  MagnifyingGlassIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'

// Configurar marked
marked.setOptions({
  breaks: true,
  gfm: true,
  headerIds: true,
  mangle: false,
})

// Documentos disponibles
const documentos = [
  {
    id: 'readme',
    nombre: 'Introducción',
    archivo: 'README.md',
    icon: BookOpenIcon,
  },
  {
    id: 'manual',
    nombre: 'Manual de Usuario',
    archivo: 'MANUAL_USUARIO.md',
    icon: DocumentTextIcon,
  },
  {
    id: 'funcionalidades',
    nombre: 'Funcionalidades',
    archivo: 'FUNCIONALIDADES.md',
    icon: CpuChipIcon,
  },
]

// Estado
const documentoActual = ref('readme')
const contenidoMarkdown = ref('')
const busqueda = ref('')
const cargando = ref(false)
const error = ref(null)
const tableOfContents = ref([])

// Cambiar documento
const cambiarDocumento = (docId) => {
  documentoActual.value = docId
  cargarDocumento()
}

// Cargar documento desde public/docs/
const cargarDocumento = async () => {
  cargando.value = true
  error.value = null

  try {
    const doc = documentos.find((d) => d.id === documentoActual.value)
    if (!doc) throw new Error('Documento no encontrado')

    const response = await fetch(`/docs/${doc.archivo}`)
    if (!response.ok) {
      throw new Error(`Error al cargar ${doc.nombre}`)
    }

    const texto = await response.text()
    contenidoMarkdown.value = texto

    // Extraer tabla de contenidos
    extraerTableOfContents(texto)
  } catch (err) {
    console.error('Error cargando documentación:', err)
    error.value = 'No se pudo cargar la documentación. Intenta nuevamente.'
  } finally {
    cargando.value = false
  }
}

// Extraer headings para tabla de contenidos
const extraerTableOfContents = (markdown) => {
  const headingRegex = /^(#{2,3})\s+(.+)$/gm
  const headings = []
  let match

  while ((match = headingRegex.exec(markdown)) !== null) {
    const level = match[1].length
    const text = match[2].trim()
    const id = text
      .toLowerCase()
      .replace(/[^\w\s-]/g, '')
      .replace(/\s+/g, '-')

    headings.push({
      level,
      text,
      id,
    })
  }

  tableOfContents.value = headings
}

// Renderizar Markdown a HTML
const contenidoHTML = computed(() => {
  if (!contenidoMarkdown.value) return ''

  let contenido = contenidoMarkdown.value

  // Si hay búsqueda, filtrar contenido
  if (busqueda.value.trim()) {
    const searchTerm = busqueda.value.toLowerCase()
    const lines = contenido.split('\n')
    const filteredLines = []
    let includeSection = false

    for (let i = 0; i < lines.length; i++) {
      const line = lines[i]
      const isHeading = /^#{1,6}\s/.test(line)

      if (line.toLowerCase().includes(searchTerm)) {
        includeSection = true
        filteredLines.push(line)
      } else if (isHeading) {
        // Nuevo heading, resetear includeSection
        includeSection = false
      } else if (includeSection) {
        filteredLines.push(line)
      }
    }

    if (filteredLines.length === 0) {
      return ''
    }

    contenido = filteredLines.join('\n')
  }

  // Parsear markdown
  let html = marked(contenido)

  // Agregar IDs a los headings para scroll
  html = html.replace(
    /<h([2-3])>(.+?)<\/h\1>/g,
    (match, level, text) => {
      const id = text
        .toLowerCase()
        .replace(/<[^>]*>/g, '')
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
      return `<h${level} id="${id}">${text}</h${level}>`
    }
  )

  return html
})

// Scroll suave a un heading
const scrollToHeading = (id) => {
  nextTick(() => {
    const element = document.getElementById(id)
    if (element) {
      element.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
      })
    }
  })
}

// Cargar documento inicial
onMounted(() => {
  cargarDocumento()
})

// Recargar cuando cambia el documento
watch(documentoActual, () => {
  busqueda.value = '' // Limpiar búsqueda al cambiar de documento
})
</script>

<style scoped>
/* Estilos adicionales para el contenido markdown */
.prose :deep(pre) {
  @apply overflow-x-auto;
}

.prose :deep(code) {
  @apply text-sm;
}

.prose :deep(table) {
  @apply w-full border-collapse;
}

.prose :deep(th),
.prose :deep(td) {
  @apply border border-gray-300 dark:border-gray-700 px-4 py-2;
}

.prose :deep(th) {
  @apply bg-gray-100 dark:bg-gray-800;
}

.prose :deep(img) {
  @apply max-w-full h-auto;
}

.prose :deep(blockquote) {
  @apply border-l-4 border-primary-500 pl-4 italic;
}

/* Smooth scroll para toda la página */
html {
  scroll-behavior: smooth;
}
</style>
