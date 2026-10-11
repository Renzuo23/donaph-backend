FROM php:8.2-apache

# Enable Apache rewrite module if needed
RUN a2enmod rewrite

# Copy all backend code into the container
COPY . /var/www/html/

# Expose HTTP port
EXPOSE 80
