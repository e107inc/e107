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
|     $Revision: 1.11 $
|     $Date: 2005-01-17 08:19:10 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
$e_sub_cat = 'main';
require_once('auth.php');
require_once(e_HANDLER.'admin_handler.php');

// update users using old layout names to their new names
if ($pref['adminstyle']=='default') { $pref['adminstyle'] = 'compact'; }
if ($pref['adminstyle']=='adminb') { $pref['adminstyle'] = 'cascade'; }
if ($pref['adminstyle']=='admin_etalkers') { $pref['adminstyle'] = 'categories'; }
if ($pref['adminstyle']=='admin_combo') { $pref['adminstyle'] = 'combo'; }
if ($pref['adminstyle']=='admin_classis') { $pref['adminstyle'] = 'classis'; }
save_prefs();

// auto db update
if ('0' == ADMINPERMS) {
	@require_once(e_ADMIN.'update_routines.php');
	@update_check();
}
// end auto db update

if (e_QUERY == 'purge') {
	$sql -> db_Delete("tmp", "tmp_ip='adminlog'");
}

$td = 1;
function render_links($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE){
	global $td;
	if (getperms($perms)){
		if ($mode=='adminb') {
			$text = "<tr><td class='forumheader3'>
			<div class='td' style='text-align:left; vertical-align:top; width:100%'
			onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">
			".$icon."	<b>".$title."</b> ".($description ? "[ <span class='smalltext'>".$description."</span> ]" : "")."</div></td></tr>";
		} else {
			if ($td==6) { $text .= '</tr>'; $td = 1; }
			if ($td==1) { $text .= '<tr>'; }
			if ($mode=='default') {
				$text .= "<td class='td' style='text-align:left; vertical-align:top; width:20%; white-space:nowrap' 
				onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">".$icon." ".$title."</td>";
			} else if ($mode=='classis') {
				$text .= "<td style='text-align:center; vertical-align:top; width:20%'><a href='".$link."'>".$icon."</a><br />
				<a href='".$link."'><b>".$title."</b></a><br />".$description."<br /><br /></td>";
			}
			$td++;
		}
	} 
	return $text;
}

function render_clean() {
	global $td;
	while ($td<=5) {
		$text .= "<td class='td' style='width:20%;'></td>";
		$td++;
	}
	$text .= "</tr>";
	$td = 1;
	return $text;
}

$newarray = asortbyindex($array_functions, 1);

require_once(e_ADMIN.'includes/'.$pref['adminstyle'].'.php');



// info -------------------------------------------------------

function admin_info($admin_style=FALSE) {
	global $sql, $ns;
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

	if ($admin_style) {
		$text = "<div style='text-align:center'>
		<table class='fborder' style='width:95%'>
		<tr><td class='fcaption'>".ADLAN_134."</td><td class='fcaption'>".ADLAN_135."</td></tr>";
		$text .= "<tr><td class='forumheader3' style='width:50%; vertical-align:top'>";
	} else {
		$text = "<div style='text-align:center'>
		<table style='width:95%'>
		<tr>
		<td style='width:50%; vertical-align:top'>";
	}

	$text .= "<div style='padding-bottom: 2px;'>".E_16_USER." ".ADLAN_110.": ".$members."</div>";
	$text .= "<div style='padding-bottom: 2px;'>".E_16_USER." ".ADLAN_111.": ".$unverified."</div>";
	$text .= "<div style='padding-bottom: 2px;'>".E_16_BANLIST." ".ADLAN_112.": ".$banned."<br /><br /></div>";

	$text .= "<div style='padding-bottom: 2px;'>".E_16_NEWS.($submitted_news ? " <a href='".e_ADMIN."newspost.php?sn'>".ADLAN_107.": $submitted_news</a>" : " ".ADLAN_107.": 0")."</div>";
	$text .= "<div style='padding-bottom: 2px;'>".E_16_ARTICLE.($submitted_articles ? " <a href='".e_ADMIN."article.php?sa'>".ADLAN_123.": $submitted_articles</a>" : " ".ADLAN_123.": ".$submitted_articles)."</div>";
	$text .= "<div style='padding-bottom: 2px;'>".E_16_REVIEW.($submitted_reviews ? " <a href='".e_ADMIN."review.php?sa'>".ADLAN_124.": $submitted_reviews</a>" : " ".ADLAN_124.": ".$submitted_reviews)."</div>";
	$text .= "<div style='padding-bottom: 2px;'>".E_16_LINKS.($submitted_links ? " <a href='".e_ADMIN."links.php?sn'>".ADLAN_119.": $submitted_links</a>" : " ".ADLAN_119.": ".$submitted_links)."<br /><br /></div>";

	$text .= "<div style='padding-bottom: 2px;'>".E_16_FORUM.($reported_posts ? " <a href='".e_ADMIN."forum.php?sr'>".ADLAN_125.": $reported_posts</a>" : " ".ADLAN_125.": ".$reported_posts)."<br /><br /></div>";

	$text .= "<div style='padding-bottom: 2px;'>".E_16_UPLOADS.($active_uploads ? " <a href='".e_ADMIN."upload.php'>".ADLAN_108.": $active_uploads</a>" : " ".ADLAN_108.": ".$active_uploads)."<br /><br /></div>";

	$text .= "<div style='padding-bottom: 2px;'>".E_16_FORUM." ".ADLAN_113.": ".$forum_posts."</div>";
	$text .= "<div style='padding-bottom: 2px;'>".E_16_COMMENT." ".ADLAN_114.": ".$comments."</div>";
	$text .= "<div style='padding-bottom: 2px;'>".E_16_CHAT." ".ADLAN_115.": ".$chatbox_posts."</div>";

	if ($admin_style) {
		$text .= "</td>
		<td class='forumheader3' style='width:50%; vertical-align:top'>";
	} else {
		$text .= "</td>
		<td style='width:50%; vertical-align:top'>";
	}

	$text .= E_16_ADMINLOG." <a style='cursor: pointer; cursor: hand' onclick=\"expandit(this)\">".ADLAN_116."</a>\n";
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

?>