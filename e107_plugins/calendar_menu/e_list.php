<?php

	if(!$calendar_install = $sql -> db_Select("plugin", "*", "plugin_path = 'calendar_menu' AND plugin_installflag = '1' ")){
		return;
	}

	$LIST_CAPTION = $arr[0];
	$LIST_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$todayarray		= getdate();
	$current_day	= $todayarray['mday'];
	$current_month	= $todayarray['mon'];
	$current_year	= $todayarray['year'];
	$current		= mktime(0,0,0,$current_month, $current_day, $current_year);

	if($mode == "new_page" || $mode == "new_menu" ){
		$lvisit = $this -> getlvisit();
		$qry = " event_datestamp>".$lvisit;
	}else{
		$qry = " ";
	}
	$qry .= " event_start>='$current' ORDER BY event_start ASC LIMIT 0,".$arr[7];
 
	$bullet = $this -> getBullet($arr[6], $mode);

	if(!$event_items = $sql -> db_select("event", "*", $qry)){
		$LIST_DATA = LIST_CALENDAR_2;
	}else{
		while($row = $sql -> db_Fetch()){

			$rowheading	= $this -> parse_heading($row['event_title'], $mode);
			$ICON		= $bullet;
			$HEADING	= "<a href='".e_BASE."event.php?".$row['event_start']."' title='".$row['event_title']."'>".$rowheading."</a>";
			$CATEGORY	= "";
			$AUTHOR		= "";
			$DATE		= ($arr[5] ? ($row['event_start'] ? $this -> getListDate($row['event_start'], $mode) : "") : "");
			$INFO		= "";
			$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>