# Docker - Frontend Vue3 + Vite + Nginx

## Archivos de Configuración

- **Dockerfile**: Build multi-stage optimizado para producción
- **.dockerignore**: Archivos excluidos del contexto de build
- **nginx.conf**: Configuración de Nginx para SPA

## Construcción de la Imagen

### Build básico

```bash
docker build -t backendprofesional-frontend:latest .
```

### Build con variables de entorno

```bash
docker build \
  --build-arg VITE_API_URL=http://localhost:8000/api \
  --build-arg VITE_APP_NAME="Backend Profesional" \
  -t backendprofesional-frontend:latest .
```

## Ejecución del Contenedor

### Modo básico

```bash
docker run -d \
  --name frontend \
  -p 80:80 \
  backendprofesional-frontend:latest
```

### Con variables de entorno

```bash
docker run -d \
  --name frontend \
  -p 80:80 \
  -e VITE_API_URL=http://backend:8000/api \
  backendprofesional-frontend:latest
```

### Acceder a la aplicación

Abre tu navegador en: `http://localhost`

## Health Check

El contenedor tiene un health check automático que verifica cada 30 segundos:

```bash
# Ver estado de salud
docker inspect --format='{{.State.Health.Status}}' frontend
```

## Nginx Configuration

### Características

- ✅ Compresión Gzip activada
- ✅ Cache de assets estáticos (1 año)
- ✅ SPA routing (fallback a index.html)
- ✅ Headers de seguridad
- ✅ Health check endpoint en `/health`
- ✅ Proxy API comentado (listo para activar)

### Activar Proxy para API Backend

Edita `nginx.conf` y descomenta:

```nginx
location /api {
    proxy_pass http://backend:8000;
    # ... resto de configuración
}
```

## Logs

### Ver logs del contenedor

```bash
docker logs -f frontend
```

### Ver logs de Nginx dentro del contenedor

```bash
docker exec frontend tail -f /var/log/nginx/access.log
docker exec frontend tail -f /var/log/nginx/error.log
```

## Detener y Eliminar

```bash
# Detener
docker stop frontend

# Eliminar
docker rm frontend

# Eliminar imagen
docker rmi backendprofesional-frontend:latest
```

## Multi-Stage Build Explicado

### Stage 1: Builder (node:20-alpine)
1. Instala todas las dependencias (npm ci)
2. Copia código fuente
3. Ejecuta build de Vite (npm run build)
4. Genera carpeta `dist/` con archivos optimizados

### Stage 2: Production (nginx:alpine)
1. Copia solo la carpeta `dist/` del stage builder
2. Configura Nginx con nginx.conf personalizado
3. Ejecuta como usuario no-root (seguridad)
4. Expone puerto 80
5. Health check cada 30s

**Resultado**: Imagen final ~50MB (solo Nginx + build estático)

## Seguridad

- ✅ Usuario no-root (nginx)
- ✅ Headers de seguridad configurados
- ✅ Archivos ocultos denegados
- ✅ Sin versión de Nginx expuesta
- ✅ Build args para secrets (no hardcoded)

## Troubleshooting

### El contenedor no inicia

```bash
# Ver logs de error
docker logs frontend

# Verificar configuración de Nginx
docker exec frontend nginx -t
```

### Error 404 en rutas de Vue Router

Verifica que nginx.conf tenga:
```nginx
location / {
    try_files $uri $uri/ /index.html;
}
```

### API requests fallan (CORS)

Si el backend está en otro dominio, configura el proxy en nginx.conf

## Integración con Docker Compose

Ver `docker-compose.yml` en el directorio raíz del proyecto.
