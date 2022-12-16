FROM binarious/symfony-cli

# Install PHP 8.1 and required libraries
RUN apk add --update \
    php8 \
    php8-cli \
    php8-curl \
    php8-mbstring \
    php8-json \
    php8-xml \
    php8-openssl \
    php8-zip \
    php8-dom \
    php8-pdo \
    php8-mysqlnd \
    php8-sqlite3

# Install Composer
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

# Install nvm
RUN apk add --update curl
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v16.2.1/install.sh | bash

# Install npm
RUN . ~/.bashrc
RUN nvm install node

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

#EXPOSE 80
#CMD ["apache2-foreground"]