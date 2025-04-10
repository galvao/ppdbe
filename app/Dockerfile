FROM php:8.2-apache

USER root

COPY . /usr/src/ppdbe

RUN apt -y update
RUN apt -y install curl sudo vim libnss-wrapper git libpq-dev && docker-php-ext-install pdo pdo_pgsql bcmath

RUN a2dissite 000-default
RUN service apache2 stop
RUN mkdir -p /var/www/html/ppdbe/public

# Replaces Apache's default DocumentRoot for the project's so there's no need for vhosts
RUN sed -i "12s/.*/DocumentRoot \/var\/www\/html\/ppdbe\/public/" /etc/apache2/sites-available/000-default.conf

RUN a2ensite 000-default
RUN a2enmod rewrite
RUN service apache2 start

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

COPY ./src/. /var/www/html/ppdbe/
WORKDIR /var/www/html/ppdbe

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html

# RUN composer update

