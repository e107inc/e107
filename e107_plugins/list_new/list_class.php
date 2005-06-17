<?php
/*
+---------------------------------------------------------------+
|       e107 website system
|
|       ©Steve Dunstan 2001-2002
|       http://e107.org
|       jalist@e107.org
|
|       Released under the terms and conditions of the
|       GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/list_new/list_class.php,v $
|		$Revision: 1.1 $
|		$Date: 2005-06-17 13:35:39 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/
global $sql, $rc, $list_pref, $sc_style, $tp, $list_shortcodes, $defaultarray;

global $LIST_MENU, $LIST_MENU_START, $LIST_MENU_END, $LIST_PAGE_START, $LIST_PAGE, $LIST_PAGE_END, $LIST_NEW_START, $LIST_NEW, $LIST_NEW_END;
global $LIST_ICON, $LIST_DATE, $LIST_HEADING, $LIST_AUTHOR, $LIST_CATEGORY, $LIST_INFO;
global $LIST_DISPLAYSTYLE, $LIST_CAPTION, $LIST_STYLE_CAPTION, $LIST_STYLE_BODY;

$listplugindir = e_PLUGIN."list_new/";

//default sections (present in this list plugin)
$defaultarray = array("news", "comment", "download", "members");

//get language file
$lan_file = $listplugindir."languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : $listplugindir."languages/English.php");

require_once($listplugindir."list_template.php");

class listclass {

	function getListPrefs(){
		global $sql,$eArrayStorage;

		//check preferences from database
		$sql = new db;
		$num_rows = $sql -> db_Select("core", "*", "e107_name='list' ");
		$row = $sql -> db_Fetch();

		//insert default preferences
		if (empty($row['e107_value'])) {

			$this -> getSections();
			$list_pref = $this -> getDefaultPrefs();
			$tmp = $eArrayStorage->WriteArray($list_pref);

			$sql -> db_Insert("core", "'list', '$tmp' ");
			$sql -> db_Select("core", "*", "e107_name='list' ");
		}

		$list_pref = $eArrayStorage->ReadArray($row['e107_value']);
		return $list_pref;
	}

	function prepareSection($mode){
		global $list_pref;

		$len = strlen($mode) + 9;
		//get all sections to use
		foreach ($list_pref as $key => $value) {
			if(substr($key,-$len) == "_{$mode}_display" && $value == "1"){
				$sections[] = substr($key,0,-$len);
			}
		}

		return $sections;
	}

	function prepareSectionArray($mode){
		global $sections, $list_pref;

		//section reference
		for($i=0;$i<count($sections);$i++){
			if($list_pref["$sections[$i]_{$mode}_display"] == "1"){
				$arr[$sections[$i]][0] = $list_pref["$sections[$i]_{$mode}_caption"];
				$arr[$sections[$i]][1] = $list_pref["$sections[$i]_{$mode}_display"];
				$arr[$sections[$i]][2] = $list_pref["$sections[$i]_{$mode}_open"];
				$arr[$sections[$i]][3] = $list_pref["$sections[$i]_{$mode}_author"];
				$arr[$sections[$i]][4] = $list_pref["$sections[$i]_{$mode}_category"];
				$arr[$sections[$i]][5] = $list_pref["$sections[$i]_{$mode}_date"];
				$arr[$sections[$i]][6] = $list_pref["$sections[$i]_{$mode}_icon"];
				$arr[$sections[$i]][7] = $list_pref["$sections[$i]_{$mode}_amount"];
				$arr[$sections[$i]][8] = $list_pref["$sections[$i]_{$mode}_order"];
				$arr[$sections[$i]][9] = $sections[$i];
			}
		}
		//sort array on order values set in preferences
		usort($arr, create_function('$e,$f','return $e[8]==$f[8]?0:($e[8]>$f[8]?1:-1);'));

		return $arr;
	}

	function getDefaultSections(){
		global $sql, $sections, $titles, $defaultarray;

		//default always present sections
		for($i=0;$i<count($defaultarray);$i++){
			$sections[] = $defaultarray[$i];
			$titles[] = $defaultarray[$i];
		}
		return;
	}

	//content needs this to split each main parent into seperate sections
	function getContentSections($mode){
		global $sql, $sections, $titles, $content_types, $content_name;

		$datequery = " AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ";

		//get main parent types
		if($mainparents = $sql -> db_Select("pcontent", "*", "content_parent = '0' ".$datequery." ORDER BY content_heading")){
			while($row = $sql -> db_Fetch()){
				$content_types[] = "content_".$row['content_id'];
				$content_name = 'content';
				if($mode == "add"){
					$sections[] = "content_".$row['content_id'];
					$titles[] = $content_name." : ".$row['content_heading'];
				}
			}
		}		
		$content_types = array_unique($content_types);

		return;
	}

	function getSections(){
		global $sql, $sections, $titles;

		$this -> getDefaultSections();

		require_once(e_HANDLER."file_class.php");
		$fl = new e_file;
		$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*', '.bak');
		$iconlist = $fl->get_files(e_PLUGIN, "e_list\.php$", "standard", 1);
		foreach($iconlist as $icon){
			
			$tmp = explode("/", $icon['path']);
			$tmp = array_reverse($tmp);
			$icon['fname'] = $tmp[1];

			if($plugin_installed = $sql -> db_Select("plugin", "*", "plugin_path = '".$icon['fname']."' AND plugin_installflag = '1' ")){

				if($icon['fname'] == "content"){
					$this -> getContentSections("add");
				}else{
					$sections[] = $icon['fname'];
					$titles[] = $icon['fname'];
				}
			}
		}
		return;
	}

	function getDefaultPrefs(){
		global $sql, $sections, $titles, $defaultarray, $content_types;

		//section preferences
		for($i=0;$i<count($sections);$i++){
			if(!in_array($sections[$i], $defaultarray)){
				if(!in_array($sections[$i], $content_types)){
					if($plugin_installed = $sql -> db_Select("plugin", "*", "plugin_path = '".$sections[$i]."' AND plugin_installflag = '1' ")){
						$list_pref["$sections[$i]_recent_menu_caption"]	= $sections[$i];
						$list_pref["$sections[$i]_recent_page_caption"]	= $sections[$i];
						$list_pref["$sections[$i]_new_menu_caption"]	= $sections[$i];
						$list_pref["$sections[$i]_new_page_caption"]	= $sections[$i];
					}
				}else{
					$list_pref["$sections[$i]_recent_menu_caption"]	= $titles[$i];
					$list_pref["$sections[$i]_recent_page_caption"]	= $titles[$i];
					$list_pref["$sections[$i]_new_menu_caption"]	= $titles[$i];
					$list_pref["$sections[$i]_new_page_caption"]	= $titles[$i];
				}
			}else{
				$list_pref["$sections[$i]_recent_menu_caption"]	= $sections[$i];
				$list_pref["$sections[$i]_recent_page_caption"]	= $sections[$i];
				$list_pref["$sections[$i]_new_menu_caption"]	= $sections[$i];
				$list_pref["$sections[$i]_new_page_caption"]	= $sections[$i];
			}

			$list_pref["$sections[$i]_recent_menu_display"]		= "1";
			$list_pref["$sections[$i]_recent_menu_open"]		= "0";
			$list_pref["$sections[$i]_recent_menu_author"]		= "0";
			$list_pref["$sections[$i]_recent_menu_category"]	= "0";
			$list_pref["$sections[$i]_recent_menu_date"]		= "1";
			$list_pref["$sections[$i]_recent_menu_amount"]		= "5";

			$list_pref["$sections[$i]_recent_page_display"]		= "1";
			$list_pref["$sections[$i]_recent_page_open"]		= "1";
			$list_pref["$sections[$i]_recent_page_author"]		= "1";
			$list_pref["$sections[$i]_recent_page_category"]	= "1";
			$list_pref["$sections[$i]_recent_page_date"]		= "1";
			$list_pref["$sections[$i]_recent_page_amount"]		= "10";

			$list_pref["$sections[$i]_new_menu_display"]		= "1";
			$list_pref["$sections[$i]_new_menu_open"]			= "0";
			$list_pref["$sections[$i]_new_menu_author"]			= "0";
			$list_pref["$sections[$i]_new_menu_category"]		= "0";
			$list_pref["$sections[$i]_new_menu_date"]			= "1";
			$list_pref["$sections[$i]_new_menu_amount"]			= "5";

			$list_pref["$sections[$i]_new_page_display"]		= "1";
			$list_pref["$sections[$i]_new_page_open"]			= "1";
			$list_pref["$sections[$i]_new_page_author"]			= "1";
			$list_pref["$sections[$i]_new_page_category"]		= "1";
			$list_pref["$sections[$i]_new_page_date"]			= "1";
			$list_pref["$sections[$i]_new_page_amount"]			= "10";

			$list_pref["$sections[$i]_icon"]					= "";
			$list_pref["$sections[$i]_order"]					= ($i+1);
			
		}
		
		//new menu preferences
		$list_pref['new_menu_caption']				= LIST_ADMIN_15;
		$list_pref['new_menu_icon_use']				= "1";
		$list_pref['new_menu_icon_default']			= "1";
		$list_pref['new_menu_char_heading']			= "20";
		$list_pref['new_menu_char_postfix']			= "...";
		$list_pref['new_menu_datestyle']			= "%d %b";
		$list_pref['new_menu_datestyletoday']		= "%H:%M";
		$list_pref['new_menu_showempty']			= "1";

		//new page preferences
		$list_pref['new_page_caption']				= LIST_ADMIN_15;
		$list_pref['new_page_icon_use']				= "1";
		$list_pref['new_page_icon_default']			= "1";
		$list_pref['new_page_char_heading']			= "";
		$list_pref['new_page_char_postfix']			= "";
		$list_pref['new_page_datestyle']			= "%d %b";
		$list_pref['new_page_datestyletoday']		= "%H:%M";
		$list_pref['new_page_showempty']			= "1";
		$list_pref['new_page_colomn']				= "1";
		$list_pref['new_page_welcometext']			= LIST_ADMIN_16;

		//recent menu preferences
		$list_pref['recent_menu_caption']			= LIST_ADMIN_14;
		$list_pref['recent_menu_icon_use']			= "1";
		$list_pref['recent_menu_icon_default']		= "1";
		$list_pref['recent_menu_char_heading']		= "20";
		$list_pref['recent_menu_char_postfix']		= "...";
		$list_pref['recent_menu_datestyle']			= "%d %b";
		$list_pref['recent_menu_datestyletoday']	= "%H:%M";
		$list_pref['recent_menu_showempty']			= "";

		//recent page preferences
		$list_pref['recent_page_caption']			= LIST_ADMIN_14;
		$list_pref['recent_page_icon_use']			= "1";
		$list_pref['recent_page_icon_default']		= "1";
		$list_pref['recent_page_char_heading']		= "";
		$list_pref['recent_page_char_postfix']		= "";
		$list_pref['recent_page_datestyle']			= "%d %b";
		$list_pref['recent_page_datestyletoday']	= "%H:%M";
		$list_pref['recent_page_showempty']			= "";
		$list_pref['recent_page_colomn']			= "1";
		$list_pref['recent_page_welcometext']		= LIST_ADMIN_13;

		return $list_pref;
	}

	function show_section_list($arr, $mode, $max=""){
		global $tp, $listplugindir, $list_shortcodes, $sql, $list_pref, $defaultarray, $content_types, $content_name;
		global $LIST_ICON, $LIST_DATE, $LIST_HEADING, $LIST_AUTHOR, $LIST_CATEGORY, $LIST_INFO;
		global $LIST_DISPLAYSTYLE, $LIST_CAPTION, $LIST_STYLE_CAPTION, $LIST_STYLE_BODY;
		global $LIST_PAGE_NEW, $LIST_PAGE_RECENT, $LIST_MENU_NEW, $LIST_MENU_RECENT, $LIST_PAGE_NEW_START, $LIST_PAGE_RECENT_START, $LIST_MENU_NEW_START, $LIST_MENU_RECENT_START, $LIST_PAGE_NEW_END, $LIST_PAGE_RECENT_END, $LIST_MENU_NEW_END, $LIST_MENU_RECENT_END;

		$menu_installed = $sql -> db_Select("menus", "*", "(menu_name = 'list_new_menu' || menu_name = 'list_recent_menu') AND menu_location != '0' AND menu_class REGEXP '".e_CLASS_REGEXP."' ");
		$LIST_DATA = "";

		$this -> getContentSections("");

		//require is needed here instead of require_once, since both the menu and the page could be visible at the same time
		if(in_array($arr[9], $content_types)){
			$file = $content_name;
			if(file_exists(e_PLUGIN.$file."/e_list.php")){
				global $contentmode;
				$contentmode = $arr[9];
				require(e_PLUGIN.$file."/e_list.php");
			}
		}else{
			$file = $arr[9];
			if(in_array($file, $defaultarray)){
				require($listplugindir."section/list_".$file.".php");
			}else{
				if(file_exists(e_PLUGIN.$file."/e_list.php")){
					require(e_PLUGIN.$file."/e_list.php");
				}
			}
		}
		$menutext = "";
		$start = "";
		$end = "";
		
		$LIST_ICON = "";
		$LIST_DATE = "";
		$LIST_HEADING = "";
		$LIST_AUTHOR = "";
		$LIST_CATEGORY = "";
		$LIST_INFO = "";

		if(is_array($LIST_DATA)){			//if it is an array, data exists and data is not empty
			for($i=0;$i<count($LIST_DATA[$mode]);$i++){				
				$LIST_ICON		= $LIST_DATA[$mode][$i][0];
				$LIST_HEADING	= $LIST_DATA[$mode][$i][1];
				$LIST_AUTHOR	= $LIST_DATA[$mode][$i][2];
				$LIST_CATEGORY	= $LIST_DATA[$mode][$i][3];
				$LIST_DATE		= $LIST_DATA[$mode][$i][4];
				$LIST_INFO		= $LIST_DATA[$mode][$i][5];
				
				if($mode == "recent_menu"){
					global $sc_style;
					$LIST_AUTHOR	= ($LIST_AUTHOR ? $sc_style['LIST_AUTHOR']['pre'].$LIST_AUTHOR.$sc_style['LIST_AUTHOR']['post'] : "");					
					$LIST_CATEGORY	= ($LIST_CATEGORY ? $sc_style['LIST_CATEGORY']['pre'].$LIST_CATEGORY.$sc_style['LIST_CATEGORY']['post'] : "");					
					$menutext .= preg_replace("/\{(.*?)\}/e", '$\1', $LIST_MENU_RECENT);
				
				}elseif($mode == "new_menu"){
					global $sc_style;
					$LIST_AUTHOR	= ($LIST_AUTHOR ? $sc_style['LIST_AUTHOR']['pre'].$LIST_AUTHOR.$sc_style['LIST_AUTHOR']['post'] : "");					
					$LIST_CATEGORY	= ($LIST_CATEGORY ? $sc_style['LIST_CATEGORY']['pre'].$LIST_CATEGORY.$sc_style['LIST_CATEGORY']['post'] : "");					
					$menutext .= preg_replace("/\{(.*?)\}/e", '$\1', $LIST_MENU_NEW);
				
				}elseif($mode == "recent_page"){
					$menutext .= $tp -> parseTemplate($LIST_PAGE_RECENT, FALSE, $list_shortcodes);
				
				}elseif($mode == "new_page"){
					$menutext .= $tp -> parseTemplate($LIST_PAGE_NEW, FALSE, $list_shortcodes);
				}
			}
		}elseif(!is_array($LIST_DATA) && $LIST_DATA != ""){
			$LIST_HEADING = $LIST_DATA;
			if($mode == "recent_menu"){
				if($list_pref[$mode."_showempty"]){
					$menutext .= preg_replace("/\{(.*?)\}/e", '$\1', $LIST_MENU_RECENT);
				}
			}elseif($mode == "new_menu"){
				if($list_pref[$mode."_showempty"]){
					$menutext .= preg_replace("/\{(.*?)\}/e", '$\1', $LIST_MENU_NEW);
				}
			}elseif($mode == "recent_page"){
				if($list_pref[$mode."_showempty"]){
					$menutext .= $tp -> parseTemplate($LIST_PAGE_RECENT, FALSE, $list_shortcodes);
				}
			}elseif($mode == "new_page"){
				if($list_pref[$mode."_showempty"]){
					$menutext .= $tp -> parseTemplate($LIST_PAGE_NEW, FALSE, $list_shortcodes);
				}

			}
		}


		if($LIST_DATA != ""){
			if($mode == "recent_menu"){
				if($list_pref[$mode."_showempty"] || $menutext){
					$start = preg_replace("/\{(.*?)\}/e", '$\1', $LIST_MENU_RECENT_START);
					$end = preg_replace("/\{(.*?)\}/e", '$\1', $LIST_MENU_RECENT_END);
				}
			
			}elseif($mode == "new_menu"){
				if($list_pref[$mode."_showempty"] || $menutext){
					$start = preg_replace("/\{(.*?)\}/e", '$\1', $LIST_MENU_NEW_START);
					$end = preg_replace("/\{(.*?)\}/e", '$\1', $LIST_MENU_NEW_END);
				}
			
			}elseif($mode == "recent_page"){
				if($list_pref[$mode."_showempty"] || $menutext){
					$start = preg_replace("/\{(.*?)\}/e", '$\1', $LIST_PAGE_RECENT_START);
					$end = preg_replace("/\{(.*?)\}/e", '$\1', $LIST_PAGE_RECENT_END);
				}

			}elseif($mode == "new_page"){
				if($list_pref[$mode."_showempty"] || $menutext){
					$start = preg_replace("/\{(.*?)\}/e", '$\1', $LIST_PAGE_NEW_START);
					$end = preg_replace("/\{(.*?)\}/e", '$\1', $LIST_PAGE_NEW_END);
				}
			}
			$text = $start.$menutext.$end;
		}else{
			$text = "";
		}
		return $text;
	}

	function getlvisit(){
		global $qs;
		$lvisit = (isset($qs[0]) && $qs[0] != "new" ? intval(e_QUERY) : USERLV);
		$lvisit = ($lvisit <= 14 ? time()-$lvisit*86400 : $lvisit);
		//$lvisit = $lvisit - 5*86400; //just for testing, so more content is shown in the new page
		return $lvisit;
	}

	function getBullet($sectionicon, $mode){
		global $list_pref, $listplugindir;

		$default_bullet = "";

		if($list_pref[$mode."_icon_default"]){
			if(file_exists(THEME."images/bullet2.gif")){
				$default_bullet = "<img src='".THEME."images/bullet2.gif' alt='' />";
			}
		}

		$icon_width = "8";
		$icon_height = "8";
		$style_pre = "";

		if($list_pref[$mode."_icon_use"]){
			if($sectionicon){
				if(file_exists($listplugindir."images/".$sectionicon)){
					$bullet = "<img src='".$listplugindir."images/".$sectionicon."' style='width:".$icon_width."px; height:".$icon_height."px; border:0; vertical-align:middle;' alt='' />";
				}
			}
		}
		$bullet = (isset($bullet) ? $bullet : $default_bullet);

		return $bullet;
	}

	function parse_heading($heading, $mode){
		global $list_pref;
		
		if($list_pref[$mode."_char_heading"] && strlen($heading) > $list_pref[$mode."_char_heading"]){
			$heading = substr($heading, 0, $list_pref[$mode."_char_heading"]).$list_pref[$mode."_char_postfix"];
		}
		return $heading;
	}

	function getListDate($datestamp, $mode){
		global $list_pref;
		
		$datestamp += TIMEOFFSET;

		$todayarray = getdate();
		$current_day = $todayarray['mday'];
		$current_month = $todayarray['mon'];
		$current_year = $todayarray['year'];

		$thisday = date("d", $datestamp);
		$thismonth = date("m", $datestamp);
		$thisyear = date("Y", $datestamp);

		//check and use the today date style if day is today
		if($thisyear == $current_year){
			if($thismonth == $current_month){
				if($thisday == $current_day){
					$datepreftoday = $list_pref[$mode."_datestyletoday"];
					$date = strftime($datepreftoday, $datestamp);
					return $date;
				}
			}
		}

		//else use default date style
		$datepref = $list_pref[$mode."_datestyle"];
		$date = strftime($datepref, $datestamp);
		return $date;
	}


	//##### FUNCTIONS BENEATH ARE ONLY USED IN THE ADMIN PAGE

	function parse_headerrow_title($title){
		global $rs, $list_pref;

		$text = "<tr><td colspan='4' class='forumheader'>".$title."</td></tr>";

		return $text;
	}

}

?>