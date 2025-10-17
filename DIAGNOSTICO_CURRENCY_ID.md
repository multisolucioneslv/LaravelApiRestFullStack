# 🔍 DIAGNÓSTICO: currency_id no se guarda

**Fecha:** 2025-10-16
**Estado:** PROBLEMA IDENTIFICADO

---

## PROBLEMA:

Al actualizar una empresa desde el frontend, el campo `currency_id` aparece como `null` en la respuesta, aunque se envía un valor válido desde el formulario.

```json
{
  "success": true,
  "message": "Empresa actualizada exitosamente",
  "data": {
    "id": 1,
    "nombre": "Yapame",
    "currency_id": null,  // ← PROBLEMA: debería tener un valor
    "email": "contacto@yapame.com"
  }
}
```

---

## EVIDENCIA RECOLECTADA:

### 1. Migración ✅
**Archivo:** `database/migrations/0000_00_00_000005_create_empresas_table.php`
**Línea 18:**
```php
$table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
```
**Estado:** El campo en la base de datos se llama `currency_id` ✅

---

### 2. Modelo ✅
**Archivo:** `app/Models/Empresa.php`
**Líneas 25-38:**
```php
protected $fillable = [
    'nombre',
    'telefono_id',
    'currency_id',  // ← Campo está en fillable
    'email',
    'direccion',
    'logo',
    'favicon',
    'fondo_login',
    'zona_horaria',
    'horarios',
    'activo',
    'show_loading_effect',
];
```
**Estado:** El campo `currency_id` SÍ está en el array `$fillable` ✅

---

### 3. Controller ⚠️
**Archivo:** `app/Http/Controllers/Api/EmpresaController.php`
**Método:** `update()` (Líneas 123-190)
**Línea 130:**
```php
$data = [
    'nombre' => $request->nombre,
    'telefono_id' => $request->telefono_id,
    'currency_id' => $request->currency_id,  // ← Intenta obtener currency_id
    'email' => $request->email,
    'direccion' => $request->direccion,
    'zona_horaria' => $request->zona_horaria,
    'activo' => $request->activo,
];
```
**Estado:** El controller espera `$request->currency_id` ⚠️

---

### 4. FormRequest ❌ (CAUSA DEL PROBLEMA)
**Archivo:** `app/Http/Requests/Empresa/UpdateEmpresaRequest.php`
**Línea 22:**
```php
public function rules(): array
{
    return [
        'nombre' => 'required|string|max:200',
        'telefono_id' => 'nullable|exists:telefonos,id',
        'moneda_id' => 'nullable|exists:currencies,id',  // ← PROBLEMA: Se valida como moneda_id
        'email' => 'nullable|email|max:100',
        // ...
    ];
}
```
**Estado:** El FormRequest valida `moneda_id` en lugar de `currency_id` ❌

---

### 5. Frontend ✅
**Archivo:** `frontend/src/views/empresas/EmpresaEdit.vue`
**Línea 357:**
```javascript
if (form.value.moneda_id) {
  formData.append('moneda_id', form.value.moneda_id)  // ← Envía moneda_id
}
```
**Estado:** El frontend envía correctamente `moneda_id` ✅

---

## CAUSA RAÍZ:

**INCONSISTENCIA EN EL NOMBRE DEL CAMPO**

Existe una desincronización entre los nombres del campo a través de las capas de la aplicación:

| Capa | Nombre del Campo |
|------|------------------|
| Base de Datos (Migración) | `currency_id` ✅ |
| Modelo (Fillable) | `currency_id` ✅ |
| Controller (Update) | `currency_id` ⚠️ |
| FormRequest (Validación) | `moneda_id` ❌ |
| Frontend (Formulario) | `moneda_id` ✅ |

### Flujo del Problema:

1. **Frontend** envía `moneda_id: 2`
2. **FormRequest** valida correctamente `moneda_id` y pasa la validación ✅
3. **Controller** intenta obtener `$request->currency_id` (que NO existe)
4. Como `currency_id` no existe en el request, se asigna `null`
5. **Modelo** recibe `currency_id: null` y lo guarda
6. **Base de datos** guarda `null` en el campo `currency_id`

---

## SOLUCIÓN:

Hay dos enfoques posibles:

### Opción 1: Cambiar FormRequest para usar `currency_id` (RECOMENDADO)

**Ventajas:**
- Mantiene consistencia con la base de datos
- Sigue la convención de Laravel (foreignKey + _id)
- Solo requiere cambiar 1 archivo en el backend

**Cambios necesarios:**

**Archivo:** `app/Http/Requests/Empresa/UpdateEmpresaRequest.php`

```php
// ANTES:
'moneda_id' => 'nullable|exists:currencies,id',

// DESPUÉS:
'currency_id' => 'nullable|exists:currencies,id',
```

Y actualizar el mensaje de validación:

```php
// ANTES:
'moneda_id.exists' => 'La moneda seleccionada no es válida.',

// DESPUÉS:
'currency_id.exists' => 'La moneda seleccionada no es válida.',
```

**Archivo:** `app/Http/Requests/Empresa/StoreEmpresaRequest.php` (verificar si tiene el mismo problema)

**Archivos Frontend que deben actualizarse:**
- `frontend/src/views/empresas/EmpresaEdit.vue`
- `frontend/src/views/empresas/EmpresaCreate.vue`

---

### Opción 2: Cambiar Controller para mapear `moneda_id` → `currency_id`

**Ventajas:**
- No requiere cambiar el frontend
- Puede ser más rápido

**Desventajas:**
- Crea inconsistencia interna
- Requiere mapeo manual

**Cambios necesarios:**

**Archivo:** `app/Http/Controllers/Api/EmpresaController.php`

```php
// Línea 130
$data = [
    'nombre' => $request->nombre,
    'telefono_id' => $request->telefono_id,
    'currency_id' => $request->moneda_id,  // ← Mapear moneda_id a currency_id
    'email' => $request->email,
    // ...
];
```

---

## RECOMENDACIÓN FINAL:

**Opción 1 es la mejor práctica** porque:
1. Mantiene consistencia en todo el sistema
2. Usa la convención estándar de Laravel
3. Evita confusión futura
4. El campo se llama `currencies` (plural en inglés), por lo que `currency_id` es más coherente

---

## ARCHIVOS A MODIFICAR:

### Backend:
1. `app/Http/Requests/Empresa/UpdateEmpresaRequest.php` (línea 22 y 39)
2. `app/Http/Requests/Empresa/StoreEmpresaRequest.php` (verificar)

### Frontend:
1. `frontend/src/views/empresas/EmpresaEdit.vue`
2. `frontend/src/views/empresas/EmpresaCreate.vue`

---

## PRÓXIMOS PASOS:

1. ✅ Diagnóstico completado
2. ⏳ Corregir FormRequests en backend
3. ⏳ Actualizar componentes Vue en frontend
4. ⏳ Probar actualización de empresa
5. ⏳ Verificar que currency_id se guarde correctamente
6. ⏳ Commit de corrección

---

**Investigado por:** LaravelAPI Agent
**Fecha:** 2025-10-16
