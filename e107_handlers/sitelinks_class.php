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
|     $Revision: 1.21 $
|     $Date: 2005-01-22 15:21:01 $
|     $Author: sweetas $
+---------------------------------------------------------------+
*/

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitelinks.php");
@include_once(e_LANGUAGEDIR."English/lan_sitelinks.php");

/**
* @return void
* @desc Outputs sitelinks
*/

class sitelinks {
	function get() {
		global $pref, $ns, $tp, $e107cache, $eLinkList;

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
//		$main_links = $this->getLinks("link_category='1' && link_name NOT REGEXP('submenu') ORDER BY link_order ASC");
//		$sub_links = $this->getLinks("link_category='1' && link_name REGEXP('submenu') ORDER BY link_order ASC");
//		foreach ($sub_links as $sub) {
//			$mainName = substr($sub['link_name'],0,strpos($sub['link_name'],'.submenu.'));
//			$submenu[$mainName][]=$sub;
//		}

		if (LINKDISPLAY != 3) {
			foreach ($eLinkList['head_menu'] as $link) {
				$text .= $this->makeLink($link);
				$main_linkname = $link['link_name'];
				if (is_array($eLinkList[$main_linkname])) {
					foreach ($eLinkList[$main_linkname] as $sub) {
						$text .= $this->makeLink($sub,TRUE);
					}
				}
			}
			$text .= POSTLINK;
			if (LINKDISPLAY == 2) {
				$text = $ns->tablerender(LAN_183, $text, 'sitelinks', TRUE);
			}
		} else {
			foreach($eLinkList['head_menu'] as $link) {
				if (!count($eLinkList[$link['link_name']])) {
					$text .= $this->makeLink($link);
				}
				$text .= POSTLINK;
			}
			$text = $ns->tablerender(LAN_183, $text, 'sitelinks_main', TRUE);
			foreach(array_keys($eLinkList) as $k) {
				$mnu = PRELINK;
				foreach($eLinkList[$k] as $link) {
					if($k != 'head_menu') {
						$mnu .= $this->makeLink($link,TRUE);
					}
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
		if (!preg_match('#(http:|mailto:|ftp:)#', $linkInfo['link_url'])) {
			$linkInfo['link_url'] = e_BASE.$linkInfo['link_url'];
		}
		if ($submenu == TRUE) {
			$tmp = explode('.',$linkInfo['link_name'],3);
			$linkInfo['link_name'] = $tmp[2];
		}

		$_link = $linkInfo['link_button'] ? preg_replace('/\<img.*\>/si', '', LINKSTART) : LINKSTART;
		$_link .= $linkInfo['link_button'] ? "<img src='".e_IMAGE."link_icons/".$linkInfo['link_button']."' alt='' style='vertical-align:middle' />" : "";
		$_link .= ($submenu == TRUE && LINKDISPLAY != 3) ? "&nbsp;&nbsp;" : "";

		if ($linkInfo['link_url']) {
			$linkadd = defined('LINKCLASS') ? " class='".LINKCLASS."'" : "";
			$screentip = ($pref['linkpage_screentip'] && $linkInfo['link_description']) ? " title = '".$linkInfo['link_description']."'" : "";
			$href = ($linkInfo['link_open'] == 4) ? " href=\"javascript:open_window('".$linkInfo['link_url']."')\"" : " href='".$linkInfo['link_url']."'";
			$link_append = ($linkInfo['link_open'] == 1) ? " rel='external'" : "";
			
			$_link .= "<a".$linkadd.$screentip.$href.$link_append.">".$linkInfo['link_name']."</a>\n";
		} else {
			$_link .= $linkInfo['link_name'];
		}
		
		return $_link.LINKEND;
	}

//	function getLinks($extra='1') {
//		global $sql;
//		$ret=array();
//		if ($sql -> db_Select('links','*',$extra)) {
//			while($row = $sql -> db_Fetch()) {
//				$ret[]=$row;
//			}
//		}
//		return $ret;
//	}
}

?>
