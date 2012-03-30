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

class OSScssLoader extends OSSLoader
{
	public function __construct()
	{
		parent::__construct();
		$this->Type = "css";
		$this->ParamName = "stylesheet";  // The name of the parameter in component / module options
		$this->SourceHeader = "/* css generator begin */\n";
		$this->SourceFooter = "\n/* css generator end */";
	}


	protected function headers()
	{
		parent::headers();
		header('content-type: text/css');
	}
}

