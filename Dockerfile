FROM php:8.1-fpm-alpine
LABEL org.opencontainers.image.authors="jerome@jutteau.fr"

# base install
RUN apk update && \
    apk add lighttpd && \
    rm -rf /var/cache/apk/* && \
    ln -snf /usr/share/zoneinfo/Etc/UTC /etc/localtime  && \
    echo "UTC" > /etc/timezone

COPY docker/cleanup.sh /cleanup.sh
COPY docker/run.sh /run.sh
RUN chmod o=,ug=rx /cleanup.sh /run.sh
COPY docker/docker_config.php /docker_config.php

RUN mkdir -p /usr/local/etc/php
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/lighttpd.conf /etc/lighttpd/lighttpd.conf

# install jirafeau
RUN mkdir /www
WORKDIR /www
# Will ignore some files through .dockerignore
COPY . .
RUN rm -rf docker && \
    touch /www/lib/config.local.php && \
    chown -R $(id -u lighttpd).$(id -g www-data) /www && \
    chmod o=,ug=rwX -R /www

CMD /run.sh
EXPOSE 80