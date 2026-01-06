# STAGE 1: Build the vendor directory using the official Composer image
# Esta fase é como um "rascunho" apenas para instalar as dependências de forma isolada.
FROM composer:2 AS vendor

# Instala as dependências de sistema e a extensão 'gd' necessária pelo PhpSpreadsheet
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

WORKDIR /app
# Copia apenas os ficheiros de definição de dependências
COPY composer.json composer.lock* ./

# Instala as dependências de forma otimizada para produção
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --optimize-autoloader


# STAGE 2: Build the final production image
FROM php:8.3-apache

# Instala as dependências do sistema e as extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    unzip \
    libzip-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install curl \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Habilita o módulo de reescrita do Apache
RUN a2enmod rewrite

# Define o diretório de trabalho
WORKDIR /var/www/html

# --- CORREÇÃO FINAL E DEFINITIVA DA ORDEM ---
# 1. Copia o código da sua aplicação primeiro
COPY . .

# 2. Copia a pasta 'vendor' (com as dependências corretas) do STAGE 1 por cima.
#    Isto garante que a versão correta da pasta 'vendor' é a que fica.
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

# Garante que o Apache tem permissão para ler todos os ficheiros da aplicação
RUN chown -R www-data:www-data /var/www/html

# Inicia o servidor Apache
CMD ["apache2-foreground"]

