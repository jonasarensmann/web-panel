#!/bin/bash
ADDRESS=$(cat /tmp/remove-email.tmp)
DOMAIN=$(echo $ADDRESS | cut -f2 -d'@')
USERNAME=$(echo $ADDRESS | cut -f1 -d'@')


BASEDIR="$(postconf | grep ^virtual_mailbox_base | cut -f3 -d' ')"

sed -i "/^$ADDRESS /d" /etc/postfix/vmailbox
postmap /etc/postfix/vmailbox

sed -i "/^$USERNAME@/d" $BASEDIR/$DOMAIN/passwd
sed -i "/^$USERNAME@/d" $BASEDIR/$DOMAIN/shadow

/etc/init.d/postfix reload

rm /tmp/remove-email.tmp