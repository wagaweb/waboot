#!/bin/bash

echo "GShopping (it)..."

(cd /srv/www/waboot && /usr/local/bin/wp wawoo:feeds:generate-gshopping --lang=it --default-google-product-category="Apparel & Accessories" )

echo "Facebook (it)..."

( rm /srv/www/waboot/wp-content/waboot-feeds/facebook-products-feed-it.xml )

( cp /srv/www/waboot/wp-content/waboot-feeds/google-products-feed-it.xml /srv/www/waboot/wp-content/waboot-feeds/facebook-products-feed-it.xml )

echo "Done!"