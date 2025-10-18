# Docker Frontend Vue3 - BackendProfesional

## Archivos Creados

- `Dockerfile` - Configuración multi-stage para build y producción
- `.dockerignore` - Exclusión de archivos innecesarios
- `nginx.conf` - Configuración optimizada de Nginx para SPA

## Comandos de Docker

### Build de la imagen

```bash
# Build básico
docker build -t frontend-backendprofesional:latest .

# Build con variables de entorno
docker build \
  --build-arg VITE_API_URL=http://localhost:8000/api \
  --build-arg VITE_APP_NAME="Backend Profesional" \
  -t frontend-backendprofesional:latest .
```

### Ejecutar contenedor

```bash
# Ejecutar en puerto 3000
docker run -d \
  --name frontend \
  -p 3000:80 \
  frontend-backendprofesional:latest

# Ejecutar con variables de entorno en runtime (si es necesario)
docker run -d \
  --name frontend \
  -p 3000:80 \
  -e VITE_API_URL=http://api.ejemplo.com \
  frontend-backendprofesional:latest
```

### Comandos útiles

```bash
# Ver logs
docker logs -f frontend

# Entrar al contenedor
docker exec -it frontend sh

# Detener contenedor
docker stop frontend

# Eliminar contenedor
docker rm frontend

# Health check manual
docker inspect --format='{{json .State.Health}}' frontend
```

## Características del Dockerfile

### Stage 1: Builder
- **Base:** node:20-alpine (ligera)
- **Optimizaciones:**
  - npm ci para builds reproducibles
  - Cache de dependencias optimizado
  - Build args para variables de entorno

### Stage 2: Production
- **Base:** nginx:alpine (ultra ligera, ~40MB)
- **Características:**
  - Servidor Nginx optimizado para SPA
  - Compresión Gzip automática
  - Cache headers para assets estáticos
  - Health checks integrados
  - Usuario no-root (seguridad)
  - Logs accesibles

## Configuración Nginx

### Rutas manejadas:
- `/` - Aplicación Vue3 (fallback a index.html)
- `/health` - Health check endpoint
- Assets estáticos con cache de 1 año
- index.html sin cache (siempre actualizado)

### Seguridad:
- Headers de seguridad (X-Frame-Options, X-XSS-Protection, etc.)
- Denegar acceso a archivos ocultos
- Usuario nginx no-root

### Performance:
- Compresión Gzip para todos los archivos de texto
- Cache agresivo para assets (JS, CSS, imágenes)
- No cache para index.html

## Integración con Docker Compose

Ejemplo de uso en `docker-compose.yml`:

```yaml
services:
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
      args:
        VITE_API_URL: http://backend:8000/api
        VITE_APP_NAME: "Backend Profesional"
    ports:
      - "3000:80"
    depends_on:
      - backend
    networks:
      - app-network
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 3s
      retries: 3
      start_period: 5s
```

## Troubleshooting

### Error: "Cannot find module"
- Asegúrate de que `package.json` y `package-lock.json` existan
- Verifica que `.dockerignore` no esté excluyendo archivos necesarios

### Error: Build falla
- Verifica que todas las dependencias estén en `package.json`
- Revisa los logs: `docker build -t frontend:latest . --no-cache`

### SPA routing no funciona (404 en rutas)
- Verifica que `nginx.conf` tenga `try_files $uri $uri/ /index.html;`
- Asegúrate de que el archivo se copió correctamente al contenedor

### Variables de entorno no se aplican
- Las variables VITE_* se procesan en **build time**, no runtime
- Usa `--build-arg` en el comando docker build
- Para cambios en runtime, necesitarías un script de inicialización

## Optimizaciones Futuras

1. **Multi-plataforma:**
   ```bash
   docker buildx build --platform linux/amd64,linux/arm64 -t frontend:latest .
   ```

2. **Registry remoto:**
   ```bash
   docker tag frontend:latest registry.ejemplo.com/frontend:latest
   docker push registry.ejemplo.com/frontend:latest
   ```

3. **Cloudflare Tunnel:**
   - Usar nginx.conf para proxy reverso a backend
   - Configurar headers adicionales para Cloudflare

## Tamaño de Imagen

- **Builder stage:** ~400MB (solo durante build)
- **Production stage:** ~40-50MB (imagen final)
- **Build completo:** ~5-50MB (dependiendo de assets)

La imagen final es extremadamente ligera gracias a:
- nginx:alpine como base
- Multi-stage build (descarta builder)
- Optimización de dependencias

---

**Creado por:** jscothserver
**Fecha:** 2025-10-17
**Proyecto:** BackendProfesional
