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
|     $Revision: 1.52 $
|     $Date: 2005-06-05 16:19:46 $
|     $Author: streaky $
+---------------------------------------------------------------+
*/

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitelinks.php");
@include_once(e_LANGUAGEDIR."English/lan_sitelinks.php");

/**
* @return void
* @desc Outputs sitelinks
*/

class sitelinks {
	var $eLinkList;
	function getlinks($cat=1) {
		global $sql;
		if ($sql->db_Select('links', '*', "link_category = $cat and link_class IN (".USERCLASS_LIST.") ORDER BY link_order ASC")){
			while ($row = $sql->db_Fetch()) {
				if (substr($row['link_name'], 0, 8) == 'submenu.') {
					$tmp=explode('.', $row['link_name'], 3);
					$this->eLinkList[$tmp[1]][]=$row;
				} else {
					$this->eLinkList['head_menu'][] = $row;
				}
			}
		}
	}

	function get($cat=1,$style='') {
		global $pref, $ns, $tp, $e107cache;
		if ($data = $e107cache->retrieve('sitelinks')) {
			return $data;
		}
		if (LINKDISPLAY == 4) {
			require_once(e_PLUGIN.'ypslide_menu/ypslide_menu.php');
			return;
		}
		$this->getlinks($cat);
		// are these defines used at all ?
		if(!defined('PRELINKTITLE')) {
			define('PRELINKTITLE', '');
		}
		if(!defined('PRELINKTITLE')) {
			define('POSTLINKTITLE', '');
		}

		if(!$style){
			$style['prelink'] = PRELINK;
			$style['linkdisplay'] = LINKDISPLAY;
			$style['postlink'] = POSTLINK;
			$style['linkclass'] = defined('LINKCLASS') ? LINKCLASS : "";
			$style['linkclass_hilite'] = defined('LINKCLASS_HILITE') ? LINKCLASS_HILITE : "";
			$style['linkstart_hilite'] = defined('LINKSTART_HILITE') ? LINKSTART_HILITE : "";
			$style['linkstart'] = LINKSTART;
			$style['linkdisplay'] = LINKDISPLAY;
			$style['linkend'] = LINKEND;
		}

		$menu_count = 0;
		$text = $style['prelink'];
		if ($style['linkdisplay'] != 3) {
			foreach ($this->eLinkList['head_menu'] as $link) {
				$main_linkname = $link['link_name'];
				$link['link_expand'] = (isset($pref['sitelinks_expandsub']) && isset($this->eLinkList[$main_linkname]) && is_array($this->eLinkList[$main_linkname])) ?  TRUE : FALSE;
				$text .= $this->makeLink($link, '', $style);
				// if there's a submenu. :
				if (isset($this->eLinkList[$main_linkname]) && is_array($this->eLinkList[$main_linkname])) {
					$substyle = (eregi($link['link_url'],e_SELF) || eregi($main_linkname,e_SELF) || $link['link_expand'] == FALSE) ? "visible" : "none";   // expanding sub-menus.
					$text .= "\n\n<div id='sub_".$main_linkname."' style='display:$substyle'>\n";
					foreach ($this->eLinkList[$main_linkname] as $sub) {
						$text .= $this->makeLink($sub, TRUE, $style);
					}
					$text .= "\n</div>\n";
				}
			}
			$text .= $style['postlink'];
			if ($style['linkdisplay'] == 2) {
				$text = $ns->tablerender(LAN_183, $text, 'sitelinks', TRUE);
			}
		} else {
			foreach($this->eLinkList['head_menu'] as $link) {
				if (!count($this->eLinkList[$link['link_name']])) {
					$text .= $this->makeLink($link,'', $style);
				}
				$text .= $style['postlink'];
			}
			$text = $ns->tablerender(LAN_183, $text, 'sitelinks_main', TRUE);
			foreach(array_keys($this->eLinkList) as $k) {
				$mnu = $style['prelink'];
				foreach($this->eLinkList[$k] as $link) {
					if ($k != 'head_menu') {
						$mnu .= $this->makeLink($link, TRUE, $style);
					}
				}
				$mnu .= $style['postlink'];
				$text .= $ns->tablerender($k, $mnu, 'sitelinks_sub', TRUE);
			}
		}
		$e107cache->set('sitelinks', $text);
		return $text;
	}

	function makeLink($linkInfo, $submenu = FALSE, $style='') {
		global $pref,$tp, $e107;

		// Start with an empty link
		$linkstart = $indent = $linkadd = $screentip = $href = $link_append = '';

		// If submenu: Fix Name, Add Indentation.
		if ($submenu == TRUE) {
			$tmp = explode('.', $linkInfo['link_name'], 3);
			$linkInfo['link_name'] = $tmp[2];
			$indent = ($style['linkdisplay'] != 3) ? "&nbsp;&nbsp;" : "";
		}

		// By default links are not highlighted.
		$linkstart = $style['linkstart'];
		$linkadd = ($style['linkclass']) ? " class='".$style['linkclass']."'" : "";

		// Check for screentip regardless of URL.
		if (isset($pref['linkpage_screentip']) && $pref['linkpage_screentip'] && $linkInfo['link_description']) {
			$screentip = " title = '".$linkInfo['link_description']."'";
		}

		// Check if its expandable first. It should override its URL.
		if (isset($linkInfo['link_expand']) && $linkInfo['link_expand']){
			$href = " href=\"javascript: expandit('sub_".$linkInfo['link_name']."')\"";
		} elseif ($linkInfo['link_url']){

			// Only add the $e107->server_path if it's an in-site relative path - fixes varuos issues (streaky).
			if (!preg_match('#(http:|mailto:|ftp:)#', $linkInfo['link_url'])) {
				$linkInfo['link_url'] = $e107->server_path.$linkInfo['link_url'];
			}

			// Only check if its highlighted if it has an URL
			if ($this->hilite($linkInfo['link_url'], $style['linkstart_hilite'])== TRUE) {
				$linkstart = $style['linkstart_hilite'];
			}
			if ($this->hilite($linkInfo['link_url'], $style['linkclass_hilite'])== TRUE) {
				$linkadd = " class='".$style['linkclass_hilite']."'";
			}

			if ($linkInfo['link_open'] == 4 || $linkInfo['link_open'] == 5) {
				$dimen = ($linkInfo['link_open'] == 4) ? "600,400" : "800,600";
				$href = " href=\"javascript:open_window('".$linkInfo['link_url']."', {$dimen})\"";
			} else {
				$href = " href='".$linkInfo['link_url']."'";
			}

			// open the link in a new window
			$link_append = ($linkInfo['link_open'] == 1) ? " rel='external'" : "";
		}

		// Remove default images if its a button and add new image at the start.
		if ($linkInfo['link_button']){
			$linkstart = preg_replace('/\<img.*\>/si', '', $linkstart);
			$linkstart .= "<img src='".e_IMAGE."icons/".$linkInfo['link_button']."' alt='' style='vertical-align:middle' />";
		}

		// If its a link.. make a link
		$_link = "";
		if (!empty($href)){
			$_link .= "<a".$linkadd.$screentip.$href.$link_append.">".$tp->toHTML($linkInfo['link_name'],"","emotes_off defs")."</a>";
		// If its not a link, but has a class or screentip do span:
		} elseif (!empty($linkadd) || !empty($screentip)){
			$_link .= "<span".$linkadd.$screentip.">".$tp->toHTML($linkInfo['link_name'],"","emotes_off defs")."</span>";
			// Else just the name:
		} else {
			$_link .= $tp->toHTML($linkInfo['link_name'],"","emotes_off defs");
		}

		$_link = $linkstart.$indent.$_link;

		return $_link.$style['linkend'];
	}

	function hilite($link,$enabled='') {

		global $PLUGINS_DIRECTORY,$tp;

		if(!$enabled){
			return true;
		}

		// --------------- highlighting for plugins. ----------------
		if(eregi($PLUGINS_DIRECTORY, $link) && !eregi("custompages", $link)) {
			if(str_replace("?","",$link)){
				if(strpos(e_SELF."?".e_QUERY, str_replace("../", "", "/".$link))) {
					return true;
				}else{
					return false;
				}
			}
			$link = str_replace("../", "", $link);
			if(eregi(dirname($link), dirname(e_SELF))){
				return true;
			}
		}
		// --------------- highlight for news items.----------------
		// eg. news.php?list.1 or news.php?cat.2 etc

		if(eregi("news.php",$link)){
			if(eregi("list", $link) && eregi("list", e_QUERY)){
				$tmp = str_replace("php?", "", explode(".",$link));
				$tmp2 = explode(".", e_QUERY);
				if($tmp[1] == $tmp2[1] && $tmp[2] == $tmp2[2]){
					return true;
				}
			}

			if((eregi("cat", $link) || eregi("list", $link)) && eregi("item", e_QUERY))	{
				$tmp = str_replace("php?", "", explode(".",$link));
				$tmp2 = explode(".", e_QUERY);
				if($tmp[2] == $tmp2[2]) {
					return true;
				}
			}
		}

		// --------------- highlight default ----------------
		if(eregi("\?", $link)) {
			if(($enabled) && (strpos(e_SELF."?".e_QUERY, str_replace("../", "", "/".$link)) !== false))	{
				return true;
			}
		}
		if(!eregi("all", e_QUERY) && !eregi("item", e_QUERY) && !eregi("cat",e_QUERY) && !eregi("list", e_QUERY) && $enabled && (strpos(e_SELF, str_replace("../", "", "/".$link)) !== false)){
			return true;
		}
		return false;
	}
}
?>