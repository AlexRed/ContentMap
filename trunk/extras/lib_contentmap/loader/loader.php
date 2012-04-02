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

jimport('joomla.log.log');

abstract class OSSLoader
{
	private $Placeholders = array();
	private $Values = array();
	private $Mid;
	protected $Source;
	protected $Type;
	protected $ParamName;
	protected $SourceHeader;
	protected $SourceFooter;
	public $Params;

	//abstract protected function source_header();

	public function __construct()
	{
		// @ avoids "Warning: ini_set() has been disabled for security reasons in /var/www/libraries/joomla/[...]"
		$application = @JFactory::getApplication('site');  // Needed to get the correct session

		JLog::addLogger(array(
		'text_file' => substr($application->scope, 4) . '.log.php'
		));

		//$owner = (bool)JRequest::getVar("mid", 0, "GET") ? "mid" : "pid";
		$get_params = "get_params_" . JRequest::getVar("owner", "", "GET");
		$this->$get_params();
	}


	public function SetVars($placeholder, $value)
	{
		$this->Placeholders[] = $placeholder;
		$this->Values[] = $value;
	}


	public function Show()
	{
		$this->headers();
		$this->load();
		echo $this->SourceHeader;
		echo $this->Source;
		echo $this->SourceFooter;
	}


	// Todo: Duplicated code
	private function get_params_mid()
	{
		$db = JFactory::getDbo();
		jimport("joomla.database.databasequery");
		$query = $db->getQuery(true);
		$query->select('params');
		$query->from('#__modules');
		$query->where("id = " . intval(JRequest::getVar("id", 0, 'GET')));
		$db->setQuery($query);

		// Load parameters from database
		$json = $db->loadResult() or JLog::add("Database error.", JLog::ERROR, 'loader');

		// Transform them as JRegistry
		$this->Params = new JRegistry($json);

		// Fill transposition map
		$params = $this->Params->toArray();
		foreach ($params as $key => $param)
		{
			// We could handle arrays as well, but currently we don't
			if (is_array($param)) continue;

			$this->Placeholders[] = "/*" . $key . "*/";
			$this->Values[] = $param;
		}
	}


	// Todo: Duplicated code
	private function get_params_pid()
	{
		$db = JFactory::getDbo();
		jimport("joomla.database.databasequery");
		$query = $db->getQuery(true);
		$query->select("params");
		$query->from("#__extensions");
		$query->where("element = 'contentmap'");
		$query->where("client_id = 0");
		$query->where("type = 'plugin'");
		$db->setQuery($query);

		// Load parameters from database
		$json = $db->loadResult() or JLog::add("Database error.", JLog::ERROR, 'loader');

		// Transform them as JRegistry
		$this->Params = new JRegistry($json);

		// Fill transposition map
		$params = $this->Params->toArray();
		foreach ($params as $key => $param)
		{
			// We could handle arrays as well, but currently we don't
			if (is_array($param)) continue;

			$this->Placeholders[] = "/*" . $key . "*/";
			$this->Values[] = $param;
		}
	}


	protected function headers()
	{
		// Prepare some useful headers
		header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		// must not be cached by the client browser or any proxy
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}


	protected function load()
	{
		// Complete the script name with its path
		$local_name = realpath(dirname(__FILE__) . "/../" . $this->Type . "/" . $this->Params->get($this->ParamName));

		// Open source file
		$handle = @fopen($local_name, 'r');
		// Read the content
		$this->Source = fread($handle, filesize($local_name));
		// Close source file
		fclose($handle);

		// swap variables values
		$this->Source = str_replace($this->Placeholders, $this->Values, $this->Source);
	}

}

