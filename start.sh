#!/bin/bash
# Run migrations and seed the database
php artisan migrate:fresh --seed --force

# Start Apache in the foreground
apache2-foreground
