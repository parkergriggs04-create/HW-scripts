# 1. Base image: PHP + Apache together. Change 8.3 if your project needs another PHP version.
FROM php:8.3-apache
# 2. PHP extensions your code needs. pdo_pgsql talks to Render's Postgres database.
RUN apt-get update \
&& apt-get install -y --no-install-recommends libpq-dev \
&& docker-php-ext-install pdo_pgsql \
&& rm -rf /var/lib/apt/lists/*
# 3. Production PHP settings: errors go to Render's logs, not onto the page.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# 4. Apache: listen on Render's port, serve the public/ folder, allow .htaccess files.
ENV PORT=10000
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf \
&& sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf \
&& sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
&& sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-
available/*.conf \
&& printf 'ServerName localhost\n<Directory ${APACHE_DOCUMENT_ROOT}>\n Options -Indexes\n
AllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/app.conf \
&& a2enconf app \
&& a2enmod rewrite
# 5. Your code. The whole project is copied, but only public/ can be reached by URL.
COPY . /var/www/html/
EXPOSE 10000