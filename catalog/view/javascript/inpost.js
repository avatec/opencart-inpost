function btnSelectPoint( id, address ) {
    $("#inpostModalMap").modal('hide');
    setTimeout( function() {
        $("#inpostModalMap").after('<div class="radio"><label>' +
        '<input type="radio" name="shipping_method" value="inpost.inpost_' + id + '" checked="checked">' +
        id + ' - ' + address + '</label></div>');

        $.ajax({
            url: '/index.php?route=extension/shipping/inpost/inpost/setData',
            type: 'POST',
            data: { id: id },
            error: function( err ) {
                if( err.responseText ) {
                    console.error( err.responseText );
                }
            },
            success: function( r ) {
                console.log( r );
            }
        });
    },1000);
}

$( document ).ajaxComplete(function() {
    $("#inpostModalMap").on('shown.bs.modal' , function() {
        console.log('Otwarto okno, ładowanie mapy google');

        getLocation();
    });

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function( position ) {
                initMap(position.coords.latitude,position.coords.longitude,14);
            }, function (error ) {
                if (error.code == error.PERMISSION_DENIED) {
                    console.log('nie ma');

                    var div_contact = document.getElementById('inpost-google-map');
                    var lat = div_contact.getAttribute('data-lat');
                    var lng = div_contact.getAttribute('data-lng');
                    var zoom = div_contact.getAttribute('data-zoom');
                    initMap(lat,lng,zoom);
                    console.error("Geolocation is not supported by this browser.");
                }
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

    function getData( postcode, id )
	{
		var def = $.Deferred();

        console.log( postcode, id );

		$.getJSON( '/index.php?route=extension/shipping/inpost/inpost/index', { postcode: postcode, id: id } ).done( function( data ) {
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

	    getData(null,null,null).done( function( data ) {
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
                        '<button type="button" onclick="btnSelectPoint(\'' + v.id + '\', \'' + v.street + ', ' + v.city + '\');" class="btn btn-default btn-sm"><i class="fa fa-check"></i> wybierz ten paczkomat</button>';

						infowindow.setContent( content );
						infowindow.open(map, marker);
			        };
			    })(marker,v,infowindow));
		    });
	    });

        $("#btnInpostFindByForm").on('click' , function() {
            var postcode = $("#inpost-postcode").val();
            var id = $("#inpost-id").val();

            getData( postcode, id ).done( function( data ) {
    		    console.log( data.data[0].lat );

                var myOptions = {
        	        scrollwheel: false,
        	        navigationControl: true,
        	        mapTypeControl: false,
        	        scaleControl: true,
        	        draggable: true,
        	        zoomControl: true,
        	        zoom: parseInt(14),
        	        center: new google.maps.LatLng(data.data[0].lat, data.data[0].lng),
        	        mapTypeId: google.maps.MapTypeId.ROADMAP
        	    };
        	    map = new google.maps.Map(
        	    	document.getElementById("inpost-google-map"), myOptions
        	    );

                var marker;
                var infowindow = new google.maps.InfoWindow();

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
                            '<button type="button" onclick="btnSelectPoint(\'' + v.id + '\', \'' + v.street + ', ' + v.city + '\');" class="btn btn-default btn-sm"><i class="fa fa-check"></i> wybierz ten paczkomat</button>';

    						infowindow.setContent( content );
    						infowindow.open(map, marker);
    			        };
    			    })(marker,v,infowindow));
    		    });
    	    });
        });
	}
});
