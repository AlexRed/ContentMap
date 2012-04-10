imageObjs = new Array();

function init_/*owner*/_/*id*/()
{
	if (!data_/*owner*/_/*id*/.places.length)
	{
		// There is no places viewable in this module
		document.getElementById('contentmap_/*owner*/_/*id*/').innerHTML += data_/*owner*/_/*id*/.nodata_msg;
		return;
	}

	// Center point is currently unknown
	//var center = new google.maps.LatLng(0.0, 0.0);
	var center = new google.maps.LatLng(data_/*owner*/_/*id*/.places[0].latitude, data_/*owner*/_/*id*/.places[0].longitude);

	// Map creation
	var map = new google.maps.Map(document.getElementById('contentmap_/*owner*/_/*id*/'),
	{
		//zoom: 1, //zoom factor is currently unknown too
		zoom: data_/*owner*/_/*id*/.zoom,
		center: center,
		mapTypeId: google.maps.MapTypeId./*map_type*/
	});

	// Used only by the module which contains more than one marker
	if (data_/*owner*/_/*id*/.places.length > 1)
	{
		// Automatic scale and center the map based on the marker points
		var bounds = new google.maps.LatLngBounds();
		var pmin = new google.maps.LatLng(data_/*owner*/_/*id*/.minlatitude, data_/*owner*/_/*id*/.minlongitude);
		var pmax = new google.maps.LatLng(data_/*owner*/_/*id*/.maxlatitude, data_/*owner*/_/*id*/.maxlongitude);
		bounds.extend(pmin);
		bounds.extend(pmax);
		map.fitBounds(bounds);
	}

	// InfoWindow creation
	var infowindow = new google.maps.InfoWindow({maxWidth: /*infowindow_width*/});

	// Markers creation
	var markers = [];
	for (var i = 0; i < data_/*owner*/_/*id*/.places.length; ++i)
	{
		// Set marker position
		var pos = new google.maps.LatLng(data_/*owner*/_/*id*/.places[i].latitude, data_/*owner*/_/*id*/.places[i].longitude);

		// Marker creation
		var marker = new google.maps.Marker(
		{
			position: pos,
			title: data_/*owner*/_/*id*/.places[i].title,
			zIndex: i
		});

		// Custom marker icon if present
		if ("icon" in data_/*owner*/_/*id*/)
		marker.setIcon(data_/*owner*/_/*id*/.icon);

		if (data_/*owner*/_/*id*/.markers_action == 'infowindow')
		{
			// InfoWindow handling event
			google.maps.event.addListener(marker, '/*infowindow_event*/', function() {
				infowindow.setContent(data_/*owner*/_/*id*/.places[this.getZIndex()].html);
				infowindow.open(map, this);
			});
		}
		else
		{
			// Redirect handling event
			google.maps.event.addListener(marker, '/*infowindow_event*/', function() {
				location.href = data_/*owner*/_/*id*/.places[this.getZIndex()].article_url;
			});
		}

		markers.push(marker);
	}

	// Marker Cluster creation
	var markerCluster = new MarkerClusterer(map, markers);

}


// Preload article images shown inside the infowindows
function preload_/*owner*/_/*id*/()
{
	for (var i = 0; i < data_/*owner*/_/*id*/.places.length; ++i)
	{
		if (data_/*owner*/_/*id*/.places[i].image)
		{
			imageObj = new Image();
			imageObj.src = data_/*owner*/_/*id*/.baseurl + data_/*owner*/_/*id*/.places[i].image;
			imageObjs.push(imageObj);
		}
	}

}

google.maps.event.addDomListener(window, 'load', init_/*owner*/_/*id*/);
google.maps.event.addDomListener(window, 'load', preload_/*owner*/_/*id*/);
//window.onload = preload_/*owner*/_/*id*/;
//google.maps.event.addDomListener(document.getElementById("contentmap_/*owner*/_/*id*/"), 'mouseover', preload_/*owner*/_/*id*/);
