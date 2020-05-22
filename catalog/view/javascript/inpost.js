$( document ).ajaxComplete(function() {
    $("#inpostModalMap").off().on('shown.bs.modal' , function() {
        console.log('Otwarto okno, ładowanie mapy google');
        initMap();
    });

    function getData()
	{
		var def = $.Deferred();
		var postcode = '';
		var city = '';

		$.getJSON( '/index.php?route=api/inpost/index', { postcode: postcode, city: city } ).done( function( data ) {
			console.log( data );
			def.resolve({
				data: data
			});

	    });
	    return def;
	}

	function initMap() {

	    var div_contact = document.getElementById('inpost-google-map');
		var lat = div_contact.getAttribute('data-lat');
		var lng = div_contact.getAttribute('data-lng');
		var zoom = div_contact.getAttribute('data-zoom');
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
		    //console.log( data );
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
                        '<a target="_blank" href="http://maps.google.com/maps?daddr=' + v.lat + ',%20'+ v.lng + '&ll="class="btn btn-default btn-sm" type="button"><i class="fa fa-map-marker"></i> pokaż na mapie</a>';

						infowindow.setContent( content );
						infowindow.open(map, marker);
						//console.log( infowindow );
			        };
			    })(marker,v,infowindow));
		    });

	    });

	    //console.log( 'run initmap' );
	}
});
