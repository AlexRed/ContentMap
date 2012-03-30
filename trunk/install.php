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

class com_contentmapInstallerScript
{

	function install($parent)
	{
		$this->logo($parent);
		$this->chain_install($parent);
	}


	function uninstall($parent) 
	{
	}


	function update($parent) 
	{
		$this->logo($parent);
		$this->chain_install($parent);

		$db = JFactory::getDBO();

		// Fixes a Joomla bug, wich adds a second repository rather than overwrite the first one if they are different
		$query = "DELETE FROM `#__update_sites` WHERE `name` = 'ContentMap update site';";
		$db->setQuery($query);
		$db->query();

		// Clear updates cache related to ContentMap
		$query = "DELETE FROM `#__updates` WHERE `name` = 'ContentMap';";
		$db->setQuery($query);
		$db->query();

	}


	function preflight($type, $parent)
	{
	}


	function postflight($type, $parent) 
	{
	}


	private function chain_install(&$parent)
	{
		$installer = new JInstaller();

		$manifest = $parent->get("manifest");
		if (!isset($manifest->chain->extension)) return;

		foreach($manifest->chain->extension as $extension)
		{
			$attributes = $extension->attributes();
			$item = $parent->getParent()->getPath("source") . DS . $attributes["directory"] . DS . $attributes["name"];
			$result = $installer->install($item);
		}
	}


	private function logo(&$parent)
	{
		$language = JFactory::getLanguage();
		$manifest = $parent->get("manifest");
		$direction = intval(JFactory::getLanguage()->get('rtl', 0));
		$left  = $direction ? "right" : "left";
		$right = $direction ? "left" : "right";

		echo(
			'<img src="' . $manifest->authorUrl->data() . 'download/' . substr($parent->get('element'), 4) . '-logo.png" ' .
			'alt="' . $language->_($manifest->name->data()) . ' Logo" ' .
			'style="float:' . $left . ';margin:15px;" width="128" height="128" />' .
			'<h2>' . $language->_($manifest->name->data()) . '</h2>'
			);

	}

}

