#!/bin/bash
# ============================================
# Docker Entrypoint Script para Laravel 12
# Backend: BackendProfesional
# ============================================

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}  Laravel 12 Backend - Iniciando Contenedor${NC}"
echo -e "${GREEN}================================================${NC}"

# ============================================
# 1. Verificar Variables de Entorno Críticas
# ============================================
echo -e "\n${YELLOW}[1/7] Verificando variables de entorno...${NC}"

if [ -z "$DB_HOST" ]; then
    echo -e "${RED}ERROR: DB_HOST no está definido${NC}"
    exit 1
fi

if [ -z "$DB_DATABASE" ]; then
    echo -e "${RED}ERROR: DB_DATABASE no está definido${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Variables de entorno verificadas${NC}"

# ============================================
# 2. Esperar a que MySQL esté disponible
# ============================================
echo -e "\n${YELLOW}[2/7] Esperando conexión a MySQL...${NC}"

MAX_RETRIES=30
RETRY_COUNT=0

until mysql -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; do
    RETRY_COUNT=$((RETRY_COUNT + 1))

    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo -e "${RED}ERROR: No se pudo conectar a MySQL después de $MAX_RETRIES intentos${NC}"
        exit 1
    fi

    echo -e "${YELLOW}Esperando MySQL... (intento $RETRY_COUNT/$MAX_RETRIES)${NC}"
    sleep 2
done

echo -e "${GREEN}✓ Conexión a MySQL establecida${NC}"

# ============================================
# 3. Verificar/Crear Base de Datos
# ============================================
echo -e "\n${YELLOW}[3/7] Verificando base de datos...${NC}"

mysql -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

echo -e "${GREEN}✓ Base de datos verificada${NC}"

# ============================================
# 4. Ejecutar Migraciones
# ============================================
echo -e "\n${YELLOW}[4/7] Ejecutando migraciones...${NC}"

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    # Verificar si hay tablas en la base de datos
    TABLE_COUNT=$(mysql -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" -D"$DB_DATABASE" -se "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_DATABASE';" 2>/dev/null || echo "0")

    if [ "$TABLE_COUNT" -eq "0" ]; then
        echo -e "${YELLOW}Base de datos vacía. Ejecutando migraciones con seeders...${NC}"
        php artisan migrate:fresh --seed --force
    else
        echo -e "${YELLOW}Base de datos existente. Ejecutando migraciones pendientes...${NC}"
        php artisan migrate --force
    fi

    echo -e "${GREEN}✓ Migraciones completadas${NC}"
else
    echo -e "${YELLOW}⊘ Migraciones deshabilitadas (RUN_MIGRATIONS=false)${NC}"
fi

# ============================================
# 5. Generar JWT Secret si no existe
# ============================================
echo -e "\n${YELLOW}[5/7] Verificando JWT Secret...${NC}"

if ! grep -q "JWT_SECRET=" .env 2>/dev/null || [ -z "$(grep JWT_SECRET= .env | cut -d '=' -f2)" ]; then
    echo -e "${YELLOW}Generando JWT Secret...${NC}"
    php artisan jwt:secret --force
    echo -e "${GREEN}✓ JWT Secret generado${NC}"
else
    echo -e "${GREEN}✓ JWT Secret ya existe${NC}"
fi

# ============================================
# 6. Cachear Configuraciones (Producción)
# ============================================
echo -e "\n${YELLOW}[6/7] Optimizando Laravel para producción...${NC}"

if [ "${APP_ENV:-production}" = "production" ]; then
    # Limpiar cachés anteriores
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    # Generar nuevos cachés
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Optimizar autoload
    composer dump-autoload --optimize --no-dev --classmap-authoritative 2>/dev/null || true

    echo -e "${GREEN}✓ Cachés de producción generados${NC}"
else
    echo -e "${YELLOW}⊘ Modo desarrollo - cachés no generados${NC}"
fi

# ============================================
# 7. Establecer Permisos
# ============================================
echo -e "\n${YELLOW}[7/7] Estableciendo permisos de storage y cache...${NC}"

# Asegurar que los directorios existen
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Establecer permisos (si tenemos permisos para hacerlo)
if [ "$(id -u)" = "0" ]; then
    # Ejecutando como root
    chown -R laravel:laravel storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    echo -e "${GREEN}✓ Permisos establecidos (root)${NC}"
else
    # Ejecutando como usuario laravel
    chmod -R 775 storage bootstrap/cache 2>/dev/null || echo -e "${YELLOW}⚠ No se pudieron cambiar algunos permisos (esperado)${NC}"
    echo -e "${GREEN}✓ Permisos verificados (usuario)${NC}"
fi

# ============================================
# 8. Información del Sistema
# ============================================
echo -e "\n${GREEN}================================================${NC}"
echo -e "${GREEN}  Información del Sistema${NC}"
echo -e "${GREEN}================================================${NC}"
echo -e "PHP Version: $(php -v | head -n 1)"
echo -e "Laravel Version: $(php artisan --version)"
echo -e "Environment: ${APP_ENV:-production}"
echo -e "Debug Mode: ${APP_DEBUG:-false}"
echo -e "Database Host: $DB_HOST:${DB_PORT:-3306}"
echo -e "Database Name: $DB_DATABASE"
echo -e "Redis Host: ${REDIS_HOST:-redis}:${REDIS_PORT:-6379}"
echo -e "${GREEN}================================================${NC}"

# ============================================
# 9. Ejecutar Comando Principal
# ============================================
echo -e "\n${GREEN}🚀 Iniciando PHP-FPM...${NC}\n"

# Ejecutar el comando pasado al contenedor (por defecto: php-fpm)
exec "$@"
