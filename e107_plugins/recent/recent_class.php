<?php

global $sql, $rc, $recent_pref, $sc_style, $tp, $recent_shortcodes, $defaultarray;

global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
global $RECENT_ICON, $RECENT_DATE, $RECENT_HEADING, $RECENT_AUTHOR, $RECENT_CATEGORY, $RECENT_INFO;
global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

$defaultarray = array("news", "comment", "download", "members");

require_once(e_PLUGIN."recent/recent_template.php");

class recent {

	function getDefaultSections(){
		global $sql, $sections, $titles, $defaultarray;

		//default always present sections
		for($i=0;$i<count($defaultarray);$i++){
			$sections[] = $defaultarray[$i];
			$titles[] = $defaultarray[$i];
		}
		return;
	}


	function getContentSections($mode){
		global $sql, $sections, $titles, $content_types, $content_name;

		$plugintable = "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
		$datequery = " AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ";

		//get main parent types
		if($mainparents = $sql -> db_Select($plugintable, "*", "content_parent = '0' ".$datequery." ORDER BY content_heading")){
			while($row = $sql -> db_Fetch()){
				$content_types[] = $row['content_heading'];
				$content_name = 'content';
				if($mode == "add"){
					$sections[] = $row['content_heading'];
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
		$iconlist = $fl->get_files(e_PLUGIN, "e_recent\.php$", "standard", 1);
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
						$recent_pref["$sections[$i]_menudisplay"] = "1";
						$recent_pref["$sections[$i]_menuopen"] = "0";
						$recent_pref["$sections[$i]_menuauthor"] = "0";
						$recent_pref["$sections[$i]_menucategory"] = "0";
						$recent_pref["$sections[$i]_menudate"] = "1";
						$recent_pref["$sections[$i]_menuamount"] = "5";

						$recent_pref["$sections[$i]_pagedisplay"] = "1";
						$recent_pref["$sections[$i]_pageopen"] = "1";
						$recent_pref["$sections[$i]_pageauthor"] = "1";
						$recent_pref["$sections[$i]_pagecategory"] = "1";
						$recent_pref["$sections[$i]_pagedate"] = "1";
						$recent_pref["$sections[$i]_pageamount"] = "10";

						$recent_pref["$sections[$i]_icon"] = "";
						$recent_pref["$sections[$i]_order"] = ($i+1);
						$recent_pref["$sections[$i]_caption"] = $sections[$i];
					}
				}else{
					$recent_pref["$sections[$i]_menudisplay"] = "1";
					$recent_pref["$sections[$i]_menuopen"] = "0";
					$recent_pref["$sections[$i]_menuauthor"] = "0";
					$recent_pref["$sections[$i]_menucategory"] = "0";
					$recent_pref["$sections[$i]_menudate"] = "1";
					$recent_pref["$sections[$i]_menuamount"] = "5";

					$recent_pref["$sections[$i]_pagedisplay"] = "1";
					$recent_pref["$sections[$i]_pageopen"] = "1";
					$recent_pref["$sections[$i]_pageauthor"] = "1";
					$recent_pref["$sections[$i]_pagecategory"] = "1";
					$recent_pref["$sections[$i]_pagedate"] = "1";
					$recent_pref["$sections[$i]_pageamount"] = "10";

					$recent_pref["$sections[$i]_icon"] = "";
					$recent_pref["$sections[$i]_order"] = ($i+1);
					$recent_pref["$sections[$i]_caption"] = $sections[$i];
				}
			}else{
				$recent_pref["$sections[$i]_menudisplay"] = "1";
				$recent_pref["$sections[$i]_menuopen"] = "0";
				$recent_pref["$sections[$i]_menuauthor"] = "0";
				$recent_pref["$sections[$i]_menucategory"] = "0";
				$recent_pref["$sections[$i]_menudate"] = "1";
				$recent_pref["$sections[$i]_menuamount"] = "5";

				$recent_pref["$sections[$i]_pagedisplay"] = "1";
				$recent_pref["$sections[$i]_pageopen"] = "1";
				$recent_pref["$sections[$i]_pageauthor"] = "1";
				$recent_pref["$sections[$i]_pagecategory"] = "1";
				$recent_pref["$sections[$i]_pagedate"] = "1";
				$recent_pref["$sections[$i]_pageamount"] = "10";

				$recent_pref["$sections[$i]_icon"] = "";
				$recent_pref["$sections[$i]_order"] = ($i+1);
				$recent_pref["$sections[$i]_caption"] = $sections[$i];
			}
		}
		
		//style preferences
		$recent_pref['style_caption'] = "fcaption";
		$recent_pref['style_body'] = "forumheader3";

		//menu preferences
		$recent_pref['menu_caption'] = RECENT_ADMIN_14;
		$recent_pref['menu_icon_use'] = "1";
		$recent_pref['menu_icon_default'] = "1";
		$recent_pref['menu_char_heading'] = "20";
		$recent_pref['menu_char_postfix'] = "...";
		$recent_pref['menu_datestyle'] = "%d %b";
		$recent_pref['menu_datestyletoday'] = "%H:%M";
		$recent_pref['menu_showempty'] = "";

		//page preferences
		$recent_pref['page_caption'] = RECENT_ADMIN_14;
		$recent_pref['page_icon_use'] = "1";
		$recent_pref['page_icon_default'] = "1";
		$recent_pref['page_char_heading'] = "";
		$recent_pref['page_char_postfix'] = "";
		$recent_pref['page_datestyle'] = "%d %b";
		$recent_pref['page_datestyletoday'] = "%H:%M";
		$recent_pref['page_showempty'] = "";
		$recent_pref['page_colomn'] = "1";
		$recent_pref["page_welcometext"] = RECENT_ADMIN_13;

		return $recent_pref;
	}

	function show_section_recent($arr, $mode, $max=""){
		global $tp, $recent_shortcodes, $sql, $recent_pref, $defaultarray, $content_types, $content_name;
		global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
		global $RECENT_ICON, $RECENT_DATE, $RECENT_HEADING, $RECENT_AUTHOR, $RECENT_CATEGORY, $RECENT_INFO;
		global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

		$menu_installed = $sql -> db_Select("menus", "*", "menu_name = 'recent_menu' AND menu_location != '0' AND menu_class REGEXP '".e_CLASS_REGEXP."' ");
		$RECENT_DATA = "";

		$this -> getContentSections("");

		if(in_array($arr[9], $content_types)){
			
			$file = $content_name;
			if(file_exists(e_PLUGIN.$file."/e_recent.php")){
				//content should always be required cause of the multiple main parents in content.
				//if(e_PAGE == "recent_page.php" && $menu_installed == 1 && $mode == "menu"){
					//unset($RECENT_DATA, $RECENT_CAPTION);
					global $contentmode;
					$contentmode = $arr[9];
					require(e_PLUGIN.$file."/e_recent.php");
				//}else{
				//	require_once(e_PLUGIN.$file."/e_recent.php");
				//}
			}
		}else{
			$file = $arr[9];
			if(in_array($file, $defaultarray)){
				if(e_PAGE == "recent_page.php" && $menu_installed == 1 && $mode == "menu"){
					require(e_PLUGIN."recent/section/recent_".$file.".php");
				}else{
					//require_once(e_PLUGIN."recent/section/recent_".$file.".php");
					require(e_PLUGIN."recent/section/recent_".$file.".php");
				}
			}else{
				if(file_exists(e_PLUGIN.$file."/e_recent.php")){
					if(e_PAGE == "recent_page.php" && $menu_installed == 1 && $mode == "menu"){
						require(e_PLUGIN.$file."/e_recent.php");
					}else{
						//require_once(e_PLUGIN.$file."/e_recent.php");
						require(e_PLUGIN.$file."/e_recent.php");
					}
				}
			}
		}
		$menutext = "";
		$start = "";
		$end = "";
		
		$RECENT_ICON = "";
		$RECENT_DATE = "";
		$RECENT_HEADING = "";
		$RECENT_AUTHOR = "";
		$RECENT_CATEGORY = "";
		$RECENT_INFO = "";

		if(is_array($RECENT_DATA)){			//if it is an array, data exists and data is not empty
			for($i=0;$i<count($RECENT_DATA[$mode]);$i++){				
				$RECENT_ICON = $RECENT_DATA[$mode][$i][0];
				$RECENT_HEADING = $RECENT_DATA[$mode][$i][1];
				$RECENT_AUTHOR = $RECENT_DATA[$mode][$i][2];
				$RECENT_CATEGORY = $RECENT_DATA[$mode][$i][3];
				$RECENT_DATE = $RECENT_DATA[$mode][$i][4];
				$RECENT_INFO = $RECENT_DATA[$mode][$i][5];
				if($mode == "page"){
					$menutext .= $tp -> parseTemplate($RECENT_PAGE, FALSE, $recent_shortcodes);
				}else{
					global $sc_style;
					$RECENT_AUTHOR = ($RECENT_AUTHOR ? $sc_style['RECENT_AUTHOR']['pre'].$RECENT_AUTHOR.$sc_style['RECENT_AUTHOR']['post'] : "");					
					$RECENT_CATEGORY = ($RECENT_CATEGORY ? $sc_style['RECENT_CATEGORY']['pre'].$RECENT_CATEGORY.$sc_style['RECENT_CATEGORY']['post'] : "");					
					$menutext .= preg_replace("/\{(.*?)\}/e", '$\1', $RECENT_MENU);
				}
			}
		}elseif(!is_array($RECENT_DATA) && $RECENT_DATA != ""){
			$RECENT_HEADING = $RECENT_DATA;
			if($mode == "page"){
				if($recent_pref['page_showempty']){
					$menutext .= $tp -> parseTemplate($RECENT_PAGE, FALSE, $recent_shortcodes);
				}
			}else{
				if($recent_pref['menu_showempty']){
					$menutext .= preg_replace("/\{(.*?)\}/e", '$\1', $RECENT_MENU);
				}
			}
		}

		if($RECENT_DATA != ""){
			if($mode == "page"){
				if($recent_pref['page_showempty'] || $menutext){
					$start = preg_replace("/\{(.*?)\}/e", '$\1', $RECENT_PAGE_START);
					$end = preg_replace("/\{(.*?)\}/e", '$\1', $RECENT_PAGE_END);
				}
			}else{
				if($recent_pref['menu_showempty'] || $menutext){
					$start = preg_replace("/\{(.*?)\}/e", '$\1', $RECENT_MENU_START);
					$end = preg_replace("/\{(.*?)\}/e", '$\1', $RECENT_MENU_END);
				}
			}
			$text = $start.$menutext.$end;
		}else{
			$text = "";
		}
		return $text;		
	}


	function getBullet($sectionicon, $mode){
		global $recent_pref;

		$default_bullet = "";
		if($recent_pref[$mode."_icon_default"]){
			if(file_exists(THEME."images/bullet2.gif")){
				$default_bullet = "<img src='".THEME."images/bullet2.gif' alt='' />";
			}
		}

		$icon_width = "8";
		$icon_height = "8";
		$style_pre = "";

		if($recent_pref[$mode."_icon_use"]){
			if($sectionicon){
				if(file_exists(e_PLUGIN."recent/images/".$sectionicon)){
					$bullet = "<img src='".e_PLUGIN."recent/images/".$sectionicon."' style='width:".$icon_width."px; height:".$icon_height."px; border:0; vertical-align:middle;' alt='' />";
				}
			}
		}
		$bullet = (isset($bullet) ? $bullet : $default_bullet);

		return $bullet;
	}

	function parse_heading($heading, $mode){
		global $recent_pref;

		if($recent_pref[$mode."_char_heading"] && strlen($heading) > $recent_pref[$mode."_char_heading"]){
			$heading = substr($heading, 0, $recent_pref[$mode."_char_heading"]).$recent_pref[$mode."_char_postfix"];
		}
		return $heading;
	}

	function getRecentDate($datestamp, $mode){
		global $recent_pref;

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
					$datepreftoday = ($mode == "page" ? $recent_pref['page_datestyletoday'] : $recent_pref['menu_datestyletoday']);
					$date = strftime($datepreftoday, $datestamp);
					return $date;
				}
			}
		}

		//else use default date style
		$datepref = ($mode == "page" ? $recent_pref['page_datestyle'] : $recent_pref['menu_datestyle']);
		$date = strftime($datepref, $datestamp);
		return $date;
	}


	//##### ALL FUNCTION BENEATH ARE ONLY USED IN THE ADMIN PAGE

	function parse_headerrow(){
		global $rs, $recent_pref;

		$text = "
		<tr>
			<td class='forumheader'>".RECENT_ADMIN_4."</td>
			<td class='forumheader'>".RECENT_ADMIN_5."</td>
			<td class='forumheader'>".RECENT_ADMIN_6."</td>
		</tr>";

		return $text;
	}

	function parse_headerrow_title($title){
		global $rs, $recent_pref;

		$text = "<tr><td colspan='2' class='forumheader'>".$title."</td></tr>";

		return $text;
	}

	function parse_display($section, $title){
		global $rs, $recent_pref;

		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_menudisplay", "1", ($recent_pref[$section."_menudisplay"] ? "1" : "0"), "", "").RECENT_ADMIN_7."
				".$rs -> form_radio($section."_menudisplay", "0", ($recent_pref[$section."_menudisplay"] ? "0" : "1"), "", "").RECENT_ADMIN_8."
			</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_pagedisplay", "1", ($recent_pref[$section."_pagedisplay"] ? "1" : "0"), "", "").RECENT_ADMIN_7."
				".$rs -> form_radio($section."_pagedisplay", "0", ($recent_pref[$section."_pagedisplay"] ? "0" : "1"), "", "").RECENT_ADMIN_8."
			</td>
		</tr>";

		return $text;
	}

	function parse_openclose($section, $title){
		global $rs, $recent_pref;
		
		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_menuopen", "1", ($recent_pref[$section."_menuopen"] ? "1" : "0"), "", "").RECENT_ADMIN_9."
				".$rs -> form_radio($section."_menuopen", "0", ($recent_pref[$section."_menuopen"] ? "0" : "1"), "", "").RECENT_ADMIN_10."
			</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_pageopen", "1", ($recent_pref[$section."_pageopen"] ? "1" : "0"), "", "").RECENT_ADMIN_9."
				".$rs -> form_radio($section."_pageopen", "0", ($recent_pref[$section."_pageopen"] ? "0" : "1"), "", "").RECENT_ADMIN_10."
			</td>
		</tr>";

		return $text;
	}

	function parse_author($section, $title){
		global $rs, $recent_pref;

		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_menuauthor", "1", ($recent_pref[$section."_menuauthor"] ? "1" : "0"), "", "").RECENT_ADMIN_7."
				".$rs -> form_radio($section."_menuauthor", "0", ($recent_pref[$section."_menuauthor"] ? "0" : "1"), "", "").RECENT_ADMIN_8."
			</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_pageauthor", "1", ($recent_pref[$section."_pageauthor"] ? "1" : "0"), "", "").RECENT_ADMIN_7."
				".$rs -> form_radio($section."_pageauthor", "0", ($recent_pref[$section."_pageauthor"] ? "0" : "1"), "", "").RECENT_ADMIN_8."
			</td>
		</tr>";

		return $text;
	}

	function parse_category($section, $title){
		global $rs, $recent_pref;

		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_menucategory", "1", ($recent_pref[$section."_menucategory"] ? "1" : "0"), "", "").RECENT_ADMIN_7."
				".$rs -> form_radio($section."_menucategory", "0", ($recent_pref[$section."_menucategory"] ? "0" : "1"), "", "").RECENT_ADMIN_8."
			</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_pagecategory", "1", ($recent_pref[$section."_pagecategory"] ? "1" : "0"), "", "").RECENT_ADMIN_7."
				".$rs -> form_radio($section."_pagecategory", "0", ($recent_pref[$section."_pagecategory"] ? "0" : "1"), "", "").RECENT_ADMIN_8."
			</td>
		</tr>";

		return $text;
	}

	function parse_date($section, $title){
		global $rs, $recent_pref;

		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_menudate", "1", ($recent_pref[$section."_menudate"] ? "1" : "0"), "", "").RECENT_ADMIN_7."
				".$rs -> form_radio($section."_menudate", "0", ($recent_pref[$section."_menudate"] ? "0" : "1"), "", "").RECENT_ADMIN_8."
			</td>
			<td class='forumheader3'>
				".$rs -> form_radio($section."_pagedate", "1", ($recent_pref[$section."_pagedate"] ? "1" : "0"), "", "").RECENT_ADMIN_7."
				".$rs -> form_radio($section."_pagedate", "0", ($recent_pref[$section."_pagedate"] ? "0" : "1"), "", "").RECENT_ADMIN_8."
			</td>
		</tr>";
		return $text;
	}

	function parse_amount($section, $title){
		global $rs, $recent_pref;

		$maxitems_amount = "15";

		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>
				".$rs -> form_select_open($section."_menuamount");
				for($a=1; $a<=$maxitems_amount; $a++){
					$text .= ($recent_pref[$section."_menuamount"] == $a ? $rs -> form_option($a, 1, $a) : $rs -> form_option($a, 0, $a));
				}
				$text .= $rs -> form_select_close()."
			</td>
			<td class='forumheader3'>
				".$rs -> form_select_open($section."_pageamount");
				for($a=1; $a<=$maxitems_amount; $a++){
					$text .= ($recent_pref[$section."_pageamount"] == $a ? $rs -> form_option($a, 1, $a) : $rs -> form_option($a, 0, $a));
				}
				$text .= $rs -> form_select_close()."
			</td>
		</tr>";

		return $text;
	}

	function parse_icon($section, $title){
		global $rs, $recent_pref, $iconlist;

		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>
				".$rs -> form_text($section."_icon", 15, $recent_pref[$section."_icon"], 100)."
				<input class='button' type='button' style='cursor:hand' size='30' value='".RECENT_ADMIN_12."' onClick='expandit(this)' />
				<div id='div".$section."icon' style='display:none;'>";
				foreach($iconlist as $icon){
					$text .= "<a href=\"javascript:insertext('".$icon['fname']."','".$section."_icon','div".$section."icon')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
				}
				$text .= "</div>
			</td>
		</tr>";

		return $text;
	}

	function parse_order($section, $title, $max){
		global $rs, $recent_pref;

		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>
				".$rs -> form_select_open($section."_order");
				for($a=1; $a<=$max; $a++){
					$text .= ($recent_pref[$section."_order"] == $a ? $rs -> form_option($a, 1, $a) : $rs -> form_option($a, 0, $a));
				}
				$text .= $rs -> form_select_close()."
			</td>
		</tr>";

		return $text;
	}

	function parse_caption($section, $title){
		global $rs, $recent_pref;

		$text = "
		<tr>
			<td class='forumheader3' style='width:10%; white-space:nowrap;'>".$title."</td>
			<td class='forumheader3'>".$rs -> form_text($section."_caption", "30", $recent_pref[$section."_caption"], "50", "tbox")."</td>
		</tr>";

		return $text;
	}

}

?>