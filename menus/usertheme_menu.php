<?php
if(USER == TRUE){

$handle=opendir(e_BASE."themes/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "templates" && $file != "shared" && $file != ""){
		$themelist[] = $file;
		$themecount[$file] = 0;
	}
}
closedir($handle);

$defaulttheme = $pref['sitetheme'][1];
$count = 0;

$totalct = $sql -> db_Select("user", "user_prefs", "user_prefs REGEXP('sitetheme') ");

while ($row = $sql -> db_Fetch()){
	$user_prefs = unserialize($row['user_prefs']);
	$themecount[$user_prefs['sitetheme']]++;
}

$defaultusers = $sql -> db_Count("user") - $totalct;

$text = "
<div style=\"text-align:center\">
<form method=\"post\" action=\"".e_SELF."\">
<select name=\"sitetheme\" class=\"tbox\">";
$counter = 0;

while($themelist[$counter]){

	if($themelist[$counter] == $defaulttheme){
		$text .= "<option style=\"font-style:bold\" value=\"".$themelist[$counter]."\" selected>[".$themelist[$counter]."] (users: ".$defaultusers.")</option>\n";
	}else if($themelist[$counter] == USERTHEME){
		$text .= "<option value=\"".$themelist[$counter]."\" selected>".$themelist[$counter]." (users: ".$themecount[$themelist[$counter]].")</option>\n";
	}else{
		$text .= "<option value=\"".$themelist[$counter]."\">".$themelist[$counter]." (users: ".$themecount[$themelist[$counter]].")</option>\n";
	}

	$counter++;
}
$text .= "</select>
<br /><br />
<input class=\"button\" type=\"submit\" name=\"settheme\" value=\"Set Theme\" />
<input type=\"hidden\" name=\"tid\" value=\"".USERID."\">
</form>
</div>";

$ns -> tablerender("Select Theme", $text);
}
?>