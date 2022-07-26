#!/bin/bash
BASEDIR=$(dirname "$0")

echo "service cron start"
service cron start
sh $BASEDIR/../bin/geoipupdate.sh
$BASEDIR/../bin/serve &
$BASEDIR/../bin/serve-http &
while true
do
sleep 100
done