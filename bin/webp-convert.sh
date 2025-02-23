#!/bin/bash
shopt -s globstar

#docs:
#https://developers.google.com/speed/webp/docs/cwebp?hl=it

#SCARICARE DA:
#https://developers.google.com/speed/webp/docs/precompiled?hl=it
#Come root:
#cd /root/downloads
#wget https://storage.googleapis.com/downloads.webmproject.org/releases/webp/libwebp-1.5.0-linux-x86-64.tar.gz
#tar -xvzf libwebp-1.5.0-linux-x86-64.tar.gz
#cp /root/downloads/libwebp-1.5.0-linux-x86-64/bin/cwebp /usr/share/
#ln -s /usr/share/cwebp /usr/local/bin/cwebp

#SENTRY_INGEST="https://o4503929881165824.ingest.sentry.io"
#SENTRY_CRONS="${SENTRY_INGEST}/api/4506791264714752/cron/breil-generate-webp/7995f57f4a8f20f0aee40f918d6bd2a9/"

#Notify Sentry your job is running:
#curl "${SENTRY_CRONS}?status=in_progress"

#https://www.digitalocean.com/community/tutorials/how-to-create-and-serve-webp-images-to-speed-up-your-website
#cwebp /home/_ftp_users/assets@breil.com/public_ftp/files/images/TW1932.jpg -o /home/_ftp_users/assets@breil.com/public_ftp/files/images/TW1932.webp

BASE_DIR=/srv/www/bindagroup_assets/images
CONVERT_DIR=/srv/www/bindagroup_assets/images-webp/

convert_image () {
 basename="$(basename -- $1)"
 webpname=$(sed 's/\.[^.]*$/.webp/' <<< "$basename");
 webp_path=$CONVERT_DIR$webpname
 echo "Converting $1 into $webp_path";
 cwebp -quiet -q 90 "$1" -o "$webp_path"
 chown assets@bindagroup.com:assets@bindagroup.com $webp_path;
}

for i in $BASE_DIR/*.{jpg,jpeg,png}; do
    echo "Parsing $i"
    convert_image $i
done

#Notify Sentry your job has completed successfully:
#curl "${SENTRY_CRONS}?status=ok"