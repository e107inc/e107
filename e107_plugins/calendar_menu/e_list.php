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
		$qry = " event_datestamp>".$lvisit." AND ";
	}else{
		$qry = "";
	}

	$bullet = $this -> getBullet($arr[6], $mode);

	$qry = "
	SELECT e.*, c.event_cat_name
	FROM #event AS e 
	LEFT JOIN #event_cat AS c ON c.event_cat_id = e.event_category 
	WHERE ".$qry." e.event_start>='$current' AND c.event_cat_class REGEXP '".e_CLASS_REGEXP."' 
	ORDER BY e.event_start ASC LIMIT 0,".$arr[7];

	if(!$event_items = $sql->db_Select_gen($qry)){
		$LIST_DATA = LIST_CALENDAR_2;
	}else{
		while($row = $sql -> db_Fetch()){

			$tmp = explode(".", $row['event_author']);
			if($tmp[0] == "0"){
				$AUTHOR = $tmp[1];
			}elseif(is_numeric($tmp[0]) && $tmp[0] != "0"){
				$AUTHOR = (USER ? "<a href='".e_BASE."user.php?id.".$tmp[0]."'>".$tmp[1]."</a>" : $tmp[1]);
			}else{
				$AUTHOR = "";
			}

			$rowheading	= $this -> parse_heading($row['event_title'], $mode);
			$ICON		= $bullet;
			$HEADING	= "<a href='".e_PLUGIN."calendar_menu/event.php?".$row['event_start'].".event.".$row['event_id']."' title='".$row['event_title']."'>".$rowheading."</a>";
			$CATEGORY	= $row['event_cat_name'];
			$DATE		= ($arr[5] ? ($row['event_start'] ? $this -> getListDate($row['event_start'], $mode) : "") : "");
			$INFO		= "";
			$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>