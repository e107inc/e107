<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/sitelinks_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
include(e_LANGUAGEDIR.e_LAN."/lan_sitelinks.php");
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function sitelinks(){
	/*
	# Render style links
	# - parameters		none
	# - return				parsed text
	# - scope					null
	*/
	$text = PRELINK;
	if(defined("LINKCLASS")){
		$linkadd = " class='".LINKCLASS."' ";
	}
	if(ADMIN == TRUE){
		$text .= LINKSTART."<a".$linkadd." href=\"".e_ADMIN."admin.php\">Admin Area</a>".LINKEND."\n";
	}
	$sql = new db;
	$sql -> db_Select("links", "*", "link_category='1' ORDER BY link_order ASC");
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(!$link_class || check_class($link_class) || ($link_class==254 && USER)){
			switch ($link_open) { 
				case 1:
					$link_append = " onclick=\"window.open('$link_url'); return false;\"";

				break; 
				case 2:
				   $link_append = " target=\"_parent\"";
				break;
				case 3:
				   $link_append = " target=\"_top\"";
				break;
				default:
				   unset($link_append);
			}
			if(!strstr($link_url, "http:")){ $link_url = e_BASE.$link_url; }
			if($link_open == 4){
				$text .=  LINKSTART."<a".$linkadd." href=\"javascript:openwindow('".$link_url."')\">".$link_name."</a>".LINKEND."\n";
			}else{
				$text .=  LINKSTART."<a".$linkadd." href=\"".$link_url."\"".$link_append.">".$link_name."</a>".LINKEND."\n";
			}
		}
		
	}
	$text .= POSTLINK;
	if(LINKDISPLAY == 2){
		$ns = new e107table;
		$ns -> tablerender(LAN_183, $text);
	}else{
		echo $text;
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>