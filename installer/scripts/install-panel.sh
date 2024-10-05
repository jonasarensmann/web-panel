#!/bin/bash

mkdir -p /etc/web-panel/interface 
cp -r $HOME/web-panel/interface /etc/web-panel/ 

cd /etc/web-panel/interface 

# create .env file
cp .env.example .env 
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=\/etc\/web-panel\/interface\/panel.db/' .env

# build caddyfile conv 
mkdir -p /etc/web-panel/utilities/caddyfile-conv
go build -o /etc/web-panel/utilities/caddyfile-conv/main $HOME/web-panel/utilities/caddyfile-conv/main.go

rm -r $HOME/web-panel/utilities/caddyfile-conv
cp -r $HOME/web-panel/utilities /etc/web-panel

# install dependencies
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader
composer update
npm install
npm run build 

# install tinyfilemanager
mkdir -p /etc/web-panel/public
curl -o /etc/web-panel/public/tinyfilemanager.php https://raw.githubusercontent.com/prasathmani/tinyfilemanager/master/tinyfilemanager.php

sed -i 's/$use_auth = true;/$use_auth = false;/' /etc/web-panel/public/tinyfilemanager.php

# artisan commands
php artisan key:generate --force 
php artisan optimize 
php artisan migrate --force 

# for domain creation
systemctl start caddy

php artisan tinker --execute "\App\Models\User::factory()->create(['username' => '$2', 'password' => Hash::make('$3'), 'is_admin' => true]);" 
php artisan tinker --execute "\App\Models\Domain::factory()->create(['name' => '$1', 'user_id' => 1]);"
echo $(php artisan tinker --execute "echo explode('|', \App\Models\User::where(['username' => '$2'])->first()->createToken('caddy-REQUIRED_FOR_SSL_CERTIFICATES')->plainTextToken)[1]") > /tmp/caddytoken.tmp

sed -i "s/CHANGETHIS/$(cat /tmp/caddytoken.tmp)/" /etc/caddy/Caddyfile

# create public directory
mkdir -p /home/$2/domains/$1/public
chown -R $2:caddy /home/$2/domains
cp /root/web-panel/installer/templates/index.html /etc/web-panel/template.html
cp /etc/web-panel/template.html /home/$2/domains/$1/public/index.html
setfacl -R -m user:$2:rwx  /home/$2/domains
setfacl -R -m user:caddy:rwx  /home/$2/domains

# give permissions to /etc/bind
chown -R caddy:caddy /etc/bind

export TEXT="Welcome! Thanks for installing. You can now start using the panel. If you have any questions, feel free to ask on github"
php artisan tinker --execute "\App\Models\Message::factory()->create(['title' => 'Welcome', 'content' => '$TEXT']);"

chown -R caddy:caddy /etc/web-panel/ 
chown -R caddy:caddy /etc/caddy
systemctl restart caddy

echo 'Defaults env_keep += "REPO"' >> /etc/sudoers.d/caddy
echo "%caddy ALL=(ALL) NOPASSWD: /bin/systemctl reload caddy" >> /etc/sudoers.d/caddy
echo "%caddy ALL=(ALL) NOPASSWD: /bin/systemctl restart named" >> /etc/sudoers.d/caddy
echo "%caddy ALL=(ALL) NOPASSWD: /etc/web-panel/utilities/add-email.sh" >> /etc/sudoers.d/caddy
echo "%caddy ALL=(ALL) NOPASSWD: /etc/web-panel/utilities/remove-email.sh" >> /etc/sudoers.d/caddy
echo "%caddy ALL=(ALL) NOPASSWD: /etc/web-panel/utilities/update.sh" >> /etc/sudoers.d/caddy
echo "%caddy ALL=(ALL) NOPASSWD: /etc/web-panel/utilities/add-user.sh" >> /etc/sudoers.d/caddy

mkdir -p /etc/web-panel/updates/scripts
echo $(ls /root/web-panel/updates/scripts | sort -n | tail -n 1) > /etc/web-panel/updates/current