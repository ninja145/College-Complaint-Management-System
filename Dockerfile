FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP PDO and MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite for clean URL handling
RUN a2enmod rewrite

# Configure Apache DocumentRoot to point to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure Apache Directory permissions for mod_rewrite and .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Adjust upload file size limits in PHP configuration (Max 2MB per PRD, set server ceiling to 10MB)
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/uploads.ini

# Set working directory
WORKDIR /var/www/html

# Create uploads directory and ensure permissions
RUN mkdir -p /var/www/html/public/uploads && chown -R www-data:www-data /var/www/html/public/uploads && chmod -R 775 /var/www/html/public/uploads

EXPOSE 80
