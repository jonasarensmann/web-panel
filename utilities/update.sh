#!/bin/bash

cd /tmp
git clone $REPO
cp -rf web-panel/interface /etc/web-panel
cp -rf web-panel/installer/templates /etc/web-panel
cp -rf web-panel/installer/templates/Caddyfile-template /etc/caddy
cp -rf web-panel/updates/scripts /etc/web-panel/updates
cp -rf web-panel/utilities /etc/web-panel
go build -o /etc/web-panel/utilities/caddyfile-conv/main /etc/web-panel/utilities/caddyfile-conv/main.go
cd web-panel/ 
export TEXT=$(git log -1 --pretty=%B)
cd ../
rm -rf /tmp/web-panel

export CURRENT_UPDATE=$(cat /etc/web-panel/updates/current)

# get file with highest number from /etc/web-panel/updates
export HIGHEST_UPDATE=$(ls /etc/web-panel/updates/scripts | sort -n | tail -n 1)

# run all updates from current to highest
for i in $(seq $CURRENT_UPDATE $HIGHEST_UPDATE); do
    if [ "$i" -eq "$CURRENT_UPDATE" ]; then
        continue
    fi
    FILE=/etc/web-panel/updates/scripts/$(printf "%08d" $i)
    chmod +x $FILE
    bash $FILE
done

# update current update number
echo $HIGHEST_UPDATE > /etc/web-panel/updates/current

cd /etc/web-panel/interface
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader &&
composer update &&
npm install &&
npm run build &&
php artisan migrate --force &&
php artisan optimize:clear &&
php artisan config:clear &&

php artisan tinker --execute "\App\Models\Message::factory()->create(['title' => 'Update Successful', 'content' => '$TEXT', 'show_admins' => true]);"