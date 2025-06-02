FROM php:8.3-fpm

# install all the dependencies and enable PHP modules
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
      nano \
      git \
      zip \
      unzip \
      cron \
      sudo \
      curl \
      wget \
      libicu-dev \
      zlib1g-dev \
      libxml2-dev \
      libreadline-dev \
      libcurl4-openssl-dev \
      libpng-dev \
      libzip-dev \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
      ctype \
      curl \
      dom \
      iconv \
      xml \
      mysqli \
      pdo_mysql \
      intl \
      gd \
      opcache

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /var/www/ninjatooken