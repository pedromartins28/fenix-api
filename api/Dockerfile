FROM php:8.4-cli

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install redis \
    && pecl install pcov \
    && docker-php-ext-enable redis pcov

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar projeto
COPY . .

# Instalar dependências do Laravel
RUN composer install

# Expor porta
EXPOSE 8000

# Comando padrão
CMD php artisan serve --host=0.0.0.0 --port=8000
