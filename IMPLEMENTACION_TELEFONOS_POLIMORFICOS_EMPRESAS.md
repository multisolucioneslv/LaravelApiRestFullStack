# IMPLEMENTACION: Sistema de Telefonos Polimorficos en Empresas

**Fecha:** 2025-10-16
**Objetivo:** Replicar el sistema de telefonos polimorficos de UserEdit a EmpresaEdit

---

## EN QUE ESTAMOS TRABAJANDO

Se implemento con exito el sistema de telefonos multiples usando relaciones polimorficas para el modulo de Empresas, replicando EXACTAMENTE la funcionalidad que ya existe en el modulo de Usuarios.

---

## RESUMEN DE LA TAREA

### Requisitos:
- Las empresas deben poder tener multiples telefonos
- Usar la tabla `phones` con relacion polimorfica `phonable_type` y `phonable_id`
- Replicar la logica EXACTA que ya funciona en UserEdit

### Fases Completadas:
1. Investigacion de como funciona en UserEdit
2. Implementacion en modelo Empresa
3. Actualizacion de EmpresaController
4. Actualizacion de validaciones (UpdateEmpresaRequest)
5. Actualizacion de EmpresaResource para retornar phones
6. Actualizacion de EmpresaEdit.vue para enviar phones
7. Generacion de este reporte

---

## FASE 1: INVESTIGACION - Sistema en UserEdit

### 1.1 UserEdit.vue (Frontend)

**Ubicacion:** `D:/MultisolucionesLV/proyectos/BackendProfesional/frontend/src/views/users/UserEdit.vue`

#### Como define phones en el form:
```javascript
const form = ref({
  usuario: '',
  name: '',
  email: '',
  gender_id: '',
  phones: [{ telefono: '' }],  // Array de objetos con telefono
  chatid: '',
  empresa_id: '',
  activo: true,
})
```

#### Como carga phones desde el backend:
```javascript
form.value = {
  // ... otros campos
  phones: user.phones && user.phones.length > 0
    ? user.phones.map(phone => ({ telefono: phone.telefono }))
    : [{ telefono: '' }],
}
```

#### Como envia phones al backend:
```javascript
const userData = {
  // ... otros campos
}

// Agregar telefonos (filtrar vacios)
const validPhones = form.value.phones.filter(phone => phone.telefono && phone.telefono.trim() !== '')
if (validPhones.length > 0) {
  userData.phones = validPhones
}

await updateUser(userId, userData)
```

#### Componente usado:
```vue
<PhoneInput v-model="form.phones" />
```

### 1.2 UserController (Backend)

**Ubicacion:** `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Http/Controllers/Api/UserController.php`

#### Metodo update - Procesamiento de telefonos (lineas 201-239):
```php
// Actualizar telefonos: el primero como principal, el resto como adicionales
if ($request->has('phones') && is_array($request->phones)) {
    $validPhones = array_filter($request->phones, function ($phoneData) {
        return !empty($phoneData['telefono']);
    });

    if (!empty($validPhones)) {
        // Actualizar o crear el telefono principal
        $firstPhoneData = array_values($validPhones)[0];
        if ($user->phone_id) {
            // Actualizar telefono existente
            $user->phone->update(['telefono' => $firstPhoneData['telefono']]);
        } else {
            // Crear nuevo telefono principal
            $firstPhone = Phone::create([
                'telefono' => $firstPhoneData['telefono'],
            ]);
            $user->update(['phone_id' => $firstPhone->id]);
        }

        // Eliminar telefonos adicionales existentes
        $user->additionalPhones()->delete();

        // Crear nuevos telefonos adicionales
        $additionalPhones = array_slice(array_values($validPhones), 1);
        foreach ($additionalPhones as $phoneData) {
            $user->additionalPhones()->create([
                'telefono' => $phoneData['telefono'],
            ]);
        }
    } else {
        // Si no hay telefonos validos, eliminar relaciones
        if ($user->phone_id) {
            $user->phone->delete();
            $user->update(['phone_id' => null]);
        }
        $user->additionalPhones()->delete();
    }
}
```

**Logica:**
1. Filtra telefonos vacios
2. Primer telefono → telefono principal (`phone_id`)
3. Telefonos restantes → relacion polimorfica (`additionalPhones`)
4. Elimina y recrea los telefonos adicionales en cada actualizacion

### 1.3 Modelo User

**Ubicacion:** `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Models/User.php`

#### Relaciones (lineas 105-118):
```php
/**
 * Telefono principal del usuario (relacion directa)
 */
public function phone()
{
    return $this->belongsTo(Phone::class, 'phone_id');
}

/**
 * Telefonos adicionales del usuario (relacion polimorfica)
 */
public function additionalPhones()
{
    return $this->morphMany(Phone::class, 'phonable');
}
```

### 1.4 Modelo Phone

**Ubicacion:** `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Models/Phone.php`

#### Configuracion (lineas 25-52):
```php
protected $fillable = [
    'telefono',
];

/**
 * Obtiene el modelo padre (User, Cliente, Empresa, etc.)
 */
public function phonable()
{
    return $this->morphTo();
}
```

---

## FASE 2: IMPLEMENTACION EN EMPRESAS

### 2.1 Modelo Empresa

**Archivo:** `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Models/Empresa.php`

#### Cambio Realizado:
Se agrego la relacion `additionalPhones()` usando `morphMany`:

```php
/**
 * Telefono principal de la empresa (relacion directa)
 */
public function phone()
{
    return $this->belongsTo(Phone::class, 'telefono_id');
}

/**
 * Telefonos adicionales de la empresa (relacion polimorfica)
 */
public function additionalPhones()
{
    return $this->morphMany(Phone::class, 'phonable');
}
```

### 2.2 EmpresaController

**Archivo:** `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Http/Controllers/Api/EmpresaController.php`

#### Cambios Realizados:

**A) Metodo index() - Cargar relacion (linea 28):**
```php
$empresas = Empresa::query()
    ->with(['phone', 'additionalPhones', 'currency'])  // ← Agregado additionalPhones
    ->when($search, function ($query, $search) {
        // ...
    })
    ->orderBy('id', 'desc')
    ->paginate($perPage);
```

**B) Metodo show() - Cargar relacion (linea 111):**
```php
$empresa = Empresa::with(['phone', 'additionalPhones', 'currency'])  // ← Agregado additionalPhones
    ->findOrFail($id);
```

**C) Metodo update() - Procesar telefonos (lineas 184-222):**
```php
// Actualizar telefonos: el primero como principal, el resto como adicionales
if ($request->has('phones') && is_array($request->phones)) {
    $validPhones = array_filter($request->phones, function ($phoneData) {
        return !empty($phoneData['telefono']);
    });

    if (!empty($validPhones)) {
        // Actualizar o crear el telefono principal
        $firstPhoneData = array_values($validPhones)[0];
        if ($empresa->telefono_id) {
            // Actualizar telefono existente
            $empresa->phone->update(['telefono' => $firstPhoneData['telefono']]);
        } else {
            // Crear nuevo telefono principal
            $firstPhone = \App\Models\Phone::create([
                'telefono' => $firstPhoneData['telefono'],
            ]);
            $empresa->update(['telefono_id' => $firstPhone->id]);
        }

        // Eliminar telefonos adicionales existentes
        $empresa->additionalPhones()->delete();

        // Crear nuevos telefonos adicionales
        $additionalPhones = array_slice(array_values($validPhones), 1);
        foreach ($additionalPhones as $phoneData) {
            $empresa->additionalPhones()->create([
                'telefono' => $phoneData['telefono'],
            ]);
        }
    } else {
        // Si no hay telefonos validos, eliminar relaciones
        if ($empresa->telefono_id) {
            $empresa->phone->delete();
            $empresa->update(['telefono_id' => null]);
        }
        $empresa->additionalPhones()->delete();
    }
}

$empresa->load(['phone', 'additionalPhones', 'currency']);  // ← Recargar relaciones
```

### 2.3 UpdateEmpresaRequest

**Archivo:** `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Http/Requests/Empresa/UpdateEmpresaRequest.php`

#### Cambios Realizados:

**A) Metodo prepareForValidation() - Decodificar JSON:**
```php
protected function prepareForValidation(): void
{
    // Si phones viene como JSON string (desde FormData), decodificarlo
    if ($this->has('phones') && is_string($this->phones)) {
        $this->merge([
            'phones' => json_decode($this->phones, true)
        ]);
    }
}
```

**B) Reglas de validacion:**
```php
public function rules(): array
{
    return [
        'nombre' => 'required|string|max:200',
        'telefono_id' => 'nullable|exists:phones,id',
        'phones' => 'nullable|array',               // ← Agregado
        'phones.*.telefono' => 'required|string|max:20',  // ← Agregado
        'currency_id' => 'nullable|exists:currencies,id',
        // ... otros campos
    ];
}
```

### 2.4 EmpresaResource

**Archivo:** `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Http/Resources/EmpresaResource.php`

#### Cambios Realizados:

Se agrego el campo `phones` que combina el telefono principal con los adicionales:

```php
// Relaciones
'phone' => $this->whenLoaded('phone', function () {
    return [
        'id' => $this->phone->id,
        'telefono' => $this->phone->telefono,
    ];
}),
'phones' => $this->whenLoaded('additionalPhones', function () {
    // Combinar telefono principal con adicionales
    $phones = [];
    if ($this->phone) {
        $phones[] = ['telefono' => $this->phone->telefono];
    }
    foreach ($this->additionalPhones as $additionalPhone) {
        $phones[] = ['telefono' => $additionalPhone->telefono];
    }
    return $phones;
}),
```

### 2.5 EmpresaEdit.vue

**Archivo:** `D:/MultisolucionesLV/proyectos/BackendProfesional/frontend/src/views/empresas/EmpresaEdit.vue`

#### Cambios Realizados:

**A) Mantuvo el componente PhoneInput (linea 66):**
```vue
<!-- Telefonos (multiples) -->
<div class="md:col-span-2">
  <PhoneInput v-model="form.phones" />
</div>
```

**B) Definicion del form (lineas 235-247):**
```javascript
const form = ref({
  nombre: '',
  email: '',
  telefono_id: null,
  phones: [{ telefono: '' }],  // ← Array de telefonos
  currency_id: '',
  direccion: '',
  zona_horaria: '',
  logo: null,
  favicon: null,
  fondo_login: null,
  activo: true,
})
```

**C) Carga de telefonos desde backend (lineas 272-275):**
```javascript
// Convertir telefonos a formato esperado por PhoneInput
form.value.phones = empresa.phones && empresa.phones.length > 0
  ? empresa.phones.map(phone => ({ telefono: phone.telefono }))
  : [{ telefono: '' }]
```

**D) Envio de telefonos al backend (lineas 356-361):**
```javascript
// Agregar telefonos (filtrar vacios)
const validPhones = form.value.phones.filter(phone => phone.telefono && phone.telefono.trim() !== '')
if (validPhones.length > 0) {
  // Enviar como JSON string para FormData
  formData.append('phones', JSON.stringify(validPhones))
}
```

**IMPORTANTE:** Se envia como JSON string porque FormData no soporta arrays directamente. El metodo `prepareForValidation()` en UpdateEmpresaRequest se encarga de decodificarlo.

---

## ARCHIVOS MODIFICADOS

### Backend (Laravel):
1. `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Models/Empresa.php`
   - Agregada relacion `additionalPhones()`

2. `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Http/Controllers/Api/EmpresaController.php`
   - Metodo `index()`: Cargar relacion `additionalPhones`
   - Metodo `show()`: Cargar relacion `additionalPhones`
   - Metodo `update()`: Logica completa de procesamiento de telefonos polimorficos

3. `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Http/Requests/Empresa/UpdateEmpresaRequest.php`
   - Agregado `prepareForValidation()` para decodificar JSON
   - Agregadas reglas de validacion para `phones` y `phones.*.telefono`

4. `D:/MultisolucionesLV/proyectos/BackendProfesional/backend/app/Http/Resources/EmpresaResource.php`
   - Agregado campo `phones` que combina telefono principal + adicionales

### Frontend (Vue3):
5. `D:/MultisolucionesLV/proyectos/BackendProfesional/frontend/src/views/empresas/EmpresaEdit.vue`
   - Mantenido componente `PhoneInput`
   - Agregado campo `phones` en el form
   - Logica para cargar telefonos desde backend
   - Logica para enviar telefonos como JSON string en FormData

---

## COMO FUNCIONA EL SISTEMA

### Flujo Completo:

1. **Frontend carga empresa:**
   - EmpresaEdit.vue llama a `fetchEmpresa(id)`
   - Backend retorna empresa con `phones` (principal + adicionales combinados)
   - Se mapean a formato `[{ telefono: '...' }]` para PhoneInput

2. **Usuario edita telefonos:**
   - Puede agregar/eliminar telefonos usando PhoneInput
   - PhoneInput maneja internamente el array de telefonos

3. **Usuario guarda cambios:**
   - Frontend filtra telefonos vacios
   - Convierte array a JSON string
   - Envia en FormData como `phones`

4. **Backend procesa request:**
   - `prepareForValidation()` decodifica JSON string a array
   - Validacion verifica que sea array y cada telefono tenga formato correcto
   - Controller procesa:
     - Primer telefono → telefono principal (`telefono_id`)
     - Telefonos restantes → relacion polimorfica (`additionalPhones`)

5. **Backend retorna respuesta:**
   - Recarga relaciones (`phone`, `additionalPhones`, `currency`)
   - EmpresaResource combina telefonos en un solo array `phones`
   - Frontend recibe empresa actualizada

---

## ESTRUCTURA DE DATOS

### Tabla `phones`:
```sql
id | telefono | phonable_type | phonable_id | created_at | updated_at | deleted_at
---|----------|---------------|-------------|------------|------------|------------
1  | 111-1111 | NULL          | NULL        | ...        | ...        | NULL
2  | 222-2222 | App\Models\Empresa | 1      | ...        | ...        | NULL
3  | 333-3333 | App\Models\Empresa | 1      | ...        | ...        | NULL
```

### Tabla `empresas`:
```sql
id | nombre     | telefono_id | ...
---|------------|-------------|-----
1  | Mi Empresa | 1           | ...
```

**Explicacion:**
- `telefono_id = 1` → Telefono principal (111-1111)
- Registros con `phonable_type = 'App\Models\Empresa'` y `phonable_id = 1` → Telefonos adicionales

---

## ESTADO FINAL

### Tareas Completadas:
- [x] Investigar sistema de telefonos en UserEdit
- [x] Agregar relacion `additionalPhones` en modelo Empresa
- [x] Actualizar EmpresaController para manejar telefonos polimorficos
- [x] Actualizar UpdateEmpresaRequest con validacion de phones
- [x] Actualizar EmpresaResource para incluir phones polimorficos
- [x] Actualizar EmpresaEdit.vue para enviar array phones
- [x] Generar reporte de implementacion

### Sistema Funcionando:
- Las empresas ahora pueden tener multiples telefonos
- Se usa la misma tabla `phones` con relacion polimorfica
- La logica replica EXACTAMENTE el sistema de UserEdit
- Frontend y backend estan sincronizados

---

## NOTAS TECNICAS

1. **Por que JSON.stringify en FormData:**
   - FormData no soporta arrays directamente
   - Se envia como string JSON
   - Backend lo decodifica en `prepareForValidation()`

2. **Por que eliminar y recrear telefonos adicionales:**
   - Simplifica la logica
   - Evita problemas de sincronizacion
   - Garantiza que los telefonos siempre esten actualizados

3. **Por que telefono principal + telefonos adicionales:**
   - Compatibilidad con estructura existente de base de datos
   - Empresa tiene campo `telefono_id` (relacion directa)
   - Telefonos adicionales usan relacion polimorfica

---

## PROXIMOS PASOS (Opcional)

Si se desea mejorar el sistema en el futuro:

1. **Optimizar eliminacion de telefonos adicionales:**
   - En lugar de eliminar y recrear, hacer sync inteligente
   - Solo crear/actualizar/eliminar los que cambiaron

2. **Agregar tipos de telefono:**
   - Principal, Secundario, Fax, etc.
   - Campo `tipo` en tabla `phones`

3. **Validaciones adicionales:**
   - Formato de telefono por pais
   - Telefono unico por empresa

---

**Fin del Reporte**
