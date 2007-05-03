<?php

if (!defined('e107_INIT')) { exit; }

	$LIST_CAPTION = $arr[0];
	$LIST_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	if($mode == "new_page" || $mode == "new_menu" ){
		$lvisit = $this -> getlvisit();
		$qry = " AND download_datestamp>".$lvisit;
	}else{
		$qry = " ";
	}

	$bullet = $this -> getBullet($arr[6], $mode);

	$qry = "SELECT d.download_id, d.download_name, d.download_author, d.download_datestamp, 
	   dc.download_category_id, dc.download_category_name, dc.download_category_class 
	   FROM #download AS d
	   LEFT JOIN #download_category AS dc ON d.download_category=dc.download_category_id
	   WHERE dc.download_category_class REGEXP '".e_CLASS_REGEXP."' AND d.download_class REGEXP '".e_CLASS_REGEXP."' AND d.download_active != '0' ".$qry." 
	   ORDER BY download_datestamp DESC LIMIT 0,".intval($arr[7])." ";

	$downloads = $sql -> db_Select_gen($qry);
	if($downloads == 0) 
	{
	  $LIST_DATA = LIST_DOWNLOAD_2;
	}
	else
	{
	  while($row = $sql -> db_Fetch())
	  {

		$rowheading	= $this -> parse_heading($row['download_name'], $mode);
		$ICON		= $bullet;
		$HEADING	= "<a href='".e_BASE."download.php?view.".$row['download_id']."' title='".$row['download_name']."'>".$rowheading."</a>";
		$AUTHOR		= ($arr[3] ? $row['download_author'] : "");
		$CATEGORY	= ($arr[4] ? "<a href='".e_BASE."download.php?list.".$row['download_category_id']."'>".$row['download_category_name']."</a>" : "");
		$DATE		= ($arr[5] ? $this -> getListDate($row['download_datestamp'], $mode) : "");
		$INFO		= "";
		$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
	  }
	}

?>