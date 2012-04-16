<?php
$owner = JRequest::getVar("owner", "", "GET");
$id = JRequest::getVar("id", "", "GET");
?>
imageObjs = new Array();

function init_<?php echo $owner; ?>_<?php echo $id; ?>()
{
	if (!data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length)
	{
		// There is no places viewable in this module
		document.getElementById('contentmap_<?php echo $owner; ?>_<?php echo $id; ?>').innerHTML += data_<?php echo $owner; ?>_<?php echo $id; ?>.nodata_msg;
		return;
	}

	if ("center" in data_<?php echo $owner; ?>_<?php echo $id; ?>)
	{
		var center = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.center.latitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.center.longitude);
	}
	else
	{
		//var center = new google.maps.LatLng(0.0, 0.0);
		var center = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[0].latitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.places[0].longitude);
	}

	// Map creation
	var map = new google.maps.Map(document.getElementById('contentmap_<?php echo $owner; ?>_<?php echo $id; ?>'),
	{
		//zoom: 1, //zoom factor is currently unknown too
		zoom: data_<?php echo $owner; ?>_<?php echo $id; ?>.zoom,
		center: center,
		mapTypeId: google.maps.MapTypeId.<?php echo $this->Params->get("map_type", "200"); ?>
	});

	// Used only by the module which contains more than one marker but only when a center is not defined
	if (!("center" in data_<?php echo $owner; ?>_<?php echo $id; ?>) && (data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length > 1))
	{
		// Automatic scale and center the map based on the marker points
		var bounds = new google.maps.LatLngBounds();
		var pmin = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.minlatitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.minlongitude);
		var pmax = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.maxlatitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.maxlongitude);
		bounds.extend(pmin);
		bounds.extend(pmax);
		map.fitBounds(bounds);
	}

	// InfoWindow creation
	var infowindow = new google.maps.InfoWindow({maxWidth: <?php echo $this->Params->get("infowindow_width", "200"); ?>});

	// Markers creation
	var markers = [];
	for (var i = 0; i < data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length; ++i)
	{
		// Set marker position
		var pos = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].latitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].longitude);

		// Marker creation
		var marker = new google.maps.Marker(
		{
			map: map,
			position: pos,
			title: data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].title,
			zIndex: i
		});

		// Custom marker icon if present
		if ("icon" in data_<?php echo $owner; ?>_<?php echo $id; ?>)
		marker.setIcon(data_<?php echo $owner; ?>_<?php echo $id; ?>.icon);

		if (data_<?php echo $owner; ?>_<?php echo $id; ?>.markers_action == 'infowindow')
		{
			// InfoWindow handling event
			google.maps.event.addListener(marker, '<?php echo $this->Params->get("infowindow_event", "200"); ?>', function() {
				infowindow.setContent(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[this.getZIndex()].html);
				infowindow.open(map, this);
			});
		}
		else
		{
			// Redirect handling event
			google.maps.event.addListener(marker, '<?php echo $this->Params->get("infowindow_event", "200"); ?>', function() {
				location.href = data_<?php echo $owner; ?>_<?php echo $id; ?>.places[this.getZIndex()].article_url;
			});
		}

		markers.push(marker);
	}
<?php if ($this->Params->get("cluster", "0")) { ?>
	// Marker Cluster creation
	var markerCluster = new MarkerClusterer(map, markers);
<?php } ?>
}


// Preload article images shown inside the infowindows
function preload_<?php echo $owner; ?>_<?php echo $id; ?>()
{
	for (var i = 0; i < data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length; ++i)
	{
		if (data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].image)
		{
			imageObj = new Image();
			imageObj.src = data_<?php echo $owner; ?>_<?php echo $id; ?>.baseurl + data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].image;
			imageObjs.push(imageObj);
		}
	}

}

google.maps.event.addDomListener(window, 'load', init_<?php echo $owner; ?>_<?php echo $id; ?>);
google.maps.event.addDomListener(window, 'load', preload_<?php echo $owner; ?>_<?php echo $id; ?>);
//window.onload = preload_<?php echo $owner; ?>_<?php echo $id; ?>;
//google.maps.event.addDomListener(document.getElementById("contentmap_<?php echo $owner; ?>_<?php echo $id; ?>"), 'mouseover', preload_<?php echo $owner; ?>_<?php echo $id; ?>);
