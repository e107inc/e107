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
|     $Source: /cvs_backup/e107_0.7/e107_admin/admin_etalkers.php,v $
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

$td = 1;
function wad($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE){
	global $td;
	$permicon = $mode ? SML_IMG_PLUGIN : $icon;
	if (getperms($perms)){
		if ($td==1) { $text .= "<tr>"; }
		$text = "<td class='td' style='text-align:left; vertical-align:top; width:20%; white-space:nowrap' 
		onmouseover='tdover(this)' onmouseout='tdnorm(this)' onclick=\"document.location.href='$link'\">$permicon $title</td>";
		if ($td==5) { $text .= '</tr>'; }
		if ($td<5) { $td++; } else { $td = 1; }
	} 
	return $text;
}

$text = "<div style='text-align:center'>
<table class='fborder' style='width:95%'>";

foreach ($admin_cat['id'] as $cat_key => $cat_id) {
	$text_check = FALSE;
	$text_cat = "<tr><td class='forumheader3' rowspan='2' style='text-align: center; vertical-align: middle;'>".$admin_cat['lrg_img'][$cat_key]."</td><td class='forumheader'>".$admin_cat['title'][$cat_key]."</td></tr>
	<tr><td class='forumheader3'>
	<table style='width:100%'><tr>";
	if ($cat_key!=7) {
		$newarray = asortbyindex($array_functions, 1);
		foreach ($newarray as $key => $funcinfo) {
			if ($funcinfo[4]==$cat_key) {
				$text_rend = wad($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5]);
				if ($text_rend) { $text_check = TRUE; }
				$text_cat .= $text_rend;
			}
		}
	} else {
		$text_rend = wad(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", "", TRUE);
		if ($text_rend) { $text_check = TRUE; }
		$text_cat .= $text_rend;
		if ($sql -> db_Select("plugin", "*", "plugin_installflag=1")) {
			while ($row = $sql -> db_Fetch()) {
				extract($row);
				include(e_PLUGIN.$plugin_path."/plugin.php");
				if ($eplug_conffile) {
					$text_rend = wad(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, "", TRUE);
					if ($text_rend) { $text_check = TRUE; }
					$text_cat .= $text_rend;
				}
				unset($eplug_conffile, $eplug_name, $eplug_caption);
			}
		}
	}
	while ($td<=5) {
		$text_cat .= "<td class='td' style='width:20%;' ></td>";
		$td++;
	}
	$td = 1;
	$text_cat .= "</tr></table>
	</td></tr>";
	if ($text_check) { $text .= $text_cat; }
}

$text .= "
</table>
</div>";

$ns -> tablerender(ADLAN_47." ".ADMINNAME, $text);


// info -------------------------------------------------------

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

$text = "<div style='text-align:center'>
<table class='fborder' style='width:95%'>
<tr><td class='forumheader'>".ADLAN_134."</td><td class='forumheader'>".ADLAN_135."</td></tr>";
$text .= "<tr><td class='forumheader3' style='width:50%; vertical-align:top'>";


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

$text .= "</td>
<td class='forumheader3' style='width:50%; vertical-align:top'>";

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

require_once("footer.php");

function headerjs(){
	$script =  "<script type=\"text/javascript\">
	function tdover(object) {
		if (object.className == 'td') object.className = 'forumheader5';
	}

	function tdnorm(object) {
		if (object.className == 'forumheader5') object.className = 'td';
	}
	</script>\n";

	return $script;
}

?>
