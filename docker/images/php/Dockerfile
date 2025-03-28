FROM php:8.4-cli

ENV COMPOSER_HOME="/.composer"

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update \
  && apt-get install -y unzip git libpq-dev zlib1g-dev libpng-dev libjpeg-dev libicu-dev \
  && install-php-extensions xdebug intl \
  && curl -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony \
  && mkdir -m 0777 -p /.symfony5 \
  && mkdir -m 0777 -p /.composer/cache \
  && chmod a+rw -R /.composer

RUN curl -sS https://get.symfony.com/cli/installer | bash

COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh

RUN { \
  echo "xdebug.mode=debug"; \
  echo "xdebug.client_host=host.docker.internal"; \
  echo "xdebug.client_port=9003"; \
  } >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  && echo "memory_limit = 4G" > /usr/local/etc/php/conf.d/memory-limit.ini \
  && echo "max_execution_time = 0" > /usr/local/etc/php/conf.d/max-execution-time.ini \
  && chmod a+x /usr/local/bin/entrypoint.sh

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-configure gd
RUN docker-php-ext-install pdo pdo_pgsql gd intl
RUN docker-php-ext-enable pdo pdo_pgsql gd intl
RUN pecl install mongodb && docker-php-ext-enable mongodb


ENTRYPOINT ["entrypoint.sh"]

WORKDIR /opt/project