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

$tdc=0;

function wad($link, $title, $description, $perms, $icon = FALSE){
	global $tdc;
	$permicon = ($icon ? e_PLUGIN.$icon : e_IMAGE."generic/e107.gif");
	if(getperms($perms)){
		if(!$tdc){$tmp1 = "<tr>";}
		if($tdc == 4){$tmp2 = "</tr>";$tdc=-1;}
		$tdc++;
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


	$text .= "<tr>
	<td colspan='5'>
	<div class='border'><div class='caption'>Plugins</div></div></div>
	<br />
	</td>
	<tr>";

        $text .= wad(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", e_PLUGIN.e_IMAGE."generic/plugin.png");

	if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			include(e_PLUGIN.$plugin_path."/plugin.php");
			if($eplug_conffile){
                                $text .= wad(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, $eplug_icon);
		}
	}
}
$text .= "</tr>
</table></div>";
$ns -> tablerender("<div style='text-align:center'>".ADLAN_47." ".ADMINNAME."</div>", $text);
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
