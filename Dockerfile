FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Enable .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/

# Set permissions for uploads and logs
RUN mkdir -p /var/www/html/uploads /var/www/html/logs && \
    chown -R www-data:www-data /var/www/html/uploads /var/www/html/logs && \
    chmod -R 755 /var/www/html/uploads /var/www/html/logs

# Set the working directory
WORKDIR /var/www/html/

# Port exposure is handled by CapRover usually, but we define 80
EXPOSE 80
