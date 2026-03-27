FROM php:8.4-cli-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/tmp/composer-cache

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        ca-certificates \
        curl \
        git \
        libicu-dev \
        libonig-dev \
        libssl-dev \
        libxml2-dev \
        libzip-dev \
        pkg-config \
        unzip \
        zip \
    && docker-php-ext-install -j"$(nproc)" intl mbstring opcache simplexml zip \
    && pecl install mongodb \
    && pecl install pcov \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-enable pcov \
    && curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm -f /tmp/composer-setup.php \
    && rm -rf /tmp/pear /var/lib/apt/lists/*

COPY docker/php/conf.d/app.ini /usr/local/etc/php/conf.d/zz-app.ini

CMD ["sleep", "infinity"]
