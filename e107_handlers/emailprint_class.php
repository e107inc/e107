<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/emailprint_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

class emailprint{
	function render_emailprint($mode, $id, $look=0){
		// $look = 0  --->  display all icons
		// $look = 1  --->  display email icons
		// $look = 2  --->  display print icons
		$text_emailprint="";
		switch($mode){
			case "article":
				$email = "article";
				$print = "content";
			break;
			case "news":
				$email = "news";
				$print = "news";				
			break;
		}
		if($look==0 || $look==1){
			$text_emailprint .= "<a href='email.php?".$email.".".$id."'><img src='".e_IMAGE."generic/friend.gif' style='border:0' alt='email to someone' /></a> ";
		}
		if($look==0 || $look==2){
			$text_emailprint .= "<a href='print.php?".$print.".".$id."'><img src='".e_IMAGE."generic/printer.gif' style='border:0' alt='printer friendly' /></a>";
		}
		return $text_emailprint;
	}
}

?>