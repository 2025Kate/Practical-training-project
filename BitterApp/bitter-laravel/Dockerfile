FROM php:8.2-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client \
    wait-for-it  # Добавляем утилиту для ожидания

# Очистка кеша
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Установка Composer
COPY --from=composer:2.7.6 /usr/bin/composer /usr/bin/composer

# Создание пользователя для приложения
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Копирование приложения
COPY --chown=www:www . /var/www

# Переключение на пользователя www
USER www

# Установка рабочей директории
WORKDIR /var/www

EXPOSE 8000

# Ждем готовности MySQL и запускаем приложение
CMD sh -c "wait-for-it mysql:3306 --timeout=30 -- composer install && php artisan key:generate && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000"
