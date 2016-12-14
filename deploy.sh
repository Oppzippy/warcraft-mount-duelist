#!/bin/bash

SOURCE_PATH="www/warcraft-mount-duelist/"
DESTINATION_PATH="/var/www/warcraftmountduelist.com"

echo "Enter server address"
read server
echo "Enter user"
read user

rsync -rlzv --exclude-from "deploy-rsync-ignore" "$SOURCE_PATH" $user@$server:$DESTINATION_PATH