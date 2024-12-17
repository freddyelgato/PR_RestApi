
FROM php:8.2-apache


WORKDIR /var/www/html


COPY . .


RUN docker-php-ext-install mysqli


EXPOSE 8080


CMD ["apache2-foreground"]
