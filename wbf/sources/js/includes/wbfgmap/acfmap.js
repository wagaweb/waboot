var wbf_rendered_gmaps = [];

(function ($) {
    "use strict";

    /**
     * Render a Google Map onto the selected jQuery element
     * @param $el (jQuery element)
     * @returns {google.maps.Map}
     */
    function render_map($el) {

        var $markers = $el.find('.marker'),
            datas = {
                mapType: (function(){
                    var mapType = $el.data("maptype"),
                        returnObj;
                    if(typeof mapType === "undefined"){
                        returnObj = google.maps.MapTypeId.ROADMAP;
                    }else{
                        switch(mapType){
                            case "roadmap":
                                returnObj = google.maps.MapTypeId.ROADMAP;
                                break;
                            case "satellite":
                                returnObj = google.maps.MapTypeId.SATELLITE;
                                break;
                            case "hybrid":
                                returnObj = google.maps.MapTypeId.HYBRID;
                                break;
                            case "terrain":
                                returnObj = google.maps.MapTypeId.TERRAIN;
                                break;
                        }
                    }
                    return returnObj;
                })(),
                streetView: {
                    active: typeof $el.attr("data-streetview") !== "undefined",
                    position: {
                        lat: $el.attr("data-stlat") || false,
                        lng: $el.attr("data-stlng") || false
                    },
                    pov: {
                        heading:  $el.data("heading") || 34,
                        pitch: $el.data("pitch") || 10
                    }
                }
            },
            args = {
                zoom: $el.attr("data-zoom") || 16,
                draggable: $el.data("dragable") || true,
                scrollwheel: $el.data("scrollwheel") || false,
                center: new google.maps.LatLng(0, 0),
                mapTypeId: datas.mapType
            };

        // create map
        var map = new google.maps.Map($el[0], args);

        // add a markers reference
        map.markers = [];
        map.$markers = [];

        // add a infowindows reference
        map.infowindows = [];
        map.$infowindows = [];

        // add markers
        $markers.each(function () {
            add_marker($(this), map);
        });

        // Add cluster?
        if($el.data("cluster")){
            var markerCluster = new MarkerClusterer(map, map.markers);
        }

        // center map
        center_map(map);

        // Streetview?
        if(datas.streetView.active){
            var panorama = map.getStreetView();
            panorama.setPosition(new google.maps.LatLng(datas.streetView.position.lat, datas.streetView.position.lng));
            panorama.setPov({
                heading: datas.streetView.pov.heading,
                pitch: datas.streetView.pov.pitch
            });
            panorama.setVisible(true);
        }

        return map;
    }

    /**
     * Adds a marker to the selected Google Map
     * @param $marker (jQuery element)
     * @param map (Google Map object)
     * @returns {google.maps.Marker}
     */
    function add_marker($marker, map) {

        // var
        var latlng = new google.maps.LatLng($marker.attr('data-lat'), $marker.attr('data-lng')),
            icon   = $marker.attr("data-icon");

        // create marker
        var marker = new google.maps.Marker({
            position: latlng,
            map: map,
            icon: icon || undefined,
            custom_openInfoWindow: $marker.data("openinfowindow") || false //WAGA MOD: our custom parameter
        });

        // if marker contains HTML, add it to an infoWindow
        if ($marker.html()) {
            // create info window
            var infowindow = new google.maps.InfoWindow({
                content: $marker.html()
            });
            map.infowindows.push(infowindow);
            var $infowindow = jQuery("<div>"+infowindow.getContent()+"</div>");
            map.$infowindows.push($infowindow);
        }

        // Bind marker click action
        google.maps.event.addListener(marker, 'click', function (e,a,b) {
            if(typeof(infowindow) !== "undefined"){
                if(this.custom_openInfoWindow){
                    // show info window when marker is clicked
                    infowindow.open(map, marker);
                }
                $marker.trigger("infowindow:open",[infowindow]);
                $infowindow.trigger("open",[infowindow]);
            }
            $marker.trigger("click",[$marker]);
        });

        // add to array
        map.markers.push(marker);
        map.$markers.push($marker);

        return marker;
    }

    /**
     * Center the specified map, showing all markers attached to this map
     * @param map
     * @returns {google.maps.LatLng|*}
     */
    function center_map(map) {
        // vars
        var bounds = new google.maps.LatLngBounds();

        // loop through all markers and create bounds
        $.each(map.markers, function (i, marker) {
            var latlng = new google.maps.LatLng(marker.position.lat(), marker.position.lng());
            bounds.extend(latlng);
        });

        // only 1 marker?
        if (map.markers.length == 1) {
            // set center of map
            map.setCenter(bounds.getCenter());
            map.setZoom(16);
        }
        else {
            // fit to bounds
            map.fitBounds(bounds);
        }

        return bounds.getCenter();
    }

    /*
     *  INITIALIZING
     */

    jQuery(document).ready(function ($) {
        $('.acf-map').each(function () {
            $(this).find(".marker").hide();
            $(this).addClass("loading");
        });
    });

    jQuery(window).bind("load", function() {
        /*
         * Rendering
         */
        jQuery('.acf-map').each(function () {
            jQuery(this).removeClass("loading");
            wbf_rendered_gmaps.push(render_map(jQuery(this)));
        });
        jQuery(document).trigger("wbf_gmaps:rendered");

        /*
         * Enabling search
         */
        $('[data-wb-map-search-field]').keypress(function(e){
            if (e.which === 13){
                $('[data-acf-map-search-button]').trigger("click");
            }
        });
        $('[data-wb-map-search-button]').on("click",function(){
            var $searchField = $("[data-wb-map-search-field]");
            var searchAddress = $searchField.val();
            if(typeof(searchAddress) !== "undefined" && searchAddress !== ""){
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({'address': searchAddress}, function(result,status){
                    if(status === google.maps.GeocoderStatus.OK && typeof(map) !== "undefined"){
                        map.setCenter(result[0].geometry.location);
                        map.setZoom(14);
                    }else{
                        console.log("Geocode was not successful for the following reason: "+status);
                    }
                });
            }
        });
    });

})(jQuery);