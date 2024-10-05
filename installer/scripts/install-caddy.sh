#!/bin/bash

# reload profile
source /etc/profile

# install jq
apt -y install jq acl

# xcaddy
go install github.com/caddyserver/xcaddy/cmd/xcaddy@latest 
mkdir -p /etc/xcaddy
cp $HOME/go/bin/xcaddy /etc/xcaddy/xcaddy 
rm -rf $HOME/go/bin/xcaddy

# create caddy dir
mkdir -p /etc/caddy
cd /etc/caddy 

[ -f go.mod ] ||
go mod init caddy 

# move libdns plugins to caddy dir
cp -r $HOME/web-panel/caddy_modules/caddy-dns /etc/caddy/caddy-dns
cp -r $HOME/web-panel/libdns /etc/caddy/libdns

/etc/xcaddy/xcaddy build --with github.com/jonasarensmann/web-panel/caddy_modules/caddy-dns --replace github.com/jonasarensmann/web-panel/caddy_modules/caddy-dns=/etc/caddy/caddy-dns --replace github.com/jonasarensmann/web-panel/libdns=/etc/caddy/libdns --output /etc/caddy/caddy

# create caddy group and user
groupadd --system caddy 
useradd --system \
    --gid caddy \
    --create-home \
    --home-dir /var/lib/caddy \
    --shell /usr/sbin/nologin \
    --comment "Caddy web server" \
    caddy


chown -R root:caddy /etc/postfix/vmailbox

# give caddy group permission to write to /etc/caddy
chown -R root:caddy /etc/caddy
chmod 0770 /etc/caddy

# create directory for caddy logs
mkdir -p /var/log/caddy
chown -R caddy:caddy /var/log/caddy

# copy caddyfile template
cp $HOME/web-panel/installer/templates/Caddyfile-template /etc/caddy/Caddyfile


mkdir -p /etc/caddy/zones

# copy service file
cp $HOME/web-panel/installer/templates/caddy.service /etc/systemd/system/caddy.service

# reload systemd
systemctl daemon-reload

# enable caddy service 
systemctl enable caddy.service 
