FROM php:7.4-apache
ENV APACHE_DOCUMENT_ROOT /var/www/app/public/
# WORKDIR /usr/src/app
RUN apt update && apt upgrade -y
RUN docker-php-ext-install mysqli
RUN a2enmod rewrite
COPY . /var/www/app
RUN rm -r /var/www/html
WORKDIR /var/www/
RUN ln -s app/public html
# COPY $USER:$USER ./src /var/www/html
# COPY ./src /var/www/
# COPY ./php/index.php /var/www/html/
# COPY index2.php /var/www/src/public/index.php
EXPOSE 80
