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

function wad2($link, $title, $description, $perms, $icon = FALSE){
	global $tdt;
	$permicon = ($icon ? e_PLUGIN.$icon : e_IMAGE."generic/e107.gif");
	if(getperms($perms)){
		if(!$tdt){$tmp1 = "<tr>";}
		if($tdt == 4){$tmp2 = "</tr>";$tdt=-1;}
		$tdt++;
		$tmp = $tmp1."<td style='text-align:center; vertical-align:top; width:20%'><a href='".$link."'><img src='$permicon' alt='$description' style='border:0'/></a><br /><a href='".$link."'><b>".$title."</b></a><br />".$description."<br /><br /></td>\n\n".$tmp2;
	}
	return $tmp;
}


$text = "<div style='text-align:center'>
<table style='width:95%'>";

$array_functions = array(
	0 => array(e_ADMIN."administrator.php", ADLAN_8, ADLAN_9, "3"),
	1 => array(e_ADMIN."updateadmin.php", ADLAN_10, ADLAN_11, ""),
	2 => array(e_ADMIN."article.php", ADLAN_14, ADLAN_15, "J"),
	3 => array(e_ADMIN."banlist.php", ADLAN_34, ADLAN_35, "4"),
	4 => array(e_ADMIN."banner.php", ADLAN_54, ADLAN_55, "D"),
	5 => array(e_ADMIN."cache.php", ADLAN_74, ADLAN_75, "0"),
	6 => array(e_ADMIN."chatbox.php", ADLAN_56, ADLAN_57, "C"),
	7 => array(e_ADMIN."content.php", ADLAN_16, ADLAN_17, "L"),
	8 => array(e_ADMIN."custommenu.php", ADLAN_42, ADLAN_43, "2"),
	9 => array(e_ADMIN."db.php",ADLAN_44, ADLAN_45,"0"),
	10 => array(e_ADMIN."download.php", ADLAN_24, ADLAN_25, "R"),
	11 => array(e_ADMIN."emoticon.php", ADLAN_58, ADLAN_59, "F"),
	12 => array(e_ADMIN."filemanager.php", ADLAN_30, ADLAN_31, "6"),
	13 => array(e_ADMIN."forum.php", ADLAN_12, ADLAN_13, "5"),
	14 => array(e_ADMIN."frontpage.php", ADLAN_60, ADLAN_61, "G"),
	15 => array(e_ADMIN."image.php", ADLAN_105, ADLAN_106, "5"),
	16 => array(e_ADMIN."links.php", ADLAN_20, ADLAN_21, "I"),
	17 => array(e_ADMIN."wmessage.php", ADLAN_28, ADLAN_29, "M"),
	18 => array(e_ADMIN."log.php", ADLAN_64, ADLAN_65, "S"),
	19 => array(e_ADMIN."ugflag.php", ADLAN_40, ADLAN_41, "9"),
	20 => array(e_ADMIN."menus.php", ADLAN_6, ADLAN_7, "2"),
	21 => array(e_ADMIN."meta.php", ADLAN_66, ADLAN_67, "T"),
	22 => array(e_ADMIN."newspost.php", ADLAN_0, ADLAN_1, "H"),
	23 => array(e_ADMIN."newsfeed.php", ADLAN_62, ADLAN_63, "E"),
	24 => array(e_ADMIN."phpinfo.php", ADLAN_68, ADLAN_69, "0"),
	25 => array(e_ADMIN."poll.php", ADLAN_70, ADLAN_71, "U"),
	26 => array(e_ADMIN."prefs.php", ADLAN_4, ADLAN_5, "1"),
	27 => array(e_ADMIN."upload.php", ADLAN_72, ADLAN_73, "V"),
	28 => array(e_ADMIN."review.php", ADLAN_18, ADLAN_19, "K"),
	29 => array(e_ADMIN."users.php", ADLAN_36, ADLAN_37, "4"),
	30 => array(e_ADMIN."userclass2.php", ADLAN_38, ADLAN_39, "4"),
	31 => array(e_ADMIN."admin.php?logout", ADLAN_46, "", "")
);

$newarray = asortbyindex ($array_functions, 1);

$text = "<div style='text-align:center'>
<table style='width:95%'>";

while(list($key, $funcinfo) = each($newarray)){
	$text .= wad($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3]);
}

if(!$tdc){ $text .= "</tr>"; }
$text3="";


if(getperms("PM")){ // Plugin Manager
	$text3 .= wad2(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "PM", e_PLUGIN.e_IMAGE."generic/plugin.png");
}

	if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			include(e_PLUGIN.$plugin_path."/plugin.php");
			if($eplug_conffile){
				$text3 .= wad2(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, $eplug_icon);
			}
		}
	}

$text .= "</tr></table></div>";
$ns -> tablerender("<div style='text-align:center'>".ADLAN_47." ".ADMINNAME."</div>", $text);

if($text3){  // Only render if some kind of P access exists.
 $ns -> tablerender("<div style='text-align:center'>Plugins</div>", "<table><tr>".$text3."</tr></table>");
}


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
$submitted_articles = $sql -> db_Select("content", "*", "content_type ='15' ");
$submitted_reviews = $sql -> db_Select("content", "*", "content_type ='16' ");

$text = "<div style='text-align:center'>
<table style='width:95%'>
<tr>
<td style='width:50%'>";


$text .= $permicon.ADLAN_110.": ".$members."<br />";
$text .= $permicon.ADLAN_111.": ".$unverified."<br />";
$text .= $permicon.ADLAN_112.": ".$banned."<br />";

$text .= ($submitted_news ? $permicon2."<a href='".e_ADMIN."submitnews.php'>".ADLAN_107.": $submitted_news</a>" : $permicon.ADLAN_107.": 0")."<br />";
$text .= ($submitted_articles ? $permicon2."<a href='".e_ADMIN."article.php?sa'>".ADLAN_123.": $submitted_articles</a>" : $permicon.ADLAN_123.": ".$submitted_articles)."<br />";
$text .= ($submitted_reviews ? $permicon2."<a href='".e_ADMIN."review.php?sa'>".ADLAN_124.": $submitted_reviews</a>" : $permicon.ADLAN_124.": ".$submitted_reviews)."<br />";
$text .= ($submitted_links ? $permicon2."<a href='".e_ADMIN."links.php?sn'>".ADLAN_119.": $submitted_links</a>" : $permicon.ADLAN_119.": ".$submitted_links)."<br /><br />";

$text .= ($active_uploads ? $permicon2."<a href='".e_ADMIN."upload.php'>".ADLAN_108.": $active_uploads</a>" : $permicon.ADLAN_108.": ".$active_uploads)."<br />";

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
require_once("footer.php");

// Multi indice array sort by sweetland@whoadammit.com
function asortbyindex ($sortarray, $index) {
	$lastindex = count ($sortarray) - 1;
	for ($subindex = 0; $subindex < $lastindex; $subindex++) {
		$lastiteration = $lastindex - $subindex;
		for ($iteration = 0; $iteration < $lastiteration;    $iteration++) {
			$nextchar = 0;
			if (comesafter ($sortarray[$iteration][$index], $sortarray[$iteration + 1][$index])) {
				$temp = $sortarray[$iteration];
				$sortarray[$iteration] = $sortarray[$iteration + 1];
				$sortarray[$iteration + 1] = $temp;
			}
		}
	}
	return ($sortarray);
}

function comesafter ($s1, $s2) {
	$order = 1;
	if (strlen ($s1) > strlen ($s2)) {
		$temp = $s1;
		$s1 = $s2;
		$s2 = $temp;
		$order = 0;
	}
	for ($index = 0; $index < strlen ($s1); $index++) {
		if ($s1[$index] > $s2[$index]) return ($order);
		if ($s1[$index] < $s2[$index]) return (1 - $order);
	}
	return ($order);
}

?>

<script type="text/javascript">
function tdover(object) {
  if (object.className == 'td') object.className = 'forumheader5';
}

function tdnorm(object) {
  if (object.className == 'forumheader5') object.className = 'td';
}
</script>