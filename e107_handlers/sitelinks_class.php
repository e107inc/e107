<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /sitelinks_class.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitelinks.php");
@include(e_LANGUAGEDIR."English/lan_sitelinks.php");
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function sitelinks()
{
	/*
	# Render style links
	# - parameters    	none
	# - return        	parsed text
	# - scope          	null
	*/
	global $pref,$ns, $tp, $sql, $sql2, $ml;
	if(!is_object($sql)){$sql = new db;}
  if(!is_object($sql2)){$sql2 = new db;}
	if($cache = retrieve_cache("sitelinks"))
	{
		echo $tp -> toHTML($cache,TRUE,'nobreak');
		return;
	}
	ob_start();

	if(LINKDISPLAY == 4)
	{
		require_once(e_PLUGIN."ypslide_menu/ypslide_menu.php");
		return;
	}

	define(PRELINKTITLE, "");
	define(POSTLINKTITLE, "");
	$menu_count=0;
	$text = PRELINK;
	if(defined("LINKCLASS"))
	{
		$linkadd = " class='".LINKCLASS."' ";
	}
	/*
	if(ADMIN == TRUE){
	$linkstart = (file_exists(e_IMAGE."link_icons/admin.png") ? preg_replace("/\<img.*\>/si", "", LINKSTART)." " : LINKSTART);
	if(LINKDISPLAY != 3) {
	$text .= $linkstart.(file_exists(e_IMAGE."link_icons/admin.png") ? "<img src='".e_IMAGE."link_icons/admin.png' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"".e_ADMIN.(!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php")."\">".LAN_502."</a>".LINKEND."\n";
	} else {
	$menu_main .= $linkstart.(file_exists(e_IMAGE."link_icons/admin.png") ? "<img src='".e_IMAGE."link_icons/admin.png' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"".e_ADMIN.(!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php")."\">".LAN_502."</a>".LINKEND."\n";
	}
	}
	*/

	if(e_MLANG == 1){
    $ml -> e107_ml_Select("links", "*", "link_category='1' && link_name NOT REGEXP('submenu') ORDER BY link_order ASC");
  }else{
    $sql -> db_Select("links", "*", "link_category='1' && link_name NOT REGEXP('submenu') ORDER BY link_order ASC");
	}
	$tmp_ok = 0;
	if(e_MLANG && $ml -> e107_ml_Select("links", 'link_name', "link_name LIKE 'submenu.%' ", "default", FALSE, "sql2") ){
    $tmp_ok = 1;
  }else if($sql2 -> db_Select("links", 'link_name', "link_name LIKE 'submenu.%' ")){
    $tmp_ok = 1;
  }
	if($tmp_ok == 1)
	{
		while($row = $sql2 -> db_Fetch())
		{
			$sub_list[]=$row['link_name'];
		}
		$submenu_list=implode(",",$sub_list);
	}

	while($row = $sql -> db_Fetch())
	{
		extract($row);
		if(!$link_class || check_class($link_class))
		{
			if(!preg_match("#(http:|mailto:|ftp:)#",$link_url)){ $link_url = e_BASE.$link_url; }
			$linkstart = ($link_button ? preg_replace("/\<img.*\>/si", "", LINKSTART) : LINKSTART);
			switch ($link_open)
			{
				case 1:
				$link_append = " rel='external'";
				break;
				case 2:
				$link_append = "";
				break;
				case 3:
				$link_append = "";
				break;
				default:
				unset($link_append);
			}

			if($link_open == 4)
			{
				$_link =  $linkstart.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "").($link_url ? "<a".$linkadd.($pref['linkpage_screentip'] ? " title = '$link_description' " : "")." href=\"javascript:open_window('".$link_url."')\">".$link_name."</a>" : $link_name)."\n";
			}
			else
			{
				$_link =  $linkstart.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "").($link_url ? "<a".$linkadd.($pref['linkpage_screentip'] ? " title = '$link_description' " : "")." href=\"".$link_url."\"".$link_append.">".$link_name."</a>" : $link_name)."\n";
			}
			if(LINKDISPLAY == 3)
			{
				$menu_title=$link_name;
			}
			else
			{
				$text .= $_link.LINKEND;
			}

			if(strpos($submenu_list,"submenu.$link_name") !== FALSE)
			{
				$tmp_ok = 0;
        if($ml -> e107_ml_Select("links", "*", "link_name REGEXP('submenu.".$link_name."') ORDER BY link_order ASC", "default", FALSE, "sql2") && HIDESUBSECTIONS !== TRUE){
          $tmp_ok = 1;
        }else if($sql2 -> db_Select("links", "*", "link_name REGEXP('submenu.".$link_name."') ORDER BY link_order ASC") && HIDESUBSECTIONS !== TRUE){
          $tmp_ok = 1;
        }
        
        if($tmp_ok == 1)
				{
					$menu_count++;
					$main_linkname = $link_name;
					while($row = $sql2 -> db_Fetch())
					{
						extract($row);
						$link_name = str_replace("submenu.".$main_linkname.".", "", $link_name);
						// if(!$link_class || check_class($link_class) || ($link_class==254 && USER))
            if(check_class($link_class))
						{
							$linkstart = ($link_button ? preg_replace("/\<img.*\>/si", "", LINKSTART)." " : LINKSTART);
							switch ($link_open)
							{
								case 1:
								$link_append = "rel='external'";

								break;
								case 2:
								$link_append = "";
								break;
								case 3:
								$link_append = "";
								break;
								default:
								unset($link_append);
							}

							if(!preg_match("#(http:|mailto:|ftp:)#",$link_url)){ $link_url = e_BASE.$link_url; }
							$indent=(LINKDISPLAY == 3) ? "" : "&nbsp;&nbsp;";
							if($link_open == 4)
							{
								$_link =  $linkstart.$indent.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"javascript:open_window('".$link_url."')\">".$link_name."</a>".LINKEND."\n";
							}
							else
							{
								$_link =  $linkstart.$indent.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"".$link_url."\"".$link_append.">".$link_name."</a>".LINKEND."\n";
							}
							if(LINKDISPLAY == 3)
							{
								$menu_text .= $_link;
							}
							else
							{
								$text .= $_link;
							}
						}
					}
					if(LINKDISPLAY == 3 && $menu_title)
					{
						$link_menu[]= $ns -> tablerender(PRELINKTITLE.$menu_title.POSTLINKTITLE,$menu_text,"",TRUE);
						$menu_title="";
						$menu_text="";
					}
				}
				else
				{
					if(LINKDISPLAY == 3){$menu_main .= $_link.LINKEND;        }
				}
			}
		}

	}
	$text .= POSTLINK;

	$text = $tp -> toHTML($text,TRUE,'nobreak');

	if(LINKDISPLAY == 2)
	{
		$ns = new e107table;
		$ns -> tablerender(LAN_183, $text);
	}
	else
	{
		if(LINKDISPLAY != 3) {echo $text;}
	}
	if(LINKDISPLAY == 3)
	{
		$ns -> tablerender(LAN_183,$menu_main);
		foreach($link_menu as $m)
		{
			echo $m;
		}
	}

	if($pref['cachestatus'])
	{
		$cache = ob_get_contents();
		set_cache("sitelinks", $cache);
	}


}
?>
