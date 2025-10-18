# Docker - BackendProfesional

Guía completa para ejecutar el proyecto BackendProfesional usando Docker y Docker Compose.

## Tabla de Contenidos

- [Requisitos Previos](#requisitos-previos)
- [Arquitectura de Servicios](#arquitectura-de-servicios)
- [Configuración Inicial](#configuración-inicial)
- [Comandos Comunes](#comandos-comunes)
- [Gestión de Servicios](#gestión-de-servicios)
- [Debugging y Logs](#debugging-y-logs)
- [Base de Datos](#base-de-datos)
- [Cloudflare Tunnel](#cloudflare-tunnel)
- [Troubleshooting](#troubleshooting)
- [Comandos Útiles](#comandos-útiles)

---

## Requisitos Previos

### Software Necesario

- **Docker:** v20.10 o superior
- **Docker Compose:** v2.0 o superior
- **Git:** Para clonar el repositorio

### Verificar Instalación

```bash
docker --version
docker-compose --version
```

### Recursos Mínimos Recomendados

- **RAM:** 4GB mínimo (8GB recomendado)
- **CPU:** 2 cores mínimo (4 cores recomendado)
- **Disco:** 10GB libres

---

## Arquitectura de Servicios

El proyecto utiliza 6 servicios principales:

| Servicio | Imagen | Puerto Interno | Puerto Externo | Descripción |
|----------|--------|----------------|----------------|-------------|
| **mysql** | mysql:8.0 | 3306 | 3306 | Base de datos MySQL |
| **redis** | redis:alpine | 6379 | 6379 | Cache y sesiones |
| **backend** | Custom (Laravel) | 9000 | 9000 | API Laravel con PHP-FPM |
| **frontend** | Custom (Vue3) | 80 | 3000 | Aplicación Vue3 |
| **nginx** | nginx:alpine | 80, 443 | 8000, 8443 | Reverse proxy |
| **cloudflare-tunnel** | cloudflare/cloudflared | - | - | Túnel seguro Cloudflare |

### Flujo de Datos

```
Internet → Cloudflare Tunnel → Nginx → Backend (Laravel API)
                                     ↘ Frontend (Vue3)
Backend → MySQL (Base de datos)
Backend → Redis (Cache/Sesiones)
```

---

## Configuración Inicial

### 1. Clonar Repositorio

```bash
git clone <url-del-repositorio>
cd BackendProfesional
```

### 2. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.docker.example .env.docker

# Editar con tus valores
nano .env.docker  # o usar el editor de tu preferencia
```

### 3. Variables Críticas a Configurar

**Obligatorias:**

```env
# MySQL
MYSQL_ROOT_PASSWORD=TuPasswordSegura2025
MYSQL_PASSWORD=PasswordBackend2025
REDIS_PASSWORD=PasswordRedis2025

# Laravel
APP_KEY=base64:XXXXX  # Generar después del build
JWT_SECRET=XXXXX      # Generar después del build

# Cloudflare (opcional)
CLOUDFLARE_TUNNEL_TOKEN=tu-token-de-cloudflare
```

### 4. Generar Claves de Laravel

```bash
# Construir los servicios primero
docker-compose build

# Generar APP_KEY
docker-compose run --rm backend php artisan key:generate --show

# Generar JWT_SECRET
docker-compose run --rm backend php artisan jwt:secret --show
```

**Importante:** Copiar las claves generadas al archivo `.env.docker`

### 5. Levantar los Servicios

```bash
# Construir imágenes
docker-compose build

# Levantar servicios en background
docker-compose up -d

# Ver logs en tiempo real
docker-compose logs -f
```

### 6. Configurar Base de Datos

```bash
# Ejecutar migraciones
docker-compose exec backend php artisan migrate

# (Opcional) Ejecutar seeders
docker-compose exec backend php artisan db:seed

# (Opcional) Migraciones frescas con seeders
docker-compose exec backend php artisan migrate:fresh --seed
```

### 7. Verificar Instalación

```bash
# Backend API
curl http://localhost:8000/api/health

# Frontend
curl http://localhost:3000

# MySQL
docker-compose exec mysql mysql -u root -p -e "SHOW DATABASES;"
```

---

## Comandos Comunes

### Gestión de Contenedores

```bash
# Levantar todos los servicios
docker-compose up -d

# Levantar servicios específicos
docker-compose up -d mysql redis backend

# Detener todos los servicios
docker-compose down

# Detener y eliminar volúmenes (⚠️ CUIDADO: Elimina datos)
docker-compose down -v

# Reiniciar un servicio específico
docker-compose restart backend

# Ver estado de servicios
docker-compose ps

# Ver uso de recursos
docker stats
```

### Logs y Debugging

```bash
# Ver logs de todos los servicios
docker-compose logs

# Ver logs de un servicio específico
docker-compose logs backend
docker-compose logs mysql

# Seguir logs en tiempo real
docker-compose logs -f backend

# Ver últimas 100 líneas
docker-compose logs --tail=100 backend

# Ver logs con timestamps
docker-compose logs -t backend
```

### Acceso a Contenedores

```bash
# Acceder al contenedor backend (bash)
docker-compose exec backend bash

# Acceder al contenedor MySQL
docker-compose exec mysql bash

# Acceder a MySQL CLI
docker-compose exec mysql mysql -u root -p

# Ejecutar comando sin entrar al contenedor
docker-compose exec backend php artisan route:list
```

---

## Gestión de Servicios

### Backend Laravel

```bash
# Artisan commands
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan route:clear
docker-compose exec backend php artisan view:clear

# Optimización
docker-compose exec backend php artisan config:cache
docker-compose exec backend php artisan route:cache
docker-compose exec backend php artisan view:cache

# Testing
docker-compose exec backend php artisan test
docker-compose exec backend vendor/bin/phpunit

# Queue workers
docker-compose exec backend php artisan queue:work
docker-compose exec backend php artisan queue:listen

# Composer
docker-compose exec backend composer install
docker-compose exec backend composer update
docker-compose exec backend composer dump-autoload
```

### Frontend Vue3

```bash
# Acceder al contenedor
docker-compose exec frontend sh

# NPM commands (dentro del contenedor)
npm run dev
npm run build
npm run lint
npm run test

# Instalar dependencias
docker-compose exec frontend npm install

# Rebuild del frontend
docker-compose up -d --build frontend
```

### MySQL

```bash
# Acceder a MySQL CLI
docker-compose exec mysql mysql -u root -p

# Backup de base de datos
docker-compose exec mysql mysqldump -u root -p backendprofesional > backup.sql

# Restaurar backup
docker-compose exec -T mysql mysql -u root -p backendprofesional < backup.sql

# Ver bases de datos
docker-compose exec mysql mysql -u root -p -e "SHOW DATABASES;"

# Ver tablas
docker-compose exec mysql mysql -u root -p backendprofesional -e "SHOW TABLES;"
```

### Redis

```bash
# Acceder a Redis CLI
docker-compose exec redis redis-cli -a ${REDIS_PASSWORD}

# Comandos útiles de Redis
redis-cli> KEYS *           # Ver todas las claves
redis-cli> FLUSHALL         # Limpiar toda la cache (⚠️ CUIDADO)
redis-cli> INFO             # Información del servidor
redis-cli> MONITOR          # Monitorear comandos en tiempo real
```

### Nginx

```bash
# Ver configuración
docker-compose exec nginx cat /etc/nginx/nginx.conf

# Test de configuración
docker-compose exec nginx nginx -t

# Reload configuración (sin downtime)
docker-compose exec nginx nginx -s reload

# Ver logs de Nginx
docker-compose logs nginx
docker-compose exec nginx tail -f /var/log/nginx/access.log
docker-compose exec nginx tail -f /var/log/nginx/error.log
```

---

## Debugging y Logs

### Ubicación de Logs

```bash
# Backend Laravel
docker-compose exec backend tail -f storage/logs/laravel.log

# Nginx Access Log
docker-compose exec nginx tail -f /var/log/nginx/backend-access.log

# Nginx Error Log
docker-compose exec nginx tail -f /var/log/nginx/backend-error.log

# MySQL Error Log
docker-compose exec mysql tail -f /var/log/mysql/error.log

# MySQL Slow Query Log
docker-compose exec mysql tail -f /var/log/mysql/mysql-slow.log
```

### Health Checks

Todos los servicios tienen health checks configurados:

```bash
# Ver estado de salud
docker-compose ps

# Inspeccionar health check de un servicio
docker inspect --format='{{json .State.Health}}' backendprofesional-backend | jq
```

### Debugging de Conexiones

```bash
# Verificar conectividad entre servicios
docker-compose exec backend ping mysql
docker-compose exec backend ping redis

# Ver red de Docker
docker network ls
docker network inspect backendprofesional_backend-network
```

---

## Base de Datos

### Migraciones

```bash
# Ejecutar migraciones
docker-compose exec backend php artisan migrate

# Rollback última migración
docker-compose exec backend php artisan migrate:rollback

# Rollback todas las migraciones
docker-compose exec backend php artisan migrate:reset

# Refrescar base de datos (⚠️ Elimina datos)
docker-compose exec backend php artisan migrate:fresh

# Con seeders
docker-compose exec backend php artisan migrate:fresh --seed
```

### Seeders

```bash
# Ejecutar todos los seeders
docker-compose exec backend php artisan db:seed

# Ejecutar seeder específico
docker-compose exec backend php artisan db:seed --class=UserSeeder
```

### Backups

```bash
# Backup completo
docker-compose exec mysql mysqldump -u root -p --all-databases > backup-completo.sql

# Backup de una base de datos
docker-compose exec mysql mysqldump -u root -p backendprofesional > backup-db.sql

# Backup con compresión
docker-compose exec mysql mysqldump -u root -p backendprofesional | gzip > backup-db.sql.gz

# Restaurar backup
docker-compose exec -T mysql mysql -u root -p backendprofesional < backup-db.sql
```

### Acceso Externo a MySQL

Si necesitas conectar desde fuera de Docker (ej: MySQL Workbench, TablePlus):

```
Host: localhost
Port: 3306
User: backend_user
Password: (ver .env.docker)
Database: backendprofesional
```

---

## Cloudflare Tunnel

### Configuración

El servicio `cloudflare-tunnel` expone tu aplicación de forma segura a Internet.

### Obtener Token

1. Ir a [Cloudflare Zero Trust](https://one.dash.cloudflare.com/)
2. Access → Tunnels → Create a Tunnel
3. Copiar el token generado
4. Agregarlo a `.env.docker`:

```env
CLOUDFLARE_TUNNEL_TOKEN=tu-token-aqui
```

### Configurar Rutas

En el panel de Cloudflare:

**Para Backend API:**
- Public hostname: `api.tudominio.com`
- Service: `http://nginx:80`

**Para Frontend:**
- Public hostname: `app.tudominio.com`
- Service: `http://frontend:80`

### Verificar Estado

```bash
# Ver logs del tunnel
docker-compose logs -f cloudflare-tunnel

# Ver procesos activos
docker-compose exec cloudflare-tunnel ps aux | grep cloudflared
```

### Deshabilitar Cloudflare Tunnel

Si no necesitas el tunnel:

```bash
# Detener el servicio
docker-compose stop cloudflare-tunnel

# O comentar el servicio en docker-compose.yml
```

---

## Troubleshooting

### El backend no se conecta a MySQL

**Síntomas:**
- Error: "SQLSTATE[HY000] [2002] Connection refused"

**Solución:**

```bash
# Verificar que MySQL esté corriendo
docker-compose ps mysql

# Ver logs de MySQL
docker-compose logs mysql

# Reiniciar MySQL
docker-compose restart mysql

# Esperar a que MySQL esté saludable
docker-compose ps
```

### El frontend no se comunica con el backend

**Síntomas:**
- Error de CORS en consola del navegador
- Error 502 Bad Gateway

**Solución:**

```bash
# Verificar que backend esté corriendo
docker-compose ps backend

# Ver logs del backend
docker-compose logs backend

# Verificar configuración de VITE_API_URL en .env.docker
# Debe ser: http://localhost:8000/api
```

### Redis connection failed

**Síntomas:**
- Error: "Connection refused" al conectar con Redis

**Solución:**

```bash
# Verificar Redis
docker-compose ps redis

# Ver logs
docker-compose logs redis

# Verificar password en .env.docker
# REDIS_PASSWORD debe coincidir en Laravel y Redis
```

### Port already in use

**Síntomas:**
- Error: "Bind for 0.0.0.0:3306 failed: port is already allocated"

**Solución:**

```bash
# Cambiar el puerto en .env.docker
MYSQL_PORT=3307  # En lugar de 3306

# O detener el servicio que usa el puerto
# Windows:
netstat -ano | findstr :3306
taskkill /PID <PID> /F

# Linux/Mac:
sudo lsof -i :3306
sudo kill -9 <PID>
```

### Permisos de archivos (Linux/Mac)

**Síntomas:**
- Laravel no puede escribir en storage/logs

**Solución:**

```bash
# Dentro del contenedor backend
docker-compose exec backend chown -R www-data:www-data /var/www/html/storage
docker-compose exec backend chmod -R 775 /var/www/html/storage
docker-compose exec backend chmod -R 775 /var/www/html/bootstrap/cache
```

### Reconstruir desde cero

Si todo falla, reconstruir completamente:

```bash
# Detener y eliminar todo (⚠️ PERDERÁS DATOS)
docker-compose down -v

# Eliminar imágenes
docker-compose down --rmi all

# Limpiar cache de Docker
docker system prune -a

# Reconstruir
docker-compose build --no-cache
docker-compose up -d
```

---

## Comandos Útiles

### Limpieza

```bash
# Eliminar contenedores detenidos
docker container prune

# Eliminar imágenes sin usar
docker image prune

# Eliminar volúmenes sin usar
docker volume prune

# Limpieza completa del sistema
docker system prune -a --volumes
```

### Monitoreo

```bash
# Ver uso de recursos en tiempo real
docker stats

# Ver tamaño de imágenes
docker images

# Ver tamaño de volúmenes
docker system df

# Inspeccionar un servicio
docker-compose config
docker inspect <container_name>
```

### Exportar/Importar

```bash
# Exportar imagen
docker save backendprofesional-backend:latest | gzip > backend-image.tar.gz

# Importar imagen
docker load < backend-image.tar.gz

# Exportar base de datos
docker-compose exec mysql mysqldump -u root -p --all-databases | gzip > full-backup.sql.gz
```

---

## Variables de Entorno Disponibles

### General

```env
TZ=America/Los_Angeles                    # Zona horaria
COMPOSE_PROJECT_NAME=backendprofesional   # Nombre del proyecto
```

### MySQL

```env
MYSQL_DATABASE=backendprofesional         # Nombre de la base de datos
MYSQL_USER=backend_user                   # Usuario MySQL
MYSQL_PASSWORD=password                   # Password del usuario
MYSQL_ROOT_PASSWORD=root_password         # Password de root
MYSQL_PORT=3306                           # Puerto expuesto
```

### Redis

```env
REDIS_PASSWORD=redis_password             # Password de Redis
REDIS_PORT=6379                           # Puerto expuesto
```

### Backend Laravel

```env
APP_NAME=BackendProfesional               # Nombre de la app
APP_ENV=production                        # Entorno (local/production)
APP_KEY=base64:xxx                        # Clave de aplicación
APP_DEBUG=false                           # Debug mode
APP_URL=http://localhost:8000             # URL de la aplicación
PHP_VERSION=8.2                           # Versión de PHP
BACKEND_PORT=9000                         # Puerto PHP-FPM
JWT_SECRET=xxx                            # Secret de JWT
JWT_TTL=60                                # TTL de tokens (minutos)
```

### Frontend Vue3

```env
NODE_ENV=production                       # Entorno Node
NODE_VERSION=20                           # Versión de Node
FRONTEND_PORT=3000                        # Puerto expuesto
VITE_API_URL=http://localhost:8000/api    # URL del backend
```

### Nginx

```env
NGINX_HTTP_PORT=8000                      # Puerto HTTP
NGINX_HTTPS_PORT=8443                     # Puerto HTTPS
```

### Cloudflare

```env
CLOUDFLARE_TUNNEL_TOKEN=xxx               # Token del tunnel
```

---

## Arquitectura de Archivos Docker

```
BackendProfesional/
├── docker-compose.yml              # Orquestación de servicios
├── .env.docker                     # Variables de entorno (gitignored)
├── .env.docker.example             # Plantilla de variables
├── README.Docker.md                # Esta guía
│
├── backend/
│   ├── Dockerfile                  # Imagen de Laravel
│   └── ...
│
├── frontend/
│   ├── Dockerfile                  # Imagen de Vue3
│   └── ...
│
└── docker/
    ├── nginx/
    │   ├── nginx.conf              # Configuración principal
    │   └── conf.d/
    │       ├── backend.conf        # Virtual host backend
    │       └── frontend.conf       # Virtual host frontend (opcional)
    │
    └── mysql/
        └── my.cnf                  # Configuración de MySQL
```

---

## Contacto y Soporte

- **Usuario:** jscothserver
- **Email:** jscothserver@gmail.com
- **Telegram:** @Multisolucioneslv_bot

---

## Licencia

Este proyecto es privado y confidencial.

---

**Última actualización:** Octubre 2025
