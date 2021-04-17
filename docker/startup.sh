#!/bin/sh

(crontab -l && echo "0 12 * * * /usr/bin/certbot renew --quiet") | crontab -

(crontab -l && echo "* * * * * php /app/artisan schedule:run >> /dev/null 2>&1") | crontab -

/usr/sbin/crond

sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

/usr/bin/supervisord -c /app/docker/supervisord.conf
