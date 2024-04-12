# Usa una imagen de PHP con Apache como servidor web
FROM php:7.4-apache

# Instala las dependencias necesarias de PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Copia el código de tu proyecto Laravel al contenedor
COPY . /var/www/html

# Cambia el propietario de los archivos al usuario de Apache
RUN chown -R www-data:www-data /var/www/html

# Instala Composer (si aún no lo tienes instalado)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala las dependencias de Composer
RUN cd /var/www/html && composer install

# Expone el puerto 80 para que Apache pueda ser accedido desde fuera del contenedor
EXPOSE 80

# Comando para iniciar Apache cuando se inicie el contenedor
CMD ["apache2-foreground"]