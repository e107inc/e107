<?php
/*
+---------------------------------------------------------------+
|     e107 website system
|     /sitelinks_class.php
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/sitelinks_class.php,v $
|     $Revision: 1.19 $
|     $Date: 2004-12-18 16:31:47 $
|     $Author: mcfly_e107 $
+---------------------------------------------------------------+
*/

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitelinks.php");
@include_once(e_LANGUAGEDIR."English/lan_sitelinks.php");

/**
* @return void
* @desc Outputs sitelinks
*/

class sitelinks {
	var $linkadd;
	
	function sitelinks() {
		if (defined('LINKCLASS')) {
			$this->linkadd = ' class="'.LINKCLASS.'" ';
		} else {
			$this->linkadd = '';
		}
	}

	function get() {
		global $pref, $ns, $tp, $e107cache;

		if ($data = $e107cache->retrieve('sitelinks')) {
			return $data;
		}

		if (LINKDISPLAY == 4) {
			require_once(e_PLUGIN.'ypslide_menu/ypslide_menu.php');
			return;
		}
		define('PRELINKTITLE', '');
		define('POSTLINKTITLE', '');

		$menu_count = 0;
		$text = PRELINK;
		$main_links = $this->getLinks("link_category='1' && link_name NOT REGEXP('submenu') ORDER BY link_order ASC");
		$sub_links = $this->getLinks("link_category='1' && link_name REGEXP('submenu') ORDER BY link_order ASC");
		foreach ($sub_links as $sub) {
			$mainName = substr($sub['link_name'],0,strpos($sub['link_name'],'.submenu.'));
			$submenu[$mainName][]=$sub;
		}

		if (LINKDISPLAY != 3) {
			foreach ($main_links as $link) {
				$text .= $this->makeLink($link);
				$main_linkname = $link['link_name'];
				if (is_array($submenu[$main_linkname])) {
					foreach ($submenu[$main_linkname] as $sub) {
						$text .= $this->makeLink($sub,TRUE);
					}
				}
			}
			$text .= POSTLINK;
			if (LINKDISPLAY == 2) {
				$text = $ns->tablerender(LAN_183, $text, 'sitelinks', TRUE);
			}
		} else {
			foreach($main_links as $link) {
				if (!count($submenu[$link['link_name']])) {
					$text .= $this->makeLink($link);
				}
				$text .= POSTLINK;
			}
			$text = $ns->tablerender(LAN_183, $text, 'sitelinks_main', TRUE);
			foreach(array_keys($submenu) as $k) {
				$mnu = PRELINK;
				foreach($submenu[$k] as $link) {
					$mnu .= $this->makeLink($link,TRUE);
				}
				$mnu .= POSTLINK;
				$text .= $ns->tablerender($k, $mnu, 'sitelinks_sub', TRUE);
			}
		}		
		$e107cache->set('sitelinks', $text);
		return $text;
	}

	function makeLink($linkInfo,$submenu=FALSE) {
		global $pref;
		if (!check_class($linkInfo['link_class'])) {
			return '';
		}
		$indent = ($submenu == TRUE && LINKDISPLAY != 3) ? "&nbsp;&nbsp;" : "";
		if (!preg_match('#(http:|mailto:|ftp:)#', $linkInfo['link_url'])) {
			$linkInfo['link_url'] = e_BASE.$linkInfo['link_url'];
		}
		if ($submenu == TRUE) {
			$linkInfo['link_name'] = substr($linkInfo['link_name'],strpos($linkInfo['link_name'],'.submenu.')+9);
		}

		$linkstart = ($linkInfo['link_button'] ? preg_replace('/\<img.*\>/si', '', LINKSTART) : LINKSTART);
		$link_append='';

		if ($linkInfo['link_open'] == 1) {
			$link_append = "rel='external'";
		}
		if ($linkInfo['link_open'] == 4) {
			$_link = $linkstart.$indent.($linkInfo['link_button'] ? '<img src="'.e_IMAGE."link_icons/{$linkInfo['link_button']}\" alt='' style='vertical-align:middle' /> " : '').($linkInfo['link_url'] ? '<a'.$linkadd.((in_array('linkpage_screentip',$pref) && $pref['linkpage_screentip']) ? " title = '{$linkInfo['link_description']}' " : "")." href=\"javascript:open_window('".$linkInfo['link_url']."')\">".$linkInfo['link_name'].'</a>' : $linkInfo['link_name'])."\n";
		} else {
			$_link = $linkstart.$indent.($linkInfo['link_button'] ? '<img src="'.e_IMAGE."link_icons/{$linkInfo['link_button']}\" alt='' style='vertical-align:middle' /> " : '').($linkInfo['link_url'] ? '<a'.$linkadd.((in_array('linkpage_screentip',$pref) && $pref['linkpage_screentip']) ? " title = '{$linkInfo['link_description']}' " : "")." href=\"".$linkInfo['link_url']."\"".$link_append.">".$linkInfo['link_name'].'</a>' : $linkInfo['link_name'])."\n";
		}
		return $_link.LINKEND;
	}

	function getLinks($extra='1') {
		global $sql;
		$ret=array();
		if ($sql -> db_Select('links','*',$extra)) {
			while($row = $sql -> db_Fetch()) {
				$ret[]=$row;
			}
		}
		return $ret;
	}
}

?>
