<?php
@include(e_LANGUAGEDIR.e_LANGUAGE."/lan_ren_help.php");
@include(e_LANGUAGEDIR."English/lan_ren_help.php");
function ren_help($mode=1){

	//	$mode == TRUE : fontsize and colour dialogs are rendered
	//	$mode == 2 : no helpbox

	if(strstr(e_SELF, "article")){
		$code[0] = array("newpage", "[newpage]", LANHELP_34);
	}
	$code[1] = array("link", "[link=".LANHELP_35."][/link]", LANHELP_23);
	$code[2] = array("b", "[b][/b]", LANHELP_24);
	$code[3] = array("i", "[i][/i]", LANHELP_25);
	$code[4] = array("u", "[u][/u]", LANHELP_26);
	$code[5] = array("img", "[img][/img]", LANHELP_27);
	$code[6] = array("center", "[center][/center]", LANHELP_28);
	$code[7] = array("left", "[left][/left]", LANHELP_29);
	$code[8] = array("right", "[right][/right]", LANHELP_30);
	$code[9] = array("bq", "[blockquote][/blockquote]", LANHELP_31);
	$code[10] = array("code", "[code][/code]",LANHELP_32 );
	if(ADMIN){
		$code[11] = array("html", "[html][/html]", LANHELP_33);
	}

	$colours[0] = array("black", LANHELP_1);
	$colours[1] = array("blue", LANHELP_2);
	$colours[2] = array("brown", LANHELP_3);
	$colours[3] = array("cyan", LANHELP_4);
	$colours[4] = array("darkblue", LANHELP_5);
	$colours[5] = array("darkred", LANHELP_6);
	$colours[6] = array("green", LANHELP_7);
	$colours[7] = array("indigo", LANHELP_8);
	$colours[8] = array("olive", LANHELP_9);
	$colours[9] = array("orange", LANHELP_10);
	$colours[10] = array("red", LANHELP_11);
	$colours[11] = array("violet", LANHELP_12);
	$colours[12] = array("white", LANHELP_13);
	$colours[13] = array("yellow", LANHELP_14);

	$fontsizes[0] = array("7", LANHELP_15);
	$fontsizes[1] = array("9", LANHELP_16);
	$fontsizes[2] = array("11", LANHELP_17);
	$fontsizes[3] = array("16", LANHELP_18);
	$fontsizes[4] = array("20", LANHELP_19);
	$fontsizes[5] = array("28", LANHELP_20);

	while(list($key, $bbcode) = each($code)){ 
		$string .= "<input class=\"button\" type=\"button\" value=\"".$bbcode[0]."\" onclick=\"addtext('".$bbcode[1]."')\" ".($mode != 2 ? "onmouseout=\"help('')\" onmouseover=\"help('".$bbcode[2]."')\"" : "" ).($bbcode[3] ? " style='".$bbcode[3]."'" : "")." />\n";
	}
	if($mode){
		$string .= "<br />\n<select class=\"tbox\" name=\"fontcol\" onchange=\"addtext('[color=' + this.options[this.selectedIndex].value + '][/color]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"help('Font Color: [color]Blue[/color]')\" onmouseout=\"help('')\"" : "").">\n<option value=\"\">".LANHELP_21."</option>\n";
		while(list($key, $bbcode) = each($colours)){
			$string .= "<option style=\"color:".strtolower($bbcode[0])."\" value=\"".strtolower($bbcode[0])."\">".$bbcode[1]."</option>\n";
		}
		$string .= "</select>\n<select class=\"tbox\" name=\"fontsiz\" onchange=\"addtext('[size=' + this.options[this.selectedIndex].value + '][/size]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"help('Font Size: [size]Big[/size]')\" onmouseout=\"help('')\">" : "" )."\n<option>".LANHELP_22."</option>\n";

		while(list($key, $bbcode) = each($fontsizes)){
			$string .= "<option value=\"".$bbcode[0]."\">".$bbcode[1]."</option>\n";
		}
		$string .= "</select>";
	}
	return $string;
}
?>