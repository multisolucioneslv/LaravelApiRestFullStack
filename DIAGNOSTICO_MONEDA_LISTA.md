# 🔍 DIAGNOSTICO: Moneda no se muestra en lista

**Fecha:** 2025-10-16
**Investigado por:** Claude Code
**Proyecto:** BackendProfesional

---

## 📌 PROBLEMA

La información de la moneda (currency) NO se muestra en la lista de empresas, aunque:
- El campo `currency_id: 4` se guarda correctamente en la base de datos
- El backend carga la relación con `with('currency')`
- El Resource envía los datos completos de la moneda

---

## 🔬 EVIDENCIA RECOLECTADA

### ✅ Backend (CORRECTO)

#### 1. EmpresaController.php (línea 28)
```php
$empresas = Empresa::query()
    ->with(['phone', 'currency'])  // ✅ Carga la relación
```

#### 2. Modelo Empresa.php (líneas 73-76)
```php
public function currency()
{
    return $this->belongsTo(Currency::class, 'currency_id');  // ✅ Relación correcta
}
```

#### 3. EmpresaResource.php (líneas 39-46)
```php
'currency' => $this->whenLoaded('currency', function () {
    return [
        'id' => $this->currency->id,
        'codigo' => $this->currency->codigo,
        'nombre' => $this->currency->nombre,
        'simbolo' => $this->currency->simbolo,
    ];
}),
// ✅ Envía datos completos con la clave 'currency'
```

**Respuesta API esperada:**
```json
{
  "id": 1,
  "nombre": "Yapame",
  "currency_id": 4,
  "currency": {
    "id": 4,
    "codigo": "USD",
    "nombre": "Dólar Estadounidense",
    "simbolo": "$"
  }
}
```

---

### ❌ Frontend (INCORRECTO)

#### Archivo: EmpresasDataTable.vue (líneas 247-256)

```javascript
{
  accessorKey: 'moneda',  // ❌ Busca 'moneda'
  id: 'moneda',
  header: 'Moneda',
  cell: ({ row }) => {
    const moneda = row.original.moneda  // ❌ PROBLEMA AQUÍ
    return h('div', {}, moneda ? `${moneda.nombre} (${moneda.simbolo})` : '-')
  },
  enableHiding: true,
}
```

**Problema:** El código busca `row.original.moneda` pero la API envía `currency`.

---

## 🎯 CAUSA RAÍZ

**DESINCRONIZACIÓN DE NOMENCLATURA:**

| Ubicación | Campo usado |
|-----------|-------------|
| Backend API | `currency` |
| Frontend DataTable | `moneda` ❌ |

El componente Vue está buscando un campo que NO existe en la respuesta de la API.

---

## ✅ SOLUCIÓN

Hay dos opciones para corregir este problema:

### Opción 1: Cambiar Frontend (RECOMENDADA)

Modificar `EmpresasDataTable.vue` línea 252:

```javascript
// ANTES (INCORRECTO)
const moneda = row.original.moneda

// DESPUÉS (CORRECTO)
const currency = row.original.currency
```

Y actualizar línea 253:

```javascript
// ANTES
return h('div', {}, moneda ? `${moneda.nombre} (${moneda.simbolo})` : '-')

// DESPUÉS
return h('div', {}, currency ? `${currency.nombre} (${currency.simbolo})` : '-')
```

**Ventaja:** Mantiene consistencia con la nomenclatura en inglés del backend (currency).

---

### Opción 2: Cambiar Backend (NO RECOMENDADA)

Modificar `EmpresaResource.php` para cambiar la clave de `currency` a `moneda`.

**Desventaja:** Rompe la consistencia con el nombre de la relación en el modelo y complica mantenimiento.

---

## 🚀 IMPLEMENTACIÓN RECOMENDADA

### Archivo a modificar:
`D:\MultisolucionesLV\proyectos\BackendProfesional\frontend\src\components\empresas\EmpresasDataTable.vue`

### Líneas a cambiar:
- **Línea 252:** `const moneda = row.original.moneda` → `const currency = row.original.currency`
- **Línea 253:** `moneda ? ...` → `currency ? ...`

### Código completo corregido:

```javascript
{
  accessorKey: 'currency',  // ✅ CORREGIDO
  id: 'moneda',  // Mantener 'moneda' para el ID de columna (solo etiqueta)
  header: 'Moneda',
  cell: ({ row }) => {
    const currency = row.original.currency  // ✅ CORREGIDO
    return h('div', {}, currency ? `${currency.nombre} (${currency.simbolo})` : '-')
  },
  enableHiding: true,
},
```

---

## 🧪 VERIFICACIÓN POST-CORRECCIÓN

Después de aplicar la corrección, verificar:

1. ✅ La columna "Moneda" muestra el nombre y símbolo
2. ✅ El formato es: "Dólar Estadounidense ($)"
3. ✅ No hay errores en la consola del navegador
4. ✅ El toggle de columnas funciona correctamente

---

## 📝 NOTAS ADICIONALES

### Otras columnas con relaciones:

El mismo patrón se usa para el teléfono (líneas 238-246):

```javascript
{
  accessorKey: 'telefono',
  id: 'telefono',
  header: 'Teléfono',
  cell: ({ row }) => {
    const telefono = row.original.telefono  // ❌ DEBERÍA SER 'phone'
    return h('div', {}, telefono?.telefono || '-')
  },
}
```

**Verificar:** ¿El backend envía `phone` o `telefono`?

Según `EmpresaResource.php` línea 33:
```php
'phone' => $this->whenLoaded('phone', ...)
```

**Acción requerida:** Cambiar también el teléfono de `row.original.telefono` a `row.original.phone`.

---

## ⚠️ RIESGO DE ERRORES SIMILARES

**Patrón detectado:** Inconsistencia español/inglés entre backend y frontend.

### Recomendación:
Establecer convención única:
- **Backend:** Usar inglés en APIs (currency, phone)
- **Frontend:** Usar inglés en código, español en UI/labels

### Checklist de consistencia:
- [ ] Revisar todas las relaciones en DataTables
- [ ] Verificar formularios de creación/edición
- [ ] Actualizar documentación de API

---

## 🎯 RESUMEN EJECUTIVO

| Aspecto | Estado |
|---------|--------|
| Backend | ✅ Funcionando correctamente |
| API Response | ✅ Enviando datos completos |
| Frontend DataTable | ❌ Buscando campo incorrecto |
| Solución | 🔧 Cambiar `moneda` → `currency` en línea 252 |
| Tiempo estimado | ⏱️ 2 minutos |
| Complejidad | 🟢 Baja |

---

**Estado:** PROBLEMA IDENTIFICADO - SOLUCIÓN LISTA PARA IMPLEMENTAR
