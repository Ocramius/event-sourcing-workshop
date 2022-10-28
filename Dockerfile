FROM ubuntu:22.04 AS base-distro
FROM composer:2.4.4 AS vendor-dependencies

COPY composer.json \
    composer.lock \
    /app/

WORKDIR /app

RUN composer install --ignore-platform-reqs

FROM base-distro AS sandbox

ENV COMPOSER_HOME=/usr/local/share/composer \
    DEBIAN_FRONTEND=noninteractive \
    ACCEPT_EULA=Y

RUN apt update \
    && apt upgrade -y \
    && apt install -y --no-install-recommends \
      gpg-agent \
      software-properties-common \
    && add-apt-repository -y ppa:ondrej/php \
    && apt install -y --no-install-recommends \
        # Base dependencies \
        git \
        unzip \
        \
        php8.1-bcmath \
        php8.1-cli \
        php8.1-intl \
        php8.1-mbstring \
        php8.1-sqlite3 \
        php8.1-phpdbg \
        php8.1-xml \
        php8.1-xsl \
        php8.1-zip \
        \
        php-xdebug \
    # Set default PHP version
    && update-alternatives --set php /usr/bin/php8.1 \
    && apt autoremove -y \
    && apt clean \
    && git config --global --add safe.directory '*'

WORKDIR /app

COPY . /app
COPY --from=vendor-dependencies /app/vendor /app/vendor
COPY --from=vendor-dependencies /usr/bin/composer /usr/bin/composer

RUN composer install
