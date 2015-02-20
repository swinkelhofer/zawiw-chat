#!bin/sh

#kills server.php and restarts for persistent database connection
pkill php
php /var/www/web10/files/websocket/server.php >> /dev/null
