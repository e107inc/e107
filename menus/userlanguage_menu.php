<?php
if(USER == TRUE){

$handle=opendir(e_BASE."languages/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "plugins" && $file != ""){
		$file = eregi_replace("lan_|.php", "", $file);
		$lanlist[] = $file;
		$lancount[$file] = 0;
	}
}
closedir($handle);

$defaultlan = $pref['sitelanguage'][1];
$count = 0;

$totalct = $sql -> db_Select("user", "user_prefs", "user_prefs REGEXP('sitelanguage') ");

while ($row = $sql -> db_Fetch()){
	$up = unserialize($row['user_prefs']);
	$lancount[$up['sitelanguage']]++;
}

$defaultusers = $sql -> db_Count("user") - $totalct;
$lancount[$defaultlan] += $defaultusers;

$text = "
<div style=\"text-align:center\">
<form method=\"post\" action=\"".e_SELF."\">
<select name=\"sitelanguage\" class=\"tbox\">";
$counter = 0;

while($lanlist[$counter]){
	$text .= "<option value=\"".$lanlist[$counter]."\" ";
	if(($lanlist[$counter] == USERLAN) || (USERLAN == FALSE && $lanlist[$counter] == $defaultlan)){
		$text .= "selected";
	}
	$text .= ">".($lanlist[$counter] == $defaultlan ? "[ ".$lanlist[$counter]." ]" : $lanlist[$counter])." (users: ".$lancount[$lanlist[$counter]].")</option>\n";
	$counter++;
}
$text .= "</select>
<br /><br />
<input class=\"button\" type=\"submit\" name=\"setlanguage\" value=\"".LAN_352."\" />
</form>
</div>";

$ns -> tablerender(LAN_353, $text);
}
?>