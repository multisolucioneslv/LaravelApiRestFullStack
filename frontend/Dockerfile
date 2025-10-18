# ========================================
# STAGE 1: Build de la aplicación Vue3
# ========================================
FROM node:20-alpine AS builder

# Metadata
LABEL maintainer="jscothserver@gmail.com"
LABEL description="Frontend Vue3 + Vite - BackendProfesional"

# Establecer directorio de trabajo
WORKDIR /app

# Copiar archivos de dependencias
COPY package*.json ./

# Instalar dependencias de producción (usa cache de npm)
# --prefer-offline y --no-audit para builds más rápidos
RUN npm ci --only=production --prefer-offline --no-audit && \
    npm cache clean --force

# Copiar código fuente
COPY . .

# Build de producción con Vite
# Variables de entorno se pueden pasar en build time
ARG VITE_API_URL
ARG VITE_APP_NAME
ENV VITE_API_URL=${VITE_API_URL}
ENV VITE_APP_NAME=${VITE_APP_NAME}

RUN npm run build

# ========================================
# STAGE 2: Servidor Nginx para producción
# ========================================
FROM nginx:alpine AS production

# Metadata
LABEL maintainer="jscothserver@gmail.com"
LABEL description="Frontend Vue3 - Servidor Nginx"

# Instalar curl para health checks
RUN apk add --no-cache curl

# Copiar build desde stage builder
COPY --from=builder /app/dist /usr/share/nginx/html

# Copiar configuración custom de nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Crear usuario nginx no-root (seguridad)
RUN chown -R nginx:nginx /usr/share/nginx/html && \
    chown -R nginx:nginx /var/cache/nginx && \
    chown -R nginx:nginx /var/log/nginx && \
    chmod -R 755 /usr/share/nginx/html && \
    touch /var/run/nginx.pid && \
    chown -R nginx:nginx /var/run/nginx.pid

# Cambiar a usuario no-root
USER nginx

# Exponer puerto 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:80/ || exit 1

# Comando de inicio
CMD ["nginx", "-g", "daemon off;"]
