<?php defined('_JEXEC') or die('Restricted access');

$jlang = JFactory::getLanguage();
$jlang->load('contentmap', JPATH_LIBRARIES.'/contentmap', 'en-GB', true);
$jlang->load('contentmap', JPATH_LIBRARIES.'/contentmap', $jlang->getDefault(), true);
$jlang->load('contentmap', JPATH_LIBRARIES.'/contentmap', null, true);

$source = JRequest::getVar("source", "", "GET");
// Only admit lowercase a-z, underscore and minus. Forbid numbers, symbols, slashes and other stuff.
// For your security, *don't* touch the following regular expression.
preg_match('/^[a-z_-]+$/', $source) or $source = "invalid";

$classname = $source . "GoogleMapMarkers";
// Call the helper to load data
$markers = new $classname($this->Params);
$markers->PrepareInfoWindows();

// Load additional data
$markers_icon = $this->Params->get("markers_icon", NULL);
$markers_icon = $markers_icon ? '"icon":' . json_encode(JURI::base(true) . '/media/contentmap/markers/icons/' . $markers_icon) . ',' : "";

$source =
$markers_icon .
'"places":' . $markers->asJSON();
echo $source;


// Required for ContentHelperRoute::getArticleRoute()
require_once(JPATH_SITE . '/' . "components" . '/' . "com_content" . '/' . "helpers" . '/' . "route.php");
require_once(JPATH_ROOT . '/' . "libraries" . '/' . "contentmap" . '/' . "language" . '/' . "contentmap.inc");

abstract class GoogleMapMarkers
{
	public $Contents;
	// Values used in order to automatically scale and center the map
	public $Zoom;
	protected $Params;

	abstract protected function Load();

	public function __construct(&$params)
	{
		$this->Params = $params;
		//$load = "load_" . JRequest::getVar("owner", "", "GET");
		//$this->$load();
		$this->Load();

		// Set default zoom level to 17, just in case we are on the module and (sad but true, it can happen) there is only one marker
		$this->Zoom = $this->Params->get('zoom', 17);
	}


	public function PrepareInfoWindows()
	{
		require_once JPATH_SITE . "/components/com_content/helpers/route.php";

		//$baseuri = str_replace("modules/mod_contentmap/lib", "", JURI::base(true));
		foreach ($this->Contents as &$content)
		{
			$content["html"] = "";

			// We haven't the active menu item, since we are acting in background, so we hope it is in the URL request
			// Itemid variable influences ContentHelperRoute::getArticleRoute() link creation
			$unsef_link = ContentHelperRoute::getArticleRoute($content["id"], $content["catid"]);

			// Sef Link examples:
			// without &Itemid : http://site/index.php/component/content/article/2-categoryalias/2-articlealias - This is always valid
			// with &Itemid :    http://site/index.php/2-categoryalias/2-articlealias - Generated if the homepage is a blog item
			// with &Itemid :    http://site/index.php/blog/2-categoryalias/2-articlealias - Generated if the homepage is *not* a blog item
			$sef_link = JRoute::_($unsef_link);

			// Prepare the title
			if ($this->Params->get('show_title', 0))
			{
				$content["html"] .= "<h3>";
				$content["html"] .= $content["title"];

				if ($this->Params->get('link_titles', 0))
				{
					$target = ' target="' . $this->Params->get("link_target", "_self") . '"';
					$content["html"] =
					'<a href="' . $sef_link . '"' . $target . '>' .
					$content["html"] .
					"</a>";
				}

				$content["html"] .= "</h3>";
			}
			unset($content["id"]);
			unset($content["alias"]);
			unset($content["catid"]);
			// Article url is useful when Marker action is set to "directly redirect", rather than "open the infowindow"
			$content["article_url"] = $sef_link;

			// Prepare the image
			if ($this->Params->get('show_image', 0) && $content["image"])
			{
				// Image size
				$format = "";
				if (function_exists("getimagesize"))
				{
					$size = getimagesize(JPATH_SITE . "/" . $content["image"]);
					$format = " " . $size[3];
				}

				// Add the base url to the image. Used by both infowindow innerhtml and preload() function
				$content["image"] = JURI::base(true) . "/" . $content["image"];
				
				
				
				// Image URL
				$content["html"] .= "<div style=\"float:" . $content["float_image"] . ";\">";
				
				if ($this->Params->get('link_titles', 0))
				{
					$target = ' target="' . $this->Params->get("link_target", "_self") . '"';
					$content["html"] .=
					'<a href="' . $sef_link . '"' . $target . '>';
				}
				$content["html"] .= "<img class=\"intro_image\"" .
				$format .
				" src=\"" . $content["image"] . "\"";
				if ($content["image_intro_alt"]) $content["html"] .= " alt=\"" . $content["image_intro_alt"] . "\"";
				if ($content["image_intro_caption"]) $content["html"] .= " title=\"" . $content["image_intro_caption"] . "\"";
				$content["html"] .= ">";
				if ($this->Params->get('link_titles', 0))
				{
					$content["html"] .= "</a>";
				}
				$content['html'] .= "</div>";
			}
			else
			{
				$content["image"] = NULL;
			}
			unset($content["image_intro_alt"]);
			unset($content["image_intro_caption"]);
			unset($content["float_image"]);
			// unset($content["image"]);

			// Other content
			if ($this->Params->get('show_created_by_alias', 0) && $content["created_by_alias"])
			{
				$content["html"] .= "<div class=\"created_by_alias\">" . $content["created_by_alias"] . "</div>";
			}
			unset($content["created_by_alias"]);

			if ($this->Params->get('show_created', 0) && $content["created"] != "0000-00-00 00:00:00")
			{
				// Search for the first empty space into the string
				//$offset = strpos($content["created"], " ") or $offset = strlen($content["created"]);
				// Cut the string at the offser above
				//$content["html"] .= "<div class=\"created\">" . substr($content["created"], 0, $offset) . "</div>";
				$content["html"] .= "<div class=\"created\">" . JHtml::_('date', $content["created"], JText::_('DATE_FORMAT_LC')) . "</div>";
			}
			unset($content["created"]);

			// Intro Text
			if ($this->Params->get('show_intro', 0) && $content["introtext"])
			{
				if ($maxsize = $this->Params->get('introtext_size', 0))
				{
					// Cut text exceeding maximum size
					$readmore = strlen($content["introtext"]) > $maxsize ? "..." : "";
					$content["html"] .= "<div>" . substr($content["introtext"], 0, $maxsize) . $readmore . "</div>";
				}
			}
			unset($content["introtext"]);

			// Add "Get Directions" inside the marker
			if($this->Params->get('showDirectionsMarker', 0))
			{
				//$content['html'] .= '<div>';
				//$content['html'] .= '<a href="http://maps.google.com/maps?saddr=&daddr='.$content["latitude"].','.$content["longitude"].'" target="_blank">'.JText::_('CONTENTMAP_GET_DIRECTIONS').'</a>';
				//$content['html'] .= '</div>';
				
				$ownerandid= JRequest::getVar("owner", "", "GET").'_'.JRequest::getVar("id", "", "GET");
				
				$formHtml = '<form class="" onsubmit="return findDirFromAddr_'.$ownerandid.'(\''.$content["latitude"].','.$content["longitude"].'\');" action="#"><div class="input-append"><input id="contentmap_input_'.$ownerandid.'" placeholder="'.JText::_('CONTENTMAP_FROM_ADDRESS').'" type="text"><button class="btn" type="submit">Go!</button></div></form>';
				
			    $content['html'] .='<div class="contentmap-m-actbar-list">';
					$content['html'] .='<div class="contentmap-m-act">';
					$content['html'] .='<a href="http://maps.google.com/maps?saddr=&daddr='.$content["latitude"].','.$content["longitude"].'" target="_blank">'.JText::_('CONTENTMAP_GET_DIRECTIONS').'</a> ';
					$content['html'] .='<a id="contentmapmstreet" onclick="toggleStreetView_'.$ownerandid.'(\''.$content["latitude"].'\',\''.$content["longitude"].'\')">Street View</a>';
					$content['html'] .='<div class="dirform-inner">'.$formHtml.'</div>';
					$content['html'] .='</div>';
			    $content['html'] .='</div>';
				
				
			}
		}

	}

	public function asArray()
	{
		return $this->Contents;
	}


	public function asJSON()
	{
		return json_encode($this->Contents);
	}


	public function Count()
	{
		return count($this->Contents);
	}

}


class articleGoogleMapMarkers extends GoogleMapMarkers
{
	protected function Load()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("id, title, alias, introtext, catid, created, created_by_alias, images, metadata");
		$query->from("#__content");

		// Condition: content id passed py plugin
		$query->where("id = '" . JRequest::getVar("contentid", 0, "GET") . "'");

		// Condition: metadata field contains "xreference":"coordinates"
		// {\"xreference\":\"} the string "xreference":"
		// {[+-]?} One character. It can be + or - sign. It is optional.
		// {([0-9]+)} At least one number. They are mandatory.
		// {(\.[0-9]+)?} A point followed by other numbers. The whole expression is optional.
		// {( +)?} one or more spaces. Optional.
		// {,} a comma
		// {( +)?} one or more spaces. Optional.
		// {[+-]?} One character. It can be + or - sign. It is optional.
		// {([0-9]+)} At least one number. They are mandatory.
		// {(\.[0-9]+)?} A point followed by other numbers. The whole expression is optional.
		// {\"} the string "
		//$query->where("metadata REGEXP '\"xreference\":\"[+-]?([0-9]+)(\.[0-9]+)?( +)?,( +)?[+-]?([0-9]+)(\.[0-9]+)?\"'");
		$query->where("metadata REGEXP '\"xreference\":\"[+-]?[0-9]{1,2}([.][0-9]{1,})?[ ]{0,},[ ]{0,}[+-]?[0-9]{1,3}([.][0-9]{1,})?\"'");

		$db->setQuery($query);
		$this->Contents = $db->loadAssocList() or $this->Contents = array();

		// Global data
		$query->clear();
		$query->select("params");
		$query->from("#__extensions");
		$query->where("name = 'com_content'");
		$db->setQuery($query);
		$contents_global_params = new JRegistry($db->loadResult());

		$check     = array();
		$i         = 0;
		$w_content = $this->Contents;
		foreach ($w_content as &$content)
		{
			// xreference database field is empty.
			// For some strange reason, it is stored in metadata field on the database
			$registry = new JRegistry($content["metadata"]); // Equivalent to $registry->loadString($content["metadata"], "JSON")
			$coordinates = explode(",", $registry->get("xreference"));

			// Let's remove points with exactly the same coords
			if(isset($check[md5($registry->get('xreference'))]))
			{
				unset($w_content[$i]);
				continue;
			}
			else
			{
				$check[md5($registry->get('xreference'))] = 1;
			}

			// Google map js needs them as two separate values (See constructor: google.maps.LatLng(lat, lon))
			$content["latitude"] = floatval($coordinates[0]);
			$content["longitude"] = floatval($coordinates[1]);

			// Specify marker
			// prepend with path
			$marker = $registry->get("marker");
			if (isset($marker)) {
			    $content["marker"] = JURI::base(true) . '/media/contentmap/markers/icons/' . $marker;
			}

			// Todo: pass data directly as jregistry, avoiding assign operations
			$registry->loadString($content["images"], "JSON");
			$content["image"] = $registry->get("image_intro");
			$content["float_image"] = $registry->get("float_intro") or $content["float_image"] = $contents_global_params->get("float_intro");
			$content["image_intro_alt"] = $registry->get("image_intro_alt");
			$content["image_intro_caption"] = $registry->get("image_intro_caption");

			// '&' in '&amp;' and other similar conversions
			$content["title"] = htmlspecialchars($content["title"]);
			$content["created_by_alias"] = htmlspecialchars($content["created_by_alias"]);
			$content["created"] = htmlspecialchars($content["created"]);

			// Remove html tags and keeps plain text
			if ($this->Params->get('intro_clean_html_tags', 1)){
				$content["introtext"] = JFilterInput::getInstance()->clean($content["introtext"], "string");
			}
			

			// Remove elements useless for the map purposes in order to increase performance
			// by saving bandwidth when sending JSON data to the client :)
			unset($content["metadata"]);
			unset($content["images"]);

			$i++;
		}

		$this->Contents = $w_content;

		// Problematic infowindows are near the upper border, so start preload from them
		// Sort by Latitude
		usort($this->Contents, "sort_markers");
	}
}

class articlesGoogleMapMarkers extends GoogleMapMarkers
{
	protected function Load()
	{
		$db = JFactory::getDBO();

		// Detect the language associated to the module. It will be used as articles filter
		$query = $db->getQuery(true);
		$query->select("language");
		$query->from("#__modules");
		$query->where("`id` = " . intval(JRequest::getVar("id", 0, "GET")));
		$query->where("`module` = 'mod_contentmap'");
		$db->setQuery($query);
		$language = $db->loadResult();

		$query->clear();
		$query->select("c.id, c.title, c.alias, c.introtext, c.catid, c.created, c.created_by_alias, c.images, c.metadata,g.title category,g.lft category_lft");
		$query->from("#__content c");

		$query->join('inner',"#__categories g ON c.catid=g.id");
		
		// Condition: metadata field contains "xreference":"coordinates"
		// {\"xreference\":\"} the string "xreference":"
		// {[+-]?} One character. It can be + or - sign. It is optional.
		// {([0-9]+)} At least one number. They are mandatory.
		// {(\.[0-9]+)?} A point followed by other numbers. The whole expression is optional.
		// {( +)?} one or more spaces. Optional.
		// {,} a comma
		// {( +)?} one or more spaces. Optional.
		// {[+-]?} One character. It can be + or - sign. It is optional.
		// {([0-9]+)} At least one number. They are mandatory.
		// {(\.[0-9]+)?} A point followed by other numbers. The whole expression is optional.
		// {\"} the string "
		$query->where("c.metadata REGEXP '\"xreference\":\"[+-]?([0-9]+)(\.[0-9]+)?( +)?,( +)?[+-]?([0-9]+)(\.[0-9]+)?\"'");

		// Condition: Published
		$query->where("c.state = '1'");

		$now = JFactory::getDate()->toSql();

		// Condition: Start Publishing in the past
		$query->where("c.publish_up <= " . $db->Quote($now));

		// Condition: Finish Publishing in the future or unset
		$query->where("(c.publish_down >= " . $db->Quote($now) . " OR c.publish_down = " . $db->Quote($db->getNullDate()) . ")");

		// Condition: Access level
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where("c.access IN (" . $groups .")");

		// Condition: Categories inclusive | exclusive filter
		$category_filter_type = $this->Params->get('category_filter_type', 0);  // Can be "IN", "NOT IN" or "0"
		if ($category_filter_type)
		{
			$categories = $this->Params->get('catid', array("0")); // Defaults to non-existing category (the system root category with id "1" would have worked as well)
			$categories = implode(',', $categories);         // Converted to string
			$query->where("c.catid " . $category_filter_type . " (" . $categories . ")");
		}

		// Condition: Author inclusive | exclusive filter
		$author_filtering_type = $this->Params->get('author_filtering_type', 0);  // Can be "IN", "NOT IN" or "0"
		if ($author_filtering_type)
		{
			$authors = $this->Params->get('created_by', array("0")); // Defaults to non-existing user
			$authors = implode(',', $authors);         // Converted to string
			$query->where("c.created_by " . $author_filtering_type . " (" . $authors . ")");
		}

		// Condition: Featured
		$query->where("c.featured IN (" . $this->Params->get('featured', "0,1") . ")");

		// Condition: Same language as the module or article associated to "ALL" languages or module associated to "ALL" languages
		if ($language !== "*")
		{
			$query->where("(c.language = " . $db->quote($language) . " OR c.language = '*')");
		}

		// Order by newest, in this way if there is any article with the same coords, I'll show the latest one
		$query->order('c.id DESC');

		$db->setQuery($query);
		$this->Contents = $db->loadAssocList() or $this->Contents = array();

		// Global data
		$query->clear();
		$query->select("params");
		$query->from("#__extensions");
		$query->where("name = 'com_content'");
		$db->setQuery($query);
		$contents_global_params = new JRegistry($db->loadResult());

		$check     = array();
		$i         = 0;
		$w_content = $this->Contents;
		
		$load_tags=version_compare(JVERSION, '3.1', 'ge');
		foreach ($w_content as &$content)
		{
			$content["tags"]=array();
			if ($load_tags){
				$query->clear();
				$query->select("t.title,t.lft");
				$query->from("#__content c");
				$query->join('inner',"#__contentitem_tag_map m ON c.id=m.content_item_id");
				$query->join('inner',"#__tags t ON m.tag_id=t.id");
				$query->where("m.type_alias='com_content.article'");
				$query->where("c.id = " . $db->Quote($content['id']));
				$db->setQuery($query);
				$content["tags"] = $db->loadAssocList();
				if (empty($content["tags"])){
					$content["tags"] = array(array('title'=>'-no tag-','lft'=>0));
				}
			}
		
			// xreference database field is empty.
			// For some strange reason, it is stored in metadata field on the database
			$registry = new JRegistry($content["metadata"]); // Equivalent to $registry->loadString($content["metadata"], "JSON")
			$coordinates = explode(",", $registry->get("xreference"));
/*
			// Let's remove points with exactly the same coords
			if(isset($check[md5($registry->get('xreference'))]))
			{
				unset($w_content[$i]);
				continue;
			}
			else
			{
				$check[md5($registry->get('xreference'))] = 1;
			}
*/
			// Google map js needs them as two separate values (See constructor: google.maps.LatLng(lat, lon))
			$content["latitude"] = floatval($coordinates[0]);
			$content["longitude"] = floatval($coordinates[1]);

			// Specify marker
			// prepend with path
			$marker = $registry->get("marker");
			if (isset($marker)) {
			    $content["marker"] = JURI::base(true) . '/media/contentmap/markers/icons/' . $marker;
			}

			// Todo: pass data directly as jregistry, avoiding assign operations
			$registry->loadString($content["images"], "JSON");
			$content["image"] = $registry->get("image_intro");
			$content["float_image"] = $registry->get("float_intro") or $content["float_image"] = $contents_global_params->get("float_intro");
			$content["image_intro_alt"] = $registry->get("image_intro_alt");
			$content["image_intro_caption"] = $registry->get("image_intro_caption");

			// '&' in '&amp;' and other similar conversions
			$content["title"] = htmlspecialchars($content["title"]);
			$content["created_by_alias"] = htmlspecialchars($content["created_by_alias"]);
			$content["created"] = htmlspecialchars($content["created"]);

			// Remove html tags and keeps plain text
			if ($this->Params->get('intro_clean_html_tags', 1)){
				$content["introtext"] = JFilterInput::getInstance()->clean($content["introtext"], "string");
			}
			

			// Remove elements useless for the map purposes in order to increase performance
			// by saving bandwidth when sending JSON data to the client :)
			unset($content["metadata"]);
			unset($content["images"]);

			$i++;
		}
		$this->Contents = $w_content;

		// Problematic infowindows are near the upper border, so start preload from them
		// Sort by Latitude,
		usort($this->Contents, "sort_markers");
	}
}


class remoteGoogleMapMarkers extends GoogleMapMarkers
{
	protected function Load()
	{
		// Get the file
		$urlwrapper = new UrlWrapper();
		$url = "http://forum.joomla.it/" . "utenti" . ".php";
		$data = $urlwrapper->Get($url);

		$xml = new SimpleXMLElement($data);

		// Converts objects to arrays
		$this->Contents = array();
		foreach ($xml->marker as $marker)
		{
			$this->Contents[] = (array)$marker;
		}

		// Global data
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("params");
		$query->from("#__extensions");
		$query->where("name = 'com_content'");
		$db->setQuery($query);
		$contents_global_params = new JRegistry($db->loadResult());

		foreach ($this->Contents as &$content)
		{
			// xreference database field is empty.
			// For some strange reason, it is stored in metadata field on the database
			$registry = new JRegistry($content["metadata"]); // Equivalent to $registry->loadString($content["metadata"], "JSON")
			$coordinates = explode(",", $registry->get("xreference"));

			// Google map js needs them as two separate values (See constructor: google.maps.LatLng(lat, lon))
			$content["latitude"] = floatval($coordinates[0]);
			$content["longitude"] = floatval($coordinates[1]);

			// Specify marker
			// prepend with path
			$marker = $registry->get("marker");
			if (isset($marker)) {
			    $content["marker"] = JURI::base(true) . '/media/contentmap/markers/icons/' . $marker;
			}

			// Todo: pass data directly as jregistry, avoiding assign operations
			$registry->loadString($content["images"], "JSON");
			$content["image"] = $registry->get("image_intro");
			$content["float_image"] = $registry->get("float_intro") or $content["float_image"] = $contents_global_params->get("float_intro");
			$content["image_intro_alt"] = $registry->get("image_intro_alt");
			$content["image_intro_caption"] = $registry->get("image_intro_caption");

			// '&' in '&amp;' and other similar conversions
			$content["title"] = htmlspecialchars($content["title"]);
			$content["created_by_alias"] = htmlspecialchars($content["created_by_alias"]);
			$content["created"] = htmlspecialchars($content["created"]);

			// Remove html tags and keeps plain text
			if ($this->Params->get('intro_clean_html_tags', 1)){
				$content["introtext"] = JFilterInput::getInstance()->clean($content["introtext"], "string");
			}

			// Remove elements useless for the map purposes in order to increase performance
			// by saving bandwidth when sending JSON data to the client :)
			unset($content["metadata"]);
			unset($content["images"]);
		}

		// Problematic infowindows are near the upper border, so start preload from them
		// Sort by Latitude,
		//usort($this->Contents, "sort_markers");

	}
}

function sort_markers($a, $b)
{
	// Sort descending
	return $b["latitude"] - $a["latitude"];
}


class UrlWrapper
{
	protected $method;

	public function __construct()
	{
		$this->method = "none";
		if (!ini_get('allow_url_fopen')) return;

		$functions = array("file_get_contents", "curl_init");
		foreach ($functions as $function)
		{
			if (function_exists($function))
			{
				$this->method = $function;
				return;
			}
		}
	}

	public function Get($url)
	{
		return $this->{$this->method}($url);
	}

	protected function file_get_contents($url)
	{
		return file_get_contents($url);
	}

	protected function curl_init($url)
	{
		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
		$data = curl_exec($handle);
		curl_close($handle);
		return $data;
	}

	protected function none()
	{
		// Server lacks. Returns an empty page.
		return "";
	}
}



class tagsGoogleMapMarkers extends GoogleMapMarkers
{
	protected function Load()
	{
		$load_tags=version_compare(JVERSION, '3.1', 'ge');
		if (!$load_tags){
			$this->Contents = array();
			return;
		}
	
		/*
		//Versione 1
		//\components\com_tags\models\tags.php
		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_tags/models');
		require_once JPATH_SITE.'/components/com_tags/helpers/route.php';
		$tagsModel = JModelLegacy::getInstance( 'Tags', 'TagsModel' , array('ignore_request' => true));

		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pid = 0;
		$tagsModel->setState('tag.parent_id', $pid);

		$language = $app->input->getString('tag_list_language_filter');
		$tagsModel->setState('tag.language', $language);

		$offset = 0;
		$tagsModel->setState('list.offset', $offset);
		$app = JFactory::getApplication();

		$params = $app->getParams();
		$tagsModel->setState('params', $params);

		$tagsModel->setState('list.limit', 99999);

		$tagsModel->setState('filter.published', 1);
		$tagsModel->setState('filter.access', true);

		$tagsModel->setState('list.filter', '');
		
		$items = $tagsModel->getItems();
		
		$w_contents=array();
		
		foreach ($items as $item){
			$content=array(
				'id' => $item->id,
				'title' => $item->title,
				'alias' => $item->alias,
				'introtext' => '',
				'catid' => 0,
				'created' => $item->created_time,
				'created_by_alias' => $item->created_by_alias,
				'category' => '',
				'category_lft' => 0,
				'tags' => 
				array (
				  0 => 
				  array (
					'title' => '-no tag-',
					'lft' => 0,
				  ),
				),
				'latitude' => 0,
				'longitude' => 0,
				'image' => NULL,
				'float_image' => 'left',
				'image_intro_alt' => NULL,
				'image_intro_caption' => NULL,
				
				
				'tag_link'=>JRoute::_(TagsHelperRoute::getTagRoute($item->id . '-' . $item->alias),false)
				);
			$w_contents[]=$content;
		}
		
		$this->Contents = $w_contents;
		
		*/
		//Versione 2
		JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');
		$db				= JFactory::getDbo();
		$user     		= JFactory::getUser();
		$groups 		= implode(',', $user->getAuthorisedViewLevels());
		$timeframe		= 'alltime';
		$maximum		= 99999;
		$order_value	= 'count';

		if ($order_value == 'rand()')
		{
			$order_direction	= '';
		}
		else
		{
			$order_value		= $db->quoteName($order_value);
			$order_direction	= 'DESC';
		}

		$query = $db->getQuery(true)
			->select(
				array(
					'MAX(' . $db->quoteName('tag_id') . ') AS tag_id',
					' COUNT(*) AS count', 'MAX(t.title) AS title',
					'MAX(' . $db->quoteName('t.access') . ') AS access',
					'MAX(' . $db->quoteName('t.alias') . ') AS alias'
				)
			)
			->group($db->quoteName(array('tag_id', 'title', 'access', 'alias')))
			->from($db->quoteName('#__contentitem_tag_map'))
			->where($db->quoteName('t.access') . ' IN (' . $groups . ')');

		// Only return published tags
		$query->where($db->quoteName('t.published') . ' = 1 ');

		// Optionally filter on language
		$language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

		if ($language != 'all')
		{
			if ($language == 'current_language')
			{
				$language = JHelperContent::getCurrentLanguage();
			}

			$query->where($db->quoteName('t.language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
		}

		if ($timeframe != 'alltime')
		{
			$now = new JDate;
			$query->where($db->quoteName('tag_date') . ' > ' . $query->dateAdd($now->toSql('date'), '-1', strtoupper($timeframe)));
		}
		
		$tags_filter_type = $this->Params->get('tags_filter_type', 0);  // Can be "IN", "NOT IN" or "0"
		if ($tags_filter_type)
		{
			$tagsid = $this->Params->get('tagsid', array("0")); // Defaults to non-existing tag
			$tagsid = implode(',', $tagsid);         // Converted to string
			$query->where("t.id " . $tags_filter_type . " (" . $tagsid . ")");
		}
		
		$typesr=$this->Params->get('tags_content_types', '');
		if ($typesr)
		{
			// Implode is needed because the array can contain a string with a coma separated list of ids
			$typesr = implode(',', $typesr);

			// Sanitise
			$typesr = explode(',', $typesr);
			JArrayHelper::toInteger($typesr);

		}
		
		$typesarray = JHelperTags::getTypes('assocList', $typesr, false);

		$typeAliases = '';

		foreach ($typesarray as $type)
		{
			$typeAliases .= "'" . $type['type_alias'] . "'" . ',';
		}

		$typeAliases = rtrim($typeAliases, ',');
		if (!empty($typeAliases)){
			$query->where('type_alias IN (' . $typeAliases . ')');
		}
		
		$pid=intval($this->Params->get('tags_parent_id', '0'));
		
		if (!empty($pid))
		{
			$query->where($db->quoteName('t.parent_id') . ' = ' . $pid);
		}

		// Exclude the root.
		$query->where($db->quoteName('t.parent_id') . ' <> 0');
		

		$query->join('INNER', $db->quoteName('#__tags', 't') . ' ON ' . $db->quoteName('tag_id') . ' = t.id')
			->order($order_value . ' ' . $order_direction);
		$db->setQuery($query, 0, $maximum);
		
		$items = $db->loadObjectList();
		
		//
		$w_contents=array();
		
		foreach ($items as $item){
			$content=array(
				'id' => $item->tag_id,
				'title' => $item->title,
				'alias' => $item->alias,
				'introtext' => '',
				'catid' => 0,
				'created' => '',
				'created_by_alias' => '',
				'category' => '',
				'category_lft' => 0,
				'tags' => 
				array (
				  0 => 
				  array (
					'title' => '-no tag-',
					'lft' => 0,
				  ),
				),
				'latitude' => 0,
				'longitude' => 0,
				'image' => NULL,
				'float_image' => 'left',
				'image_intro_alt' => NULL,
				'image_intro_caption' => NULL,
				
				
				'tag_link'=>JRoute::_(TagsHelperRoute::getTagRoute($item->tag_id . '-' . $item->alias),false),
				'tag_count'=>$item->count
				);
				
			
			if ($item->count==1){
			
				$tagsHelper = new JHelperTags;
				$includeChildren=false;
				$orderByOption='c.core_title';
				$orderDir='ASC';
				$matchAll=true;
				$stateFilter = 1;//solo quelli pubblicati
				$query = $tagsHelper->getTagItemsQuery($item->tag_id, $typesr, $includeChildren, $orderByOption, $orderDir, $matchAll, $language, $stateFilter);
			
				$db->setQuery($query, 0, 1);
				
				$detail_item = $db->loadObject();
			
				if (!empty($detail_item)){
					$content['tag_link']=JRoute::_(TagsHelperRoute::getItemRoute($detail_item->content_item_id, $detail_item->core_alias, $detail_item->core_catid, $detail_item->core_language, $detail_item->type_alias, $detail_item->router),false);
				}
				
				
			}			
			
			$w_contents[]=$content;
		}
		
		$this->Contents = $w_contents;		
	}
}

