<?php defined('_JEXEC') or die('Restricted access');
/*
Do not edit this file or it will be overwritten at the first upgrade
Copy from this source using another file name and select your own new created css in the module or plugin options
*/
$owner = JRequest::getVar("owner", "", "GET");
$id = JRequest::getVar("id", "", "GET");
?>
#contentmap_wrapper_<?php echo $owner; ?>_<?php echo $id; ?>
{
	clear: both; /*Avoid overlapping with joomla article image, but it can create problems with some templates*/
}

#contentmap_container_<?php echo $owner; ?>_<?php echo $id; ?>
{
}

#contentmap_container_module_<?php echo $id; ?>
{
	margin-top:0px;
}
#contentmap_container_plugin_<?php echo $id; ?>
{
}


#contentmap_<?php echo $owner; ?>_<?php echo $id; ?>
{
	width: <?php echo $this->Params->get("map_width", "100"); ?><?php echo $this->Params->get("map_width_unit", "%"); ?>;
	height: <?php echo $this->Params->get("map_height", "400"); ?>px;
	color: #505050;
}

#contentmap_<?php echo $owner; ?>_<?php echo $id; ?> a, #contentmap_<?php echo $owner; ?>_<?php echo $id; ?> a:hover, #contentmap_<?php echo $owner; ?>_<?php echo $id; ?> a:visited
{
	color: #0055ff;
}

/* Article image inside the balloon */
.intro_image
{
	margin: 8px;
}

/* Author alias inside the balloon */
.created_by_alias
{
	font-size: 0.8em;
	font-style:italic;
}

/* Creation date inside the balloon */
.created
{
	font-size: 0.8em;
	font-style:italic;
}
