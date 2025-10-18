#!/bin/bash

# ========================================
# Script de Setup Inicial - BackendProfesional
# ========================================
# Este script facilita la configuración inicial del proyecto
# ========================================

set -e

echo "========================================="
echo "BackendProfesional - Setup Inicial"
echo "========================================="
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para imprimir en color
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar Docker
if ! command -v docker &> /dev/null; then
    print_error "Docker no está instalado"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose no está instalado"
    exit 1
fi

print_info "Docker y Docker Compose detectados"

# Verificar archivo .env.docker
if [ ! -f ".env.docker" ]; then
    print_warning "Archivo .env.docker no encontrado"
    print_info "Copiando desde .env.docker.example..."
    cp .env.docker.example .env.docker
    print_warning "Por favor edita .env.docker con tus valores antes de continuar"
    print_warning "Especialmente: MYSQL_ROOT_PASSWORD, MYSQL_PASSWORD, REDIS_PASSWORD"
    echo ""
    read -p "Presiona ENTER cuando hayas editado .env.docker..."
fi

print_info "Archivo .env.docker encontrado"

# Leer variables del .env.docker
export $(grep -v '^#' .env.docker | xargs)

# Construir imágenes
print_info "Construyendo imágenes Docker..."
docker-compose build

# Levantar servicios
print_info "Iniciando servicios..."
docker-compose up -d mysql redis

print_info "Esperando a que MySQL esté listo..."
sleep 15

# Verificar si MySQL está saludable
print_info "Verificando salud de MySQL..."
until docker-compose exec -T mysql mysqladmin ping -h localhost -u root -p${MYSQL_ROOT_PASSWORD} --silent 2>/dev/null; do
    print_warning "MySQL aún no está listo, esperando..."
    sleep 5
done

print_info "MySQL está listo"

# Levantar backend
print_info "Iniciando backend..."
docker-compose up -d backend

print_info "Esperando a que backend esté listo..."
sleep 10

# Generar APP_KEY si no existe
if grep -q "APP_KEY=base64:GENERAR_CON" .env.docker; then
    print_info "Generando APP_KEY..."
    APP_KEY=$(docker-compose exec -T backend php artisan key:generate --show)

    # Actualizar .env.docker
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' "s|APP_KEY=base64:GENERAR_CON.*|APP_KEY=${APP_KEY}|g" .env.docker
    else
        # Linux
        sed -i "s|APP_KEY=base64:GENERAR_CON.*|APP_KEY=${APP_KEY}|g" .env.docker
    fi

    print_info "APP_KEY generada: ${APP_KEY}"
fi

# Generar JWT_SECRET si no existe
if grep -q "JWT_SECRET=GENERAR_CON" .env.docker; then
    print_info "Generando JWT_SECRET..."
    JWT_SECRET=$(docker-compose exec -T backend php artisan jwt:secret --show 2>/dev/null || echo "")

    if [ -z "$JWT_SECRET" ]; then
        print_warning "No se pudo generar JWT_SECRET automáticamente"
        print_warning "Ejecuta manualmente: docker-compose exec backend php artisan jwt:secret"
    else
        # Actualizar .env.docker
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS
            sed -i '' "s|JWT_SECRET=GENERAR_CON.*|JWT_SECRET=${JWT_SECRET}|g" .env.docker
        else
            # Linux
            sed -i "s|JWT_SECRET=GENERAR_CON.*|JWT_SECRET=${JWT_SECRET}|g" .env.docker
        fi

        print_info "JWT_SECRET generado"
    fi
fi

# Ejecutar migraciones
print_info "Ejecutando migraciones..."
docker-compose exec -T backend php artisan migrate --force

# Preguntar si ejecutar seeders
echo ""
read -p "¿Deseas ejecutar los seeders? (s/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[SsYy]$ ]]; then
    print_info "Ejecutando seeders..."
    docker-compose exec -T backend php artisan db:seed --force
fi

# Levantar frontend y nginx
print_info "Iniciando frontend y nginx..."
docker-compose up -d frontend nginx

# Levantar Cloudflare Tunnel (opcional)
echo ""
read -p "¿Deseas iniciar Cloudflare Tunnel? (s/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[SsYy]$ ]]; then
    if [ -z "$CLOUDFLARE_TUNNEL_TOKEN" ] || [ "$CLOUDFLARE_TUNNEL_TOKEN" == "eyJhIjoiNzM4M2NkZGY3NmIwMDVmZWZiNTJhNGZkMjYzNGVmZGMiLCJ0IjoiNzBkOWQxZTEtOGZiZi00OWJkLTkxNmUtZDdhOTU4NDk3MDcwIiwicyI6IlpUUm1NV1k1T0RBdE9HTmhaUzAwWlRNeExXSm1ZelF0T1Rka04yTTNNVFUwTVdFMyJ9" ]; then
        print_warning "Token de Cloudflare no configurado o es el de ejemplo"
        print_warning "Configura CLOUDFLARE_TUNNEL_TOKEN en .env.docker"
    else
        print_info "Iniciando Cloudflare Tunnel..."
        docker-compose up -d cloudflare-tunnel
    fi
fi

# Mostrar estado de servicios
echo ""
print_info "Estado de los servicios:"
docker-compose ps

# Mostrar URLs
echo ""
print_info "========================================="
print_info "Setup completado exitosamente!"
print_info "========================================="
echo ""
print_info "URLs de acceso:"
print_info "  - Backend API: http://localhost:${NGINX_HTTP_PORT:-8000}/api"
print_info "  - Frontend:    http://localhost:${FRONTEND_PORT:-3000}"
print_info "  - MySQL:       localhost:${MYSQL_PORT:-3306}"
print_info "  - Redis:       localhost:${REDIS_PORT:-6379}"
echo ""
print_info "Comandos útiles:"
print_info "  - Ver logs:           docker-compose logs -f"
print_info "  - Detener servicios:  docker-compose down"
print_info "  - Reiniciar:          docker-compose restart"
print_info "  - Acceder a backend:  docker-compose exec backend bash"
echo ""
print_info "Para más información, consulta README.Docker.md"
echo ""
