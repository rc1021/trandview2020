FROM php:7.4-fpm-alpine

RUN apk add --no-cache nginx supervisor wget

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd mysqli pdo_mysql

RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /app
COPY . /app

RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN cd /app && \
    /usr/local/bin/composer install --no-dev

RUN chown -R www-data: /app

CMD ["sh", "/app/docker/startup.sh"]
