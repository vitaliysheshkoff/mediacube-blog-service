#!/bin/sh
set -e

# Initialize storage directory if empty
if [ ! "$(ls -A /var/www/storage)" ]; then
  echo "Initializing storage directory..."
  cp -R /var/www/storage-init/. /var/www/storage
  chown -R www-data:www-data /var/www/storage
fi

# Remove storage-init directory
rm -rf /var/www/storage-init

# Run Laravel migrations
php artisan migrate --force

# Clear and cache configurations
php artisan config:cache
php artisan route:cache

# Run the default command
exec "$@"
