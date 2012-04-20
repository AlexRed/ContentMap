<?php
// Prepare some useful headers
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// must not be cached by the client browser or any proxy
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('content-type: application/json');

echo "var data_" . $_GET["owner"] . "_" . $_GET["id"] . "={\n";


$markers = new smfGoogleMapMarkers();

// Load additional data
$baseurl = json_encode('http://forum.joomla.it/');

echo
'"minlatitude":' . $markers->MinLatitude . ',' .
'"maxlatitude":' . $markers->MaxLatitude . ',' .
'"minlongitude":' . $markers->MinLongitude . ',' .
'"maxlongitude":' . $markers->MaxLongitude . ',' .
'"baseurl":' . $baseurl . ',' .
'"nodata_msg":' . json_encode("There are no places viewable by this module.") . ',' .
'"markers_action":"' . "infowindow" . '",' .
'"places":' . $markers->asJSON();


echo "\n}";





abstract class GoogleMapMarkers
{
	public $Contents;
	// Values used in order to automatically scale and center the map
	public $MinLatitude = 90.0;
	public $MaxLatitude = -90.0;
	public $MinLongitude = 180.0;
	public $MaxLongitude = -180.0;

	abstract protected function Load();

	public function __construct()
	{
		$this->Load();
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


class smfGoogleMapMarkers extends GoogleMapMarkers
{
	protected function Load()
	{
		//include("SSI.php");
		//$db = mysql_connect($db_server, $db_user, $db_passwd) or die("Connessione al database fallita");
		//mysql_select_db($db_name) or die(mysql_error());
		$db = mysql_connect("localhost", "mysqluser", "mysqlpass") or die("Connessione al database fallita");
		mysql_select_db("forum_joomla") or die(mysql_error());

		$gender = array();
		$gender[0] = "Non specificato";
		$gender[1] = "Maschio";
		$gender[2] = "Femmina";

		$regex = '^[+-]?[0-9]{1,2}([.][0-9]{1,})?[ ]{0,},[ ]{0,}[+-]?[0-9]{1,3}([.][0-9]{1,})?$';

		$query = "SELECT m.member_name, m.id_member, m.posts, m.gender, m.avatar, t.value
		FROM smf_themes as t LEFT JOIN smf_members as m ON t.id_member = m.id_member
		WHERE variable  = 'coordina'
		AND value REGEXP '$regex'
		";
		$dataset = mysql_query($query, $db);

		while ($result = mysql_fetch_array($dataset, MYSQL_ASSOC))
		{
			$content = array();
			// Nickname
			$content["title"] = $result["member_name"];

			$coordinates = explode(",", $result["value"]);
			// Google map js needs them as two separate values (See constructor: google.maps.LatLng(lat, lon))
			$content["latitude"] = floatval($coordinates[0]);
			$content["longitude"] = floatval($coordinates[1]);
			// Store max e min values
			$this->MinLatitude = min($content["latitude"], $this->MinLatitude);
			$this->MaxLatitude = max($content["latitude"], $this->MaxLatitude);
			$this->MinLongitude = min($content["longitude"], $this->MinLongitude);
			$this->MaxLongitude = max($content["longitude"], $this->MaxLongitude);

			/*
			if (strpos($result["avatar"], "http://") === 0)
			$image = '<img src="' . $result["avatar"] . '"/>';
			else
			$image = '<img src="' . "http://forum.joomla.it/avatars/" . $result["avatar"] . '"/>';
			*/

			$content["html"] =
			//			'<div style="float:left;margin-right:16px;">' . $image . "</div>" .
			'<div style="font-weight:bold;">' . $result["member_name"] . "</div>" .
			"<div class=\"created\">" . $gender[$result["gender"]] . "</div>" .
			'<div class="created"><a href="http://forum.joomla.it/index.php?action=profile;area=showposts;u=' . $result["id_member"] . '" target="_blank">Post: ' . $result["posts"] . "</a></div>" .
			'<div class="created"><a href="http://forum.joomla.it/index.php?action=profile;u=' . $result["id_member"] . '" target="_blank">Visualizza profilo</a></div>';

			// Lo aggiunge alla catasta
			$this->Contents[] = $content;

		}

		mysql_close($db);
	}
}
