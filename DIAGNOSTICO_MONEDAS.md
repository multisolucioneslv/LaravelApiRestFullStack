# 🔍 DIAGNÓSTICO: Problema tabla "monedas"

**Fecha:** 2025-10-16
**Proyecto:** BackendProfesional
**Error reportado:** `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'backend_profesional.monedas' doesn't exist`

---

## ❌ PROBLEMA REPORTADO

Al intentar actualizar una empresa en la ruta `http://localhost:5173/empresas/1/edit`, se produce el siguiente error:

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'backend_profesional.monedas' doesn't exist
(Connection: mysql, SQL: select count(*) as aggregate from `monedas` where `id` = 4)
```

---

## 🎯 CAUSA RAÍZ

**INCONSISTENCIA ENTRE MODELO Y VALIDACIONES**

- ✅ La migración crea la tabla: **`currencies`**
- ✅ El modelo `Currency` apunta correctamente a: **`currencies`**
- ❌ Los FormRequest validan contra la tabla: **`monedas`** (que NO existe)

### El problema ocurre en la VALIDACIÓN, no en el modelo

Cuando Laravel ejecuta la validación `exists:monedas,id`, intenta verificar si el ID existe en la tabla `monedas`, pero esta tabla no existe en la base de datos. La tabla real se llama `currencies`.

---

## 📋 EVIDENCIA

### 1️⃣ Migración

**Archivo:** `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\database\migrations\0000_00_00_000002_create_currencies_table.php`

```php
Schema::create('currencies', function (Blueprint $table) {
    $table->id();
    $table->string('codigo', 10);
    $table->string('nombre', 100);
    $table->string('simbolo', 10);
    $table->decimal('tasa_cambio', 10, 4)->default(1.0000);
    $table->boolean('activo')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

**✅ Nombre de tabla en migración:** `currencies`

---

### 2️⃣ Modelo

**Archivo:** `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Models\Currency.php`

```php
class Currency extends Model
{
    protected $table = 'currencies'; // ✅ CORRECTO

    protected $fillable = [
        'codigo',
        'nombre',
        'simbolo',
        'tasa_cambio',
        'activo',
    ];
}
```

**✅ Propiedad $table:** `'currencies'` (CORRECTO)

---

### 3️⃣ Validaciones (ORIGEN DEL ERROR)

**Archivos afectados con validaciones incorrectas:**

1. ❌ `app\Http\Requests\Empresa\UpdateEmpresaRequest.php` (línea 22) - `exists:monedas,id`
2. ❌ `app\Http\Requests\Empresa\StoreEmpresaRequest.php` (línea 22) - `exists:monedas,id`
3. ❌ `app\Http\Requests\Venta\UpdateVentaRequest.php` (línea 26) - `exists:monedas,id`
4. ❌ `app\Http\Requests\Venta\StoreVentaRequest.php` (línea 26) - `exists:monedas,id`
5. ❌ `app\Http\Requests\Cotizacion\UpdateCotizacionRequest.php` (línea 25) - `exists:monedas,id`
6. ❌ `app\Http\Requests\Cotizacion\StoreCotizacionRequest.php` (línea 25) - `exists:monedas,id`
7. ❌ `app\Http\Requests\Moneda\StoreMonedaRequest.php` (línea 20) - `unique:monedas,codigo`
8. ❌ `app\Http\Requests\Moneda\UpdateMonedaRequest.php` (línea 27) - `unique:monedas,codigo`

**Ejemplos de los errores:**

```php
// ❌ INCORRECTO - Validación "exists" busca en tabla "monedas" que NO existe
'moneda_id' => 'nullable|exists:monedas,id',

// ✅ CORRECTO - Debe buscar en tabla "currencies"
'moneda_id' => 'nullable|exists:currencies,id',

// ❌ INCORRECTO - Validación "unique" busca en tabla "monedas" que NO existe
'codigo' => 'required|string|max:10|unique:monedas,codigo',

// ✅ CORRECTO - Debe buscar en tabla "currencies"
'codigo' => 'required|string|max:10|unique:currencies,codigo',
```

---

### 4️⃣ Base de Datos

**Verificación de migración:**

```bash
php artisan migrate:status
```

**Resultado:**
```
0000_00_00_000002_create_currencies_table ........ [Ran]
```

✅ La tabla **`currencies`** existe y está migrada correctamente.
❌ La tabla **`monedas`** NO existe en la base de datos.

---

## 🛠️ SOLUCIÓN PROPUESTA

### ✅ Opción 1: Corregir las validaciones (RECOMENDADA)

**Cambiar todas las validaciones de `monedas` a `currencies`**

Esta es la solución correcta porque:
- Mantiene la consistencia con el modelo
- No requiere cambios en la base de datos
- Es el enfoque estándar de Laravel (nombres en inglés)

**Archivos a modificar (8 archivos):**

**Para validaciones `exists`:**
```php
// CAMBIAR ESTO:
'moneda_id' => 'nullable|exists:monedas,id',

// POR ESTO:
'moneda_id' => 'nullable|exists:currencies,id',
```

**Para validaciones `unique`:**
```php
// CAMBIAR ESTO:
'codigo' => 'required|string|max:10|unique:monedas,codigo',

// POR ESTO:
'codigo' => 'required|string|max:10|unique:currencies,codigo',

// Y TAMBIÉN (UpdateMonedaRequest):
Rule::unique('monedas', 'codigo')->ignore($monedaId),

// POR:
Rule::unique('currencies', 'codigo')->ignore($monedaId),
```

---

### ⚠️ Opción 2: Cambiar el modelo a tabla "monedas" (NO RECOMENDADA)

Requeriría:
1. Modificar el modelo `Currency` para usar `$table = 'monedas'`
2. Cambiar la migración para crear tabla `monedas`
3. Ejecutar `php artisan migrate:fresh` (PERDERÍA DATOS)

**NO se recomienda** porque:
- Inconsistencia con otros modelos en inglés (Gender, Phone, etc.)
- Pérdida de datos en producción
- Más cambios necesarios

---

## 📝 ARCHIVOS A MODIFICAR

### ✅ Archivos a corregir (8 en total):

**Con validación `exists:monedas,id` → `exists:currencies,id`:**

1. `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Requests\Empresa\UpdateEmpresaRequest.php` (línea 22)
2. `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Requests\Empresa\StoreEmpresaRequest.php` (línea 22)
3. `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Requests\Venta\UpdateVentaRequest.php` (línea 26)
4. `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Requests\Venta\StoreVentaRequest.php` (línea 26)
5. `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Requests\Cotizacion\UpdateCotizacionRequest.php` (línea 25)
6. `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Requests\Cotizacion\StoreCotizacionRequest.php` (línea 25)

**Con validación `unique:monedas,codigo` → `unique:currencies,codigo`:**

7. `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Requests\Moneda\StoreMonedaRequest.php` (línea 20)
8. `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Requests\Moneda\UpdateMonedaRequest.php` (línea 27)

---

## 🚀 PASOS PARA SOLUCIONAR

### Paso 1: Corregir validaciones

**En los 6 primeros archivos (Empresa, Venta, Cotizacion), cambiar:**

```php
'moneda_id' => 'nullable|exists:monedas,id',
```

Por:

```php
'moneda_id' => 'nullable|exists:currencies,id',
```

**En StoreMonedaRequest.php (línea 20), cambiar:**

```php
'codigo' => 'required|string|max:10|unique:monedas,codigo',
```

Por:

```php
'codigo' => 'required|string|max:10|unique:currencies,codigo',
```

**En UpdateMonedaRequest.php (línea 27), cambiar:**

```php
Rule::unique('monedas', 'codigo')->ignore($monedaId),
```

Por:

```php
Rule::unique('currencies', 'codigo')->ignore($monedaId),
```

### Paso 2: Verificar otros FormRequests

Buscar si hay más archivos con el mismo problema:

```bash
grep -r "exists:monedas" app/Http/Requests/
```

### Paso 3: Limpiar caché de validaciones

```bash
php artisan config:clear
php artisan cache:clear
```

### Paso 4: Probar la actualización de empresa

Volver a intentar actualizar la empresa desde el frontend.

---

## ✅ VERIFICACIÓN POST-CORRECCIÓN

Después de aplicar la solución, verificar:

1. ✅ La empresa se actualiza sin errores
2. ✅ La validación de `moneda_id` funciona correctamente
3. ✅ El ID de moneda existe en la tabla `currencies`
4. ✅ Las ventas y cotizaciones también funcionan

---

## 📊 IMPACTO

- **Severidad:** 🔴 ALTA (bloquea actualización de empresas)
- **Módulos afectados:** Empresas, Ventas, Cotizaciones
- **Tipo de error:** Inconsistencia en validaciones
- **Tiempo estimado de corrección:** ⏱️ 5-10 minutos
- **Riesgo de corrección:** 🟢 BAJO (solo cambios en validaciones)

---

## 🔍 LECCIÓN APRENDIDA

**Problema:** Inconsistencia entre nombres de tablas en migraciones/modelos vs validaciones.

**Prevención futura:**
1. Usar constantes para nombres de tablas
2. Validar que los FormRequest usen el nombre correcto de tabla
3. Crear tests de integración que validen las reglas de validación
4. Mantener convención consistente (inglés o español, no mezclar)

---

**Generado por:** Claude Code
**Fecha:** 2025-10-16
