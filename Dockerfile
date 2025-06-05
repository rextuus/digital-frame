# Use a PHP with Apache base image optimized for production
FROM php:8.2-apache

# Install required PHP extensions and system dependencies
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    git \
    default-mysql-client \
    nodejs \
    npm \
    libsqlite3-dev && \
    docker-php-ext-install pdo pdo_mysql pdo_sqlite opcache


# Install Symfony CLI (to manage the Symfony app easily)
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Install Composer globally for PHP package management
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Setup working directory in the container
WORKDIR /var/www/html

# Copy Symfony project files to the container
COPY . /var/www/html

# Adjust permissions so that Symfony’s cache and logs are writable
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/var

# Install PHP and Node.js dependencies
RUN composer install --no-dev --optimize-autoloader && \
    npm install && npm run build

# Configure Apache for Symfony
RUN a2enmod rewrite && a2enmod ssl

# Configure SSL (optional self-signed certificate)
RUN openssl req -x509 -newkey rsa:4096 -nodes \
    -keyout /etc/ssl/private/selfsigned.key \
    -out /etc/ssl/certs/selfsigned.crt \
    -sha256 -days 365 -subj "/C=DE/ST=Berlin/L=Berlin/O=My Company/OU=My Department/CN=localhost"

COPY docker/apache/default.conf /etc/apache2/sites-available/000-default.conf

# Mount default-ssl site configuration (for HTTPS)
RUN echo '<IfModule mod_ssl.c>\n\
<VirtualHost _default_:443>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    SSLEngine on\n\
    SSLCertificateFile /etc/ssl/certs/selfsigned.crt\n\
    SSLCertificateKeyFile /etc/ssl/private/selfsigned.key\n\
</VirtualHost>\n\
</IfModule>' > /etc/apache2/sites-available/default-ssl.conf && \
    a2ensite default-ssl

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# Start the Apache server in the foreground
CMD ["apache2-foreground"]