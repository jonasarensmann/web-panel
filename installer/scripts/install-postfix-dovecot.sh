#!/bin/bash

debconf-set-selections <<< "postfix postfix/mailname string mail.$1"
debconf-set-selections <<< "postfix postfix/main_mailer_type string 'Internet Site'"
apt install -y postfix dovecot-imapd dovecot-pop3d dovecot-sqlite

groupadd -g 5000 vmail
useradd -s /usr/sbin/nologin -u 5000 -g 5000 vmail
usermod -aG vmail postfix
usermod -aG vmail dovecot

mkdir -p /var/mail/vhosts/$1
chown -R vmail:vmail /var/mail/vhosts
chmod -R 775 /var/mail/vhosts

touch /var/log/dovecot
chgrp vmail /var/log/dovecot
chmod 660 /var/log/dovecot

echo "$1" > /etc/postfix/virtual_domains
touch /etc/postfix/vmailbox
echo "root@$1 $2@$1" > /etc/postfix/virtual_alias
postmap /etc/postfix/virtual_alias

systemctl enable --now postfix
systemctl enable --now dovecot

# $1 = domain
# $2 = password
# $3 = username

# download roundcube
wget https://github.com/roundcube/roundcubemail/releases/download/1.6.7/roundcubemail-1.6.7.tar.gz
tar -xzf roundcubemail-1.6.7.tar.gz
mkdir -p /var/www/roundcube
mv roundcubemail-1.6.7/* /var/www/roundcube
chown -R caddy:caddy /var/www/roundcube
chmod -R 755 /var/www/roundcube
mv /var/www/roundcube/composer.json-dist /var/www/roundcube/composer.json
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --working-dir=/var/www/roundcube
/var/www/roundcube/bin/install-jsdeps.sh

# configure roundcube
cp /var/www/roundcube/config/config.inc.php.sample /var/www/roundcube/config/config.inc.php
sed -i "s|\$config\['db_dsnw'\] = 'mysql://roundcube:pass@localhost/roundcubemail';|\$config\['db_dsnw'\] = 'sqlite:////var/www/roundcube/roundcube.db';|" /var/www/roundcube/config/config.inc.php
sed -i "s|\$config\['smtp_host'\] = 'localhost:587';|\$config\['smtp_host'\] = 'localhost:25';|" /var/www/roundcube/config/config.inc.php

# configure dovecot
cat <<EOF >> /etc/dovecot/dovecot-sql.conf.ext
driver = sqlite
connect = /var/www/roundcube/roundcube.db
default_pass_scheme = SHA512-CRYPT
password_query = SELECT username as user, password FROM users WHERE username = '%u'
EOF

cat <<EOF >> /etc/dovecot/dovecot.conf
auth_mechanisms = plain login
disable_plaintext_auth = no
log_path = /var/log/dovecot
mail_location = maildir:/var/mail/vhosts/%d/%n

passdb {
	args = /var/mail/vhosts/%d/shadow
	driver = passwd-file
}

protocols = imap pop3

service auth {
	unix_listener /var/spool/postfix/private/auth {
		group = vmail
		mode = 0660
		user = postfix
	}
		unix_listener auth-master {
		group = vmail
		mode = 0600
		user = vmail
	}
}

ssl_cert = /var/lib/caddy/.local/share/caddy/certificates/acme-v02.api.letsencrypt.org-directory/wildcard_.$1/wildcard_.$1.crt
ssl_key = /var/lib/caddy/.local/share/caddy/certificates/acme-v02.api.letsencrypt.org-directory/wildcard_.$1/wildcard_.$1.key

userdb {
	args = /var/mail/vhosts/%d/passwd
	driver = passwd-file
}
EOF

# configure postfix
cat <<EOF >> /etc/postfix/main.cf
append_dot_mydomain = no
recipient_delimiter = +
readme_directory = no
myhostname = $1
mydomain = $1
myorigin = $1
inet_interfaces = all
mydestination = localhost, mail.$1
mynetworks = 127.0.0.0/8 192.168.0.0/16

virtual_mailbox_domains = /etc/postfix/virtual_domains
virtual_mailbox_base = /var/mail/vhosts
virtual_mailbox_maps = hash:/etc/postfix/vmailbox
virtual_alias_maps = hash:/etc/postfix/virtual_alias
virtual_minimum_uid = 100
virtual_uid_maps = static:5000
virtual_gid_maps = static:5000
virtual_transport = virtual
virtual_mailbox_limit = 104857600
mailbox_size_limit = 0
message_size_limit = 52428800
dovecot_destination_recipient_limit = 1

smtpd_sasl_auth_enable = yes
smtpd_sasl_type = dovecot
smtpd_sasl_path = private/auth
smtpd_sasl_security_options = noanonymous
smtpd_sasl_local_domain = $1
broken_sasl_auth_clients = yes

smtpd_use_tls = yes
smtpd_tls_security_level = may
smtpd_tls_auth_only = no
smtpd_tls_cert_file = /var/lib/caddy/.local/share/caddy/certificates/acme-v02.api.letsencrypt.org-directory/wildcard_.$1/wildcard_.$1.crt
smtpd_tls_key_file = /var/lib/caddy/.local/share/caddy/certificates/acme-v02.api.letsencrypt.org-directory/wildcard_.$1/wildcard_.$1.key
smtpd_tls_session_cache_database = btree:${data_directory}/smtpd_scache
smtpd_tls_received_header = yes
smtpd_tls_security_level = may
smtp_tls_security_level = may
tls_random_source = dev:/dev/urandom

smtpd_recipient_restrictions = permit_mynetworks,
  permit_sasl_authenticated,
  reject_invalid_hostname,
  reject_non_fqdn_hostname,
  reject_non_fqdn_sender,
  reject_non_fqdn_recipient,
  reject_unauth_destination,
  reject_unauth_pipelining

smtpd_recipient_limit = 250
broken_sasl_auth_clients = yes
EOF

cat <<EOF >> /etc/postfix/master.cf
smtp       inet  n       -       -       -       -       smtpd
smtps      inet  n       -       -       -       -       smtpd
submission inet  n       -       n       -       -       smtpd
pickup     fifo  n       -       -       60      1       pickup
cleanup    unix  n       -       -       -       0       cleanup
qmgr       fifo  n       -       n       300     1       qmgr
tlsmgr     unix  -       -       -       1000?   1       tlsmgr
rewrite    unix  -       -       -       -       -       trivial-rewrite
bounce     unix  -       -       -       -       0       bounce
defer      unix  -       -       -       -       0       bounce
trace      unix  -       -       -       -       0       bounce
verify     unix  -       -       -       -       1       verify
flush      unix  n       -       -       1000?   0       flush
proxymap   unix  -       -       n       -       -       proxymap
proxywrite unix  -       -       n       -       1       proxymap
smtp       unix  -       -       -       -       -       smtp
relay      unix  -       -       -       -       -       smtp
showq      unix  n       -       -       -       -       showq
error      unix  -       -       -       -       -       error
retry      unix  -       -       -       -       -       error
discard    unix  -       -       -       -       -       discard
local      unix  -       n       n       -       -       local
virtual    unix  -       n       n       -       -       virtual
lmtp       unix  -       -       -       -       -       lmtp
anvil      unix  -       -       -       -       1       anvil
scache     unix  -       -       -       -       1       scache
uucp       unix  -       n       n       -       -       pipe
  flags=Fqhu user=uucp argv=uux -r -n -z -a\$sender - \$nexthop!rmail (\$recipient)
ifmail     unix  -       n       n       -       -       pipe
  flags=F user=ftn argv=/usr/lib/ifmail/ifmail -r \$nexthop (\$recipient)
bsmtp      unix  -       n       n       -       -       pipe
  flags=Fq. user=bsmtp argv=/usr/lib/bsmtp/bsmtp -t\$nexthop -f\$sender \$recipient
scalemail-backend unix	-	n	n	-	2	pipe
  flags=R user=scalemail argv=/usr/lib/scalemail/bin/scalemail-store \${nexthop} \${user} \${extension}
mailman    unix  -       n       n       -       -       pipe
  flags=FR user=list argv=/usr/lib/mailman/bin/postfix-to-mailman.py
  \${nexthop} \${user}
dovecot    unix  -       n       n       -       -       pipe
  flags=DRhu user=vmail:vmail argv=/usr/lib/dovecot/deliver -f \${sender} -d \${recipient}
EOF

systemctl restart postfix
systemctl restart dovecot

USERNAME=$3
PASSWD=$2
DOMAIN=$1
ADDRESS=$3@$1

BASEDIR="$(postconf | grep ^virtual_mailbox_base | cut -f3 -d' ')";

if [ -f /etc/postfix/vmailbox ]
then
	echo $ADDRESS $DOMAIN/$USERNAME/ >> /etc/postfix/vmailbox
	postmap /etc/postfix/vmailbox

	if [ $? -eq 0 ]
	then
		echo $ADDRESS::5000:5000::$BASEDIR/$DOMAIN/$ADDRESS>> $BASEDIR/$DOMAIN/passwd
		echo $ADDRESS":"$(doveadm pw -p $PASSWD) >> $BASEDIR/$DOMAIN/shadow
		chown vmail:vmail $BASEDIR/$DOMAIN/passwd && chmod 775 $BASEDIR/$DOMAIN/passwd
		chown vmail:vmail $BASEDIR/$DOMAIN/shadow && chmod 775 $BASEDIR/$DOMAIN/shadow
		/etc/init.d/postfix reload
	fi
fi

setfacl -R -m user:caddy:rwx  /etc/postfix

# cleanup
rm roundcubemail-1.6.7.tar.gz
