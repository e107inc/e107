<?php
function r_emote(){
	$sql = new db;
	$sql -> db_Select("core", "*", "e107_name='emote'");
	$row = $sql -> db_Fetch(); extract($row);
	$emote = unserialize($e107_value);

	$str = "<div class='spacer'>";

	$c=0;
	while(list($code, $name) = @each($emote[$c])){
		if(!$orig[$name]){
			$str .= "<a href=\"javascript:addtext(' $code')\"><img src=\"".e_IMAGE."emoticons/$name\" style=\"border:0\" alt=\"\" /></a> \n";
			$orig[$name] = TRUE;
		}
		$c++;
	}

	$str .= "</div>";
	return $str;
}
?>