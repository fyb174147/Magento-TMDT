FROM php:8.3-fpm

# Cài đặt các thư viện hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libxslt1-dev libicu-dev libzip-dev libonig-dev libxml2-dev \
    git unzip curl

# Cài đặt các PHP Extensions mà Magento yêu cầu
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    gd bcmath intl pdo_mysql soap zip sockets xsl ftp

# Cài đặt Composer (Để cài Magento sau này)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN echo "memory_limit=2G" > /usr/local/etc/php/conf.d/memory-limit.ini
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf
