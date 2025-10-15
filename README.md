# Backend Profesional

Sistema de gestión empresarial profesional desarrollado con Laravel 12 + Vue 3.

## Información del Proyecto

- **Proyecto**: BackendProfesional
- **Cliente**: MultisolucionesLV
- **Fecha Inicio**: 2025-10-14
- **Stack**: Laravel 12 + Vue 3 + Docker
- **Estado**: En Desarrollo

## Tecnologías

### Backend
- Laravel 12.34
- PHP 8.2+
- MySQL 8.0
- JWT Authentication (tymon/jwt-auth)
- Spatie Laravel Permission
- Redis (cache y sessions)

### Frontend (Próximamente)
- Vue 3 (Composition API)
- Vite
- Vue Router 4
- Pinia
- Axios
- Tailwind CSS

### DevOps
- Docker & Docker Compose
- Cloudflare Tunnel
- GitHub Actions (CI/CD)

## Características

### Autenticación
- Login dual (usuario o email)
- JWT Tokens
- Refresh tokens
- Logout

### Sistema de Permisos
- 6 roles predefinidos:
  - SuperAdmin
  - Administrador
  - Supervisor
  - Vendedor
  - Usuario
  - Contabilidad

### Módulos del Sistema
- Gestión de Empresas
- Control de Inventario
- Cotizaciones
- Ventas
- Pedidos
- Rutas de Entrega
- Reportes

## Instalación

### Requisitos
- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL >= 8.0
- Redis (opcional)

### Backend

```bash
cd backend

# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Configurar base de datos en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=backend_profesional
DB_USERNAME=root
DB_PASSWORD=

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Iniciar servidor
php artisan serve
```

## Estructura del Proyecto

```
BackendProfesional/
├── backend/              # Laravel API
├── frontend/             # Vue 3 SPA (próximamente)
├── docker/               # Configuraciones Docker
├── docs/                 # Documentación
├── reportes/             # Reportes por fecha
├── logs/                 # Logs del sistema
├── .componentes-usados/  # Registro de componentes
├── PlanTrabajo.md        # Plan de desarrollo
└── README.md             # Este archivo
```

## API Endpoints

### Autenticación
```
POST   /api/auth/login    - Login
POST   /api/auth/logout   - Logout
POST   /api/auth/refresh  - Refresh token
GET    /api/auth/me       - Usuario actual
```

### Recursos (Protegidos con JWT)
```
GET    /api/empresas      - Listar empresas
POST   /api/empresas      - Crear empresa
GET    /api/empresas/{id} - Ver empresa
PUT    /api/empresas/{id} - Actualizar empresa
DELETE /api/empresas/{id} - Eliminar empresa
```

## Usuario por Defecto

```
usuario: jscothserver
email: jscothserver@gmail.com
password: 72900968
rol: SuperAdmin
```

## Testing

```bash
# Ejecutar tests
php artisan test

# Con cobertura
php artisan test --coverage

# Tests específicos
php artisan test --filter AuthTest
```

## Docker

```bash
# Construir e iniciar servicios
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down
```

## Documentación

- [Plan de Trabajo](PlanTrabajo.md)
- [API Documentation](docs/API.md) (próximamente)
- [Arquitectura](docs/ARCHITECTURE.md) (próximamente)
- [Deployment](docs/DEPLOYMENT.md) (próximamente)

## Agentes Utilizados

Este proyecto fue desarrollado usando los siguientes agentes especializados:

- **CoordinadorProyecto** - Orquestación general
- **CreadorProyectoLaravel** - Inicialización de Laravel
- **ArquitectoBaseDatos** - Diseño de base de datos
- **ArquitectoJWT** - Configuración de autenticación
- **ExpertoPermisos** - Sistema de roles y permisos
- **ExpertoSeeders** - Datos iniciales
- **DockerBackend** - Dockerización

## Contacto

- **Desarrollador**: jscothserver
- **Email**: jscothserver@gmail.com
- **Telegram**: @Multisolucioneslv_bot

## Licencia

Propietario - MultisolucionesLV
