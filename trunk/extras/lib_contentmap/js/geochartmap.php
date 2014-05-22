<?php defined('_JEXEC') or die('Restricted access');
$owner = JRequest::getVar("owner", "", "GET");
$id = JRequest::getVar("id", "", "GET");
JFactory::getLanguage()->load("contentmap", JPATH_LIBRARIES . "/contentmap");
?>

function init_<?php echo $owner; ?>_<?php echo $id; ?>()
{

	if (!data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length)
	{
		// There is no places viewable in this module
		document.getElementById('contentmap_<?php echo $owner; ?>_<?php echo $id; ?>').innerHTML += '<?php echo str_replace("'", "\\'", JText::_("CONTENTMAP_TAGS_NO_DATA")); ?>';
		return;
	}
	
	

	/*
	// Map creation
	var map = new google.maps.Map(document.getElementById('contentmap_<?php echo $owner; ?>_<?php echo $id; ?>'),
	{
		zoom: <?php echo $this->Params->get("zoom", 0); ?>,
	});
	
	
	*/
		
	google.load('visualization', '1', {'callback':drawVisualization_<?php echo $owner; ?>_<?php echo $id; ?>, packages: ['geochart']});
}

function drawVisualization_<?php echo $owner; ?>_<?php echo $id; ?>() {

	var lista_citta=[['City']];
	
	for (var i = 0; i < data_<?php echo $owner; ?>_<?php echo $id; ?>.places.length; ++i)
	{
		
		lista_citta.push([data_<?php echo $owner; ?>_<?php echo $id; ?>.places[i].title]);

	}
	

	var data = google.visualization.arrayToDataTable(lista_citta);


	var options = {
		region: 'IT',
		displayMode: 'markers',
		legend:'none',
		//width: 900,
		colorAxis: {colors: ['blue', 'blue']}
	  };

	var geochart = new google.visualization.GeoChart(document.getElementById('contentmap_<?php echo $owner; ?>_<?php echo $id; ?>'));
	google.visualization.events.addListener(geochart, 'select', function() {
		var selectionIdx = geochart.getSelection()[0].row;
		location.href=data_<?php echo $owner; ?>_<?php echo $id; ?>.places[selectionIdx].tag_link;
		
	});

	document.getElementById('contentmap_<?php echo $owner; ?>_<?php echo $id; ?>').className = "";
	geochart.draw(data, options);

}


function preload_<?php echo $owner; ?>_<?php echo $id; ?>()
{

}


