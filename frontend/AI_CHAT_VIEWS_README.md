# Vistas de Gestión de AI Chat - Documentación

## Resumen de Implementación

Se han creado 2 vistas completas de Vue 3 Composition API para el sistema de gestión de AI Chat multi-tenant, junto con sus composables correspondientes.

---

## Archivos Creados

### 1. Composables

#### `src/composables/useAIManagement.js`
Composable para SuperAdmin - Gestión Global de AI

**Funcionalidades:**
- Obtener estadísticas globales del sistema AI
- Listar empresas con paginación y filtros
- Ver detalles de configuración de cada empresa
- Toggle AI habilitado/deshabilitado por empresa
- Actualizar configuración de AI de empresas
- Resetear uso mensual de empresas
- Obtener planes disponibles
- Búsqueda y filtros avanzados

**Endpoints utilizados:**
- `GET /api/superadmin/ai/stats/global`
- `GET /api/superadmin/ai/empresas`
- `GET /api/superadmin/ai/empresas/{id}/config`
- `POST /api/superadmin/ai/empresas/{id}/toggle`
- `PUT /api/superadmin/ai/empresas/{id}/config`
- `POST /api/superadmin/ai/empresas/{id}/reset-usage`
- `GET /api/superadmin/ai/plans`

---

#### `src/composables/useAIConfig.js`
Composable para Admin de Empresa - Configuración AI

**Funcionalidades:**
- Obtener configuración actual de AI
- Actualizar configuración (plan, credenciales, presupuesto)
- Ver estadísticas de uso mensual
- Listar usuarios de la empresa
- Actualizar permisos de AI por usuario
- Validaciones de formularios (API key, tokens, temperatura, presupuesto)
- Métodos helper para actualizar modo, credenciales y presupuesto

**Endpoints utilizados:**
- `GET /api/ai-config`
- `PUT /api/ai-config`
- `GET /api/ai-config/usage-stats`
- `GET /api/ai-config/users`
- `POST /api/ai-config/users/permissions`
- `GET /api/ai-config/plans`

**Validaciones implementadas:**
- API Key: mínimo 20 caracteres, debe comenzar con "sk-"
- Temperature: entre 0 y 2
- Max Tokens: entre 100 y 4000
- Presupuesto: mayor a 0

---

### 2. Vistas

#### `src/views/settings/AIManagementView.vue`
Vista para SuperAdmin - Gestión Global de AI

**Ubicación de la ruta:** `/settings/ai-management`

**Secciones implementadas:**

1. **Estadísticas Globales**
   - Total de empresas
   - Empresas con AI habilitado
   - Empresas con API key propia
   - Total queries del mes
   - Uso total mensual
   - Presupuesto total mensual

2. **Planes Disponibles**
   - Plan Básico (Regex): $0.002 - $0.005 por query
   - Plan Intermedio (Function Calling): $0.005 - $0.010 por query
   - Plan Premium (Double Call): $0.010 - $0.020 por query
   - Descripción, pros y contras de cada plan

3. **Gestión de Empresas**
   - Tabla con todas las empresas
   - Columnas: Nombre, Email, AI Habilitado (toggle), Modo, Uso/Presupuesto, Queries, Acciones
   - Barra de progreso de uso
   - Filtros: búsqueda, AI habilitado/deshabilitado, modo de detección
   - Paginación
   - Acciones: Ver detalles, Resetear uso

4. **Modal: Detalles de Empresa**
   - Información de la empresa
   - Configuración AI (modo, presupuesto)
   - Estadísticas detalladas
   - Lista de usuarios con acceso AI
   - Formulario para editar configuración

**Características:**
- Diseño responsive (mobile-first)
- Dark mode compatible
- Loading states
- Validaciones en tiempo real
- Animaciones y transiciones suaves

---

#### `src/views/empresa/AIConfigView.vue`
Vista para Admin de Empresa - Configuración AI

**Ubicación de la ruta:** `/empresa/configuracion/ai`

**Secciones implementadas:**

1. **Estado del Servicio**
   - Alert informativo del estado de AI (habilitado/deshabilitado)
   - Mensaje descriptivo según el estado

2. **Plan Actual**
   - Card con información del plan activo
   - Selector para cambiar de plan (dropdown)
   - Costos estimados por plan
   - Botón para guardar cambios

3. **Credenciales OpenAI (Opcional)**
   - Checkbox para usar credenciales propias
   - Formulario con:
     - API Key (input password)
     - Modelo (select: gpt-3.5-turbo, gpt-4, gpt-4-turbo)
     - Max Tokens (input number: 100-4000)
     - Temperature (input range: 0-2)
   - Validaciones en tiempo real
   - Botón para guardar credenciales

4. **Presupuesto Mensual**
   - Input para establecer presupuesto mensual (USD)
   - Visualización de uso actual
   - Barra de progreso con colores según % usado:
     - Verde: < 70%
     - Amarillo: 70-89%
     - Rojo: >= 90%
   - Fecha de próximo reset (primer día del siguiente mes)
   - Botón para actualizar presupuesto

5. **Estadísticas de Uso**
   - Total de consultas del mes
   - Uso monetario del mes
   - Última consulta (fecha/hora)
   - Usuarios con acceso AI

6. **Gestión de Permisos de Usuarios**
   - Tabla con todos los usuarios de la empresa
   - Columnas: Usuario, Rol, Acceso AI (toggle)
   - Toggle para otorgar/revocar permisos de AI por usuario

**Características:**
- Solo se muestra si AI está habilitado
- Diseño responsive (mobile-first)
- Dark mode compatible
- Loading states
- Validaciones reactivas
- Toast notifications

---

### 3. Rutas

Se agregaron las siguientes rutas en `src/router/index.js`:

```javascript
// Configuración de AI para Admin de Empresa
{
  path: '/empresa/configuracion/ai',
  name: 'empresa.ai-config',
  component: () => import('@/views/empresa/AIConfigView.vue'),
  meta: {
    requiresAuth: true,
    title: 'Configuración de AI Chat'
  }
},

// Gestión Global de AI (Solo SuperAdmin)
{
  path: '/settings/ai-management',
  name: 'settings.ai-management',
  component: () => import('@/views/settings/AIManagementView.vue'),
  meta: {
    requiresAuth: true,
    title: 'Gestión Global de AI'
  }
}
```

---

## Patrones y Tecnologías Utilizadas

### Vue 3 Composition API
- `<script setup>` syntax
- `ref`, `computed`, `watch`, `onMounted`
- Reactivity completa

### Tailwind CSS
- Utility-first CSS
- Responsive design (md:, lg: breakpoints)
- Dark mode (`dark:` classes)
- Custom colors y gradientes

### Composables
- Lógica reutilizable
- Separación de concerns
- Fácil testing

### Validaciones
- En tiempo real
- Mensajes descriptivos en español
- Estados de error visuales

### UX/UI
- Loading states con spinners
- Toast notifications (éxito/error)
- Confirmaciones con SweetAlert2 (vía useAlert)
- Animaciones smooth
- Color coding (verde/amarillo/rojo según uso)

---

## Integración con Backend

### Estructura de Respuestas Esperadas

#### Estadísticas Globales
```json
{
  "data": {
    "total_empresas": 50,
    "empresas_ai_enabled": 30,
    "empresas_api_key_propia": 10,
    "total_queries_mes": 1500,
    "uso_total_mensual": 15.50,
    "presupuesto_total_mensual": 200.00
  }
}
```

#### Lista de Empresas
```json
{
  "data": [
    {
      "id": 1,
      "nombre": "Empresa ABC",
      "email": "admin@empresa.com",
      "ai_chat_enabled": true,
      "ai_detection_mode": "function_calling",
      "ai_monthly_budget": 100.00,
      "monthly_usage": 45.50,
      "total_queries": 250,
      "last_query_at": "2025-10-17 10:30:00"
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "total": 50
}
```

#### Configuración de AI (Admin)
```json
{
  "data": {
    "ai_chat_enabled": true,
    "ai_detection_mode": "function_calling",
    "openai_api_key": "sk-...",
    "openai_model": "gpt-4-turbo",
    "openai_max_tokens": 1500,
    "openai_temperature": 0.7,
    "ai_monthly_budget": 100.00
  }
}
```

#### Estadísticas de Uso
```json
{
  "data": {
    "total_queries": 250,
    "monthly_cost": 45.50,
    "last_query_at": "2025-10-17 10:30:00",
    "users_with_access": 5
  }
}
```

#### Usuarios
```json
{
  "data": [
    {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@empresa.com",
      "role": "Administrador",
      "has_ai_permission": true
    }
  ]
}
```

---

## Cómo Usar

### Para SuperAdmin

1. Navegar a `/settings/ai-management`
2. Ver estadísticas globales del sistema
3. Revisar y comparar planes disponibles
4. Filtrar empresas por estado de AI o modo
5. Ver detalles de cada empresa (click en "Ver")
6. Editar configuración de empresas
7. Habilitar/deshabilitar AI con el toggle
8. Resetear uso mensual si es necesario

### Para Admin de Empresa

1. Navegar a `/empresa/configuracion/ai`
2. Verificar que AI esté habilitado
3. Seleccionar plan deseado (Regex, Function Calling, Double Call)
4. (Opcional) Configurar credenciales propias de OpenAI
5. Establecer presupuesto mensual
6. Monitorear uso actual y estadísticas
7. Gestionar permisos de usuarios (quién puede usar AI Chat)

---

## Notas Importantes

### Seguridad
- Las API keys se muestran como password (ocultas)
- Solo SuperAdmin puede ver/editar todas las empresas
- Admin de empresa solo ve su propia configuración
- Permisos de AI se gestionan por usuario

### Validaciones
- Todos los inputs tienen validación en tiempo real
- Mensajes de error descriptivos en español
- Prevención de valores inválidos

### Performance
- Paginación para listas grandes
- Loading states para evitar confusión
- Lazy loading de componentes (vue-router)

### Responsive
- Mobile-first design
- Breakpoints: sm, md, lg, xl
- Tablas con scroll horizontal en móvil

### Dark Mode
- Soporte completo para tema oscuro
- Todos los colores tienen variantes dark:

---

## Próximos Pasos Sugeridos

1. **Backend**: Implementar los endpoints especificados
2. **Testing**: Crear tests unitarios para composables
3. **Permisos**: Agregar guards de ruta para verificar rol (SuperAdmin vs Admin)
4. **Gráficas**: Agregar gráficas de uso histórico con Chart.js o Recharts
5. **Notificaciones**: Implementar sistema de alertas cuando se alcance el 80% del presupuesto
6. **Logs**: Vista de historial de queries de AI por empresa
7. **Export**: Función para exportar estadísticas a CSV/PDF

---

## Dependencias Necesarias

Asegúrate de que estas dependencias estén instaladas:

```json
{
  "vue": "^3.x",
  "vue-router": "^4.x",
  "pinia": "^2.x",
  "axios": "^1.x",
  "tailwindcss": "^3.x"
}
```

---

## Estructura de Archivos Final

```
src/
├── composables/
│   ├── useAIManagement.js    ← Nuevo
│   ├── useAIConfig.js        ← Nuevo
│   └── useAlert.js           (existente)
├── views/
│   ├── settings/
│   │   └── AIManagementView.vue    ← Nuevo
│   └── empresa/
│       ├── EmpresaConfigView.vue   (existente)
│       └── AIConfigView.vue        ← Nuevo
└── router/
    └── index.js              (actualizado con nuevas rutas)
```

---

## Contacto y Soporte

Para cualquier duda o mejora sobre esta implementación, consultar con el equipo de desarrollo.

**Fecha de implementación:** 17 de Octubre, 2025
**Versión:** 1.0.0
**Framework:** Vue 3 Composition API
**Autor:** Claude Code (ExpertoVue)
