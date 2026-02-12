#!/bin/bash

BASEDIR=/srv/www/waboot

if [ "$BASEDIR" = "/srv/www/waboot" ]; then
    echo "Base dir is still /srv/www/waboot!"
    exit 1
fi

# Ansible: Generate feeds
# 0 5 * * * /home/waboot/bin/genfeeds >> /home/waboot/logs/cli_genfeeds-$(date +\%Y-\%m-\%d).log 2>&1

echo "GShopping (it)..."

(cd $BASEDIR && /usr/local/bin/wp wawoo:feeds:generate-gshopping --lang=it --default-google-product-category="Apparel & Accessories" )

echo "Facebook (it)..."

(cd $BASEDIR && /usr/local/bin/wp wawoo:feeds:generate-facebook --lang=it --default-google-product-category="Apparel & Accessories" )

echo "Pinterest (it)..."

(cd $BASEDIR && /usr/local/bin/wp wawoo:feeds:generate-pinterest --lang=it --default-google-product-category="Apparel & Accessories" )

echo "TikTok (it)..."

(cd $BASEDIR && /usr/local/bin/wp wawoo:feeds:generate-tiktok --lang=it --default-google-product-category="Apparel & Accessories" )

echo "Done!"