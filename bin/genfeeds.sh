#!/bin/bash

BASEDIR=/srv/www/waboot

if [ "$BASEDIR" = "/srv/www/waboot" ]; then
    echo "Base dir is still /srv/www/waboot!"
    exit 1
fi

echo "GShopping (it)..."

(cd $BASEDIR && /usr/local/bin/wp wawoo:feeds:generate-gshopping --lang=it --default-google-product-category="Apparel & Accessories" )

echo "Facebook (it)..."

( rm $BASEDIR/wp-content/wb-feeds/facebook-products-feed-it.xml )

( cp $BASEDIR/wp-content/wb-feeds/google-products-feed-it.xml $BASEDIR/wp-content/wb-feeds/facebook-products-feed-it.xml )

echo "Done!"