<?php
function shortcuts($mode=FALSE){
	if($mode == "article"){
		$shc = "<input class=\"button\" type=\"button\" value=\"newpage\" onclick=\"addtext('[newpage]')\"> ";
	}
	if($mode == "article" || $mode == "content"){
		$shc .= "<input class=\"button\" type=\"button\" value=\"preserve\" onclick=\"addtext('[preserve] [/preserve]')\"> ";
	}
	$shc .= "<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link][/link]')\">
<input class=\"button\" type=\"button\" style=\"font-weight:bold\" value=\"b\" onclick=\"addtext('[b][/b]')\">
<input class=\"button\" type=\"button\" style=\"font-style:italic\" value=\"i\" onclick=\"addtext('[i][/i]')\">
<input class=\"button\" type=\"button\" style=\"text-decoration: underline\" value=\"u\" onclick=\"addtext('[u][/u]')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"addtext('[img][/img]')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"addtext('[center][/center]')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"addtext('[left][/left]')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"addtext('[right][/right]')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"addtext('[blockquote][/blockquote]')\">

<select class=\"tbox\" name=\"fontcol\" onChange=\"addtext('[color=' + this.form.fontcol.options[this.form.fontcol.selectedIndex].value + '][/color]');this.selectedIndex=0;\">
<option value=\"\">Color ..</option>
<option style=\"color:black\" value=\"black\">Black</option>
<option style=\"color:blue\" value=\"blue\">Blue</option>
<option style=\"color:brown\" value=\"brown\">Brown</option>
<option style=\"color:cyan\" value=\"cyan\">Cyan</option>
<option style=\"color:darkblue\" value=\"darkblue\">Dark Blue</option>
<option style=\"color:darkred\" value=\"darkred\">Dark Red</option>
<option style=\"color:green\" value=\"green\">Green</option>
<option style=\"color:indigo\" value=\"indigo\">Indigo</option>
<option style=\"color:olive\" value=\"olive\">Olive</option>
<option style=\"color:orange\" value=\"orange\">Orange</option>
<option style=\"color:red\" value=\"red\">Red</option>
<option style=\"color:violet\" value=\"violet\">Violet</option>
<option style=\"color:white\" value=\"white\">White</option>
<option style=\"color:yellow\" value=\"yellow\">Yellow</option>
</select>

<select class=\"tbox\" name=\"fontsiz\" onChange=\"addtext('[size=' + this.form.fontsiz.options[this.form.fontsiz.selectedIndex].value + '][/size]');this.selectedIndex=0;\">
<option>Size ..</option>
<option value=\"7\">Tiny</option>
<option value=\"9\">Small</option>
<option value=\"11\">Normal</option>
<option value=\"16\">Large</option>
<option  value=\"20\">Larger</option>
<option  value=\"28\">Massive</option>
</select>";


	return $shc;
}
?>