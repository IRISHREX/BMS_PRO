FROM php:8.1-apache

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Allow .htaccess overrides in Apache config
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files into container
COPY . /var/www/html/

# Set appropriate directory ownership and permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/uploads \
    && chmod -R 775 /var/www/html/temp \
    && chmod -R 775 /var/www/html/application/logs

# Set port environment variable (Render assigns $PORT)
ENV PORT=80
EXPOSE 80

CMD ["apache2-foreground"]
