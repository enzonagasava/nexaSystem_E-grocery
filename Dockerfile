FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip git curl gnupg zip libzip-dev libonig-dev libxml2-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql zip mbstring \
    && a2enmod rewrite

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN node -v && npm -v

RUN git config --global --add safe.directory /var/www/html


RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure PHP for large file uploads (support up to 2GB)
RUN echo "upload_max_filesize = 2G" >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
    echo "post_max_size = 2G" >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
    echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
    echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini



COPY . .

RUN composer install --no-dev --optimize-autoloader && \
    npm ci && \
    npm run build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY laravel.conf /etc/apache2/sites-available/laravel.conf
RUN a2dissite 000-default.conf \
    && a2ensite laravel.conf

EXPOSE 80
EXPOSE 5176

CMD ["apache2-foreground"]
