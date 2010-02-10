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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/download_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
require_once(e_HANDLER.'shortcode_handler.php');
register_shortcode('download_shortcodes', true);
initShortcodeClass('download_shortcodes');

/**
 * download_shortcodes
 */
class download_shortcodes
{
	var $e107;
	var $postInfo;

   /**
    * download_shortcodes constructor
    */
	function download_shortcodes()
	{
		$this->e107 = e107::getInstance();
		$this->postInfo = array();
	}
	// Category ************************************************************************************
   function sc_download_cat_main_name() {
      global $tp, $dlrow;
      return $tp->toHTML($dlrow['download_category_name'], FALSE, 'TITLE');
   }
   function sc_download_cat_main_description() {
      global $tp, $dlrow;
      return $tp->toHTML($dlrow['download_category_description'], TRUE, 'DESCRIPTION');
   }
   function sc_download_cat_main_icon() {
      global $dlrow;
      // Pass count as 1 to force non-empty icon
      return $this->_sc_cat_icons($dlrow['download_category_icon'], 1, $dlrow['download_category_name']);
   }
	// Sub-Category ********************************************************************************
   function sc_download_cat_sub_name() {
      global $tp, $dlsubrow;
      if ($dlsubrow['d_count'])
      {
         return "<a href='".e_PLUGIN."download/download.php?list.".$dlsubrow['download_category_id']."'>".$tp->toHTML($dlsubrow['download_category_name'], FALSE, 'TITLE')."</a>";
      }
      else
      {
         return $tp->toHTML($dlsubrow['download_category_name'], FALSE, 'TITLE');
      }
   }
   function sc_download_cat_sub_description() {
      global $tp, $dlsubrow;
      return $tp->toHTML($dlsubrow['download_category_description'], TRUE, 'DESCRIPTION');
   }
   function sc_download_cat_sub_icon() {
      global $dlsubrow;
      return $this->_sc_cat_icons($dlsubrow['download_category_icon'], $dlsubrow['d_count'], $dlsubrow['download_category_name']);
   }
   function sc_download_cat_sub_new_icon() {
      global $dlsubrow;
      return $this->_check_new_download($dlsubrow['d_last_subs']);
   }
   function sc_download_cat_sub_count() {
      global $dlsubrow;
      return $dlsubrow['d_count'];
   }
   function sc_download_cat_sub_size() {
      global $e107, $dlsubrow;
      return $this->e107->parseMemorySize($dlsubrow['d_size']);
   }
   function sc_download_cat_sub_downloaded() {
      global $dlsubrow;
      return intval($dlsubrow['d_requests']);
   }
	// Sub-Sub-Category ****************************************************************************
   function sc_download_cat_subsub_name() {
      global $tp, $dlsubsubrow;
      if ($dlsubsubrow['d_count'])
      {
         return "<a href='".e_PLUGIN."download/download.php?list.".$dlsubsubrow['download_category_id']."'>".$tp->toHTML($dlsubsubrow['download_category_name'], FALSE, 'TITLE')."</a>";
      }
      else
      {
         return $tp->toHTML($dlsubsubrow['download_category_name'], FALSE, 'TITLE');
      }
   }
   function sc_download_cat_subsub_description() {
      global $tp, $dlsubsubrow;
      return $tp->toHTML($dlsubsubrow['download_category_description'], TRUE, 'DESCRIPTION');
   }
   function sc_download_cat_subsub_icon() {
      global $dlsubsubrow;
      return $this->_sc_cat_icons($dlsubsubrow['download_category_icon'], $dlsubsubrow['d_count'], $dlsubsubrow['download_category_name']);
   }
   function sc_download_cat_subsub_count() {
      global $dlsubsubrow;
      return $dlsubsubrow['d_count'];
   }
   function sc_download_cat_subsub_size() {
      global $e107, $dlsubsubrow;
      return $this->e107->parseMemorySize($dlsubsubrow['d_size']);
   }
   function sc_download_cat_subsub_downloaded() {
      global $dlsubsubrow;
      return intval($dlsubsubrow['d_requests']);
   }
	// List ****************************************************************************************
   function sc_download_list_name()
   {
      global $dlrow,$tp,$pref,$parm;
      if ($parm == "nolink"){
      	return $tp->toHTML($dlrow['download_name'],TRUE,'LINKTEXT');
      }
      if ($parm == "request"){
      	$agreetext = $tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'));
      	if ($dlrow['download_mirror_type']){
      		$text = ($pref['agree_flag'] ? "<a href='".e_PLUGIN."download/download.php?mirror.".$dlrow['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_PLUGIN."download/download.php?mirror.".$dlrow['download_id']."' title='".LAN_dl_32."'>");
      	}else{
      		$text = ($pref['agree_flag'] ? "<a href='".e_BASE."request.php?".$dlrow['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."request.php?".$dlrow['download_id']."' title='".LAN_dl_32."'>");
      	}
      	$text .= $tp->toHTML($dlrow['download_name'], FALSE, 'TITLE')."</a>";
      	return $text;
      }
      return  "<a href='".e_PLUGIN."download/download.php?view.".$dlrow['download_id']."'>".$tp->toHTML($dlrow['download_name'],TRUE,'LINKTEXT')."</a>";
   }
   function sc_download_list_author()
   {
      global $dlrow;
      return $dlrow['download_author'];
   }
   function sc_download_list_requested()
   {
      global $dlrow;
      return $dlrow['download_requested'];
   }
   function sc_download_list_newicon()
   {
      global $dlrow;
      return (USER && $dlrow['download_datestamp'] > USERLV ? "<img src='".IMAGE_NEW."' alt='*' style='vertical-align:middle' />" : "");
   }
   function sc_download_list_recenticon()
   {
      global $dlrow, $pref;
      // convert "recent_download_days" to seconds
      return ($dlrow['download_datestamp'] > time()-($pref['recent_download_days']*86400) ? '<img src="'.IMAGE_NEW.'" alt="" style="vertical-align:middle" />' : '');
   }
   function sc_download_list_filesize()
   {
      global $dlrow, $e107;
      return $e107->parseMemorySize($dlrow['download_filesize']);
   }
   function sc_download_list_datestamp()
   {
      global $dlrow;
      $gen = new convert;
      return $gen->convert_date($dlrow['download_datestamp'], "short");
   }
   function sc_download_list_thumb()
   {
      global $dlrow,$parm;
      $img = ($dlrow['download_thumb']) ? "<img src='".e_FILE."downloadthumbs/".$dlrow['download_thumb']."' alt='*' style='".DL_IMAGESTYLE."' />" : "";
      if ($parm == "link" && $dlrow['download_thumb']){
      	return "<a href='".e_PLUGIN."download/download.php?view.".$dlrow['download_id']."'>".$img."</a>";
      }
      else
      {
      	return $img;
      }
   }
   function sc_download_list_id()
   {
      global $dlrow;
      return $dlrow['download_id'];
   }
   function sc_download_list_rating()
   {
      global $dlrow;
      $rater = new rater();
      $ratearray = $rater->getrating("download", $dlrow['download_id']);
     	if (!$ratearray[0]) {
     		return LAN_dl_13;
     	}
     	else
     	{
     		return ($ratearray[2] ? "{$ratearray[1]}.{$ratearray[2]}/{$ratearray[0]}" : "{$ratearray[1]}/{$ratearray[0]}");
     	}
   }
   function sc_download_list_link()
   {
      global $tp, $dlrow, $pref, $parm;
      $agreetext = $tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'));

      $img = "<img src='".IMAGE_DOWNLOAD."' alt='".LAN_dl_32."' title='".LAN_dl_32."' />";
     	if ($dlrow['download_mirror_type'])
     	{
     		return "<a href='".e_PLUGIN."download/download.php?mirror.".$dlrow['download_id']."'>{$img}</a>";
     	}
     	else
     	{
     		return ($pref['agree_flag'] ? "<a href='".e_BASE."request.php?".$dlrow['download_id']."' onclick= \"return confirm('{$agreetext}');\">{$img}</a>" : "<a href='".e_BASE."request.php?".$dlrow['download_id']."' >{$img}</a>");
     	}
   }
   function sc_download_list_icon()
   {
      global $dlrow,$parm;
      if ($parm == "link"){
      	return "<a href='".e_PLUGIN."download/download.php?view.".$dlrow['download_id']."' >".$img."</a>";
      }
      else
      {
      	return $img;
      }
      return;
   }
   function sc_download_list_nextprev()
   {
     	global $nextprev_parms,$tp;
     	return $tp->parseTemplate("{NEXTPREV={$nextprev_parms}}");
   }
   function sc_download_list_total_amount() {
      global $dltdownloads;
      return $dltdownloads." ".LAN_dl_16;
   }
   function sc_download_list_total_files() {
      global $dlft;
      return $dlft." ".LAN_dl_17;
   }
	// View ****************************************************************************************
   function sc_download_view_id()
   {
      global $dlrow;
      return $dlrow['download_id'];
   }
   function sc_download_admin_edit()
   {
      global $dlrow;
      return (ADMIN && getperms('6')) ? "<a href='".e_ADMIN."download.php?create.edit.".$dlrow['download_id']."' title='edit'><img src='".e_IMAGE."generic/lite/edit.png' alt='*' style='padding:0px;border:0px' /></a>" : "";
   }
   function sc_download_category()
   {
      global $dlrow;
      return $dlrow['download_category_name'];
   }
   function sc_download_category_description()
   {
      global $tp,$dl,$parm;
      $text = $tp -> toHTML($dl['download_category_description'], TRUE,'DESCRIPTION');
      if ($parm){
      	return substr($text,0,$parm);
      }else{
      	return $text;
      }
   }
   function sc_download_view_name()
   {
      global $dlrow,$parm;
      $link['view'] = "<a href='".e_PLUGIN."download/download.php?view.".$dlrow['download_id']."'>".$dlrow['download_name']."</a>";
      $link['request'] = "<a href='".e_BASE."request.php?".$dlrow['download_id']."' title='".LAN_dl_46."'>".$dlrow['download_name']."</a>";
      if ($parm){
      	return $link[$parm];
      }
      return $dlrow['download_name'];
   }
   function sc_download_view_name_linked()
   {
      global $pref,$dl,$tp;
      if ($pref['agree_flag'] == 1) {
      	return "xxx<a href='".e_BASE."request.php?".$dl['download_id']."' onclick= \"return confirm('".$tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'))."');\" title='".LAN_dl_46."'>".$dl['download_name']."</a>";
      } else {
      	return "<a href='".e_BASE."request.php?".$dl['download_id']."' title='".LAN_dl_46."'>".$dl['download_name']."</a>";
      }
   }
   function sc_download_view_author()
   {
      global $dlrow;
      return ($dlrow['download_author'] ? $dlrow['download_author'] : "");
   }
   function sc_download_view_authoremail()
   {
      global $tp,$dlrow;
      return ($dlrow['download_author_email']) ? $tp -> toHTML($dlrow['download_author_email'], TRUE, 'LINKTEXT') : "";
   }
   function sc_download_view_authorwebsite()
   {
      global $tp,$dlrow;
      return ($dlrow['download_author_website']) ? $tp -> toHTML($dlrow['download_author_website'], TRUE,'LINKTEXT') : "";
   }
   function sc_download_view_description()
   {
      global $tp,$dlrow,$parm;
      $maxlen = ($parm ? intval($parm) : 0);
      $text = ($dlrow['download_description'] ?  $tp->toHTML($dlrow['download_description'], TRUE, 'DESCRIPTION') : "");
      if ($maxlen){
      	return substr($text, 0, $maxlen);
      }else{
      	return $text;
      }
      return $text;
   }
   function sc_download_view_date()
   {
      global $gen,$dlrow,$parm;
      return ($dlrow['download_datestamp']) ? $gen->convert_date($dlrow['download_datestamp'], $parm) : "";
   }
   function sc_download_view_date_short()
   {
      // deprecated: DOWNLOAD_VIEW_DATE should be used instead.
      global $gen,$dlrow;
      return ($dlrow['download_datestamp']) ? $gen->convert_date($dlrow['download_datestamp'], "short") : "";
   }
   function sc_download_view_date_long()
   {
      // deprecated: DOWNLOAD_VIEW_DATE should be used instead.
      global $gen,$dlrow;
      return ($dlrow['download_datestamp']) ? $gen->convert_date($dlrow['download_datestamp'], "long") : "";
   }
   function sc_download_view_image()
   {
      global $dlrow;
      if ($dlrow['download_thumb']) {
      	return ($dlrow['download_image'] ? "<a href='".e_BASE."request.php?download.".$dlrow['download_id']."'><img class='dl_image' src='".e_FILE."downloadthumbs/".$dlrow['download_thumb']."' alt='*' style='".DL_IMAGESTYLE."' /></a>" : "<img class='dl_image' src='".e_FILE."downloadthumbs/".$dlrow['download_thumb']."' alt='*' style='".DL_IMAGESTYLE."' />");
      }
      else if ($dlrow['download_image']) {
      	return "<a href='".e_BASE."request.php?download.".$dlrow['download_id']."'>".LAN_dl_40."</a>";
      }
      else
      {
      	return LAN_dl_75;
      }
   }
   function sc_download_view_imagefull()
   {
      global $dlrow;
      return ($dlrow['download_image']) ? "<img class='dl_image' src='".e_FILE."downloadimages/".$dlrow['download_image']."' alt='*' style='".DL_IMAGESTYLE."' />" : "";
   }
   function sc_download_view_link()
   {
      global $pref,$dlrow,$tp;
      $click = "";
      if ($pref['agree_flag'] == 1) {
      	$click = " onclick='return confirm(\"".$tp->toJS($tp->toHTML($pref['agree_text'],true,'emotes, no_tags'))."\")'";
      }
     	$dnld_link = "<a href='".e_BASE."request.php?".$dlrow['download_id']."'{$click}>";
      if ($dlrow['download_mirror'])
      {
      	if ($dlrow['download_mirror_type'])
      	{
      		return "<a href='".e_PLUGIN."download/download.php?mirror.".$dlrow['download_id']."'>".LAN_dl_66."</a>";
      	}
      	else
      	{
      		return $dnld_link."<img src='".IMAGE_DOWNLOAD."' alt='*' /></a>";
      	}
      }
      else
      {
      	return $dnld_link." xxx<img src='".IMAGE_DOWNLOAD."' alt='*' /></a>";
      }
   }
   function sc_download_view_filesize()
   {
      global $dlrow, $e107;
      return ($dlrow['download_filesize']) ? $e107->parseMemorySize($dlrow['download_filesize']) : "";
   }
   function sc_download_view_rating()
   {
      	require_once(e_HANDLER."rate_class.php");
      	$rater = new rater;
      	global $dlrow;
      	$text = "
      		<table style='width:100%'>
      		<tr>
      		<td style='width:50%'>";
      	if ($ratearray = $rater->getrating("download", $dlrow['download_id'])) {
      		for($c = 1; $c <= $ratearray[1]; $c++) {
      			$text .= "<img src='".e_IMAGE."rate/star.png' alt='*' />";
      		}
      		if ($ratearray[2]) {
      			$text .= "<img src='".e_IMAGE."rate/".$ratearray[2].".png'  alt='*' />";
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
      	if (!$rater->checkrated("download", $dlrow['download_id']) && USER) {
      		$text .= $rater->rateselect("&nbsp;&nbsp;&nbsp;&nbsp; <b>".LAN_dl_14, "download", $dlrow['download_id'])."</b>";
      	}
      	else if (!USER) {
      		$text .= "&nbsp;";
      	} else {
      		$text .= LAN_dl_15;
      	}
      	$text .= "</td></tr></table>";
      return $text;
   }
   function sc_download_report_link()
   {
      global $dlrow,$pref;
      return (check_class($pref['download_reportbroken'])) ? "<a href='".e_PLUGIN."download/download.php?report.".$dlrow['download_id']."'>".LAN_dl_45."</a>" : "";
   }
   function sc_download_view_caption()
   {
      global $dlrow;
     	$text = $dlrow['download_category_name'];
     	$text .= ($dlrow['download_category_description']) ? " [ ".$dlrow['download_category_description']." ]" : "";
      return $text;
   }
	// Mirror **************************************************************************************
	function sc_download_mirror_request() {
	   global $dlrow;
	   return $dlrow['download_name'];
	}
	function sc_download_mirror_request_icon() {
	   global $dlrow;
      return ($dlrow['download_thumb'] ? "<img src='".e_FILE."downloadthumbs/".$dlrow['download_thumb']."' alt='*'/>" : "");
	}
	function sc_download_mirror_name() {
	   global $dlmirror;
      return "<a href='{$dlmirror['mirror_url']}' rel='external'>".$dlmirror['mirror_name']."</a>";
	}
	function sc_download_mirror_image() {
	   global $dlrow, $dlmirror;
      return ($dlmirror['mirror_image'] ? "<a href='{$dlmirror['mirror_url']}' rel='external'><img src='".e_FILE."downloadimages/".$dlmirror['mirror_image']."' alt='*'/></a>" : "");
	}
	function sc_download_mirror_location() {
	   global $dlmirror;
      return ($dlmirror['mirror_location'] ? $dlmirror['mirror_location'] : "");
	}
	function sc_download_mirror_description() {
	   global $dlmirror,$tp;
      return ($dlmirror['mirror_description'] ? $tp->toHTML($dlmirror['mirror_description'], TRUE) : "");
	}
	function sc_download_mirror_filesize() {
	   global $e107, $dlmirrorfile;
      return $e107->parseMemorySize($dlmirrorfile[3]);
	}
	function sc_download_mirror_link() {
	   global $dlrow, $dlmirrorfile, $tp, $pref;
    	$click = " onclick='return confirm(\"".$tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'))."\")'";
      return "<a href='".e_PLUGIN."download/download.php?mirror.{$dlrow['download_id']}.{$dlmirrorfile[0]}' title='".LAN_dl_32."'{$click}>
              <img src='".IMAGE_DOWNLOAD."' alt='*' title='".LAN_dl_32."' /></a>";
	}
	function sc_download_mirror_requests() {
	   global $dlmirrorfile;
      return (ADMIN ? LAN_dl_73.$dlmirrorfile[2] : "");
	}
	function sc_download_total_mirror_requests() {
	   global $dlmirror;
	   return (ADMIN ? LAN_dl_74.$dlmirror['mirror_count'] : "");
	}
   // --------- Download View Lans -----------------------------
   function sc_download_view_author_lan()
   {
      global $dlrow;
      return ($dlrow['download_author']) ? LAN_dl_24 : "";
   }
   function sc_download_view_authoremail_lan()
   {
      global $dlrow;
      return ($dlrow['download_author_email']) ? LAN_dl_30 : "";
   }
   function sc_download_view_authorwebsite_lan()
   {
      global $dlrow;
      return ($dlrow['download_author_website']) ? LAN_dl_31 : "";
   }
   function sc_download_view_date_lan()
   {
      global $dlrow;
      return ($dlrow['download_datestamp']) ? LAN_dl_22 : "";
   }
   function sc_download_view_image_lan()
   {
      return LAN_dl_11;
   }
   function sc_download_view_requested()
   {
      global $dlrow;
      return $dlrow['download_requested'];
   }
   function sc_download_view_rating_lan()
   {
      return LAN_dl_12;
   }
   function sc_download_view_filesize_lan()
   {
      return LAN_dl_10;
   }
   function sc_download_view_description_lan()
   {
      return LAN_dl_7;
   }
   function sc_download_view_requested_lan()
   {
      return LAN_dl_77;
   }
   function sc_download_view_link_lan()
   {
      return LAN_dl_32;
   }
      //  -----------  Download View : Previous and Next  ---------------
   function sc_download_view_prev()
   {
      global $dlrow,$sql;
      	$dlrow_id = intval($dlrow['download_id']);
      	if ($sql->db_Select("download", "*", "download_category='".intval($dlrow['download_category_id'])."' AND download_id < {$dlrow_id} AND download_active > 0 && download_visible IN (".USERCLASS_LIST.") ORDER BY download_datestamp DESC LIMIT 1")) {
      		$dlrowrow = $sql->db_Fetch();
      		return "<a href='".e_PLUGIN."download/download.php?view.".$dlrowrow['download_id']."'>&lt;&lt; ".LAN_dl_33." [".$dlrowrow['download_name']."]</a>\n";
      	} else {
      		return "&nbsp;";
      	}
   }
   function sc_download_view_next()
   {
      global $dlrow,$sql;
      $dlrow_id = intval($dlrow['download_id']);
      	if ($sql->db_Select("download", "*", "download_category='".intval($dlrow['download_category_id'])."' AND download_id > {$dlrow_id} AND download_active > 0 && download_visible IN (".USERCLASS_LIST.") ORDER BY download_datestamp ASC LIMIT 1")) {
      		$dlrowrow = $sql->db_Fetch();
      		 extract($dlrowrow);
      		return "<a href='".e_PLUGIN."download/download.php?view.".$dlrowrow['download_id']."'>[".$dlrowrow['download_name']."] ".LAN_dl_34." &gt;&gt;</a>\n";
      	} else {
      		return "&nbsp;";
      	}
   }
   function sc_download_back_to_list()
   {
      global $dlrow;
      return "<a href='".e_PLUGIN."download/download.php?list.".$dlrow['download_category']."'>".LAN_dl_35."</a>";
   }
   function sc_download_back_to_category_list()
   {
      	return "<a href='".e_SELF."'>".LAN_dl_9."</a>";
   }
   // Misc stuff ---------------------------------------------------------------------------------
   function sc_download_cat_newdownload_text()
   {
      return "<img src='".IMAGE_NEW."' alt='*' style='vertical-align:middle' /> ".LAN_dl_36;
   }
   function sc_download_cat_search()
   {
      return "<form method='get' action='".e_BASE."search.php'>
      		  <p>
      		  <input class='tbox' type='text' name='q' size='30' value='' maxlength='50' />
      		  <input class='button' type='submit' name='s' value='".LAN_dl_41."' />
      		  <input type='hidden' name='r' value='0' />
      		  </p>
      		  </form>";
   }
	/**
	 * @private
	 */
	function _sc_cat_icons($source, $count, $alt)
	{
	   if (!$source) return "&nbsp;";
	   list($ret[TRUE],$ret[FALSE]) = explode(chr(1), $source.chr(1));
	   if (!$ret[FALSE]) $ret[FALSE] = $ret[TRUE];
		return "<img src='".e_IMAGE."icons/{$ret[($count!=0)]}' alt='*'/>";
	}
   function _check_new_download($last_val)
	{
		if (USER && ($last_val > USERLV))
		{
		   return "<img src='".IMAGE_NEW."' alt='*' style='vertical-align:middle' />";
		}
		else
		{
		   return "";
		}
	}
}
?>