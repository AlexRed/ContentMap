<?php defined("_JEXEC") or die("Restricted access");

// Type could be css, js or markers
$type = JRequest::getVar("type", 0, "GET");
// Import appropriate library
jimport("contentmap.loader." . $type . "loader") or die("unknown type");
// Instantiate the loader
$classname = "OSS" . $type . "Loader";
$loader = new $classname();

$loader->SetVars("/*owner*/", JRequest::getVar("owner", "", "GET"));
$loader->SetVars("/*id*/", JRequest::getVar("id", "", "GET"));
// Used for markersloader only
$loader->SetVars("/*infowindow_width*/", $loader->Params->get("infowindow_width", "200"));

$loader->Show();

die();
