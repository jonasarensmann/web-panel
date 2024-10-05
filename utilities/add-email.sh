#!/bin/bash
# The details will be saved to /tmp/add-email.tmp in the format:
# username@domain password
# This script will then add the email to the mail server

ADDRESS=$(cat /tmp/add-email.tmp | cut -f1 -d' ')
PASSWD=$(cat /tmp/add-email.tmp | cut -f2 -d' ')
DOMAIN=$(echo $ADDRESS | cut -f2 -d'@')
USERNAME=$(echo $ADDRESS | cut -f1 -d'@')

BASEDIR="$(postconf | grep ^virtual_mailbox_base | cut -f3 -d' ')";

if [ -f /etc/postfix/vmailbox ]
then
	echo $ADDRESS $DOMAIN/$USERNAME/ >> /etc/postfix/vmailbox
	postmap /etc/postfix/vmailbox

	echo $ADDRESS::5000:5000::$BASEDIR/$DOMAIN/$ADDRESS>> $BASEDIR/$DOMAIN/passwd
	echo $ADDRESS":"$(doveadm pw -p $PASSWD) >> $BASEDIR/$DOMAIN/shadow
    chown vmail:vmail $BASEDIR/$DOMAIN/passwd && chmod 775 $BASEDIR/$DOMAIN/passwd
	chown vmail:vmail $BASEDIR/$DOMAIN/shadow && chmod 775 $BASEDIR/$DOMAIN/shadow
	/etc/init.d/postfix reload
fi

rm /tmp/add-email.tmp