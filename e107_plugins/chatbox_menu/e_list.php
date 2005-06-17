<?php

	if(!$chatbox_install = $sql -> db_Select("plugin", "*", "plugin_path = 'chatbox_menu' AND plugin_installflag = '1' ")){
		return;
	}

	$LIST_CAPTION = $arr[0];
	$LIST_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	if($mode == "new_page" || $mode == "new_menu" ){
		$lvisit = $this -> getlvisit();
		$qry = "cb_datestamp>".$lvisit;
	}else{
		$qry = "cb_id != '0' ";
	}
	$qry .= " ORDER BY cb_datestamp DESC LIMIT 0,".$arr[7];

	$bullet = $this -> getBullet($arr[6], $mode);

	if(!$chatbox_posts = $sql -> db_Select("chatbox", "*", $qry)){ 
		$LIST_DATA = LIST_CHATBOX_2;
	}else{
		while($row = $sql -> db_Fetch()) {

			$cb_id		= substr($row['cb_nick'] , 0, strpos($row['cb_nick'] , "."));
			$cb_nick	= substr($row['cb_nick'] , (strpos($row['cb_nick'] , ".")+1));
			$cb_message	= ($row['cb_blocked'] ? CHATBOX_L6 : str_replace("<br />", "", $tp -> toHTML($row['cb_message'])));
			$rowheading	= $this -> parse_heading($cb_message, $mode);
			$ICON		= $bullet;
			$HEADING	= $rowheading;
			$AUTHOR		= ($arr[3] ? ($cb_id != 0 ? "<a href='".e_BASE."user.php?id.$cb_id'>".$cb_name."</a>" : $cb_name) : "");
			$CATEGORY	= "";
			$DATE		= ($arr[5] ? ($row['cb_datestamp'] ? $this -> getListDate($row['cb_datestamp'], $mode) : "") : "");
			$INFO		= "";
			$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>