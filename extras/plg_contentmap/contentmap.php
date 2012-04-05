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

jimport( 'joomla.event.plugin' );

class plgContentcontentmap extends JPlugin
{
	protected $document;

	public function __construct(&$subject, $config = array())
	{
		$this->document = JFactory::getDocument();
		parent::__construct($subject, $config);
	}


	function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm)) return false;
		if ($form->getName() != "com_content.article") return true;

		JHtml::_('behavior.framework', true);

		$this->document->addStyleSheet("../plugins/content/contentmap/css/picker.css");
		$this->document->addScript("../plugins/content/contentmap/js/api.js");
		//$this->document->addScript(JURI::root(true) . "/libraries/contentmap/js/geopicker-min.js");
		$this->document->addScript(JURI::root(true)
		. "/index.php"
		. "?option=com_contentmap"
		. "&amp;view=smartloader"
		. "&amp;owner=article"
		. "&amp;id=" . $data->id
		. "&amp;type=js"
		. "&amp;filename=geopicker");

/*
		if (preg_match('/ 1$/', $GLOBALS["contentmap"]["version"])) return "";

		$name = basename(realpath(dirname(__FILE__) . "/../.."));

		$direction = intval(JFactory::getLanguage()->get('rtl', 0));
		$left  = $direction ? "right" : "left";
		$right = $direction ? "left" : "right";

		echo '<div class="clr"></div>';
		$image = '';
		$icon	= (string)$this->element['icon'];
		if (!empty($icon))
		{
			$image .= '<img style="margin:0; float:' . $left . ';" src="' . JURI::base(true) . '/../media/' . $name . '/images/' . $icon . '">';
		}

		$style = 'background:#f4f4f4; border:1px solid silver; padding:5px; margin:5px 0;';

		return '<div style="' . $style . '">' .
		$image .
		'<span style="padding-' . $left . ':5px; line-height:16px;">' .
		JText::_($this->element['text']) .
		' <a href="' . $this->element['url'] . '" target="_blank">' .
		JText::_(strtoupper($name) . '_DOCUMENTATION') .
		'</a>' .
		'</span>' .
		'</div>';

*/
		return true;
	}


	function onContentAfterDisplay($article, $params, $limitstart)
	{
		if (JRequest::getCmd("option") != "com_content" || JRequest::getCmd("view") != "article" ) return;

		// Does current article have a map?
		$xreference = $params->metadata->get("xreference");
		$pattern = '/[+-]?([0-9]+)(\.[0-9]+)?,( +)?[+-]?([0-9]+)(\.[0-9]+)?/';
		if (!(bool)preg_match($pattern, $xreference)) return;

		// Load shared language files for frontend side
		require_once(JPATH_ROOT . DS . "libraries" . DS . "contentmap" . DS . "language" . DS . "contentmap.inc");

		// Api key parameter for Google map
		$api_key = $this->params->get('api_key', NULL);
		$api_key = $api_key ? "&key=" . $api_key : "";

		// Language parameter for Google map
		// See Google maps Language coverage at https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1
		// Use JFactory::getLanguage(), because we can't rely on $lang variable
		$language = JFactory::getLanguage()->get("tag", NULL);
		$language = $language ? "&language=" . $language : "";

		// Plugin id passed to the component
		$db = JFactory::getDbo();
		jimport("joomla.database.databasequery");
		$query = $db->getQuery(true);
		$query->select("extension_id");
		$query->from("#__extensions");
		$query->where("element = 'contentmap'");
		$query->where("client_id = 0");
		$query->where("type = 'plugin'");
		$db->setQuery($query);
		$id = $db->loadResult() or JLog::add("Database error.", JLog::ERROR, 'plugin');

		// Itemid required in order to build SEF links (see markers.php)
		/*
		$itemid = JFactory::getApplication()->getMenu()->getActive();
		$itemid = $itemid ? "&Itemid=" . $itemid->id : "";
		*/
		$menu = JFactory::getApplication()->getMenu();
		$itemid = $menu->getActive() or $itemid = $menu->getDefault();
		$itemid = "&Itemid=" . $itemid->id;
		$template = "template";
		$params->text .= "<!-- plg_contentmap " . $GLOBALS["contentmap"]["version"] . "-->";

		//$document = JFactory::getDocument();

		// Slash is intentionally "/" since it refers to URLs, not actually paths
		$prefix = JURI::base(true) . "/index.php?option=com_contentmap&amp;owner=pid&amp;view=loader";

		$stylesheet = pathinfo($this->params->get("stylesheet", "default.css"));
		$this->document->addStyleSheet($prefix . "&amp;id=" . $id . "&amp;type=css" . "&amp;filename=" . $stylesheet["filename"]);
		$this->document->addScript("http://maps.google.com/maps/api/js?sensor=false" . $language . $api_key);
		$this->document->addScript($prefix . "&amp;id=" . $id . "&amp;type=markers" . "&amp;contentid=" . $params->id . $itemid);
		$this->document->addScript(JURI::base(true) . "/libraries/contentmap/js/markerclusterer_compiled.js");
		$this->document->addScript($prefix . "&amp;id=" . $id . "&amp;type=js&amp;filename=map");
		$params->text .= $template($id, JText::_("CONTENTMAP_JAVASCRIPT_REQUIRED"));
	}
}
