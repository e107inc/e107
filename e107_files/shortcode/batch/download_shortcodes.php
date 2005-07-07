<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_files/shortcode/batch/download_shortcodes.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-07-07 15:08:14 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
include_once(e_HANDLER.'shortcode_handler.php');
$download_shortcodes = e_shortcode::parse_scbatch(__FILE__);
/*
SC_BEGIN DOWNLOAD_LIST_NAME
global $row,$tp;
return  "<a href='".e_SELF."?view.".$row['download_id']."'>".$tp->toHTML($row['download_name'])."</a>";
SC_END


SC_BEGIN DOWNLOAD_LIST_AUTHOR
global $row;
return $row['download_author'];
SC_END


SC_BEGIN DOWNLOAD_LIST_REQUESTED
global $row;
return $row['download_requested'];
SC_END


SC_BEGIN DOWNLOAD_LIST_ICON
global $row;
$img = "<img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' />";
if($parm == "link"){
	return "<a href='".e_SELF."?view.".$row['download_id']."'>".$img."</a>";
}else{
	return $img;
}
return
SC_END


SC_BEGIN DOWNLOAD_LIST_NEWICON
global $row;
return (USER && $row['download_datestamp'] > USERLV ? "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />" : "");
SC_END


SC_BEGIN DOWNLOAD_LIST_FILESIZE
global $row;
return parsesize($row['download_filesize']);
SC_END


SC_BEGIN DOWNLOAD_LIST_DATESTAMP
global $row;
$gen = new convert;
return $gen->convert_date($row['download_datestamp'], "short");
SC_END


SC_BEGIN DOWNLOAD_LIST_THUMB
global $row;
$img = ($row['download_thumb']) ? "<img src='".e_FILE."downloadthumbs/".$row['download_thumb']."' alt='' style='".DL_IMAGESTYLE."' />" : "";
if($parm == "link" && $row['download_thumb']){
	return "<a href='".e_SELF."?view.".$row['download_id']."'>".$img."</a>";
}else{
	return $img;
}
SC_END


SC_BEGIN DOWNLOAD_LIST_ID
global $row;
return $row['download_id'];
SC_END


SC_BEGIN DOWNLOAD_LIST_RATING
global $row;
$rater = new rater;
$ratearray = $rater->getrating("download", $row['download_id']);
	if (!$ratearray[0]) {
		return LAN_dl_13;
	} else {
		return ($ratearray[2] ? "{$ratearray[1]}.{$ratearray[2]}/{$ratearray[0]}" : "{$ratearray[1]}/{$ratearray[0]}");
	}
SC_END


SC_BEGIN DOWNLOAD_LIST_LINK
global $tp,$row,$pref;
$agreetext = $tp->toJS($pref['agree_text']);

	if($row['download_mirror_type']){
		return ($pref['agree_flag'] ? "<a href='".e_SELF."?mirror.".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_SELF."?mirror.".$row['download_id']."'>");
	}else{
		return ($pref['agree_flag'] ? "<a href='".e_BASE."request.php?".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."request.php?".$row['download_id']."'>");
	}
SC_END


*/
?>