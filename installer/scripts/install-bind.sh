#!/bin/bash

# reload profile
source /etc/profile

# install deps
apt install -y bind9 nodejs npm

# add domain
echo "zone \"$1\" { type master; file \"/etc/bind/$1.db\"; };" >> /etc/bind/named.conf 

npm install dns-zonefile --global

# copy zone template to /etc/templates/zone-template.json
mkdir -p /etc/web-panel/templates
cp $HOME/web-panel/installer/templates/zone-template.json /etc/web-panel/templates/zone-template.json

# create zone file from template
jq --arg domain $1 --arg ip $2 '
    .["$origin"] = $domain + "." |
    .soa.mname |= "ns1." + $domain + "." |
    .soa.rname |= "hostmaster." + $domain + "." |
    .ns[] |= {host: (.host | sub("\\$domain"; $domain))} |
    .a[] |= {name: .name, ip: $ip} |
    .mx[0].host |= "mail." + $domain + "."
' /etc/web-panel/templates/zone-template.json > updated-zone-template.json

# create zone file
zonefile -g updated-zone-template.json > /etc/bind/$1.db

# restart bind
systemctl restart bind9 named
