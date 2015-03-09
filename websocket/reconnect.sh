#!bin/sh

#kills server.php and restarts for persistent database connection
#pkill php
id web10 | sed -r "s:.*uid=([0-9]{4}).*:\1:" | xargs -i pkill -9 -U {} php
php /var/www/web10/files/websocket/server.php
