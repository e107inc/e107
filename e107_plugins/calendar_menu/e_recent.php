<?php

	global $sql;
	if(!$calendar_install = $sql -> db_Select("plugin", "*", "plugin_path = 'calendar_menu' AND plugin_installflag = '1' ")){
		return;
	}

	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $HEADING, $RECENT_AUTHOR, $CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	$RECENT_CAPTION = $arr[0];
	$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$bullet = $this -> getBullet($arr[6], $mode);

	$todayarray = getdate();
	$current_day = $todayarray['mday'];
	$current_month = $todayarray['mon'];
	$current_year = $todayarray['year'];
	$current = mktime(0,0,0,$current_month, $current_day, $current_year);

	if(!$event_items = $sql -> db_select("event", "*", "event_start>='$current' ORDER BY event_start ASC LIMIT 0,".$arr[7]."")){
		$RECENT_DATA = "no events yet";
	}else{
		while($row = $sql -> db_Fetch()){

			$rowheading = $this -> parse_heading($row['event_title'], $mode);

			$ICON = $bullet;
			$HEADING = "<a href='".e_BASE."event.php?".$row['event_start']."' title='".$row['event_title']."'>".$rowheading."</a>";
			$CATEGORY = "";
			$AUTHOR = "";
			$DATE = ($arr[5] ? $this -> getRecentDate($row['event_start'], $mode) : "");
			$INFO = "";

			$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>