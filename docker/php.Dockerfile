FROM php:8.2-fpm

# Update package list and install dependencies
RUN apt-get update && \
    apt-get install -y \
    libpq-dev \
    zlib1g-dev \
    libmemcached-dev \
    curl \
    vim \
    zip && \
    docker-php-ext-install mysqli pdo_mysql pdo_pgsql pgsql && \
    pecl install -o -f redis && \
    docker-php-ext-enable redis && \
    rm -rf /var/lib/apt/lists/* /tmp/pear

COPY ./app /var/www/html

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Verify composer installation
RUN composer --version

# Configure PHP-FPM
COPY docker/etc/php/php.ini-development /usr/local/etc/php/php.ini
COPY docker/etc/php/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf


# Set working directory
WORKDIR /var/www/html