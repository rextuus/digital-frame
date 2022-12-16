FROM arodax/php8.1-apache

RUN apt-get update && apt-get install -y \
git \
unzip \
libzip-dev \
curl \
 && docker-php-ext-configure zip \
 && docker-php-ext-install zip
 \
#    nvm
# Install NVM and set up the environment
RUN mkdir -p /usr/local/nvm
ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 16.15.1

# Install NVM
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.37.2/install.sh | bash

# Set up the NVM environment
RUN . $NVM_DIR/nvm.sh && \
    nvm install $NODE_VERSION && \
    nvm alias default $NODE_VERSION && \
    nvm use default

# Add NVM to the PATH
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

# Enable the Apache mod_rewrite module and restart Apache
RUN a2enmod rewrite && \
    service apache2 restart

#   composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filname=composer

#   copy and install dependencies
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

RUN ls -l
RUN npm install
RUN composer update

# Erstelle ein selbst signiertes Zertifikat
RUN openssl req -x509 -newkey rsa:4096 -keyout /etc/ssl/private/selfsigned.key -out /etc/ssl/certs/selfsigned.crt -days 365 -nodes -subj "/C=DE/ST=Berlin/L=Berlin/O=My Company/OU=My Department/CN=localhost"

# Kopiere das Zertifikat und den privaten Schlüssel in den Container
COPY selfsigned.crt /etc/ssl/certs/selfsigned.crt
COPY selfsigned.key /etc/ssl/private/selfsigned.key

# Konfiguriere Apache, um das Zertifikat und den privaten Schlüssel zu verwenden
RUN sed -i "s/#SSLCertificateFile/SSLCertificateFile/g" /etc/apache2/sites-available/default-ssl.conf
RUN sed -i "s/#SSLCertificateKeyFile/SSLCertificateKeyFile/g" /etc/apache2/sites-available/default-ssl.conf

# Aktiviere SSL und Standard-SSL-Site
RUN a2enmod ssl
RUN a2ensite default-ssl

EXPOSE 80
CMD ["apache2-foreground"]