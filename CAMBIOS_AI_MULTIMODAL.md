# Cambios Implementados: Sistema Multi-Modo de Detección de Intención

## Fecha: 2025-10-17

## Archivos Modificados

### 1. AIChatController.php
**Ubicación:** `D:\MultisolucionesLV\proyectos\BackendProfesional\backend\app\Http\Controllers\Api\AIChatController.php`

---

## Resumen de Cambios

Se implementó un sistema multi-modo de detección de intención que permite a cada empresa configurar su propio método de análisis de mensajes del usuario. El sistema ahora soporta 3 modos:

1. **REGEX** (por defecto): Detección basada en expresiones regulares
2. **FUNCTION_CALLING**: Uso de Function Calling de OpenAI
3. **DOUBLE_CALL**: Doble llamada a OpenAI (primero detecta intención, luego consulta BD)

---

## Modificaciones Detalladas

### 1. Método `sendMessage()` - Lógica Multi-Modo

**Cambios principales:**

- **Configuración de credenciales personalizadas por empresa:**
  ```php
  $empresa = $user->empresa;

  if ($empresa->openai_api_key) {
      $this->openAIService->setCustomCredentials(
          $empresa->openai_api_key,
          $empresa->openai_model,
          $empresa->openai_max_tokens,
          $empresa->openai_temperature
      );
  }
  ```

- **Detección multi-modo:**
  ```php
  $detectionMode = $empresa->ai_detection_mode ?? 'regex';

  switch ($detectionMode) {
      case 'function_calling':
          $databaseData = $this->detectWithFunctionCalling($request->message, $user);
          break;

      case 'double_call':
          $databaseData = $this->detectWithDoubleCall($request->message, $user);
          break;

      case 'regex':
      default:
          $databaseData = $this->detectAndQueryDatabase($request->message, $user);
          break;
  }
  ```

- **Actualización de estadísticas de uso:**
  ```php
  $empresa->increment('ai_total_queries');
  $empresa->update([
      'ai_last_used_at' => now(),
  ]);
  ```

- **Logs adicionales:** Se agregaron logs en puntos clave para debugging

---

### 2. Refactorización de Queries

Se extrajeron las consultas de base de datos a métodos separados para reutilización:

#### Métodos creados:

- **`queryVentas($arguments, $user)`**
  - Consulta ventas y productos más vendidos
  - Parámetros: `limit` (por defecto 10, máximo 50)

- **`queryProductos($user)`**
  - Consulta productos e inventario
  - Límite: 50 registros

- **`queryUsuarios($user)`**
  - Consulta usuarios de la empresa
  - Límite: 50 registros

- **`queryEmpresas($user)`**
  - Consulta todas las empresas registradas
  - Sin límite

- **`queryProveedores($user)`**
  - Consulta proveedores de la empresa
  - Límite: 50 registros

- **`queryCategorias($user)`**
  - Consulta categorías con conteo de productos
  - Sin límite

---

### 3. Modo REGEX - `detectAndQueryDatabase()`

**Modificación:**
- Refactorizado para usar los nuevos métodos de query
- Mantiene la misma lógica de detección por expresiones regulares
- Ahora retorna llamadas a métodos separados

**Ejemplo:**
```php
if (preg_match('/(venta|ventas|vendido)/i', $message)) {
    return [
        'type' => 'ventas',
        'data' => $this->queryVentas(['limit' => $limit], $user),
    ];
}
```

---

### 4. Modo FUNCTION CALLING - `detectWithFunctionCalling()`

**Nuevo método implementado**

**Descripción:**
- Utiliza OpenAI Function Calling para detectar intenciones
- Define 6 funciones disponibles para OpenAI
- OpenAI decide automáticamente qué función llamar

**Funciones definidas:**
1. `query_ventas` - Consultar ventas y productos más vendidos
2. `query_productos` - Consultar productos e inventario
3. `query_usuarios` - Consultar usuarios registrados
4. `query_empresas` - Consultar empresas
5. `query_proveedores` - Consultar proveedores
6. `query_categorias` - Consultar categorías

**Flujo:**
```php
1. Enviar mensaje del usuario a OpenAI con funciones disponibles
2. OpenAI analiza y decide si llamar a una función
3. Si hay function_call:
   - Extraer nombre de función y argumentos
   - Ejecutar función correspondiente vía executeDatabaseFunction()
4. Retornar datos de la base de datos
```

**Características:**
- Manejo de errores con try-catch
- Logs detallados en cada paso
- Parseo seguro de argumentos JSON

---

### 5. Modo DOUBLE CALL - `detectWithDoubleCall()`

**Nuevo método implementado**

**Descripción:**
- Realiza dos llamadas separadas a OpenAI
- Primera llamada: Detectar intención del usuario
- Segunda llamada: Generar respuesta con datos de BD

**Flujo:**
```php
1. Llamar a OpenAIService->detectIntent($message)
2. Obtener intención detectada (ej: 'ventas', 'productos', 'usuarios')
3. Mapear intención a consulta de base de datos
4. Ejecutar query correspondiente vía queryDatabaseByIntent()
5. Retornar datos
```

**Mapeo de intenciones:**
```php
[
    'ventas' => 'ventas',
    'productos' => 'productos',
    'usuarios' => 'usuarios',
    'empresas' => 'empresas',
    'proveedores' => 'proveedores',
    'categorias' => 'categorias',
]
```

**Características:**
- Validación de respuesta de detectIntent
- Logs en cada paso
- Manejo de intenciones no mapeadas

---

### 6. Método Auxiliar - `executeDatabaseFunction()`

**Nuevo método**

**Propósito:**
- Ejecutar funciones de base de datos según function calling de OpenAI

**Switch case por función:**
```php
switch ($functionName) {
    case 'query_ventas':
        return ['type' => 'ventas', 'data' => $this->queryVentas($arguments, $user)];

    case 'query_productos':
        return ['type' => 'productos', 'data' => $this->queryProductos($user)];

    // ... más casos
}
```

---

### 7. Método Auxiliar - `queryDatabaseByIntent()`

**Nuevo método**

**Propósito:**
- Ejecutar consultas según intención detectada en modo double_call

**Switch case por intención:**
```php
switch ($intent) {
    case 'ventas':
        return ['type' => 'ventas', 'data' => $this->queryVentas([], $user)];

    case 'productos':
        return ['type' => 'productos', 'data' => $this->queryProductos($user)];

    // ... más casos
}
```

---

## Metadata Actualizada

Se agregó el campo `detection_mode` a los metadata de los mensajes del asistente:

```php
'metadata' => [
    'model' => $aiResponse['model'] ?? null,
    'tokens_used' => $aiResponse['usage'] ?? null,
    'database_data_used' => $databaseData !== null,
    'detection_mode' => $detectionMode, // NUEVO
],
```

Y también en el contexto de las conversaciones:

```php
$conversation->addContext('last_database_query', [
    'type' => $databaseData['type'],
    'timestamp' => now()->toDateTimeString(),
    'detection_mode' => $detectionMode, // NUEVO
]);
```

---

## Logs Implementados

Se agregaron logs en puntos estratégicos para debugging:

### Logs de configuración:
```php
Log::info('Configuración AI de empresa', [
    'empresa_id' => $empresa->id,
    'ai_detection_mode' => $empresa->ai_detection_mode,
    'has_custom_api_key' => !empty($empresa->openai_api_key),
]);

Log::info('Credenciales OpenAI personalizadas configuradas', [
    'model' => $empresa->openai_model,
    'max_tokens' => $empresa->openai_max_tokens,
    'temperature' => $empresa->openai_temperature,
]);
```

### Logs de detección de modo:
```php
Log::info('Usando modo: function_calling');
Log::info('Usando modo: double_call');
Log::info('Usando modo: regex (por defecto)');
```

### Logs de function calling:
```php
Log::info('Respuesta de function calling', ['response' => $response]);
Log::info('Function call detectada', ['function' => $functionName, 'arguments' => $arguments]);
Log::info('No se detectó function call');
```

### Logs de double call:
```php
Log::info('Respuesta de detectIntent', ['response' => $intentResponse]);
Log::info('Intención detectada', ['intent' => $intent]);
Log::info('Intención no mapeada a consulta de BD', ['intent' => $intent]);
```

### Logs de estadísticas:
```php
Log::info('Estadísticas AI actualizadas', [
    'empresa_id' => $empresa->id,
    'total_queries' => $empresa->ai_total_queries,
]);
```

### Logs de errores:
```php
Log::error('Error en detectWithFunctionCalling', [...]);
Log::error('Error en detectWithDoubleCall', [...]);
Log::warning('Función no reconocida', ['function' => $functionName]);
Log::warning('Intención no reconocida', ['intent' => $intent]);
```

---

## Compatibilidad

- ✅ **Backward Compatible:** El código existente sigue funcionando sin cambios
- ✅ **Modo por defecto:** Si no se especifica `ai_detection_mode`, se usa 'regex'
- ✅ **Sin credenciales personalizadas:** Funciona con las credenciales globales del sistema
- ✅ **Manejo de errores:** Try-catch en todos los nuevos métodos

---

## Beneficios

1. **Flexibilidad:** Cada empresa puede elegir el modo de detección que mejor se adapte a sus necesidades
2. **Escalabilidad:** Fácil agregar nuevos modos de detección en el futuro
3. **Mantenibilidad:** Código modular y bien organizado
4. **Debugging:** Logs completos en cada paso del proceso
5. **Reutilización:** Métodos de query compartidos entre todos los modos
6. **Performance:** Posibilidad de elegir el modo más eficiente según el caso de uso

---

## Configuración de Empresa

Para cambiar el modo de detección de una empresa:

```php
$empresa->update([
    'ai_detection_mode' => 'regex',        // Modo por defecto
    // o
    'ai_detection_mode' => 'function_calling',
    // o
    'ai_detection_mode' => 'double_call',
]);
```

Para configurar credenciales personalizadas:

```php
$empresa->update([
    'openai_api_key' => 'sk-...',
    'openai_model' => 'gpt-4',
    'openai_max_tokens' => 1000,
    'openai_temperature' => 0.7,
]);
```

---

## Testing Recomendado

1. **Test modo REGEX:**
   - Enviar mensajes con palabras clave conocidas
   - Verificar que detecta correctamente las intenciones

2. **Test modo FUNCTION_CALLING:**
   - Configurar empresa con modo function_calling
   - Enviar mensajes variados
   - Verificar logs de function calls

3. **Test modo DOUBLE_CALL:**
   - Configurar empresa con modo double_call
   - Enviar mensajes ambiguos
   - Verificar logs de detección de intención

4. **Test credenciales personalizadas:**
   - Configurar empresa con API key personalizada
   - Verificar que se usan las credenciales correctas
   - Monitorear logs

5. **Test estadísticas:**
   - Enviar varios mensajes
   - Verificar que ai_total_queries se incrementa
   - Verificar que ai_last_used_at se actualiza

---

## Próximos Pasos Sugeridos

1. **Implementar cálculo de costos reales** para actualizar `ai_monthly_usage`
2. **Agregar validación de presupuesto** antes de procesar mensajes
3. **Crear endpoint para consultar estadísticas de uso**
4. **Implementar alertas** cuando se alcance cierto % del presupuesto
5. **Agregar más funciones** para function calling (clientes, facturas, reportes)
6. **Crear tests automatizados** para cada modo de detección
7. **Dashboard de métricas** de uso de AI por empresa

---

## Notas Técnicas

- Sintaxis PHP verificada: ✅ Sin errores
- Logs implementados: ✅ En todos los puntos clave
- Manejo de errores: ✅ Try-catch en métodos críticos
- Backward compatibility: ✅ Código legacy funcional
- Type hints: ⚠️ Faltan en algunos métodos protected (considerar agregar en futuro)
