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
|     $Source: /cvs_backup/e107_0.7/e107_admin/admin.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-05 16:57:36 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
require_once('auth.php');
require(e_ADMIN.'ad_links.php');
require_once(e_HANDLER.'admin_handler.php');

// auto db update
if ('0' == ADMINPERMS) {
	@require_once(e_ADMIN.'update_routines.php');
	@update_check();
}
// end auto db update

if (e_QUERY == 'purge') {
	$sql -> db_Delete("tmp", "tmp_ip='adminlog'");
}

$adminfpage = (!$pref['adminstyle'] || $pref['adminstyle'] == 'default') ? 'admin_default.php' : $pref['adminstyle'].'.php';
require_once(e_ADMIN.'includes/'.$adminfpage);



// info -------------------------------------------------------
if ($admin_info) {
$members = $sql -> db_Count("user");
$unverified = $sql -> db_Count("user", "(*)", "WHERE user_ban=2");
$banned = $sql -> db_Count("user", "(*)", "WHERE user_ban=1");
$chatbox_posts = $sql -> db_Count("chatbox");
$forum_posts = $sql -> db_Count("forum_t");
$comments = $sql -> db_Count("comments");
$permicon = "<img src='".e_IMAGE."generic/location.png' alt='' style='vertical-align:middle' /> ";
$permicon2 = "<img src='".e_IMAGE."generic/rname.png' alt='' style='vertical-align:middle' /> ";
$active_uploads = $sql -> db_Select("upload", "*", "upload_active=0");
$submitted_links = $sql -> db_Select("tmp", "*", "tmp_ip='submitted_link' ");
$reported_posts = $sql -> db_Select("tmp", "*", "tmp_ip='reported_post' ");
$submitted_news = $sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ");
$submitted_articles = $sql -> db_Select("content", "*", "content_type ='15' ");
$submitted_reviews = $sql -> db_Select("content", "*", "content_type ='16' ");

if ($admin_info_style) {
	$text = "<div style='text-align:center'>
	<table class='fborder' style='width:95%'>
	<tr><td class='forumheader'>".ADLAN_134."</td><td class='forumheader'>".ADLAN_135."</td></tr>";
	$text .= "<tr><td class='forumheader3' style='width:50%; vertical-align:top'>";
} else {
	$text = "<div style='text-align:center'>
	<table style='width:95%'>
	<tr>
	<td style='width:50%; vertical-align:top'>";
}

$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_USER." ".ADLAN_110.": ".$members."</div>";
$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_USER." ".ADLAN_111.": ".$unverified."</div>";
$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_BANLIST." ".ADLAN_112.": ".$banned."<br /><br /></div>";

$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_NEWS.($submitted_news ? " <a href='".e_ADMIN."newspost.php?sn'>".ADLAN_107.": $submitted_news</a>" : " ".ADLAN_107.": 0")."</div>";
$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_ARTICLE.($submitted_articles ? " <a href='".e_ADMIN."article.php?sa'>".ADLAN_123.": $submitted_articles</a>" : " ".ADLAN_123.": ".$submitted_articles)."</div>";
$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_REVIEW.($submitted_reviews ? " <a href='".e_ADMIN."review.php?sa'>".ADLAN_124.": $submitted_reviews</a>" : " ".ADLAN_124.": ".$submitted_reviews)."</div>";
$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_LINKS.($submitted_links ? " <a href='".e_ADMIN."links.php?sn'>".ADLAN_119.": $submitted_links</a>" : " ".ADLAN_119.": ".$submitted_links)."<br /><br /></div>";

$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_FORUM.($reported_posts ? " <a href='".e_ADMIN."forum.php?sr'>".ADLAN_125.": $reported_posts</a>" : " ".ADLAN_125.": ".$reported_posts)."<br /><br /></div>";

$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_UPLOADS.($active_uploads ? " <a href='".e_ADMIN."upload.php'>".ADLAN_108.": $active_uploads</a>" : " ".ADLAN_108.": ".$active_uploads)."<br /><br /></div>";

$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_FORUM." ".ADLAN_113.": ".$forum_posts."</div>";
$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_COMMENT." ".ADLAN_114.": ".$comments."</div>";
$text .= "<div style='padding-bottom: 2px;'>".SML_IMG_CHAT." ".ADLAN_115.": ".$chatbox_posts."</div>";

if ($admin_info_style) {
	$text .= "</td>
	<td class='forumheader3' style='width:50%; vertical-align:top'>";
} else {
	$text .= "</td>
	<td style='width:50%; vertical-align:top'>";
}

$text .= SML_IMG_ADMINLOG." <a style='cursor: pointer; cursor: hand' onclick=\"expandit(this)\">".ADLAN_116."</a>\n";
if (e_QUERY != "logall") {
	$text .= "<div style='display: none;'>";
}
if (e_QUERY == "logall") {
	$sql -> db_Select("tmp", "*", "tmp_ip='adminlog' ORDER BY tmp_time DESC");
} else {
	$sql -> db_Select("tmp", "*", "tmp_ip='adminlog' ORDER BY tmp_time DESC LIMIT 0,10");
}
$text .= '<ul>';
$gen = new convert;
while ($row = $sql -> db_Fetch()) {
        $datestamp = $gen->convert_date($row['tmp_time'], 'short');
        $text .= '<li>'.$datestamp.$row['tmp_info'].'</li>';
}
$text .= '</ul>';

$text .= "[ <a href='".e_SELF."?logall'>".ADLAN_117."</a> ][ <a href='".e_SELF."?purge'>".ADLAN_118."</a> ]\n</div>
</td></tr></table></div>";

$ns -> tablerender(ADLAN_109, $text);
}

require_once("footer.php");

function headerjs(){
	global $pref;
	if ($pref['adminstyle']=='adminb') {
		$norm_class = 'forumheader3';
		$over_class = 'forumheader4';
	} else {
		$norm_class = 'td';
		$over_class = 'forumheader5';
	}
	$script =  "<script type=\"text/javascript\">
	function tdover(object) {
		if (object.className == '".$norm_class."') object.className = '".$over_class."';
	}

	function tdnorm(object) {
		if (object.className == '".$over_class."') object.className = '".$norm_class."';
	}
	</script>\n";

	return $script;
}

?>