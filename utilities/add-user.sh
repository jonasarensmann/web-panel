#!/bin/bash
USERNAME=$(cat /tmp/add-user.tmp | cut -f1 -d' ')
PASSWD=$(cat /tmp/add-user.tmp | cut -f2 -d' ')

# Add User
useradd --create-home --shell /bin/bash $USERNAME &&
echo "$USERNAME:$PASSWD" | chpasswd

mkdir -p /home/$USERNAME/domains
setfacl -R -m user:$USERNAME:rwx  /home/$USERNAME/domains
setfacl -R -m user:caddy:rwx  /home/$USERNAME/domains

rm /tmp/add-user.tmp