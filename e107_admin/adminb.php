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

$tdc=0;

function wad($link, $title, $description, $perms, $icon = FALSE){
	global $tdc;
	$permicon = ($icon ? e_PLUGIN.$icon : e_IMAGE."generic/hme.png");
	if(getperms($perms)){
		$tmp = "<tr>\n<td class='forumheader3' style='text-align:left; vertical-align:top; width:100%' 
		onmouseover='tdover(this)' onmouseout='tdnorm(this)'
		onclick=\"document.location.href='$link'\">
		<img src='$permicon' alt='$description' style='border:0; vertical-align:middle'/> <b>".$title."</b> ".($description ? "[ <span class='smalltext'>".$description."</span> ]" : "")."</td></tr>\n";
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
	<td colspan='5'>
		<br />
	<div class='border'><div class='caption'>Plugins</div></div></div>
	</td>
	<tr>";

	$text .= wad(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "P", e_PLUGIN.e_IMAGE."generic/plugin.png");
	$text .= wad(e_PLUGIN."theme_layout/theme_layout.php", ADLAN_100, ADLAN_101, "P", e_PLUGIN."theme_layout/images/icon.png");

	if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			include(e_PLUGIN.$plugin_path."/plugin.php");
			if($eplug_conffile){
				$text .= wad(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P", $eplug_icon);
			}
		}
	}
}
$text .= "</tr>
</table></div>";
$ns -> tablerender("<div style='text-align:center'>".ADLAN_47." ".ADMINNAME."</div>", $text);
require_once("footer.php");
?>

<script type="text/javascript">
function tdover(object) {
  if (object.className == 'forumheader3') object.className = 'forumheader4';
}

function tdnorm(object) {
  if (object.className == 'forumheader4') object.className = 'forumheader3';
}
</script>