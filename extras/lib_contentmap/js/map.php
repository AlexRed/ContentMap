<?php defined('_JEXEC') or die('Restricted access');
$owner = JRequest::getVar("owner", "", "GET");
$id = JRequest::getVar("id", "", "GET");
JFactory::getLanguage()->load("contentmap", JPATH_LIBRARIES . "/contentmap");
?>
imageObjs = new Array();

function init_<?php echo $owner; ?>_<?php echo $id; ?>()
{
	var g_category=[];
	var g_next_category_id=1;

	if (!data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length)
	{
		// There is no places viewable in this module
		document.getElementById('contentmap_<?php echo $owner; ?>_<?php echo $id; ?>').innerHTML += '<?php echo str_replace("'", "\\'", JText::_("CONTENTMAP_NO_DATA")); ?>';
		return;
	}

<?php if ($center = $this->Params->get("center", NULL)) {
	$coordinates = explode(",", $center);
	// Google map js needs them as two separate values (See constructor: google.maps.LatLng(lat, lon))
	$center = new stdClass();
	$center->latitude = floatval($coordinates[0]);
	$center->longitude = floatval($coordinates[1]);
 ?>
 	var center = new google.maps.LatLng(<?php echo $center->latitude; ?>, <?php echo $center->longitude; ?>);
<?php } else { ?>
	var center = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[0].latitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.places[0].longitude);
<?php } ?>

	// Map creation
	var map = new google.maps.Map(document.getElementById('contentmap_<?php echo $owner; ?>_<?php echo $id; ?>'),
	{
		zoom: <?php echo $this->Params->get("zoom", 0); ?>,
		center: center,
		mapTypeId: google.maps.MapTypeId.<?php echo $this->Params->get("map_type", "ROADMAP"); ?>,
		scrollwheel: false
	});
<?php echo 'var kml_url='.json_encode(trim($this->Params->get("kml_url"))).';'; ?>
	if (kml_url!=''){
		var ctaLayer = new google.maps.KmlLayer({
	    	url: kml_url
	  	});
	  	ctaLayer.setMap(map);	
	}

<?php if (!$center) {
// Used only by the module which contains more than one marker but only when a center is not defined
?>
/*
	if (data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length > 1)
	{
		// Automatic scale and center the map based on the marker points
		var bounds = new google.maps.LatLngBounds();
		var pmin = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.minlatitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.minlongitude);
		var pmax = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.maxlatitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.maxlongitude);
		bounds.extend(pmin);
		bounds.extend(pmax);
		map.fitBounds(bounds);
	}
*/
<?php } ?>

	// InfoWindow creation
	var infowindow = new google.maps.InfoWindow({maxWidth: <?php echo $this->Params->get("infowindow_width", "200"); ?>});

	// Markers creation
	var markers = [];
	var minlatitude = 90.0;
	var maxlatitude = -90.0;
	var minlongitude = 180.0;
	var maxlongitude = -180.0;

	for (var i = 0; i < data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length; ++i)
	{
		// Compute bounds rectangle
		minlatitude = Math.min(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].latitude, minlatitude);
		maxlatitude = Math.max(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].latitude, maxlatitude);
		minlongitude = Math.min(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].longitude, minlongitude);
		maxlongitude = Math.max(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].longitude, maxlongitude);

		// Set marker position
		var pos = new google.maps.LatLng(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].latitude, data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].longitude);

		// Marker creation
		var marker = new google.maps.Marker(
		{
			map: map,
			position: pos,
			title: data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].title,
			zIndex: i,
			cmapdata: {category:data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].category}
		});

		// Custom marker icon if present
		if (data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].marker) {
		    marker.setIcon(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].marker);
		} else {
		if ("icon" in data_<?php echo $owner; ?>_<?php echo $id; ?>)
		marker.setIcon(data_<?php echo $owner; ?>_<?php echo $id; ?>.icon);
		}
		google.maps.event.addListener(marker, '<?php echo $this->Params->get("infowindow_event", "click"); ?>',
		function()
		{
<?php if ($this->Params->get("markers_action") == "infowindow") { ?>
			// InfoWindow handling event
			infowindow.setContent(data_<?php echo $owner; ?>_<?php echo $id; ?>.places[this.getZIndex()].html);
			infowindow.open(map, this);
<?php } else { ?>
			// Redirect handling event
			location.href = data_<?php echo $owner; ?>_<?php echo $id; ?>.places[this.getZIndex()].article_url;
<?php } ?>
		});
		if (marker.cmapdata.category){
			addCategoryMarker_<?php echo $owner; ?>_<?php echo $id; ?>(marker.cmapdata.category);
		}
		markers.push(marker);
	}

<?php if (!$center) {
// Set bounds rectangle
// Used only by the module which contains more than one marker but only when a center is not defined
?>
	if (data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length > 1)
	{
		// Automatic scale and center the map based on the marker points
		var bounds = new google.maps.LatLngBounds();
		var pmin = new google.maps.LatLng(minlatitude, minlongitude);
		var pmax = new google.maps.LatLng(maxlatitude, maxlongitude);
		bounds.extend(pmin);
		bounds.extend(pmax);
		map.fitBounds(bounds);
	}
<?php } ?>

<?php if ($this->Params->get("cluster", "1")) { ?>
	// Marker Cluster creation
	var markerCluster = new MarkerClusterer(map, markers);
<?php } ?>

<?php if($this->Params->get('streetView', 0)):?>
	var panoramaOptions = {
	position: center,
	scrollwheel: false,
	pov: {
		heading: 0,
		pitch: 0
		}
	};

	var panorama = new google.maps.StreetViewPanorama(document.getElementById("contentmap_plugin_streetview_<?php echo $id; ?>"), panoramaOptions);
	//map.setStreetView(panorama);
<?php endif; ?>

	function addCategoryMarker_<?php echo $owner; ?>_<?php echo $id; ?>(name){
		if (typeof g_category[name] === "undefined"){
			g_category[name]={};
			g_category[name].checked=true;
			g_category[name].num=0;
			g_category[name].id=g_next_category_id;
			g_next_category_id+=1;
<?php if ($this->Params->get("category_legend_filter", "0")) { ?>
			var checkbox = document.createElement('input');
			checkbox.type="checkbox";
			checkbox.checked="checked";
			checkbox.className ="checkbox";
			
			checkbox.onchange=function(){
				g_category[name].checked=checkbox.checked;
	        	for (var i=0;i<markers.length;i++){
	        		if (markers[i].cmapdata.category==name){
	        			var markervisible=g_category[name].checked;
	        			markers[i].setVisible(markervisible);
	<?php 				if ($this->Params->get("cluster", "1")) { ?>
							if (markervisible){
								markerCluster.addMarker(markers[i]);
							}else{
								markerCluster.removeMarker(markers[i]);
							}
	<?php 				} ?>
	        		}
	        	}
			}
			
			var div = document.createElement('span');
			div.className ="contentmap-checkcontainer";

			var categoryname=document.createElement('span');
			categoryname.appendChild(document.createTextNode(name));
			
			var categorynumphotos=document.createElement('span');
			categorynumphotos.appendChild(document.createTextNode(' (0)'));
			categorynumphotos.setAttribute("id",'cmap_<?php echo $owner; ?>_<?php echo $id; ?>_category_num_photos_'+g_category[name].id);
			
			div.appendChild(checkbox);
			div.appendChild(categoryname);
			div.appendChild(categorynumphotos);
			
			document.getElementById('contentmap_legend_<?php echo $owner; ?>_<?php echo $id; ?>').appendChild(div);
			document.getElementById('contentmap_legend_<?php echo $owner; ?>_<?php echo $id; ?>').appendChild(document.createTextNode(" "));
<?php } ?>
			
		}
		g_category[name].num+=1;
<?php if ($this->Params->get("category_legend_filter", "0")) { ?>
		var num_p=document.getElementById('cmap_<?php echo $owner; ?>_<?php echo $id; ?>_category_num_photos_'+g_category[name].id);
		num_p.innerHTML='';
		num_p.appendChild(document.createTextNode(' ('+g_category[name].num+')'));
<?php } ?>
	}


}


// Preload article images shown inside the infowindows
function preload_<?php echo $owner; ?>_<?php echo $id; ?>()
{
	for (var i = 0; i < data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length; ++i)
	{
		if (data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].image)
		{
			imageObj = new Image();
			//imageObj.src = data_<?php echo $owner; ?>_<?php echo $id; ?>.baseurl + data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].image;
			imageObj.src = data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].image;
			imageObjs.push(imageObj);
		}
	}

}

google.maps.event.addDomListener(window, 'load', init_<?php echo $owner; ?>_<?php echo $id; ?>);
google.maps.event.addDomListener(window, 'load', preload_<?php echo $owner; ?>_<?php echo $id; ?>);
//window.onload = preload_<?php echo $owner; ?>_<?php echo $id; ?>;
//google.maps.event.addDomListener(document.getElementById("contentmap_<?php echo $owner; ?>_<?php echo $id; ?>"), 'mouseover', preload_<?php echo $owner; ?>_<?php echo $id; ?>);
