#syntax=docker/dockerfile:1-labs

FROM hyperf/hyperf:8.3-alpine-v3.20-swoole
LABEL maintainer="Admin.IM <dev@admin.im>" version="1.0" license="Apache-2.0" app.name="Admin.IM Backend"

ARG TZ
ARG APP_VERSION

ENV TIMEZONE=${TZ} \
    APP_VERSION=${APP_VERSION} \
    APP_ENV=prod \
    SCAN_CACHEABLE=(true) \
    LD_PRELOAD=/usr/lib/preloadable_libiconv.so \
    WELCOME_FILE=../app/Adm/welcome

RUN --mount=type=cache,target=/var/cache/apk \
    set -ex \
    && apk update \
    && apk add --no-cache \
        libstdc++ \
        openssl \
        git \
        bash \
        autoconf \
        pcre2-dev \
        zlib-dev \
        re2c \
        gcc \
        g++ \
        make \
        busybox-extras \
        mysql-client \
        mariadb-connector-c-dev \
        redis \
        php83-pear \
        php83-dev \
        php83-tokenizer \
        php83-fileinfo \
        php83-simplexml \
        php83-xmlwriter \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        zlib-dev \
        libaio-dev \
        openssl-dev \
        curl-dev \
        c-ares-dev

RUN set -ex \
    && cd /etc/php* \
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99_overrides.ini \
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone

RUN --mount=type=cache,target=/root/.cache/pecl \
    set -ex \
    && pecl channel-update pecl.php.net \
    && pecl install --configureoptions 'enable-reader="yes"' xlswriter \
    && echo "extension=xlswriter.so" >> /etc/php83/conf.d/60-xlswriter.ini \
    && mkdir -p /app-src \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man /usr/local/bin/php*

WORKDIR /opt/www

COPY composer.* ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-scripts --no-autoloader

COPY --exclude=web-admin/ --exclude=web-user/ --exclude=app/Adm/Install --exclude=builder --exclude=*.sh . .
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install -o

RUN mkdir /data && touch /data/.env && ln -s /data/.env .env

COPY --chmod=755 ./docker/docker-entrypoint.sh /docker-entrypoint.sh

EXPOSE 9501 9502 9503

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["php", "bin/hyperf.php", "start"]