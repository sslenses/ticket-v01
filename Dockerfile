FROM dunglas/frankenphp:latest-php8.3

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    pcntl \
    opcache

# Restore frankenphp binary if it was backed up by install-php-extensions
RUN if [ -f /usr/local/bin/frankenphp.backup ]; then cp /usr/local/bin/frankenphp.backup /usr/local/bin/frankenphp; fi

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Composer is no longer run during build as the local vendor folder is synced directly.

# Create symlink for FrankenPHP binary to prevent Octane from downloading it
RUN ln -sf /usr/local/bin/frankenphp /app/frankenphp

# Configure custom PHP settings for file uploads
RUN printf "upload_max_filesize = 100M\npost_max_size = 100M\nmemory_limit = 256M\n" > /usr/local/etc/php/conf.d/uploads.ini

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expose port 80 and 443
EXPOSE 80
EXPOSE 443
EXPOSE 2019
