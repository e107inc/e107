<?php

	global $sql;
	if(!$chatbox_install = $sql -> db_Select("plugin", "*", "plugin_path = 'chatbox_menu' AND plugin_installflag = '1' ")){
		return;
	}

	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $HEADING, $RECENT_AUTHOR, $CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	$RECENT_CAPTION = $arr[0];
	$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$bullet = $this -> getBullet($arr[6], $mode);

	if(!$chatbox_posts = $sql -> db_Select("chatbox", "*", "ORDER BY cb_datestamp DESC LIMIT 0,".$arr[7]."", "mode=no_where")){ 
		$RECENT_DATA = "no chatbox posts yet";
	}else{
		while($row = $sql -> db_Fetch()) {

			$cb_id = substr($row['cb_nick'] , 0, strpos($row['cb_nick'] , "."));
			$cb_nick = substr($row['cb_nick'] , (strpos($row['cb_nick'] , ".")+1));
			$cb_message = ($row['cb_blocked'] ? CHATBOX_L6 : str_replace("<br />", "", $tp -> toHTML($row['cb_message'])));

			$rowheading = $this -> parse_heading($cb_message, $mode);

			$ICON = $bullet;
			$HEADING = $rowheading;
			$AUTHOR = ($arr[3] ? ($cb_id != 0 ? "<a href='".e_BASE."user.php?id.$cb_id'>".$cb_name."</a>" : $cb_name) : "");
			$CATEGORY = "";
			$DATE = ($arr[5] ? $this -> getRecentDate($row['cb_datestamp'], $mode) : "");
			$INFO = "";

			$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>