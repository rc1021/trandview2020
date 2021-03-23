#!/bin/sh

(crontab -l && echo "0 12 * * * /usr/bin/certbot renew --quiet") | crontab -

(crontab -l && echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1") | crontab -

sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

/usr/bin/supervisord -c /app/docker/supervisord.conf
