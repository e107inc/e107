<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/admin.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
require_once("auth.php");


// auto db update ...



$handle=opendir(e_ADMIN."sql/db_update");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "index.html"){
		$updatelist[] = $file;
	}
}
closedir($handle);

if(IsSet($_POST['inst_update'])){
	require_once(e_ADMIN."sql/db_update/".$updatelist[0]);
}else if(is_array($updatelist)){
	$text = "<div style='text-align:center'>".ADLAN_120."<br /><br />
	<form method='post' action='".e_SELF."'><input class='button' type='submit' name='inst_update' value='".ADLAN_121."' /></form>
	</div>";
	$ns -> tablerender(ADLAN_122, $text);
}










//	end auto db update



if(e_QUERY == "purge"){
	$sql -> db_Delete("tmp", "tmp_ip='adminlog' ");
}

$tdc=0;
function wad($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE){
	global $tdc;
	$permicon = ($mode == TRUE ? e_IMAGE."generic/rname.png" : e_IMAGE."generic/location.png");
	if(getperms($perms)){
		if(!$tdc){$tmp1 = "<tr>";}
		if($tdc == 4){$tmp2 = "</tr>";$tdc=-1;}
		$tdc++;
		$tmp = $tmp1."<td class='td' style='text-align:left; vertical-align:top; width:20%; whitespace:nowrap' onmouseover='tdover(this)' onmouseout='tdnorm(this)'
		onclick=\"document.location.href='$link'\"><img src='$permicon' alt='$description' style='border:0; vertical-align:middle'/> $title</td>\n\n".$tmp2;
	}
	return $tmp;
}




$text = "<div style='text-align:center'>
<table style='width:95%'>";

$text .= wad("newspost.php", ADLAN_0, ADLAN_1, "H");
$text .= wad("news_category.php", ADLAN_2, ADLAN_3, "7");
$text .= wad("prefs.php", ADLAN_4, ADLAN_5, "1");
$text .= wad("menus.php", ADLAN_6, ADLAN_7, "2");
$text .= wad("administrator.php", ADLAN_8, ADLAN_9, "3");

$text .= wad("updateadmin.php", ADLAN_10, ADLAN_11, "");
$text .= wad("forum.php", ADLAN_12, ADLAN_13, "5");
$text .= wad("article.php", ADLAN_14, ADLAN_15, "J");
$text .= wad("content.php", ADLAN_16, ADLAN_17, "L");
$text .= wad("review.php", ADLAN_18, ADLAN_19, "K");

$text .= wad("links.php", ADLAN_20, ADLAN_21, "I");
$text .= wad("link_category.php", ADLAN_22, ADLAN_23, "8");
$text .= wad("download.php", ADLAN_24, ADLAN_25, "R");
$text .= wad("download_category.php", ADLAN_26, ADLAN_27, "Q");
$text .= wad("wmessage.php", ADLAN_28, ADLAN_29, "M");

$text .= wad("filemanager.php", ADLAN_30, ADLAN_31, "6");
$text .= wad("submitnews.php", ADLAN_32, ADLAN_33, "N");
$text .= wad("banlist.php", ADLAN_34, ADLAN_35, "4");
$text .= wad("users.php", ADLAN_36, ADLAN_37, "4");
$text .= wad("userclass2.php", ADLAN_38, ADLAN_39, "4");

$text .= wad("banner.php", ADLAN_54, ADLAN_55, "D");
$text .= wad("chatbox.php", ADLAN_56, ADLAN_57, "C");
$text .= wad("newsfeed.php", ADLAN_62, ADLAN_63, "E");
$text .= wad("emoticon.php", ADLAN_58, ADLAN_59, "F");
$text .= wad("frontpage.php", ADLAN_60, ADLAN_61, "G");

$text .= wad("log.php", ADLAN_64, ADLAN_65, "S");
$text .= wad("meta.php", ADLAN_66, ADLAN_67, "T");
$text .= wad("phpinfo.php", ADLAN_68, ADLAN_69, "0");
$text .= wad("poll.php", ADLAN_70, ADLAN_71, "U");
$text .= wad("image.php", ADLAN_105, ADLAN_106, "5");

$text .= wad("upload.php", ADLAN_72, ADLAN_73, "V");
$text .= wad("cache.php", ADLAN_74, ADLAN_75, "0");
$text .= wad("ugflag.php", ADLAN_40, ADLAN_41, "9");
$text .= wad("custommenu.php", ADLAN_42, ADLAN_43, "2");
$text .= wad("db.php",ADLAN_44, ADLAN_45,"0");

$text .= wad("admin.php?logout", ADLAN_46, "", "");

if(!$tdc){ $text .= "</tr>"; }

if(getperms("P")){

	$text .= "<tr>
	<td colspan='5'><br />
	</td>
	<tr>";

	$text .= wad(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "P", "", TRUE);
	$text .= wad(e_PLUGIN."theme_layout/theme_layout.php", ADLAN_100, ADLAN_101, "P", "", TRUE);

	if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			include(e_PLUGIN.$plugin_path."/plugin.php");
			if($eplug_conffile){
				$text .= wad(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P", "", TRUE);
			}
		}
	}
}
$text .= "</tr>
</table></div>";
$ns -> tablerender("<div style='text-align:center'>".ADLAN_47." ".ADMINNAME."</div>", $text);



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
$submitted_news = $sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ");

$text = "<div style='text-align:center'>
<table style='width:95%'>
<tr>
<td style='width:50%'>";


$text .= $permicon.ADLAN_110.": ".$members."<br />";
$text .= $permicon.ADLAN_111.": ".$unverified."<br />";
$text .= $permicon.ADLAN_112.": ".$banned."<br />";

$text .= ($submitted_news ? $permicon2."<a href='".e_ADMIN."submitnews.php'>".ADLAN_107.": $submitted_news</a>" : $permicon.ADLAN_107.": 0")."<br />";
$text .= ($active_uploads ? $permicon2."<a href='".e_ADMIN."upload.php'>".ADLAN_108.": $active_uploads</a>" : $permicon.ADLAN_108.": ".$active_uploads)."<br />";

$text .= ($submitted_links ? $permicon2."<a href='".e_ADMIN."links.php'>".ADLAN_119.": $submitted_links</a>" : $permicon.ADLAN_119.": ".$submitted_links)."<br /><br />";
$text .= $permicon.ADLAN_113.": ".$forum_posts."<br />";
$text .= $permicon.ADLAN_114.": ".$comments."<br />";
$text .= $permicon.ADLAN_115.": ".$chatbox_posts;

$text .= "</td>
<td style='width:50%; vertical-align:top'>";

$text .= $permicon." <a style='cursor: pointer; cursor: hand' onclick=\"expandit(this)\">".ADLAN_116."</a>\n";
if(e_QUERY != "logall"){
	$text .= "<div style='display: none;'>";
}
if(e_QUERY == "logall"){
	$sql -> db_Select("tmp", "*", "tmp_ip='adminlog' ORDER BY tmp_time DESC");
}else{
	$sql -> db_Select("tmp", "*", "tmp_ip='adminlog' ORDER BY tmp_time DESC LIMIT 0,10");
}
$text .= "<ul>";
$gen = new convert;
while($row = $sql -> db_Fetch()){
	extract($row);
	$datestamp = $gen->convert_date($tmp_time, "short");
	$text .= "<li>".$datestamp.$tmp_info."</li>";
}
$text .= "</ul>";

$text .= "[ <a href='".e_SELF."?logall'>".ADLAN_117."</a> ][ <a href='".e_SELF."?purge'>".ADLAN_118."</a> ]\n</div>
</td></tr></table></div>

";

$ns -> tablerender(ADLAN_109, $text);







// -------------------------------------------------------------

require_once("footer.php");
?>

<script type="text/javascript">
function tdover(object) {
  if (object.className == 'td') object.className = 'forumheader5';
}

function tdnorm(object) {
  if (object.className == 'forumheader5') object.className = 'td';
}
</script>