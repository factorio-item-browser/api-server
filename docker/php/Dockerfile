FROM php:7.1-apache
LABEL maintainer="BluePsyduck <buepsyduck@gmx.com>"

RUN apt-get update && apt-get install -y --quiet --no-install-recommends \
        git \
        libbz2-dev \
        libxml2-dev \
        ssh \
        zlib1g-dev \
    && docker-php-ext-install \
        bz2 \
        mbstring \
        pdo_mysql \
        xml \
        zip

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

#RUN pecl install xdebug \
#    && docker-php-ext-enable xdebug

COPY vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

WORKDIR /var/www/html