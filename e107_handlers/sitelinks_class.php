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
@include(e_LANGUAGEDIR.e_LAN."/lan_sitelinks.php");
@include(e_LANGUAGEDIR."English/lan_sitelinks.php");
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function sitelinks(){
	/*
	# Render style links
	# - parameters		none
	# - return				parsed text
	# - scope					null
	*/
	global $pref,$ns;

	if($cache = retrieve_cache("sitelinks")){
		$aj = new textparse;
		echo $aj -> formtparev($cache);
		return;
	}
	ob_start();


	if(LINKDISPLAY == 4){
		require_once(e_PLUGIN."ypslide_menu/ypslide_menu.php");
		return;
	}

	define(PRELINKTITLE, "");
	define(POSTLINKTITLE, "");
	$menu_count=0;
	$text = PRELINK;
	if(defined("LINKCLASS")){
		$linkadd = " class='".LINKCLASS."' ";
	}
	// Added for grafx plugin
	if($pref['gxhz_but']){
		require(e_PLUGIN."grafxheadz/gxhzslm.php");
	}
	/*
	if(ADMIN == TRUE){
		$linkstart = (file_exists(e_IMAGE."link_icons/admin.png") ? preg_replace("/\<img.*\>/si", "", LINKSTART)." " : LINKSTART);
		if(LINKDISPLAY != 3) {
			// Added for grafx plugin
			if($pref['gxhz_but']){
				require(e_PLUGIN."grafxheadz/gxhzslaa.php");
			}else{
				$text .= $linkstart.(file_exists(e_IMAGE."link_icons/admin.png") ? "<img src='".e_IMAGE."link_icons/admin.png' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"".e_ADMIN.(!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php")."\">".LAN_502."</a>".LINKEND."\n";
			}
		} else {
			$menu_main .= $linkstart.(file_exists(e_IMAGE."link_icons/admin.png") ? "<img src='".e_IMAGE."link_icons/admin.png' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"".e_ADMIN.(!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php")."\">".LAN_502."</a>".LINKEND."\n";
		}
	}
	*/
	$sql = new db; $sql2 = new db;
	$sql -> db_Select("links", "*", "link_category='1' && link_name NOT REGEXP('submenu') ORDER BY link_order ASC");
	while($row = $sql -> db_Fetch()){
		extract($row);
		// Added for grafx plugin
		if($pref['gxhz_but']){
			require(e_PLUGIN."grafxheadz/gxhzslnm.php");
		}
		if(!$link_class || check_class($link_class) || ($link_class==254 && USER)){
			$linkstart = ($link_button ? preg_replace("/\<img.*\>/si", "", LINKSTART) : LINKSTART);
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
				$_link =  $linkstart.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "").($link_url ? "<a".$linkadd.($pref['linkpage_screentip'] ? " title = '$link_description' " : "")." href=\"javascript:open_window('".$link_url."')\">".$link_name."</a>" : $link_name)."\n";
			}else{
				$_link =  $linkstart.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "").($link_url ? "<a".$linkadd.($pref['linkpage_screentip'] ? " title = '$link_description' " : "")." href=\"".$link_url."\"".$link_append.">".$link_name."</a>" : $link_name)."\n";
			}
			if(LINKDISPLAY == 3){
				$menu_title=$link_name;
			} else {
				$text .= $_link.LINKEND;
			}

			if($sql2 -> db_Select("links", "*", "link_name REGEXP('submenu.".$link_name."') ORDER BY link_order ASC") && !HIDESUBSECTIONS){
				$menu_count++;
				$main_linkname = $link_name;
				while($row = $sql2 -> db_Fetch()){
					extract($row);
					$link_name = str_replace("submenu.".$main_linkname.".", "", $link_name);
					if(!$link_class || check_class($link_class) || ($link_class==254 && USER)){
						$linkstart = ($link_button ? preg_replace("/\<img.*\>/si", "", LINKSTART)." " : LINKSTART);
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
						$indent=(LINKDISPLAY == 3) ? "" : "&nbsp;&nbsp;";
						if($link_open == 4){
							$_link =  $linkstart.$indent.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"javascript:open_window('".$link_url."')\">".$link_name."</a>".LINKEND."\n";
						} else { 
							$_link =  $linkstart.$indent.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"".$link_url."\"".$link_append.">".$link_name."</a>".LINKEND."\n";
						}
						if(LINKDISPLAY == 3){
							$menu_text .= $_link;
						} else {
							$text .= $_link;
						}
					}
				}
				if(LINKDISPLAY == 3 && $menu_title){
					$link_menu[]= $ns -> tablerender(PRELINKTITLE.$menu_title.POSTLINKTITLE,$menu_text,"",TRUE);
					$menu_title="";
					$menu_text="";
				}
			} else {
				if(LINKDISPLAY == 3){$menu_main .= $_link.LINKEND;	}
			}
		}
		
	}
	$text .= POSTLINK;
	// Added for grafx plugin
	if($pref['gxhz_but']){
		require(e_PLUGIN."grafxheadz/gxhzslm2.php");
	}
	if(LINKDISPLAY == 2){
		$ns = new e107table;
		$ns -> tablerender(LAN_183, $text);
	} else {
		if(LINKDISPLAY != 3) {echo $text;}
	}
	if(LINKDISPLAY == 3){
		$ns -> tablerender(LAN_183,$menu_main);
		foreach($link_menu as $m){
			echo $m;
		}
	}

	if($pref['cachestatus']){
		$aj = new textparse;
		$cache = $aj -> formtpa(ob_get_contents(), "admin");
		set_cache("sitelinks", $cache);
	}


}
?>