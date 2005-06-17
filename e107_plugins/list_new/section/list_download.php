<?php

	$LIST_CAPTION = $arr[0];
	$LIST_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	if($mode == "new_page" || $mode == "new_menu" ){
		$lvisit = $this -> getlvisit();
		$qry = " AND download_datestamp>".$lvisit;
	}else{
		$qry = " ";
	}

	$bullet = $this -> getBullet($arr[6], $mode);

	$mp = MPREFIX;
	$qry = "SELECT download_id, download_name, download_author, download_datestamp, {$mp}download_category.download_category_id, {$mp}download_category.download_category_name, {$mp}download_category.download_category_class FROM {$mp}download LEFT JOIN {$mp}download_category ON {$mp}download.download_category={$mp}download_category.download_category_id WHERE download_category_class REGEXP '".e_CLASS_REGEXP."' AND download_class REGEXP '".e_CLASS_REGEXP."' AND {$mp}download.download_active = '1' ".$qry." ORDER BY download_datestamp DESC LIMIT 0,".$arr[7]." ";
	$downloads = $sql -> db_Select_gen($qry);
	if($downloads == 0) {
		$LIST_DATA = LIST_DOWNLOAD_2;
	}else{
		$row = $sql -> db_Fetch();

		$rowheading	= $this -> parse_heading($row['download_name'], $mode);
		$ICON		= $bullet;
		$HEADING	= "<a href='".e_BASE."download.php?view.".$row['download_id']."' title='".$row['download_name']."'>".$rowheading."</a>";
		$AUTHOR		= ($arr[3] ? $row['download_author'] : "");
		//$AUTHOR	= ($arr[3] ? (USERID ? "<a href='".e_BASE."user.php?id.".$row['download_author_id']."'>".$row['download_author']."</a>" : $row['download_author']) : "");
		$CATEGORY	= ($arr[4] ? "<a href='".e_BASE."download.php?list.".$row['download_category_id']."'>".$row['download_category_name']."</a>" : "");
		$DATE		= ($arr[5] ? $this -> getListDate($row['download_datestamp'], $mode) : "");
		$INFO		= "";
		$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
	}

?>