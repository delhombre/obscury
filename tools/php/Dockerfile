FROM php:7.4-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN COMPOSER_ALLOW_SUPERUSER=1 composer config --global allow-plugins.symfony/flex true
RUN COMPOSER_ALLOW_SUPERUSER=1 composer update --with-all-dependencies

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN git config --global user.email "brunodogbase@gmail.com" \ 
    && git config --global user.name "delhombre"