<?php
function ren_help($func, $rencolsize = FALSE){
	unset($str);
	if(strstr(e_SELF, "article")){
		$str = "<input class=\"button\" type=\"button\" value=\"newpage\" onclick=\"addtext('[newpage]')\" onMouseOver=\"help('Insert newpage tag, splits article into more than one page')\" onMouseOut=\"help('')\"> ";
	}
	if(strstr(e_SELF, "article") || strstr(e_SELF, "content")){
		$str .= "<input class=\"button\" type=\"button\" value=\"preserve\" onclick=\"addtext('[preserve] [/preserve]')\" onMouseOver=\"help('[preserve]html[/preserve] Preserves HTML tags')\" onMouseOut=\"help('')\"> ";
	}



	$str .= <<< EOT
<input class="button" type="button" value="link" onclick="$func('[link=hyperlink url]hyperlink text[/link]')" onMouseOver="help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')" onMouseOut="help('')">
<input class="button" type="button" style="font-weight:bold; width: 20px" value="b" onclick="$func('[b][/b]')" onMouseOver="help('Bold text: [b]This text will be bold[/b]')" onMouseOut="help('')">
<input class="button" type="button" style="font-style:italic; width: 20px" value="i" onclick="$func('[i][/i]')" onMouseOver="help('Italic text: [i]This text will be italicised[/i]')" onMouseOut="help('')">
<input class="button" type="button" style="text-decoration: underline; width: 20px" value="u" onclick="$func('[u][/u]')" onMouseOver="help('Underline text: [u]This text will be underlined[/u]')" onMouseOut="help('')">
<input class="button" type="button" value="img" onclick="$func('[img][/img]')" onMouseOver="help('Insert image: [img]mypicture.jpg[/img]')" onMouseOut="help('')">
<input class="button" type="button" value="center" onclick="$func('[center][/center]')" onMouseOver="help('Center align: [center]This text will be centered[/center]')" onMouseOut="help('')">
<input class="button" type="button" value="left" onclick="$func('[left][/left]')" onMouseOver="help('Left align: [left]This text will be left aligned[/left]')" onMouseOut="help('')">
<input class="button" type="button" value="right" onclick="$func('[right][/right]')" onMouseOver="help('Right align: [right]This text will be right aligned[/right]')" onMouseOut="help('')">
<input class="button" type="button" value="blockquote" onclick="$func('[blockquote][/blockquote]')" onMouseOver="help('Blockquote text: [blockquote]This text will be indented[/blockquote]')" onMouseOut="help('')">
<input class="button" type="button" value="code" onclick="$func('[code][/code]')" onMouseOver="help('Code - preformatted text: [code]\$var = foobah;[/code]')" onMouseOut="help('')">

EOT;

if($rencolsize){

	$str .= <<< EOT
<br />
<select class="tbox" name="fontcol" onChange="$func('[color=' + this.form.fontcol.options[this.form.fontcol.selectedIndex].value + '] [/color]');this.selectedIndex=0;" onMouseOver="help('Font Color: [color]Black[/color]')" onMouseOut="help('')">
<option value="">Color ..</option>
<option style="color:black" value="black">Black</option>
<option style="color:blue" value="blue">Blue</option>
<option style="color:brown" value="brown">Brown</option>
<option style="color:cyan" value="cyan">Cyan</option>
<option style="color:darkblue" value="darkblue">Dark Blue</option>
<option style="color:darkred" value="darkred">Dark Red</option>
<option style="color:green" value="green">Green</option>
<option style="color:indigo" value="indigo">Indigo</option>
<option style="color:olive" value="olive">Olive</option>
<option style="color:orange" value="orange">Orange</option>
<option style="color:red" value="red">Red</option>
<option style="color:violet" value="violet">Violet</option>
<option style="color:white" value="white">White</option>
<option style="color:yellow" value="yellow">Yellow</option>
</select>
<select class="tbox" name="fontsiz" onChange="$func('[size=' + this.form.fontsiz.options[this.form.fontsiz.selectedIndex].value + '] [/size]');this.selectedIndex=0;" onMouseOver="help('Font Size: [size]Big[/size]')" onMouseOut="help('')">
<option>Size ..</option>
<option value="7">Tiny</option>
<option value="9">Small</option>
<option value="11">Normal</option>
<option value="16">Large</option>
<option  value="20">Larger</option>
<option  value="28">Massive</option>
</select>

EOT;
}
	return $str;
}
?>