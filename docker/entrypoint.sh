#!/bin/bash
echo "service cron start"
service cron start

BASEDIR=$(dirname "$0")
$BASEDIR/../bin/serve &
$BASEDIR/../bin/serve-http &
while true
do
sleep 100
done