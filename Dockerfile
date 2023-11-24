# Use an official PHP runtime as a parent image
FROM php:7.4-fpm

# Set the working directory in the container
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev

# Install PHP extensions, including MySQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the Symfony project files into the container
COPY . .

# Install Symfony application dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer config --global allow-plugins.symfony/flex true
RUN COMPOSER_ALLOW_SUPERUSER=1 composer update --with-all-dependencies

# Expose port 9000 to communicate with PHP-FPM
EXPOSE 9000

# Start the PHP-FPM server
CMD ["php-fpm"]
