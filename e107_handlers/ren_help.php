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
|     $Revision: 1.21 $
|     $Date: 2005-04-01 12:24:37 $
|     $Author: mcfly_e107 $
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
	$code[12] = array("fontcol", "[color][/color]", LANHELP_21);
	$code[13] = array("fontsize", "[size][/size]", LANHELP_22);

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
	$img[13] = "fontsize.png";

	$imgpath = (file_exists(THEME."bbcode/bold.png") ? THEME."bbcode/" : e_IMAGE."generic/bbcode/");


	foreach($code as $key=>$bbcode){
		if($key == 12){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('col_selector')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else if($key == 13){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('size_selector')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else{
		  $text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"{$addtextfunc}('".$bbcode[1]."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}
	}

	$text .= Size_Select();
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
		$string .= "</select>\n

		<select class=\"tbox\" name=\"fontsiz\" onchange=\"{$addtextfunc}('[size=' + this.options[this.selectedIndex].value + '][/size]');this.selectedIndex=0;\"".($mode != 2 ? " onmouseover=\"{$helpfunc}('Font Size: [size]Big[/size]','{$tagid}')\" onmouseout=\"{$helpfunc}('','{$tagid}')\"" : "" )." >\n<option>".LANHELP_22."</option>\n";

		while (list($key, $bbcode) = each($fontsizes)) {
			$string .= "<option value=\"".$bbcode[0]."\">".$bbcode[1]."</option>\n";
		}
		$string .= "</select>";
	}

	return $string;
}

function Color_Select($field, $embed = FALSE){
	if (!$embed) {
		$text = "<!-- Start of Color selector -->
		<div style='margin-left: 0px; margin-right: 0px; width: 221px; position: relative; z-index: 1000; float: right; display: none' id='col_selector' onclick=\"this.style.display='none'\">
		<div style='position: absolute; bottom: 30px; right: 145px; width: 221px'>";
		
		$render_td = "var tdtop = '<td style=\'background-color: #';
		var tdmid = '; height: 10px; width: 10px;\' ';
		var tdmid2 = ' onclick=\"addtext(\'[color=#';
		var tdbot = '][/color]\')\"></td>';";
	} else {
		$text ="<table border='0' cellspacing='0' cellpadding='0' style='width: 100%; border: 0px; padding: 4px;'>
		<tr>
		<td colspan='3'>
		<div id='gxhzct_".$field."' onclick=\"window.close();\">";

		$render_td = "var tdtop = '<td style=\'background-color: #';
		var tdmid = '; height: 10px; width: 10px;\' onmouseover=\"View(\'".$field."\',\'';
		var tdmid2 = '\')\" onclick=\"Set(\'".$field."\',\'';
		var tdbot = '\')\"></td>';";
	}

	$text .= "<script>
	var maxtd = 18; 
	var maxtddiv = -1; 
	var coloursrgb = new Array('00', '33', '66', '99', 'cc', 'ff'); 
	var coloursgrey = new Array('000000', '333333', '666666', '999999', 'cccccc', 'ffffff');
	var colourssol = new Array('ff0000', '00ff00', '0000ff', 'ffff00', '00ffff', 'ff00ff');
	var rowswitch = 0;
	var rowline = '';
	var rows1 = '';
	var rows2 = '';
	var notr = 0;
	".$render_td."
	var tdblk = '<td style=\'background-color: #000000; cursor: default; height: 10px; width: 10px;\'></td>';
	var g = 1;
	var s = 0;

	for (i=0; i < coloursrgb.length; i++) { 
		for (j=0; j < coloursrgb.length; j++) { 
			for (k=0; k < coloursrgb.length; k++) { 
				maxtddiv++; 
				if (maxtddiv % maxtd == 0) { 
					if (rowswitch) {
						if (notr < 5){
							rows1 += '</tr><tr>'+tdtop+coloursgrey[g]+tdmid+coloursgrey[g]+tdmid2+coloursgrey[g]+tdbot+tdblk;
							g++;
						}
						rowswitch = 0;
						notr++;
					}else{
						rows2 += '</tr><tr>'+tdtop+colourssol[s]+tdmid+colourssol[s]+tdmid2+colourssol[s]+tdbot+tdblk;
						s++;
						rowswitch = 1;
					}
					maxtddiv = 0; 
				}
				rowline = tdtop+coloursrgb[j]+coloursrgb[k]+coloursrgb[i]+tdmid+coloursrgb[j]+coloursrgb[k]+coloursrgb[i]+tdmid2+coloursrgb[j]+coloursrgb[k]+coloursrgb[i]+tdbot;
				if (rowswitch) {
					rows1 += rowline;
				}else{
					rows2 += rowline;
				}
			}
		}
	}
	document.write('<table border=\'0\' cellspacing=\'1\' cellpadding=\'0\' style=\'cursor: hand; cursor: pointer; background-color: #000000; width: 100%; border: 0px\'><tr>'+tdtop+coloursgrey[0]+tdmid+coloursgrey[0]+tdmid2+coloursgrey[0]+tdbot+tdblk+rows1+rows2+'</tr></table>');
	</script>";
	
	if (!$embed) {
		$text .="</div>
		</div>
		<!-- End of Color selector -->";
	} else {
		$text .="</div>
		</td>
		</tr>
		</table>";
	}

	return $text;
}


function Size_Select() {
	$text ="<!-- Start of Size selector -->
		<div style='margin-left:0px;margin-right:0px;width:60px;position:relative;z-index:1000;float:right;display:none' id='size_selector' onclick=\"this.style.display='none'\">";
	$text .="<div style='position:absolute;bottom:30px;right:200px;'>";
	$text .= "<table class='fborder' style='background-color: #fff; cursor: pointer; cursor: hand; width: 100px;'>";

	$sizes = array(7,8,9,10,11,12,14,15,18,20,22,24,26,28,30,36);
	foreach($sizes as $s){
		$text .= "<tr><td class='button' onclick=\"addtext('[size=".$s."][/size]')\">".$s."px</td></tr>\n";
	}
	$text .="	\n </table></div>
	</div>\n<!-- End of Size selector -->";
	return $text;
}


?>