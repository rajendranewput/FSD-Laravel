# Use an official PHP image as a parent image
FROM php:8.3-fpm

# Arguments provided in docker-compose.yml
ARG WWWGROUP
ARG WWWUSER

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libmcrypt-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    supervisor \
    vim \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets \
    && docker-php-ext-enable sockets

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set up working directory
WORKDIR /var/www/html

# Copy application code to the container
COPY . /var/www/html

# Set permissions
RUN chown -R $WWWUSER:$WWWGROUP /var/www/html

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - && apt-get install -y nodejs

# Install Laravel Sail (optional)
RUN composer global require laravel/sail --prefer-dist

# Add global Composer binaries to PATH
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# Expose port 80
EXPOSE 80

# Start the PHP-FPM server
CMD ["php-fpm"]
