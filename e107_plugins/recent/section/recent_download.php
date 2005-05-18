<?php

	global $sql;
	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $RECENT_HEADING, $RECENT_AUTHOR, $RECENT_CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	$RECENT_CAPTION = $arr[0];
	$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$bullet = $this -> getBullet($arr[6], $mode);

	$mp = MPREFIX;
	$qry = "SELECT download_id, download_name, download_author, download_datestamp, {$mp}download_category.download_category_id, {$mp}download_category.download_category_name, {$mp}download_category.download_category_class FROM {$mp}download LEFT JOIN {$mp}download_category ON {$mp}download.download_category={$mp}download_category.download_category_id WHERE download_category_class REGEXP '".e_CLASS_REGEXP."' AND download_class REGEXP '".e_CLASS_REGEXP."' AND {$mp}download.download_active = '1' ORDER BY download_datestamp DESC LIMIT 0,".$arr[7]." ";
	$downloads = $sql -> db_Select_gen($qry);
	if($downloads == 0) {
		$RECENT_DATA = RECENT_DOWNLOAD_2;
	}else{
		$row = $sql -> db_Fetch();

		$rowheading = $this -> parse_heading($row['download_name'], $mode);

		$ICON = $bullet;
		$HEADING = "<a href='".e_BASE."download.php?view.".$row['download_id']."' title='".$row['download_name']."'>".$rowheading."</a>";
		$AUTHOR = ($arr[3] ? $row['download_author'] : "");
		//$AUTHOR = ($arr[3] ? (USERID ? "<a href='".e_BASE."user.php?id.".$row['download_author_id']."'>".$row['download_author']."</a>" : $row['download_author']) : "");
		$CATEGORY = ($arr[4] ? "<a href='".e_BASE."download.php?list.".$row['download_category_id']."'>".$row['download_category_name']."</a>" : "");
		$DATE = ($arr[5] ? $this -> getRecentDate($row['download_datestamp'], $mode) : "");
		$INFO = "";

		$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
	}

?>