#!/bin/bash
BASEDIR=/var/www
conf_file=/etc/GeoIP.conf

echo "service cron start"
service cron start
echo "Init geoipupdate"
if [ -z "$GEOIPUPDATE_ACCOUNT_ID" ] || [ -z  "$GEOIPUPDATE_LICENSE_KEY" ]; then
    echo "ERROR: You must set the environment variables GEOIPUPDATE_ACCOUNT_ID, GEOIPUPDATE_LICENSE_KEY!"
    exit 1
fi

# Create configuration file
echo "# STATE: Creating configuration file at $conf_file"
cat <<EOF > "$conf_file"
AccountID $GEOIPUPDATE_ACCOUNT_ID
LicenseKey $GEOIPUPDATE_LICENSE_KEY
EditionIDs GeoLite2-Country GeoLite2-City
DatabaseDirectory $BASEDIR/data
EOF

echo "0 13 * * 1 root test -x /usr/bin/geoipupdate && grep -q '^AccountID .*[^0]\+' /etc/GeoIP.conf && test ! -d /run/systemd/system && /var/www/bin/geoipupdate.sh" > /etc/cron.d/geoipupdate
echo "============/etc/cron.d/geoipupdate============"
cat /etc/cron.d/geoipupdate
echo "==============================================="
sh $BASEDIR/bin/geoipupdate.sh
$BASEDIR/bin/serve -d
$BASEDIR/bin/serve-http -d
touch $BASEDIR/data/logs/app.log
tail -f $BASEDIR/data/logs/app.log
