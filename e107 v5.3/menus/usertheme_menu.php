<?php
if(USER == TRUE){

$handle=opendir("themes/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "templates" && $file != "shared"){
		$dirlist[] = $file;
	}
}
closedir($handle);

$text = "
<div style=\"text-align:center\">
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<select name=\"sitetheme\" class=\"tbox\">
<option value=\"none\"> - Use default theme -</option>";
$counter = 0;
if(USERTHEME == FALSE){
	$ut = $pref['sitetheme'][1];
}

while($dirlist[$counter]){
	if($dirlist[$counter] == USERTHEME || $dirlist[$counter] == $ut){
		$text .= "<option selected>".$dirlist[$counter]."</option>\n";
	}else{
		$text .= "<option>".$dirlist[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
<br /><br />
<input class=\"button\" type=\"submit\" name=\"settheme\" value=\"Set Theme\" />
</form>
</div>";

$ns -> tablerender("Select Theme", $text);
}
?>