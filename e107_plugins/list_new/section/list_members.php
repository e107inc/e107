<?php

	$LIST_CAPTION = $arr[0];
	$LIST_DISPLAYSTYLE = ($arr[2] ? "" : "none");
	
	if($mode == "new_page" || $mode == "new_menu" ){
		$lvisit = $this -> getlvisit();
		$qry = "user_join>".$lvisit." AND user_ban='0' AND ";
	}else{
		$qry = " ";
	}
	$qry .= " user_class REGEXP '".e_CLASS_REGEXP."' ORDER BY user_join DESC LIMIT 0,".$arr[7];

	$bullet = $this -> getBullet($arr[6], $mode);

	if(!$sql -> db_Select("user", "*", $qry)){ 
		$LIST_DATA = LIST_MEMBER_2;
	}else{
		while($row = $sql -> db_Fetch()){

			$rowheading	= $this -> parse_heading($row['user_name'], $mode);
			$ICON		= $bullet;
			$HEADING	= (USER ? "<a href='".e_BASE."user.php?id.".$row['user_id']."' title='".$row['user_name']."'>".$rowheading."</a>" : $rowheading);
			$CATEGORY	= "";
			$AUTHOR		= "";
			$DATE		= ($arr[5] ? $this -> getListDate($row['user_join'], $mode) : "");
			$INFO		= "";
			$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>