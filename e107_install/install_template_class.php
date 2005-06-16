<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_install/install_template_class.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-16 16:55:07 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

// A streaky original work of genius, used correctly probably the fastest and lightest PHP Template engine there is.. KISS

class SimpleTemplate {

	var $Tags = array();
	var $open_tag = "{";
	var $close_tag = "}";
	
	function SimpleTemplate() {
		define("TEMPLATE_TYPE_FILE", 0);
		define("TEMPLATE_TYPE_DATA", 1);
	}

	function SetTag($TagName, $Data) {
		$this->Tags[$TagName] = array(	'Tag'  => $TagName,
										'Data' => $Data
									);
	}

	function RemoveTag($TagName) {
		unset($this->Tags[$TagName]);
	}

	function ClearTags() {
		$this->Tags = array();
	}

	function ParseTemplate($Template, $template_type = TEMPLATE_TYPE_FILE) {
		if($template_type == TEMPLATE_TYPE_DATA) {
			$TemplateData = $Template;
		} else {
			$TemplateData = file_get_contents($Template);
		}
		foreach ($this->Tags as $Tag) {
			$TemplateData = str_replace($this->open_tag.$Tag['Tag'].$this->close_tag, $Tag['Data'], $TemplateData);
		}
		return $TemplateData;
	}
}

?>