#!/bin/bash

apt install -y php8.2 php8.2-fpm php8.2-sqlite3 composer openssl php8.2-bcmath php8.2-curl php-json php8.2-mbstring php8.2-mysql php8.2-common php8.2-xml php8.2-zip php8.2-imagick

config_file="/etc/php/8.2/fpm/pool.d/www.conf"

old_user="www-data"
new_user="caddy"

# change user for php fpm
sed -i "s/\(user\s*=\s*\)$old_user/\1$new_user/" "$config_file"
sed -i "s/\(group\s*=\s*\)$old_user/\1$new_user/" "$config_file"
sed -i "s/\(listen.owner\s*=\s*\)$old_user/\1$new_user/" "$config_file"
sed -i "s/\(listen.group\s*=\s*\)$old_user/\1$new_user/" "$config_file"

# restart php fpm
systemctl restart php8.2-fpm