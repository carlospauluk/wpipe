FROM php:7.4-apache

# Instalação de dependências
RUN apt-get update && \
    apt-get install -y \
        libcurl4-gnutls-dev \
        libonig-dev \
        libxml2-dev \
        libbz2-dev \
        libmcrypt-dev \
        libzip-dev \
        libmagickwand-dev \
        libssl-dev

RUN docker-php-ext-install -j$(nproc) mysqli
RUN docker-php-ext-install -j$(nproc) bz2
RUN docker-php-ext-install -j$(nproc) curl
RUN docker-php-ext-install -j$(nproc) dom
RUN docker-php-ext-install -j$(nproc) exif
RUN docker-php-ext-install -j$(nproc) fileinfo
RUN docker-php-ext-install -j$(nproc) intl
RUN docker-php-ext-install -j$(nproc) mbstring
RUN docker-php-ext-install -j$(nproc) xml
RUN docker-php-ext-install -j$(nproc) zip

# Instalação de extensões PECL
RUN pecl install imagick igbinary && \
    docker-php-ext-enable imagick


# Gambiarra encontrada em https://github.com/docker-library/php/issues/233#issuecomment-288727629
# Instalação do OpenSSL
RUN docker-php-ext-install openssl; exit 0
#Now we rename the in step 1 downloaded file to desired filename
RUN cp /usr/src/php/ext/openssl/config0.m4 /usr/src/php/ext/openssl/config.m4
#And try to install extension again, this time it works
RUN docker-php-ext-install openssl


# Configuração do Apache
RUN a2enmod rewrite

# Limpeza
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN echo "display_errors = Off" >> /usr/local/etc/php/php.ini

COPY site.conf /etc/apache2/sites-available/site.conf

RUN a2dissite 000-default.conf
RUN a2ensite site.conf

RUN chown -R www-data:www-data /var/www/html

# Defina um volume para os arquivos do site
VOLUME /var/www/html

# Exposição da porta 80
EXPOSE 80
