# ============================================
# Dockerfile para Laravel 12 con PHP 8.2 FPM
# Backend: BackendProfesional
# ============================================

# ===== STAGE 1: Builder =====
FROM php:8.2-fpm-alpine AS builder

# Metadata
LABEL maintainer="jscothserver@gmail.com"
LABEL description="Laravel 12 Backend Profesional - Optimizado para producción"

# Variables de entorno para PHP y Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_REVALIDATE_FREQ=0

# Instalar dependencias del sistema y extensiones PHP
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    curl-dev \
    libtool \
    libxml2-dev \
    mysql-client \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    && apk add --no-cache \
    bash \
    curl \
    git \
    mysql-client \
    postgresql-client \
    libzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    supervisor \
    nginx

# Instalar extensiones PHP requeridas
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    mysqli \
    mbstring \
    xml \
    ctype \
    bcmath \
    zip \
    gd \
    exif \
    pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

# Instalar Composer 2.x
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario no-root para la aplicación
RUN addgroup -g 1000 laravel \
    && adduser -D -u 1000 -G laravel laravel

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de dependencias
COPY --chown=laravel:laravel composer.json composer.lock ./

# Instalar dependencias de Composer (sin dev en producción)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    && composer clear-cache

# ===== STAGE 2: Production =====
FROM php:8.2-fpm-alpine

# Metadata
LABEL maintainer="jscothserver@gmail.com"
LABEL description="Laravel 12 Backend Profesional - Imagen de producción"

# Variables de entorno
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_REVALIDATE_FREQ=0 \
    APP_ENV=production \
    APP_DEBUG=false

# Instalar dependencias runtime
RUN apk add --no-cache \
    bash \
    curl \
    mysql-client \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    supervisor \
    nginx

# Copiar extensiones PHP desde builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Crear usuario no-root
RUN addgroup -g 1000 laravel \
    && adduser -D -u 1000 -G laravel laravel

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar vendor desde builder
COPY --from=builder --chown=laravel:laravel /var/www/html/vendor ./vendor

# Copiar código de la aplicación
COPY --chown=laravel:laravel . .

# Crear directorios necesarios y establecer permisos
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configuración PHP personalizada
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory-limit.ini \
    && echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/upload.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/upload.ini \
    && echo "max_execution_time = 300" > /usr/local/etc/php/conf.d/execution.ini

# Configuración de OPcache para producción
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Copiar script de entrypoint
COPY --chown=laravel:laravel docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Optimizar Laravel para producción
RUN php artisan config:cache 2>/dev/null || true \
    && php artisan route:cache 2>/dev/null || true \
    && php artisan view:cache 2>/dev/null || true

# Health check para verificar que PHP-FPM está funcionando
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD php artisan health:check || exit 1

# Cambiar a usuario no-root
USER laravel

# Exponer puerto 9000 para PHP-FPM
EXPOSE 9000

# Punto de entrada
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Comando por defecto
CMD ["php-fpm"]
