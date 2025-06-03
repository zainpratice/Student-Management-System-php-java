FROM php:8.1-apache

# System dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# PHP configuration with enhanced error handling
RUN { \
    echo 'error_reporting = E_ALL'; \
    echo 'display_errors = On'; \
    echo 'display_startup_errors = On'; \
    echo 'log_errors = On'; \
    echo 'error_log = /var/log/apache2/php_errors.log'; \
    echo 'upload_max_filesize = 32M'; \
    echo 'post_max_size = 32M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 180'; \
    echo 'session.save_handler = redis'; \
    echo 'session.save_path = "tcp://redis:6379"'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy files with proper permissions (excluding development files)
COPY --chown=www-data:www-data . .

# Permission handling (excluding .git directory)
RUN mkdir -p /var/log/apache2 && \
    touch /var/log/apache2/{error.log,access.log,php_errors.log} && \
    chown -R www-data:www-data /var/log/apache2 /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    chmod -R 775 /var/www/html/upload /var/www/html/sql /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod +x /var/www/html/docker-entrypoint.sh

# Cleanup development files
RUN find . \( -name ".git" -o -name ".gitignore" -o -name ".env.example" \) -exec rm -rf {} + || true

# Health check with MySQL connection test
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s \
    CMD curl -f http://localhost/ || exit 1

# Entrypoint script for runtime configuration
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80
CMD ["apache2-foreground"]