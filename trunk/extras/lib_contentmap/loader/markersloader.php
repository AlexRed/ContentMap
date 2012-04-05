<?php defined('_JEXEC') or die('Restricted access');
/*
This file is part of "Content Map Joomla Extension".
Author: Open Source solutions http://www.opensourcesolutions.es

You can redistribute and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation,
either version 2 of the License, or (at your option) any later version.

GNU/GPL license gives you the freedom:
* to use this software for both commercial and non-commercial purposes
* to share, copy, distribute and install this software and charge for it if you wish.

Under the following conditions:
* You must attribute the work to the original author

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this software.  If not, see http://www.gnu.org/licenses/gpl-2.0.html.

@copyright Copyright (C) 2012 Open Source Solutions S.L.U. All rights reserved.
*/

require_once("loader.php");
require_once("markers.php");

class OSSmarkersLoader extends OSSLoader
{
	public function __construct()
	{
		parent::__construct();
		$this->Type = "js";
		$this->SourceHeader = "var data_" . JRequest::getVar("owner", "", 'GET') . "_" . JRequest::getVar("id", 0, 'GET') . "={";
		$this->SourceFooter = "}";
	}


	protected function headers()
	{
		parent::headers();
		header('content-type: application/json');
	}


	protected function load()
	{
		// Call the helper to load data
		$markers = new GoogleMapMarkers($this->Params);
		$markers->PrepareInfoWindows();

		// Load additional data
		$markers_icon = $this->Params->get("markers_icon", NULL);
		$markers_icon = $markers_icon ? '"icon":' . json_encode(JURI::base(true) . '/media/contentmap/markers/icons/' . $markers_icon) . ',' : "";

		$this->Source =
		'"minlatitude":' . $markers->MinLatitude . ',' .
		'"maxlatitude":' . $markers->MaxLatitude . ',' .
		'"minlongitude":' . $markers->MinLongitude . ',' .
		'"maxlongitude":' . $markers->MaxLongitude . ',' .
		'"zoom":' . $markers->Zoom . ',' .
		'"baseurl":' . json_encode(JURI::base(true) . '/') . ',' .
		$markers_icon .
		'"nodata_msg":' . json_encode(JText::_("CONTENTMAP_NO_DATA")) . ',' .
		'"markers_action":"' . $this->Params->get("markers_action", "infowindow") . '",' .
		'"places":' . $markers->asJSON();
	}
}



