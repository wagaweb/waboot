#!/bin/bash

BASEDIR=/srv/www/waboot

if [ "$BASEDIR" = "/srv/www/waboot" ]; then
    echo "Base dir is still /srv/www/waboot!"
    exit 1
fi

#
# NOT PAGINATED (default) version
#

(cd $BASEDIR && /usr/local/bin/wp wawoo:gen-order-stats-table --be-quiet)

#
# PAGINATED VERSION
#

#echo "Generating order stats table (paginated)"
#cd $BASEDIR || { echo "Failed to change directory"; exit 1; }
#COMMAND="/usr/local/bin/wp wawoo:gen-order-stats-table --pagination=1000 --be-quiet"
#while true; do
#    # Flag to track if termination condition is met
#    found_last_page=0
#
#    # Execute command and process output line-by-line
#    while IFS= read -r line; do
#        # Print output in real time
#        echo "$line"
#
#        # Check for termination string
#        if [[ "$line" == *"last page"* ]]; then
#            found_last_page=1
#        fi
#    done < <($COMMAND 2>&1)
#
#    # Exit loop if condition met
#    if [[ $found_last_page -eq 1 ]]; then
#        echo "Found 'last page' - stopping execution"
#        break
#    fi
#    sleep 1
#done