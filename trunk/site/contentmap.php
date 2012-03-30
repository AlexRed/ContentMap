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

// Type could be css, js or markers
$type = JRequest::getVar("type", 0, 'GET');
// Import appropriate library
jimport("contentmap.loader." . $type . "loader") or die("unknown type");
// Instantiate the loader
$classname = "OSS" . $type . "Loader";
$loader = new $classname();

$loader->SetVars("/*owner*/", JRequest::getVar("owner", "", "GET"));
$loader->SetVars("/*id*/", JRequest::getVar("id", "", "GET"));
// Used for markersloader only
$loader->SetVars("/*infowindow_width*/", $loader->Params->get('infowindow_width', "200"));

$loader->Show();

die();
