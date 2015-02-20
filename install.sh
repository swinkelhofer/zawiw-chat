#!bin/sh

#edit this to your destination and eventually set to mv
cp -r websocket ../../../../files

echo "Enter username for database: "
read username
echo "\nEnter password for database: "
read password
echo "\nEnter IP for Domain: "
read domain
echo "\nEnter port for websocket: "
read port

#edit destination if you've changed above
sed -i -e "s:web10:$username:g" -e "s:FD26Ur2k:$password:g" -e "s:9999:$port:g" ../../../../files/websocket/server.php
sed -i -e "s:mirror.forschendes-lernen.de:$domain:g" -e "s:9999:$port:g" websocket.js
sed -i -e "s:web10:$username:g" ../../../../files/websocket/reconnect.sh

echo "Websocket is set up. Do not forget to configure the cronjob for the reconnect.sh to provide persistent database connection!"
