#!/usr/bin/env bash

BASEDIR=$(dirname $(dirname "$0"))
#echo "$BASEDIR"

geoipupdate

CITY_FILE=$BASEDIR"/data/GeoLite2-City.mmdb";
COUNTRY_FILE=$BASEDIR"/data/GeoLite2-Country.mmdb";
CITY_MD5=$CITY_FILE".md5";
COUNTRY_MD5=$COUNTRY_FILE".md5";

#echo $CITY_FILE
#echo $COUNTRY_FILE
#echo $CITY_MD5
#echo $COUNTRY_MD5

if [ ! -f "$CITY_MD5" ]; then md5sum "$CITY_FILE" > "$CITY_MD5" ; fi;
if [ ! -f "$COUNTRY_MD5" ]; then md5sum "$COUNTRY_FILE" > "$COUNTRY_MD5" ; fi;

if ! md5sum --status -c $CITY_MD5 || ! md5sum --status -c $COUNTRY_MD5 ; then
  # See https://wiki.swoole.com/#/server/methods?id=reload
  if [ -f "$BASEDIR/data/http.pid" ]; then echo 'Restart http server.' && kill -USR1 $(cat $BASEDIR/data/http.pid); fi;
  if [ -f "$BASEDIR/data/redis.pid" ]; then echo 'Restart redis server.' && kill -USR1 $(cat $BASEDIR/data/redis.pid); fi;
fi;

md5sum "$CITY_FILE" > "$CITY_MD5"
md5sum "$COUNTRY_FILE" > "$COUNTRY_MD5"
