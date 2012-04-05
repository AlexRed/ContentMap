function lookupGeoData()
{
	myGeoPositionGeoPicker(
	{
		startAddress     : 'White House, Washington',
		returnFieldMap   :
		{
			'jform_metadata_xreference' : '<LAT>, <LNG>'
		}
	}
	);
}

window.addEvent('domready',function()
{
	var xref_field = document.id('jform_metadata_xreference');
	// If we are on frontend editing, xreference field doesn't exist. Avoid javascript error which block execution of other js
	if (!xref_field) return;

	var picker = new Element('button',{'id':'contentmap_picker','class':'contentmap_picker','type':'button','onclick':'lookupGeoData();','html':'<img src="../media/contentmap/images/map-16.png" />'});
	picker.inject(xref_field,'after');

	document.getElementById('jform_metadata_xreference-lbl').innerHTML = 'ContentMap';
	document.getElementById('jform_metadata_xreference-lbl').title = 'ContentMap';


<?php
$articleid = JRequest::getVar("id", "1", "GET");
$position = $this->Params->get("contentmap_position", $this->Params->get("xreference", ""));

require_once(JPATH_ROOT . DS . "libraries" . DS . "contentmap" . DS . "language" . DS . "contentmap.inc");
$language = JFactory::getLanguage();
$language->load("com_contentmap.sys", JPATH_ROOT . "/administrator/components/com_contentmap");
$langcode = preg_replace("/-.*/", "", $language->get("tag"));
?>

<?php if ($GLOBALS["contentmap"]["version"][strlen($GLOBALS["contentmap"]["version"]) - 1] == " ") { ?>
	var container = document.getElementById('content-sliders-<?php echo $articleid; ?>');
	var new_element = document.createElement('div');
	new_element.className = 'contentmap_message contentmap_red';
/*
	new_element.innerHTML = "<h3>ContentMap</h3>" +
	'<input type="text" size="20" class="inputbox" value="<?php echo $position; ?>" id="jform_metadata_xreference_position" name="jform[metadata][xreference][position]">';
*/
	new_element.innerHTML =
	'<img style="margin:0; float:left' + ';" src="<?php echo JURI::base(true); ?>/../media/contentmap/images/cross-circle-frame.png">' +
	'<span style="padding-left' + ':5px; line-height:16px;">' +
	'<?php echo($language->_("COM_CONTENTMAP_PURCHASE")); ?> <a href="http://www.opensourcesolutions.es/index.php?option=com_content&view=article&id=9&Itemid=8&lang=<?php echo($langcode); ?>" target="_blank"><?php echo($language->_("COM_CONTENTMAP_BUYNOW")); ?></a>' +
	'</span>';

	container.appendChild(new_element);
<?php } ?>

}
);
