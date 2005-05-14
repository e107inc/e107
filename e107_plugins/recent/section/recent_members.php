<?php

	global $sql;
	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $RECENT_HEADING, $RECENT_AUTHOR, $RECENT_CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	$RECENT_CAPTION = $arr[0];
	$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$bullet = $this -> getBullet($arr[6], $mode);

	if(!$sql -> db_Select("user", "*", "user_class REGEXP '".e_CLASS_REGEXP."' ORDER BY user_id DESC LIMIT 0,".$arr[7]."")){ 
		$RECENT_DATA = RECENT_MEMBER_2;
	}else{
		while($row = $sql -> db_Fetch()){

			$rowheading = $this -> parse_heading($row['user_name'], $mode);

			$ICON = $bullet;
			$HEADING = (USER ? "<a href='".e_BASE."user.php?id.".$row['user_id']."' title='".$row['user_name']."'>".$rowheading."</a>" : $rowheading);
			$CATEGORY = "";
			$AUTHOR = "";
			$DATE = ($arr[5] ? $this -> getRecentDate($row['user_join'], $mode) : "");
			$INFO = "";

			$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>