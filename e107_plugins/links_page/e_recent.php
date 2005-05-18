<?php

	global $sql;
	if(!$links_install = $sql -> db_Select("plugin", "*", "plugin_path = 'links_page' AND plugin_installflag = '1' ")){
		return;
	}

	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $HEADING, $RECENT_AUTHOR, $CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	$RECENT_CAPTION = $arr[0];
	$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$bullet = $this -> getBullet($arr[6], $mode);

	$sql2 = new db;	
	if(!$sql -> db_select("links_page", "*", "link_class REGEXP '".e_CLASS_REGEXP."' AND link_category != '1' ORDER BY link_id DESC LIMIT 0,".$arr[7]."")){
		$RECENT_DATA = "no links yet";
	}else{
		while($row = $sql -> db_Fetch()){

			//get category				
			$tmp = $sql2 -> db_select("links_page_cat", "link_category_id, link_category_name", "link_category_id = '".$row['link_category']."' ");
			list($catid, $catname) = $sql2 -> db_Fetch();

			$rowheading = $this -> parse_heading($row['link_name'], $mode);

			$ICON = $bullet;
			$HEADING = "<a href='".$row['link_url']."' target='_blank' title='".$row['link_name']."'>".$rowheading."</a>";
			$AUTHOR = "";
			$CATEGORY = ($arr[4] ? "<a href='".e_PLUGIN."links_page/links.php?cat.".$catid."'>".$catname."</a>" : "");
			$DATE = "";
			$INFO = "";

			$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );

		}
	}

?>