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
|     $Source: /cvs_backup/e107_0.7/e107_install/forms_class.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-16 17:37:39 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

class e_forms {
	
	var $form;
	var $opened;
	
	function start_form($id, $action, $method = "post" ) {
		$this->form = "\n<form method='{$method}' id='{$id}' action='{$action}'>\n";
		$this->opened = true;
	}
	
	function add_select_item($id, $labels, $selected) {
		$this->form .= "
		<select name='{$id}' id='{$id}'>\n";
		foreach ($labels as $label) {
			$this->form .= "<option".($label == $selected ? " selected='selected'" : "").">{$label}</option>\n";
		}
		$this->form .= "</select>\n";
	}
	
	function add_button($id, $title, $align = "right", $type = "submit") {	
		$this->form .= "<div style='text-align: {$align};'><input type='{$type}' id='{$id}' value='{$title}' /></div>\n";
	}

	function add_hidden_data($id, $data) {
		$this->form .= "<input type='hidden' name='{$id}' value='{$data}' />\n";
	}
	
	function add_plain_html($html_data) {
		$this->form .= $html_data;
	}
	
	function return_form() {
		if($this->opened == true) {
			$this->form .= "</form>\n";
		}
		$this->opened = false;
		return $this->form;
	}
}
	
?>