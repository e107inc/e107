<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/ren_help.php,v $
|     $Revision: 1.12 $
|     $Date: 2005-03-31 18:49:58 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.e_LANGUAGE."/lan_ren_help.php");
@include(e_LANGUAGEDIR."English/lan_ren_help.php");
function ren_help($mode = 1, $addtextfunc = "addtext", $helpfunc = "help") {

	//        $mode == TRUE : fontsize and colour dialogs are rendered
	//        $mode == 2 : no helpbox

	if (strstr(e_SELF, "article")) {
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
	$code[10] = array("code", "[code][/code]", LANHELP_32 );
	$code[11] = array("list", "[list][/list]", LANHELP_36);
	$code[12] = array("fontcol", "[color][/color]", LANHELP_36);

	$img[1] = "link.png";
	$img[2] = "bold.png";
	$img[3] = "italic.png";
	$img[4] = "underline.png";
	$img[5] = "image.png";
	$img[6] = "center.png";
	$img[7] = "left.png";
	$img[8] = "right.png";
	$img[9] = "blockquote.png";
	$img[10] = "code.png";
	$img[11] = "list.png";
	$img[12] = "fontcol.png";

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

	$imgpath = (file_exists(THEME."bbcode/bold.png") ? THEME."bbcode/" : e_IMAGE."generic/bbcode/");


	foreach($code as $key=>$bbcode){
		if($key != 12){
		  $text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"{$addtextfunc}('".$bbcode[1]."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else{
          $text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('col_selector')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}
	}

	if ($mode) {
	  //	$text .= "\n<br />\n<span><select class=\"tbox\" name=\"fontcol\" onchange=\"{$addtextfunc}('[color=' + this.options[this.selectedIndex].value + '][/color]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"{$helpfunc}('Font Color: [color]Blue[/color]')\" onmouseout=\"{$helpfunc}('')\"" : "")." >\n<option value=\"\">".LANHELP_21."</option>\n";
		while (list($key, $bbcode) = each($colours)) {
	   //		$text .= "<option style=\"color:".strtolower($bbcode[0])."\" value=\"".strtolower($bbcode[0])."\">".$bbcode[1]."</option>\n";
		}
		   //		$text .= "</select>\n";
				$text .= "<select class=\"tbox\" name=\"fontsiz\" onchange=\"{$addtextfunc}('[size=' + this.options[this.selectedIndex].value + '][/size]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"{$helpfunc}('Font Size: [size]Big[/size]')\" onmouseout=\"{$helpfunc}('')\"" : "" )." >\n<option>".LANHELP_22."</option>\n";

		while (list($key, $bbcode) = each($fontsizes)) {
			$text .= "<option value=\"".$bbcode[0]."\">".$bbcode[1]."</option>\n";
		}
		$text .= "</select>";
	}
	$text .= Color_Select('color_'.$key);
	return $text;
}

// New Function - display_help() [ ren_help() is deprecated. ]

function display_help($tagid="helpb", $mode = 1, $addtextfunc = "addtext", $helpfunc = "help") {

	global $pref;
	//        $mode == TRUE : fontsize and colour dialogs are rendered
	//        $mode == 2 : no helpbox

	if (strstr(e_SELF, "article")) {
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
	$code[10] = array("code", "[code][/code]", LANHELP_32 );
	$code[11] = array("list", "[list][/list]", LANHELP_36);

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

	$img[1] = "link.png";
	$img[2] = "bold.png";
	$img[3] = "italic.png";
	$img[4] = "underline.png";
	$img[5] = "image.png";
	$img[6] = "center.png";
	$img[7] = "left.png";
	$img[8] = "right.png";
	$img[9] = "blockquote.png";
	$img[10] = "code.png";
	$img[11] = "list.png";

	$imgpath = (file_exists(THEME."bbcode/bold.png") ? THEME."bbcode/" : e_IMAGE."generic/bbcode/");

	while (list($key, $bbcode) = each($code)) {
		//$string .= "<input class=\"button\" type=\"button\" value=\"".$bbcode[0]."\" onclick=\"{$addtextfunc}('".$bbcode[1]."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('','{$tagid}')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."','{$tagid}')\"" : "" ).($bbcode[3] ? " style='".$bbcode[3]."'" :"")." />\n";
		$string .= "<img src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"{$addtextfunc}('".$bbcode[1]."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />";
	}

/*
	if(file_exists(e_PLUGIN."sphpell/spellcheckpageinc.php"))
	{
		require_once(e_PLUGIN."sphpell/spellcheckpageinc.php");
		$string .= "<input type='button' value='Check Spelling' onclick=\"DoSpellCheck('top.opener.parent.document.dataform.data')\">";
	}
*/

	if ($mode) {
		$string .= "<br />\n<select class=\"tbox\" name=\"fontcol\" onchange=\"{$addtextfunc}('[color=' + this.options[this.selectedIndex].value + '][/color]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"{$helpfunc}('Font Color: [color]Blue[/color]','{$tagid}')\" onmouseout=\"{$helpfunc}('','{$tagid}')\"" : "")." >\n<option value=\"\">".LANHELP_21."</option>\n";
		while (list($key, $bbcode) = each($colours)) {
			$string .= "<option style=\"color:".strtolower($bbcode[0])."\" value=\"".strtolower($bbcode[0])."\">".$bbcode[1]."</option>\n";
		}
		$string .= "</select>\n<select class=\"tbox\" name=\"fontsiz\" onchange=\"{$addtextfunc}('[size=' + this.options[this.selectedIndex].value + '][/size]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"{$helpfunc}('Font Size: [size]Big[/size]','{$tagid}')\" onmouseout=\"{$helpfunc}('','{$tagid}')\"" : "" )." >\n<option>".LANHELP_22."</option>\n";


		while (list($key, $bbcode) = each($fontsizes)) {
			$string .= "<option value=\"".$bbcode[0]."\">".$bbcode[1]."</option>\n";
		}
		$string .= "</select>";
	}
	return $string;
}

function Color_Select($field){

	$text ="<!-- Start of Color selector -->
		<div style='margin-left:0px;margin-right:0px;width:180px;position:relative;z-index:1000;float:right;display:none' id='col_selector' onclick=\"this.style.display='none'\">";
	$text .="<div style='position:absolute;bottom:35px;right:180px;'>";
	$text .= "<table cellspacing=\"1px\" cellpadding=\"0px\"  style=\"width:180px;background-color:#000000;border:1px solid #cccccc;cursor: pointer;\">
    <tr>";

  $colors = array("#000000","#000033","#000066","#000099",
        "#0000cc","#0000ff","#330000","#330033","#330066",
        "#330099","#3300cc","#3300ff","#660000","#660033",
        "#660066","#660099","#6600cc","#6600ff","#990000",
        "#990033","#990066","#990099","#9900cc","#9900ff",
        "#cc0000","#cc0033","#cc0066","#cc0099","#cc00cc",
        "#cc00ff",
        "#ff0000",
        "#ff0033",
        "#ff0066",
        "#ff0099",
        "#ff00cc",
        "#ff00ff",
        "#003300",
        "#003333",
        "#003366",
        "#003399",
        "#0033cc",
        "#0033ff",
        "#333300",
        "#333333",
        "#333366",
        "#333399",
        "#3333cc",
        "#3333ff",
        "#663300",
        "#663333",
        "#663366",
        "#663399",
        "#6633cc",
        "#6633ff",
        "#993300",
        "#993333",
        "#993366",
        "#993399",
        "#9933cc",
        "#9933ff",
        "#cc3300",
        "#cc3333",
        "#cc3366",
        "#cc3399",
        "#cc33cc",
        "#cc33ff",
        "#ff3300",
        "#ff3333",
        "#ff3366",
        "#ff3399",
        "#ff33cc",
        "#ff33ff",
        "#006600",
        "#006633",
        "#006666",
        "#006699",
        "#0066cc",
        "#0066ff",
        "#336600",
        "#336633",
        "#336666",
        "#336699",
        "#3366cc",
        "#3366ff",
        "#666600",
        "#666633",
        "#666666",
        "#666699",
        "#6666cc",
        "#6666ff",
        "#996600",
        "#996633",
        "#996666",
        "#996699",
        "#9966cc",
        "#9966ff",
        "#cc6600",
        "#cc6633",
        "#cc6666",
        "#cc6699",
        "#cc66cc",
        "#cc66ff",
        "#ff6600",
        "#ff6633",
        "#ff6666",
        "#ff6699",
        "#ff66cc",
        "#ff66ff",
        "#009900",
        "#009933",
        "#009966",
        "#009999",
        "#0099cc",
        "#0099ff",
        "#339900",
        "#339933",
        "#339966",
        "#339999",
        "#3399cc",
        "#3399ff",
        "#669900",
        "#669933",
        "#669966",
        "#669999",
        "#6699cc",
        "#6699ff",
        "#999900",
        "#999933",
        "#999966",
        "#999999",
        "#9999cc",
        "#9999ff",
        "#cc9900",
        "#cc9933",
        "#cc9966",
        "#cc9999",
        "#cc99cc",
        "#cc99ff",
        "#ff9900",
        "#ff9933",
        "#ff9966",
        "#ff9999",
        "#ff99cc",
        "#ff99ff",
        "#00cc00",
        "#00cc33",
        "#00cc66",
        "#00cc99",
        "#00cccc",
        "#00ccff",
        "#33cc00",
        "#33cc33",
        "#33cc66",
        "#33cc99",
        "#33cccc",
        "#33ccff",
        "#66cc00",
        "#66cc33",
        "#66cc66",
        "#66cc99",
        "#66cccc",
        "#66ccff",
        "#99cc00",
        "#99cc33",
        "#99cc66",
        "#99cc99",
        "#99cccc",
        "#99ccff",
        "#cccc00",
        "#cccc33",
        "#cccc66",
        "#cccc99",
        "#cccccc",
        "#ccccff",
        "#ffcc00",
        "#ffcc33",
        "#ffcc66",
        "#ffcc99",
        "#ffcccc",
        "#ffccff",
        "#00ff00",
        "#00ff33",
        "#00ff66",
        "#00ff99",
        "#00ffcc",
        "#00ffff",
        "#33ff00",
        "#33ff33",
        "#33ff66",
        "#33ff99",
        "#33ffcc",
        "#33ffff",
        "#66ff00",
        "#66ff33",
        "#66ff66",
        "#66ff99",
        "#66ffcc",
        "#66ffff",
        "#99ff00",
        "#99ff33",
        "#99ff66",
        "#99ff99",
        "#99ffcc",
        "#99ffff",
        "#ccff00",
        "#ccff33",
        "#ccff66",
        "#ccff99",
        "#ccffcc",
        "#ccffff",
        "#ffff00",
        "#ffff33",
        "#ffff66",
        "#ffff99",
        "#ffffcc",
        "#ffffff"
    );

    foreach($colors as $key=>$c){
      $text .= "\n<td style='width:10px;height:10px;background-color:$c' onclick=\"addtext('[color=$c][/color]')\"  ></td>";
      $text .= (($key+1) % 18 == 0 && $key !=215 ) ? "\n</tr><tr>" : "";

    }
    $text .="</tr>\n </table></div>";
  	//  $text .= "<span id=\"ColorPreview_".$field."\" style=\"border:1px;height: 100%; width: 28px\">&nbsp;&nbsp;&nbsp;&nbsp;</span>";
//  $text .=" <input class='tbox' type=\"text\" name=\"".$field."\" id=\"".$field."\" value=\"".$dbvalue."\" size='15' onblur=\"View('".$field."',this.value)\" />
	$text .="</div>\n<!-- End of Color selector -->";

return $text;
}
?>