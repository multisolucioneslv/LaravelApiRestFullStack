<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

# Backend Professional - Módulos Implementados

## Módulo de Productos y Categorías

Sistema completo de gestión de inventario con las siguientes características:

### Productos
- CRUD completo de productos
- Upload de imágenes (JPG, PNG, WEBP, max 2MB)
- Gestión de stock con alertas de stock bajo
- Múltiples categorías por producto
- Precios: compra, venta, mayoreo
- SKU y código de barras únicos
- Soft delete con restauración
- Multi-tenancy (aislamiento por empresa)

### Categorías
- CRUD completo de categorías
- Slug automático generado desde el nombre
- Iconos y colores personalizados
- Prevención de eliminación si tiene productos asociados
- Relación Many-to-Many con productos

### Endpoints Principales

**Productos:**
```
GET    /api/productos              - Listar con paginación y búsqueda
POST   /api/productos              - Crear producto (multipart/form-data)
GET    /api/productos/bajo-stock   - Productos con stock bajo
GET    /api/productos/{id}         - Ver producto con detalles
POST   /api/productos/{id}         - Actualizar producto
DELETE /api/productos/{id}         - Eliminar producto (soft delete)
POST   /api/productos/{id}/restore - Restaurar producto eliminado
POST   /api/productos/{id}/stock   - Actualizar stock
POST   /api/productos/bulk/delete  - Eliminar múltiples productos
```

**Categorías:**
```
GET    /api/categorias              - Listar con paginación
POST   /api/categorias              - Crear categoría
GET    /api/categorias/all          - Todas activas (sin paginación)
GET    /api/categorias/{id}         - Ver categoría con productos
PUT    /api/categorias/{id}         - Actualizar categoría
DELETE /api/categorias/{id}         - Eliminar categoría
POST   /api/categorias/{id}/restore - Restaurar categoría
POST   /api/categorias/bulk/delete  - Eliminar múltiples categorías
```

### Documentación Completa

- **API REST:** `backend/docs/api/PRODUCTOS_API.md`
- **Schema de BD:** `backend/docs/database/PRODUCTOS_SCHEMA.md`
- **Permisos:** `backend/docs/PRODUCTOS_PERMISOS.md`
- **Guía de Usuario:** `docs/PRODUCTOS_GUIA_USUARIO.md`

### Características de Seguridad

- **JWT Authentication:** Autenticación mediante tokens
- **Multi-tenancy:** Datos aislados por empresa
- **Permisos:** 14 permisos granulares (Spatie Permission)
  - 7 para productos
  - 7 para categorías
- **Middleware:** Validación en cada endpoint
- **Policies:** Validación adicional de acceso
- **Validaciones:** Mensajes en español
- **Soft Delete:** Eliminación lógica, no física

### Instalación del Módulo

```bash
# Ejecutar migraciones del módulo
php artisan migrate --path=database/migrations/2025_10_16_210000_create_productos_table.php
php artisan migrate --path=database/migrations/2025_10_16_210001_create_categorias_table.php
php artisan migrate --path=database/migrations/2025_10_16_210002_create_categoria_producto_table.php

# Crear link simbólico para imágenes
php artisan storage:link

# Seeders (si existen)
php artisan db:seed --class=ProductosPermissionsSeeder
```

### Estructura de Archivos

```
backend/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           ├── ProductoController.php
│   │           └── CategoriaController.php
│   ├── Models/
│   │   ├── Producto.php
│   │   └── Categoria.php
│   └── Traits/
│       └── MultiTenantScope.php
├── database/
│   └── migrations/
│       ├── 2025_10_16_210000_create_productos_table.php
│       ├── 2025_10_16_210001_create_categorias_table.php
│       └── 2025_10_16_210002_create_categoria_producto_table.php
├── docs/
│   ├── api/
│   │   └── PRODUCTOS_API.md
│   ├── database/
│   │   └── PRODUCTOS_SCHEMA.md
│   └── PRODUCTOS_PERMISOS.md
└── storage/
    └── app/
        └── public/
            └── productos/
```

---

## Soporte y Contacto

- **Email:** jscothserver@gmail.com
- **Telegram:** @Multisolucioneslv_bot

**Última actualización:** 2025-10-16
