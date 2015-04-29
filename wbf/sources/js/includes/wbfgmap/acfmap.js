(function ($) {

    /*
     *  render_map
     *
     *  This function will render a Google Map onto the selected jQuery element
     *
     *  @type	function
     *  @date	8/11/2013
     *  @since	4.3.0
     *
     *  @param	$el (jQuery element)
     *  @return	n/a
     */
    function render_map($el) {

        var $markers = $el.find('.marker'),
            datas = {
                mapType: (function(){
                    var mapType = $el.data("maptype");
                    if(typeof mapType == "undefined"){
                        return google.maps.MapTypeId.ROADMAP
                    }else{
                        switch(mapType){
                            case "roadmap":
                                return google.maps.MapTypeId.ROADMAP;
                                break;
                            case "satellite":
                                return google.maps.MapTypeId.SATELLITE;
                                break;
                            case "hybrid":
                                return google.maps.MapTypeId.HYBRID;
                                break;
                            case "terrain":
                                return google.maps.MapTypeId.TERRAIN;
                                break;
                        }
                    }
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
                zoom: 16,
                draggable: true,
                scrollwheel: false,
                center: new google.maps.LatLng(0, 0),
                mapTypeId: datas.mapType
            };

        // create map
        var map = new google.maps.Map($el[0], args);

        // add a markers reference
        map.markers = [];

        // add markers
        $markers.each(function () {
            add_marker($(this), map);
        });

        var markerCluster = new MarkerClusterer(map, map.markers); // ADD CLUSTERER

        // center map
        center_map(map);

        //Streetview?
        if(datas.streetView.active){
            var panorama = map.getStreetView();
            panorama.setPosition(new google.maps.LatLng(datas.streetView.position.lat, datas.streetView.position.lng));
            panorama.setPov({
                heading: datas.streetView.pov.heading,
                pitch: datas.streetView.pov.pitch
            });
            panorama.setVisible(true);
        }
    }

    /*
     *  add_marker
     *
     *  This function will add a marker to the selected Google Map
     *
     *  @type	function
     *  @date	8/11/2013
     *  @since	4.3.0
     *
     *  @param	$marker (jQuery element)
     *  @param	map (Google Map object)
     *  @return	n/a
     */
    function add_marker($marker, map) {

        // var
        var latlng = new google.maps.LatLng($marker.attr('data-lat'), $marker.attr('data-lng')),
            icon   = $marker.attr("data-icon");

        // create marker
        var marker = new google.maps.Marker({
            position: latlng,
            map: map,
            icon: icon || undefined
        });

        // add to array
        map.markers.push(marker);

        // if marker contains HTML, add it to an infoWindow
        if ($marker.html()) {
            // create info window
            var infowindow = new google.maps.InfoWindow({
                content: $marker.html()
            });
            // show info window when marker is clicked
            google.maps.event.addListener(marker, 'click', function () {
                infowindow.open(map, marker);
            });
        }
    }

    /*
     *  center_map
     *
     *  This function will center the map, showing all markers attached to this map
     *
     *  @type	function
     *  @date	8/11/2013
     *  @since	4.3.0
     *
     *  @param	map (Google Map object)
     *  @return	n/a
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
    }

    /*
     *  document ready
     *
     *  This function will render each map when the document is ready (page has loaded)
     *
     *  @type	function
     *  @date	8/11/2013
     *  @since	5.0.0
     *
     *  @param	n/a
     *  @return	n/a
     */

    jQuery(document).ready(function ($) {
        $('.acf-map').each(function () {
            render_map($(this));
        });
    });
})(jQuery);