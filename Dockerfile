FROM php:8.1-fpm-alpine
LABEL org.opencontainers.image.authors="jerome@jutteau.fr"

# install base
RUN apk update && \
    ln -snf /usr/share/zoneinfo/Etc/UTC /etc/localtime  && \
    echo "UTC" > /etc/timezone

# install lighttpd
RUN apk add lighttpd git

# install jirafeau
RUN mkdir /www
WORKDIR /www
COPY .git .git
RUN git reset --hard && rm -rf docker install.php .git .gitignore .gitlab-ci.yml CONTRIBUTING.md Dockerfile README.md
RUN touch /www/lib/config.local.php
RUN chown -R $(id -u lighttpd).$(id -g www-data) /www
RUN chmod o=,ug=rwX -R /www

COPY docker/cleanup.sh /cleanup.sh
COPY docker/run.sh /run.sh
RUN chmod o=,ug=rx /cleanup.sh /run.sh
COPY docker/docker_config.php /docker_config.php

RUN mkdir -p /usr/local/etc/php
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/lighttpd.conf /etc/lighttpd/lighttpd.conf

# cleanup
RUN apk del git
RUN rm -rf /var/cache/apk/*

CMD /run.sh
EXPOSE 80