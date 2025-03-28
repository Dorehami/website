#!/bin/bash

ls /tmp

if [ "$ENV" == "dev" ]; then
  echo "Enabling Xdebug"
  pecl install xdebug
  mv /tmp/xdebug.ini /usr/local/etc/php/conf.d/
else
  rm /tmp/xdebug.ini
fi