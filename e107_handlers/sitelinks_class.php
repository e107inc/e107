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
|     $Revision: 1.33 $
|     $Date: 2005-03-12 09:42:59 $
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

	var $eLinkList;

	function getlinks()
	{
		global $sql;
		if ($sql->db_Select('links', '*', "link_category = 1 and link_class IN (".USERCLASS_LIST.") ORDER BY link_order ASC"))
		{
			while ($row = $sql->db_Fetch())
			{
				if (substr($row['link_name'], 0, 8) == 'submenu.')
				{
					$tmp=explode('.', $row['link_name'], 3);
					$this->eLinkList[$tmp[1]][]=$row;
				}
				else
				{
					$this->eLinkList['head_menu'][] = $row;
				}
			}
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

		$this->getlinks();

		if(!defined('PRELINKTITLE'))
		{
			define('PRELINKTITLE', '');
		}
		if(!defined('PRELINKTITLE'))
		{
			define('POSTLINKTITLE', '');
		}

		$menu_count = 0;
		$text = PRELINK;

		if (LINKDISPLAY != 3) {
			foreach ($this->eLinkList['head_menu'] as $link) {
				$text .= $this->makeLink($link);
				/*
				$main_linkname = $link['link_name'];
				if (isset($this->eLinkList[$main_linkname]) && is_array($this->eLinkList[$main_linkname])) {
					foreach ($this->eLinkList[$main_linkname] as $sub) {
						$text .= $this->makeLink($sub, TRUE);
					}
				}
				*/
			}
			$text .= POSTLINK;
			if (LINKDISPLAY == 2) {
				$text = $ns->tablerender(LAN_183, $text, 'sitelinks', TRUE);
			}
		} else {
			foreach($this->eLinkList['head_menu'] as $link) {
				if (!count($this->eLinkList[$link['link_name']])) {
					$text .= $this->makeLink($link);
				}
				$text .= POSTLINK;
			}
			$text = $ns->tablerender(LAN_183, $text, 'sitelinks_main', TRUE);
			foreach(array_keys($this->eLinkList) as $k) {
				$mnu = PRELINK;
				foreach($this->eLinkList[$k] as $link) {
					if ($k != 'head_menu') {
						$mnu .= $this->makeLink($link, TRUE);
					}
				}
				$mnu .= POSTLINK;
				$text .= $ns->tablerender($k, $mnu, 'sitelinks_sub', TRUE);
			}
		}
		$e107cache->set('sitelinks', $text);
		return $text;
	}

	function makeLink($linkInfo, $submenu = FALSE) {
		global $pref;
		if (!preg_match('#(http:|mailto:|ftp:)#', $linkInfo['link_url'])) {
			$linkInfo['link_url'] = e_BASE.$linkInfo['link_url'];
		}
		if ($submenu == TRUE) {
			$tmp = explode('.', $linkInfo['link_name'], 3);
			$linkInfo['link_name'] = $tmp[2];
		}

		if (hilite($linkInfo['link_url'])== TRUE){
			$_link = $linkInfo['link_button'] ? preg_replace('/\<img.*\>/si', '', LINKSTART_HILITE) :  LINKSTART_HILITE;
		}else{
			$_link = $linkInfo['link_button'] ? preg_replace('/\<img.*\>/si', '', LINKSTART) :  LINKSTART;
		}



		$_link .= $linkInfo['link_button'] ? "<img src='".e_IMAGE."icons/".$linkInfo['link_button']."' alt='' style='vertical-align:middle' />" : "";
		$_link .= ($submenu == TRUE && LINKDISPLAY != 3) ? "&nbsp;&nbsp;" : "";

		if ($linkInfo['link_url']) {
			$linkadd = defined('LINKCLASS') ? " class='".LINKCLASS."'" : "";
			$screentip = '';
			if(isset($pref['linkpage_screentip']) && $pref['linkpage_screentip'] && $linkInfo['link_description'])
			{
				$screentip = " title = '".$linkInfo['link_description']."'";
			}

//			$screentip = ($pref['linkpage_screentip'] && $linkInfo['link_description']) ? " title = '".$linkInfo['link_description']."'" : "";
			$href = ($linkInfo['link_open'] == 4) ? " href=\"javascript:open_window('".$linkInfo['link_url']."')\"" : " href='".$linkInfo['link_url']."'";
			$link_append = ($linkInfo['link_open'] == 1) ? " rel='external'" : "";

			$_link .= "<a".$linkadd.$screentip.$href.$link_append.">".$linkInfo['link_name']."</a>\n";
		} else {
			$_link .= $linkInfo['link_name'];
		}

		return $_link.LINKEND;
	}
}

function hilite($link){

	global $PLUGINS_DIRECTORY;

	if(!defined("LINKSTART_HILITE")){ return FALSE; }

// --------------- highlighting for plugins. ----------------
	if(eregi($PLUGINS_DIRECTORY,$link)){
		$link = str_replace("../","",$link);
		if(eregi(dirname($link),dirname(e_SELF))){
			return TRUE;
		}
	}
// --------------- highlight for news items.----------------
// eg. news.php?list.1 or news.php?cat.2 etc

	if(eregi("news.php",$link)){
		if(eregi("list",$link) && eregi("list",e_QUERY)){
			$tmp = str_replace("php?","",explode(".",$link));
			$tmp2 = explode(".",e_QUERY);
			if($tmp[1] == $tmp2[1] && $tmp[2]==$tmp2[2]){ return TRUE; }
		}

		if((eregi("cat",$link) || eregi("list",$link)) && eregi("item",e_QUERY)){
			$tmp = str_replace("php?","",explode(".",$link));
			$tmp2 = explode(".",e_QUERY);
			if($tmp[2]==$tmp2[2]){ return TRUE; }
		}
	}

// --------------- highlight default ----------------
	if(eregi("\?",$link)){
		if(defined("LINKSTART_HILITE") && (strpos(e_SELF."?".e_QUERY, str_replace("../","","/".$link)) !== FALSE)){
			return TRUE;
		}
	}

	if(!eregi("all",e_QUERY) && !eregi("item",e_QUERY) && !eregi("cat",e_QUERY) && !eregi("list",e_QUERY) && defined("LINKSTART_HILITE") && (strpos(e_SELF, str_replace("../","","/".$link)) !== FALSE)){
		return TRUE;
	}

	return FALSE;
}
?>