FROM php:8.1-fpm-alpine
LABEL org.opencontainers.image.authors="jerome@jutteau.fr"

# lighttpd user
ARG USER_ID=100
# www-data group
ARG GROUP_ID=82

# install base
RUN apk update && \
    ln -snf /usr/share/zoneinfo/Etc/UTC /etc/localtime  && \
    echo "UTC" > /etc/timezone


# install jirafeau
RUN mkdir /www
WORKDIR /www
COPY .git .git
RUN apk add git && \
    git reset --hard && rm -rf docker install.php .git .gitignore .gitlab-ci.yml CONTRIBUTING.md Dockerfile README.md && \
    apk del git && \
    touch /www/lib/config.local.php && \
    chown -R $USER_ID.$GROUP_ID /www && \
    chmod o=,ug=rwX -R /www

COPY docker/cleanup.sh /cleanup.sh
COPY docker/run.sh /run.sh
RUN chmod o=,ug=rx /cleanup.sh /run.sh
COPY docker/docker_config.php /docker_config.php

# install lighttpd
RUN apk add lighttpd && \
    chown -R $USER_ID /var/log/lighttpd && \
    mkdir -p /usr/local/etc/php
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/lighttpd.conf /etc/lighttpd/lighttpd.conf

# cleanup
RUN rm -rf /var/cache/apk/*

CMD /run.sh
EXPOSE 80