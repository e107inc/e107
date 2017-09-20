//<?php

if (!check_class(varset($pref['email_item_class'],e_UC_MEMBER)))
{
	return '';
}


$parms = explode("^",$parm);

if(deftrue('BOOTSTRAP'))
{
	$img = "<span class='glyphicon glyphicon-envelope' aria-hidden='true'></span>";
}
elseif (defined("ICONMAIL") && file_exists(THEME."images/".ICONMAIL)) 
{
	$icon = THEME_ABS."images/".ICONMAIL;
	$img = "<img src='".$icon."' style='border:0' alt='{$parms[0]}' class='icon S16 action' />";
}
else
{
	$icon = e_IMAGE_ABS."generic/email.png";
	$img = "<img src='".$icon."' style='border:0' alt='{$parms[0]}' class='icon S16 action' />";
}

// message^source^other_parms
return "<a href='".e_HTTP."email.php?{$parms[1]}' title=\"".$parms[0]."\" >".$img."</a>";
