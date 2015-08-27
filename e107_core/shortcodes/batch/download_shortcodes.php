<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_files/shortcode/batch/download_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$download_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*
SC_BEGIN DOWNLOAD_LIST_NAME
global $row,$tp,$pref;
if($parm == "nolink"){
	return $tp->toHTML($row['download_name'],TRUE,'LINKTEXT');
}
if($parm == "request"){

	$agreetext = $tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'));
	if($row['download_mirror_type']){
		$text = ($pref['agree_flag'] ? "<a href='".e_BASE."download.php?mirror.".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."download.php?mirror.".$row['download_id']."' title='".LAN_DOWNLOAD."'>");
	}else{
		$text = ($pref['agree_flag'] ? "<a href='".e_BASE."request.php?".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."request.php?".$row['download_id']."' title='".LAN_DOWNLOAD."'>");
	}
	$text .= $tp->toHTML($row['download_name'], FALSE, 'USER_TITLE')."</a>";
	return $text;
}

return  "<a href='".e_BASE."download.php?view.".$row['download_id']."'>".$tp->toHTML($row['download_name'],TRUE,'LINKTEXT')."</a>";
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
$img = "<img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' title='".LAN_DOWNLOAD."' />";
if($parm == "link"){
	return "<a href='".e_BASE."download.php?view.".$row['download_id']."' >".$img."</a>";
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
global $row, $e107;
return $e107->parseMemorySize($row['download_filesize']);
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
	return "<a href='".e_BASE."download.php?view.".$row['download_id']."'>".$img."</a>";
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
$agreetext = $tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'));
	if($row['download_mirror_type']){
		return ($pref['agree_flag'] ? "<a href='".e_BASE."download.php?mirror.".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."download.php?mirror.".$row['download_id']."' >");
	}else{
		return ($pref['agree_flag'] ? "<a href='".e_BASE."request.php?".$row['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."request.php?".$row['download_id']."' >");
	}
SC_END

SC_BEGIN DOWNLOAD_LIST_NEXTPREV
	global $nextprev_parms,$tp;
 	return $tp->parseTemplate("{NEXTPREV={$nextprev_parms}}");
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

SC_BEGIN DOWNLOAD_CATEGORY_ICON
global $dl;
list($present,$missing) = explode(chr(1),$dl['download_category_icon']);
if($present)
{
	return "<img class='dl_cat_icon' src='".e_IMAGE."icons/".$present."' alt='' />";
}

SC_END

SC_BEGIN DOWNLOAD_CATEGORY_DESCRIPTION
global $tp,$dl;
$text = $tp -> toHTML($dl['download_category_description'], TRUE,'DESCRIPTION');
if($parm){
	return substr($text,0,$parm);
}else{
	return $text;
}
SC_END

SC_BEGIN DOWNLOAD_VIEW_NAME
global $dl;
$link['view'] = "<a href='".e_BASE."download.php?view.".$dl['download_id']."'>".$dl['download_name']."</a>";
$link['request'] = "<a href='".e_BASE."request.php?".$dl['download_id']."' title='".LAN_dl_46."'>".$dl['download_name']."</a>";

if($parm){
	return $link[$parm];
}
return $dl['download_name'];
SC_END

SC_BEGIN DOWNLOAD_VIEW_NAME_LINKED
global $pref,$dl,$tp;
if ($pref['agree_flag'] == 1) {
	return "<a href='".e_BASE."request.php?".$dl['download_id']."' onclick= \"return confirm('".$tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'))."');\" title='".LAN_dl_46."'>".$dl['download_name']."</a>";
} else {
	return "<a href='".e_BASE."request.php?".$dl['download_id']."' title='".LAN_dl_46."'>".$dl['download_name']."</a>";
}
SC_END

SC_BEGIN DOWNLOAD_VIEW_AUTHOR
global $dl;
return ($dl['download_author'] ? $dl['download_author'] : "");
SC_END


SC_BEGIN DOWNLOAD_VIEW_AUTHOREMAIL
global $tp,$dl;
return ($dl['download_author_email']) ? $tp -> toHTML($dl['download_author_email'], TRUE, 'LINKTEXT') : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_AUTHORWEBSITE
global $tp,$dl;
return ($dl['download_author_website']) ? $tp -> toHTML($dl['download_author_website'], TRUE,'LINKTEXT') : "";
SC_END



SC_BEGIN DOWNLOAD_VIEW_DESCRIPTION
global $tp, $dl;
$maxlen = ($parm ? intval($parm) : 0);
$text = ($dl['download_description'] ?  $tp->toHTML($dl['download_description'], TRUE, 'DESCRIPTION') : "");
if($maxlen){
	return substr($text, 0, $maxlen);
}else{
	return $text;
}
return $text;
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
	$dnld_link = "<a href='".e_BASE."request.php?".$dl['download_id']."' onclick= \"return confirm('".$tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'))."');\">";
} else {
	$dnld_link = "<a href='".e_BASE."request.php?".$dl['download_id']."'>";
}

if($dl['download_mirror'])
{
	if($dl['download_mirror_type'])
	{
		return "<a href='".e_BASE."download.php?mirror.".$dl['download_id']."'>".LAN_dl_66."</a>";
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
global $dl, $e107;
return ($dl['download_filesize']) ? $e107->parseMemorySize($dl['download_filesize']) : "";
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
			$text .= "<img src='".e_IMAGE."rate/star.png' alt='' />";
		}
		if ($ratearray[2]) {
			$text .= "<img src='".e_IMAGE."rate/".$ratearray[2].".png'  alt='' />";
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
		$text .= LAN_THANK_YOU;
	}
	$text .= "</td></tr></table>";
return $text;
SC_END

SC_BEGIN DOWNLOAD_REPORT_LINK
global $dl,$pref;
return (check_class($pref['download_reportbroken'])) ? "<a href='".e_BASE."download.php?report.".$dl['download_id']."'>".LAN_dl_45."</a>" : "";
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
return ($dl['download_author']) ? LAN_AUTHOR : "";
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
return ($dl['download_datestamp']) ? LAN_DATE : "";
SC_END

SC_BEGIN DOWNLOAD_VIEW_IMAGE_LAN
return LAN_IMAGE;
SC_END

SC_BEGIN DOWNLOAD_VIEW_REQUESTED
global $dl;
return $dl['download_requested'];
SC_END

SC_BEGIN DOWNLOAD_VIEW_RATING_LAN
return LAN_RATING;
SC_END

SC_BEGIN DOWNLOAD_VIEW_FILESIZE_LAN
return LAN_SIZE;
SC_END

SC_BEGIN DOWNLOAD_VIEW_DESCRIPTION_LAN
return LAN_DESCRIPTION;
SC_END

SC_BEGIN DOWNLOAD_VIEW_REQUESTED_LAN
return LAN_dl_77;
SC_END

SC_BEGIN DOWNLOAD_VIEW_LINK_LAN
return LAN_DOWNLOAD;
SC_END



//  -----------  Download View : Previous and Next  ---------------

SC_BEGIN DOWNLOAD_VIEW_PREV
global $dl,$sql;

	$dl_id = intval($dl['download_id']);

	if ($sql->db_Select("download", "*", "download_category='".intval($dl['download_category_id'])."' AND download_id < {$dl_id} AND download_active > 0 && download_visible IN (".USERCLASS_LIST.") ORDER BY download_datestamp DESC LIMIT 1")) {
		$row = $sql->db_Fetch();
		return "<a href='".e_BASE."download.php?view.".$row['download_id']."'>&lt;&lt; ".LAN_dl_33." [".$row['download_name']."]</a>\n";
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
		return "<a href='".e_BASE."download.php?view.".$row['download_id']."'>[".$row['download_name']."] ".LAN_dl_34." &gt;&gt;</a>\n";
	} else {
		return "&nbsp;";
	}
SC_END


SC_BEGIN DOWNLOAD_BACK_TO_LIST
global $dl;
return "<a href='".e_BASE."download.php?list.".$dl['download_category']."'>".LAN_BACK."</a>";
SC_END

SC_BEGIN DOWNLOAD_BACK_TO_CATEGORY_LIST
	return "<a href='".e_SELF."'>".LAN_dl_9."</a>";
SC_END


//  ---------------   Download - Admin  -----------------------------------

SC_BEGIN DOWNLOAD_CATEGORY_SELECT
	global $sql;
	$cdc = $parm;

      	$boxinfo = "\n";
	  	$qry = "
	  	SELECT dc.download_category_name, dc.download_category_order, dc.download_category_id, dc.download_category_parent,
	  	dc1.download_category_parent AS d_parent1
	  	FROM #download_category AS dc
	  	LEFT JOIN #download_category as dc1 ON dc1.download_category_id=dc.download_category_parent AND dc1.download_category_class IN (".USERCLASS_LIST.")
	    LEFT JOIN #download_category as dc2 ON dc2.download_category_id=dc1.download_category_parent ";
        if (ADMIN === FALSE) $qry .= " WHERE dc.download_category_class IN (".USERCLASS_LIST.") ";
	 	$qry .= " ORDER by dc2.download_category_order, dc1.download_category_order, dc.download_category_order";   // This puts main categories first, then sub-cats, then sub-sub cats

  	  	if (!$sql->db_Select_gen($qry))
	  	{
	    	return "Error reading categories<br />";
	    	exit;
	  	}

	  	$boxinfo .= "<select name='download_category' id='download_category' class='tbox form-control' required>
					<option value=''>&nbsp;</option>\n";

		// Its a structured display option - need a 2-step process to create a tree
	    $catlist = array();
	    while ($row = $sql->db_Fetch(MYSQL_ASSOC))
	    {
			$tmp = $row['download_category_parent'];
	      	if ($tmp == '0')
	      	{
		    	$row['subcats'] = array();
	        	$catlist[$row['download_category_id']] = $row;
	      	}
	      	else
	      	{
	        	if (isset($catlist[$tmp]))
		    	{  // Sub-Category
		      		$catlist[$tmp]['subcats'][$row['download_category_id']] = $row;
		      		$catlist[$tmp]['subcats'][$row['download_category_id']]['subsubcats'] = array();
		    	}
		    	else
		    	{  // Its a sub-sub category
		      		if (isset($catlist[$row['d_parent1']]['subcats'][$tmp]))
		      		{
		        		$catlist[$row['d_parent1']]['subcats'][$tmp]['subsubcats'][$row['download_category_id']] = $row;
		      		}
		    	}
	      	}
	    }

		// Now generate the options
	    foreach ($catlist as $thiscat)
	    {  // Main categories
			// Could add a display class to the group, but the default looked OK

            if(count($thiscat['subcats'])>0)
			{
				$boxinfo .= "<optgroup label='".htmlspecialchars($thiscat['download_category_name'])."'>\n";
		  		$scprefix = '';
			}
			else
			{
				$sel = ($cdc == $thiscat['download_category_id']) ? " selected='selected'" : "";
            	$boxinfo .= "<option value='".$thiscat['download_category_id']."' {$sel}>".htmlspecialchars($thiscat['download_category_name'])."</option>\n";
			}

	      	foreach ($thiscat['subcats'] as $sc)
	      	{  // Sub-categories
		    	$sscprefix = '--> ';
		    	$boxinfo .= "<option value='".$sc['download_category_id']."'";
		    	if ($cdc == $sc['download_category_id']) { $boxinfo .= " selected='selected'"; }
		   		$boxinfo .= ">".$scprefix.htmlspecialchars($sc['download_category_name'])."</option>\n";
		    	foreach ($sc['subsubcats'] as $ssc)
		    	{  // Sub-sub categories
		      		$boxinfo .= "<option value='".$ssc['download_category_id']."'";
		      		if ($cdc == $ssc['download_category_id']) { $boxinfo .= " selected='selected'"; }
		      		$boxinfo .= ">".htmlspecialchars($sscprefix.$ssc['download_category_name'])."</option>\n";
		    	}
	      	}
			$boxinfo .= "</optgroup>\n";
	    }

	  $boxinfo .= "</select>\n";
	  return $boxinfo;

SC_END






*/
?>
