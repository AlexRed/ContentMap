<?php defined("_JEXEC") or die("Restricted access");

jimport("joomla.application.component.view");

class ContentMapViewContentMap extends JView
{
	function display($tpl = null)
	{
		$this->msg = "Hello World";
		parent::display($tpl);
	}
}
