# 🚀 Inicio Rápido - Frontend BackendProfesional

**Última actualización:** 2025-10-14

---

## ⚡ Ejecutar en 3 Pasos

### 1. Abrir Terminal

```bash
cd D:\MultisolucionesLV\proyectos\BackendProfesional\frontend
```

### 2. Ejecutar Servidor

```bash
npm run dev
```

### 3. Abrir Navegador

**URL:** http://localhost:5173

---

## 🔐 Credenciales de Prueba

### Opción 1 - Usuario

```
Usuario: jscothserver
Password: 72900968
```

### Opción 2 - Email

```
Email: jscothserver@gmail.com
Password: 72900968
```

**Ambas opciones funcionan igual** (Login Dual implementado)

---

## ✅ Verificar que Funciona

1. ✅ La página de login debe cargar
2. ✅ Botón de Dark Mode debe estar visible (esquina superior derecha)
3. ✅ Al hacer login, debe redirigir a `/dashboard`
4. ✅ Al recargar la página, la sesión debe mantenerse
5. ✅ El Dark Mode debe persistir al recargar

---

## ⚙️ Configuración del Backend

**IMPORTANTE:** El backend Laravel debe estar ejecutándose en:

```
http://localhost:8000
```

Si el backend está en otra URL, editar:

```
D:\MultisolucionesLV\proyectos\BackendProfesional\frontend\.env.development
```

Cambiar:
```bash
VITE_API_BASE_URL=http://localhost:8000/api
```

---

## 🐛 Solución de Problemas

### Error: "Cannot connect to backend"

**Solución:**
1. Verificar que el backend Laravel esté ejecutándose
2. Verificar la URL en `.env.development`
3. Verificar que no haya CORS issues

### Error: "Token inválido"

**Solución:**
1. Hacer logout
2. Limpiar localStorage del navegador
3. Volver a hacer login

### Error: "Dark mode no funciona"

**Solución:**
1. Limpiar localStorage del navegador
2. Recargar página
3. Probar toggle de dark mode

---

## 📚 Documentación Completa

- `README.md` - Documentación principal
- `VERSIONS.md` - Versiones y compatibilidad
- `reportes/2025/10/14/Frontend-Implementacion-Completa.md` - Reporte detallado

---

## 🎯 Características Principales

- ✅ **Login Dual:** Usuario O Email
- ✅ **Persistencia:** Sesión se mantiene al recargar
- ✅ **Dark Mode:** Modo oscuro persistente
- ✅ **Seguridad:** JWT + Guards de navegación
- ✅ **Responsive:** Funciona en móvil, tablet y desktop

---

**¿Problemas?** Revisar `VERSIONS.md` para más detalles.
