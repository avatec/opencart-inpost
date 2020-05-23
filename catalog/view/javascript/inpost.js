function btnSelectPoint( id ) {
    console.log( id );
    alert( id );
    $("#inpostModalMap").modal('hide');
}

$( document ).ajaxComplete(function() {
    $("#inpostModalMap").off().on('shown.bs.modal' , function() {
        console.log('Otwarto okno, ładowanie mapy google');

        getLocation();
    });

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function( position ) {
                initMap(position.coords.latitude,position.coords.longitude,14);
            });
        } else {
            var div_contact = document.getElementById('inpost-google-map');
            var lat = div_contact.getAttribute('data-lat');
            var lng = div_contact.getAttribute('data-lng');
            var zoom = div_contact.getAttribute('data-zoom');
            initMap(lat,lng,zoom);
            console.error("Geolocation is not supported by this browser.");
        }
    }

    function showPosition(position) {

    }

    function getData()
	{
		var def = $.Deferred();
		var postcode = '';
		var city = '';

		$.getJSON( '/index.php?route=api/inpost/index', { postcode: postcode, city: city } ).done( function( data ) {
			def.resolve({

				data: data.data
			});

	    });
	    return def;
	}

	function initMap(lat,lng,zoom) {
        var div_contact = document.getElementById('inpost-google-map');
		var mapcontent = div_contact.getAttribute('data-content');

	    var myOptions = {
	        scrollwheel: false,
	        navigationControl: true,
	        mapTypeControl: false,
	        scaleControl: true,
	        draggable: true,
	        zoomControl: true,
	        zoom: parseInt(zoom),
	        center: new google.maps.LatLng(lat, lng),
	        mapTypeId: google.maps.MapTypeId.ROADMAP
	    };
	    map = new google.maps.Map(
	    	document.getElementById("inpost-google-map"), myOptions
	    );

	    var marker;
	    var infowindow = new google.maps.InfoWindow();

	    getData().done( function( data ) {
		    console.log( data );
		    $.each( data.data, function(k,v) {
			   	marker = new google.maps.Marker({
			        map: map,
			        position: new google.maps.LatLng(v.lat, v.lng)
			    });

			    google.maps.event.clearListeners(marker, 'idle');
			    google.maps.event.addListener(marker, "click", (function(marker,v,infowindow) {
			        return function() {
				        var lang = $("#inpost-google-map");

				        // console.log( v );
				        var content = '<div class="iw-map-container text-left">' +
				        '<p class="text-left"><b>' + v.description + '</b><br/>' + v.street + '<br/>' + v.postcode + ' ' + v.city + '</p>' +
				        '</div>' +
                        '<button type="button" onclick="btnSelectPoint(\'' + v.id + '\');" class="btn btn-default btn-sm"><i class="fa fa-check"></i> wybierz ten paczkomat</button>';

						infowindow.setContent( content );
						infowindow.open(map, marker);
			        };
			    })(marker,v,infowindow));
		    });

	    });
	}
});
