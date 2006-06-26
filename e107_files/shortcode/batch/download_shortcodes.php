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
|     $Revision: 1.10 $
|     $Date: 2006-06-26 02:48:04 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$download_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*
SC_BEGIN DOWNLOAD_LIST_NAME
global $row,$tp,$pref;
if($parm == "nolink"){
	return $tp->toHTML($row['download_name']);
}
if($parm == "request"){

	$agreetext = $tp->toJS($pref['agree_text']);
	if($row['download_mirror_type']){
		$text = ($pref['agree_flag'] ? "<a href='".e_SELF."?mirror.".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_SELF."?mirror.".$row['download_id']."' title='".LAN_dl_32."'>");
	}else{
		$text = ($pref['agree_flag'] ? "<a href='".e_BASE."request.php?".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."request.php?".$row['download_id']."' title='".LAN_dl_32."'>");
	}
	$text .= $tp->toHTML($row['download_name'])."</a>";
	return $text;
}

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
$img = "<img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' title='".LAN_dl_32."' />";
if($parm == "link"){
	return "<a href='".e_SELF."?view.".$row['download_id']."' >".$img."</a>";
}else{
	return $img;
}
return;
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
		return ($pref['agree_flag'] ? "<a href='".e_SELF."?mirror.".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_SELF."?mirror.".$row['download_id']."' >");
	}else{
		return ($pref['agree_flag'] ? "<a href='".e_BASE."request.php?".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."request.php?".$row['download_id']."' >");
	}
SC_END



// ---------------------- Download View ----------------------------------------

SC_BEGIN DOWNLOAD_VIEW_ID
global $dl;
return $dl['download_id'];
SC_END


SC_BEGIN DOWNLOAD_ADMIN_EDIT
global $dl;
return (ADMIN && getperms('6')) ? "<a href='".e_ADMIN."download.php?create.edit.".$dl['download_id']."' title='edit'><img src='".e_IMAGE."generic/lite/edit.png' alt='' style='padding:0px;border:0px' /></a>" : "";
SC_END

SC_BEGIN DOWNLOAD_CATEGORY
global $dl;
return $dl['download_category_name'];
SC_END

SC_BEGIN DOWNLOAD_CATEGORY_DESCRIPTION
global $tp,$dl;
return $tp -> toHTML($dl['download_category_description'], TRUE);
SC_END

SC_BEGIN DOWNLOAD_VIEW_NAME
global $dl;
return $dl['download_name'];
SC_END

SC_BEGIN DOWNLOAD_VIEW_NAME_LINKED
global $dl;
return "<a href='".e_BASE."request.php?".$dl['download_id']."' title='".LAN_dl_46."'>".$dl['download_name']."</a>";
SC_END

SC_BEGIN DOWNLOAD_VIEW_AUTHOR
global $dl;
return ($dl['download_author'] ? $dl['download_author'] : "");
SC_END


SC_BEGIN DOWNLOAD_VIEW_AUTHOREMAIL
global $tp,$dl;
return ($dl['download_author_email']) ? $tp -> toHTML($dl['download_author_email'], TRUE) : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_AUTHORWEBSITE
global $tp,$dl;
return ($dl['download_author_website']) ? $tp -> toHTML($dl['download_author_website'], TRUE) : "";
SC_END



SC_BEGIN DOWNLOAD_VIEW_DESCRIPTION
global $tp,$dl;
return ($dl['download_description']) ?  $tp->toHTML($dl['download_description'], TRUE) : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_DATE
global $gen,$dl;
return ($dl['download_datestamp']) ? $gen->convert_date($dl['download_datestamp'], $parm) : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_DATE_SHORT
// deprecated: DOWNLOAD_VIEW_DATE should be used instead.
global $gen,$dl;
return ($dl['download_datestamp']) ? $gen->convert_date($dl['download_datestamp'], "short") : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_DATE_LONG
// deprecated: DOWNLOAD_VIEW_DATE should be used instead.
global $gen,$dl;
return ($dl['download_datestamp']) ? $gen->convert_date($dl['download_datestamp'], "long") : "";
SC_END



SC_BEGIN DOWNLOAD_VIEW_IMAGE
global $dl;
if ($dl['download_thumb']) {
	return ($dl['download_image'] ? "<a href='".e_BASE."request.php?download.".$dl['download_id']."'><img class='dl_image' src='".e_FILE."downloadthumbs/".$dl['download_thumb']."' alt='' style='".DL_IMAGESTYLE."' /></a>" : "<img class='dl_image' src='".e_FILE."downloadthumbs/".$dl['download_thumb']."' alt='' style='".DL_IMAGESTYLE."' />");
}
else if($dl['download_image']) {
	return "<a href='".e_BASE."request.php?download.".$dl['download_id']."'>".LAN_dl_40."</a>";
}
else
{
	return LAN_dl_75;
}

SC_END

SC_BEGIN DOWNLOAD_VIEW_IMAGEFULL
global $dl;
return ($dl['download_image']) ? "<img class='dl_image' src='".e_FILE."downloadimages/".$dl['download_image']."' alt='' style='".DL_IMAGESTYLE."' />" : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_LINK
global $pref,$dl,$tp;
if ($pref['agree_flag'] == 1) {
	$dnld_link = "<a href='request.php?".$dl['download_id']."' onclick= \"return confirm('".$tp->toJS($pref['agree_text'])."');\">";
} else {
	$dnld_link = "<a href='request.php?".$dl['download_id']."'>";
}

if($dl['download_mirror'])
{
	if($dl['download_mirror_type'])
	{
		return "<a href='".e_SELF."?mirror.".$dl['download_id']."'>".LAN_dl_66."</a>";
	}
	else
	{
		return $dnld_link." <img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' /></a>";
	}
}
else
{
	return $dnld_link." <img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' /></a>";
}
SC_END

SC_BEGIN DOWNLOAD_VIEW_FILESIZE
global $dl;
return ($dl['download_filesize']) ? parsesize($dl['download_filesize']) : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_RATING
	require_once(e_HANDLER."rate_class.php");
	$rater = new rater;
global $dl;
	$text = "
		<table style='width:100%'>
		<tr>
		<td style='width:50%'>";

	if ($ratearray = $rater->getrating("download", $dl['download_id'])) {
		for($c = 1; $c <= $ratearray[1]; $c++) {
			$text .= "<img src='".e_IMAGE."rate/".IMODE."/star.png' alt=''>";
		}
		if ($ratearray[2]) {
			$text .= "<img src='".e_IMAGE."rate/".IMODE."/".$ratearray[2].".png'  alt=''>";
		}
		if ($ratearray[2] == "") {
			$ratearray[2] = 0;
		}
		$text .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
		$text .= ($ratearray[0] == 1 ? LAN_dl_43 : LAN_dl_44);
	} else {
		$text .= LAN_dl_13;
	}
	$text .= "</td><td style='width:50%; text-align:right'>";

	if (!$rater->checkrated("download", $dl['download_id']) && USER) {
		$text .= $rater->rateselect("&nbsp;&nbsp;&nbsp;&nbsp; <b>".LAN_dl_14, "download", $dl['download_id'])."</b>";
	}
	else if(!USER) {
		$text .= "&nbsp;";
	} else {
		$text .= LAN_dl_15;
	}
	$text .= "</td></tr></table>";
return $text;
SC_END

SC_BEGIN DOWNLOAD_REPORT_LINK
global $dl;
return "<a href='".e_SELF."?report.".$dl['download_id']."'>".LAN_dl_45."</a>";
SC_END

SC_BEGIN DOWNLOAD_VIEW_CAPTION
global $dl;
	$text = $dl['download_category_name'];
	$text .= ($dl['download_category_description']) ? " [ ".$dl['download_category_description']." ]" : "";
return $text;
SC_END


// --------- Download View Lans -----------------------------

SC_BEGIN DOWNLOAD_VIEW_AUTHOR_LAN
global $dl;
return ($dl['download_author']) ? LAN_dl_24 : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_AUTHOREMAIL_LAN
global $dl;
return ($dl['download_author_email']) ? LAN_dl_30 : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_AUTHORWEBSITE_LAN
global $dl;
return ($dl['download_author_website']) ? LAN_dl_31 : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_DATE_LAN
global $dl;
return ($dl['download_datestamp']) ? LAN_dl_22 : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_IMAGE_LAN
return LAN_dl_11;
SC_END

SC_BEGIN DOWNLOAD_VIEW_REQUESTED
global $dl;
return $dl['download_requested'];
SC_END

SC_BEGIN DOWNLOAD_VIEW_RATING_LAN
return LAN_dl_12;
SC_END

SC_BEGIN DOWNLOAD_VIEW_FILESIZE_LAN
return LAN_dl_10;
SC_END

SC_BEGIN DOWNLOAD_VIEW_DESCRIPTION_LAN
return LAN_dl_7;
SC_END

SC_BEGIN DOWNLOAD_VIEW_REQUESTED_LAN
return LAN_dl_18;
SC_END

SC_BEGIN DOWNLOAD_VIEW_LINK_LAN
return LAN_dl_32;
SC_END



//  -----------  Download View : Previous and Next  ---------------

SC_BEGIN DOWNLOAD_VIEW_PREV
global $dl,$sql;

	$dl_id = intval($dl['download_id']);

	if ($sql->db_Select("download", "*", "download_category='".intval($dl['download_category_id'])."' AND download_id < {$dl_id} AND download_active > 0 && download_visible IN (".USERCLASS_LIST.") ORDER BY download_datestamp DESC LIMIT 1")) {
		$row = $sql->db_Fetch();
		return "<a href='".e_SELF."?view.".$row['download_id']."'>&lt;&lt; ".LAN_dl_33." [".$row['download_name']."]</a>\n";
	} else {
		return "&nbsp;";
	}
SC_END

SC_BEGIN DOWNLOAD_VIEW_NEXT
global $dl,$sql;
$dl_id = intval($dl['download_id']);
	if ($sql->db_Select("download", "*", "download_category='".intval($dl['download_category_id'])."' AND download_id > {$dl_id} AND download_active > 0 && download_visible IN (".USERCLASS_LIST.") ORDER BY download_datestamp ASC LIMIT 1")) {
		$row = $sql->db_Fetch();
		 extract($row);
		return "<a href='".e_SELF."?view.".$row['download_id']."'>[".$row['download_name']."] ".LAN_dl_34." &gt;&gt;</a>\n";
	} else {
		return "&nbsp;";
	}
SC_END


SC_BEGIN DOWNLOAD_BACK_TO_LIST
global $dl;
return "<a href='".e_SELF."?list.".$dl['download_category']."'>".LAN_dl_35."</a>";
SC_END


*/
?>