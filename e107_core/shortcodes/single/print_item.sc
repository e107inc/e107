//<?php


$parms = explode("^",$parm);

if(deftrue('BOOTSTRAP'))
{
	$img = "<span class='glyphicon glyphicon-print' aria-hidden='true'></span>";
}
elseif (defined("ICONPRINT") && file_exists(THEME."images/".ICONPRINT))
{
	$icon = THEME_ABS."images/".ICONPRINT;
	$img = "<img src='".$icon."' style='border:0' alt='{$parms[0]}'  class='icon S16 action'  />";
}
else
{
	$icon = e_IMAGE_ABS."generic/printer.png";
	$img = "<img src='".$icon."' style='border:0' alt='{$parms[0]}'  class='icon S16 action'  />";
}



return "<a href='".e_HTTP."print.php?{$parms[1]}' title=\"".$parms[0]."\" >".$img."</a>";