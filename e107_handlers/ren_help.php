<?php
function ren_help($mode=1){

	//	$mode == TRUE : fontsize and colour dialogs are rendered
	//	$mode == 2 : no helpbox

	if(strstr(e_SELF, "article")){
		$code[0] = array("newpage", "[newpage]", "Insert newpage tag, splits article into more than one page");
	}
	$code[1] = array("link", "[link=hyperlink url][/link]", "Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]");
	$code[2] = array("b", "[b][/b]", "Bold text: [b]This text will be bold[/b]", "font-weight:bold; width: 20px");
	$code[3] = array("i", "[i][/i]", "Italic text: [i]This text will be italicised[/i]", "font-style:italic; width: 20px");
	$code[4] = array("u", "[u][/u]", "Underline text: [u]This text will be underlined[/u]", "text-decoration: underline; width: 20px");
	$code[5] = array("img", "[img][/img]", "Insert image: [img]mypicture.jpg[/img]");
	$code[6] = array("center", "[center][/center]", "Center align: [center]This text will be centered[/center]");
	$code[7] = array("left", "[left][/left]", "Left align: [left]This text will be left aligned[/left]");
	$code[8] = array("right", "[right][/right]", "Right align: [right]This text will be right aligned[/right]");
	$code[9] = array("bq", "[blockquote][/blockquote]", "Blockquote text: [blockquote]This text will be blockquoted (indented)[/blockquote]");
	$code[10] = array("code", "[code][/code]", "Code - preformatted text: [code]\$foo = bah;[/code]");
	if(ADMIN){
		$code[11] = array("html", "[html][/html]", "HTML - removes linebreaks from text: [html]<table><tr><td> etc[/html]");
	}

	$colours[0] = "Black";
	$colours[1] = "Blue";
	$colours[2] = "Brown";
	$colours[3] = "Cyan";
	$colours[4] = "Dark Blue";
	$colours[5] = "Dark Red";
	$colours[6] = "Green";
	$colours[7] = "Indigo";
	$colours[8] = "Olive";
	$colours[9] = "Orange";
	$colours[10] = "Red";
	$colours[11] = "Violet";
	$colours[12] = "White";
	$colours[13] = "Yellow";

	$fontsizes[0] = array("7", "Tiny");
	$fontsizes[1] = array("9", "Small");
	$fontsizes[2] = array("11", "Normal");
	$fontsizes[3] = array("16", "Large");
	$fontsizes[4] = array("20", "Larger");
	$fontsizes[5] = array("28", "Massive");

	while(list($key, $bbcode) = each($code)){ 
		$string .= "<input class=\"button\" type=\"button\" value=\"".$bbcode[0]."\" onclick=\"addtext('".$bbcode[1]."')\" ".($mode != 2 ? "onmouseout=\"help('')\" onmouseover=\"help('".$bbcode[2]."')\"" : "" ).($bbcode[3] ? " style='".$bbcode[3]."'" : "")." />\n";
	}
	if($mode){
		$string .= "<br />\n<select class=\"tbox\" name=\"fontcol\" onchange=\"addtext('[color=' + this.options[this.selectedIndex].value + '][/color]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"help('Font Color: [color]Blue[/color]')\" onmouseout=\"help('')\"" : "").">\n<option value=\"\">Color ..</option>\n";
		while(list($key, $bbcode) = each($colours)){
			$string .= "<option style=\"color:".strtolower($bbcode)."\" value=\"".strtolower($bbcode)."\">".$bbcode."</option>\n";
		}
		$string .= "</select>\n<select class=\"tbox\" name=\"fontsiz\" onchange=\"addtext('[size=' + this.options[this.selectedIndex].value + '][/size]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"help('Font Size: [size]Big[/size]')\" onmouseout=\"help('')\">" : "" )."\n<option>Size ..</option>\n";

		while(list($key, $bbcode) = each($fontsizes)){
			$string .= "<option value=\"".$bbcode[0]."\">".$bbcode[1]."</option>\n";
		}
		$string .= "</select>";
	}
	return $string;
}
?>