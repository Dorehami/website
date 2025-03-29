#!/bin/sh
set -e

# Enable more verbose output
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Clearing cache..."
php bin/console cache:clear --env=prod --no-debug

echo "Warming up cache..."
php bin/console cache:warmup --env=prod --no-debug

# Create log directory if it doesn't exist
mkdir -p /var/log/nginx

# Ensure proper upstream configuration
echo "upstream php-upstream { server 127.0.0.1:9000; }" > /etc/nginx/conf.d/upstream.conf

# Test nginx configuration
echo "Testing Nginx configuration..."
nginx -t

# Start PHP-FPM with debug logging
echo "Starting PHP-FPM..."
php-fpm -D

# Check if PHP-FPM is running
echo "Checking PHP-FPM status..."
ps aux | grep php-fpm

# Check if the socket is listening
echo "Checking PHP-FPM socket..."
netstat -tuln | grep 9000

echo "Starting Nginx..."
# Run nginx in the foreground
exec nginx -g "daemon off;"