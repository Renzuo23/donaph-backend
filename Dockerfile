FROM php:8.2-apache

# Fix multiple MPM loaded error
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Copy project files into Apache root
COPY . /var/www/html/

# Expose HTTP port
EXPOSE 80
