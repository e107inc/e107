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

/**
* @return void
* @desc Outputs sitelinks
*/
function sitelinks() {
	global $pref, $ns, $tp, $sql, $sql2, $ml, $e107cache;

	if (!$data = $e107cache->retrieve('sitelinks')) {

		ob_start();
		
		if (!is_object($sql)) {
			$sql = new db;
		}
		if (!is_object($sql2)) {
			$sql2 = new db;
		}

		if (LINKDISPLAY == 4) {
			require_once(e_PLUGIN.'ypslide_menu/ypslide_menu.php');
			return;
		}
		define(PRELINKTITLE, '');
		define(POSTLINKTITLE, '');
		$menu_count = 0;
		$text = PRELINK;
		if (defined('LINKCLASS')) {
			$linkadd = ' class="'.LINKCLASS.'" ';
		} else {
			$linkadd = '';
		}
		
		$sql->db_Select('links', '*', "link_category='1' && link_name NOT REGEXP('submenu') ORDER BY link_order ASC");
		while ($row = $sql->db_Fetch()) {
			extract($row);
			if (!$link_class || check_class($link_class)) {
				if (!preg_match('#(http:|mailto:|ftp:)#', $link_url)) {
					$link_url = e_BASE.$link_url;
				}
				$linkstart = ($link_button ? preg_replace('/\<img.*\>/si', '', LINKSTART) : LINKSTART);
				$link_append='';
				if($link_open == 1) {
					$link_append = "rel='external'";
				}

//				switch ($link_open) {
//					case 1:
//					$link_append = ' rel=\'external\'';
//					break;
//					case 2:
//					$link_append = '';
//					break;
//					case 3:
//					$link_append = '';
//					break;
//					default:
//					unset($link_append);
//				}
				if ($link_open == 4) {
					$_link = $linkstart.($link_button ? '<img src="'.e_IMAGE."link_icons/$link_button\" alt='' style='vertical-align:middle' /> " : '').($link_url ? '<a'.$linkadd.((in_array('linkpage_screentip',$pref) && $pref['linkpage_screentip']) ? " title = '$link_description' " : "")." href=\"javascript:open_window('".$link_url."')\">".$link_name.'</a>' : $link_name)."\n";
				} else {
					$_link = $linkstart.($link_button ? '<img src="'.e_IMAGE."link_icons/$link_button\" alt='' style='vertical-align:middle' /> " : '').($link_url ? '<a'.$linkadd.((in_array('linkpage_screentip',$pref) && $pref['linkpage_screentip']) ? " title = '$link_description' " : "")." href=\"".$link_url."\"".$link_append.">".$link_name.'</a>' : $link_name)."\n";
				}
				if (LINKDISPLAY == 3) {
					$menu_title = $link_name;
				} else {
					$text .= $_link.LINKEND;
				}
				if ($sql2->db_Select('links', "*", "link_name REGEXP('submenu.".$link_name."') ORDER BY link_order ASC") && !HIDESUBSECTIONS) {
					$menu_count++;
					$main_linkname = $link_name;
					while ($row = $sql2->db_Fetch()) {
						extract($row);
						$link_name = str_replace('submenu.'.$main_linkname.'.', '', $link_name);
						if (check_class($link_class)) {
							$linkstart = ($link_button ? preg_replace('/\<img.*\>/si', '', LINKSTART)." " : LINKSTART);
							$link_append='';
							if($link_open == 1) {
								$link_append = "rel='external'";
							}
							if (!preg_match('#(http:|mailto:|ftp:)#', $link_url)) {
								$link_url = e_BASE.$link_url;
							}
							$indent = (LINKDISPLAY == 3) ? "" :
							"&nbsp;&nbsp;";
							if ($link_open == 4) {
								$_link = $linkstart.$indent.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"javascript:open_window('".$link_url."')\">".$link_name."</a>".LINKEND."\n";
							} else {
								$_link = $linkstart.$indent.($link_button ? "<img src='".e_IMAGE."link_icons/$link_button' alt='' style='vertical-align:middle' /> " : "")."<a".$linkadd." href=\"".$link_url."\"".$link_append.">".$link_name."</a>".LINKEND."\n";
							}
							if (LINKDISPLAY == 3) {
								$menu_text .= $_link;
							} else {
								$text .= $_link;
							}
						}
					}
					if (LINKDISPLAY == 3 && $menu_title) {
						$link_menu[] = $ns->tablerender(PRELINKTITLE.$menu_title.POSTLINKTITLE, $menu_text, 'sitelinks', TRUE);
						$menu_title = "";
						$menu_text = "";
					}
				} else {
					if (LINKDISPLAY == 3) {
						$menu_main .= $_link.LINKEND;
					}
				}
			}
		}
		$text .= POSTLINK;
		$text = $tp->toHTML($text, TRUE, 'nobreak');
		if (LINKDISPLAY == 2) {
			$ns = new e107table;
			$ns->tablerender(LAN_183, $text, 'sitelinks');
		} else {
			if (LINKDISPLAY != 3) {
				echo $text;
			}
		}
		if (LINKDISPLAY == 3) {
			$ns->tablerender(LAN_183, $menu_main, 'sitelinks');
			foreach($link_menu as $m) {
				echo $m;
			}
		}
		
		$data = ob_get_contents();
		ob_end_clean();
		
		$e107cache->set('sitelinks', $data);
	}
	echo $data;
}

?>
