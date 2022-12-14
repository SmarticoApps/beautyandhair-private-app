FROM php:8.1.13-apache 

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive \
    apt-get install -y \
    nano \
    cron \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    --no-install-recommends \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j "$(nproc)" gd

RUN a2enmod rewrite && a2enmod expires

COPY ./config/default-dev.conf /etc/apache2/sites-available/000-default.conf
COPY ./config/php.ini-development /usr/local/etc/php/php.ini
COPY ./config/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./ /var/www/html

RUN printf "ServerName localhost \n\
LimitRequestBody 104857600 \n\
ServerTokens Prod \n\
ServerSignature Off" >> /etc/apache2/apache2.conf

RUN sed -i 's/^exec /service cron start\n\nexec /' /usr/local/bin/apache2-foreground

EXPOSE 80