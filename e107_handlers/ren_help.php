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
|     $Revision: 1.24 $
|     $Date: 2005-04-03 17:17:54 $
|     $Author: stevedunstan $
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
	$text .= Color_Select();
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


/*
	if(file_exists(e_PLUGIN."sphpell/spellcheckpageinc.php"))
	{
		require_once(e_PLUGIN."sphpell/spellcheckpageinc.php");
		$string .= "<input type='button' value='Check Spelling' onclick=\"DoSpellCheck('top.opener.parent.document.dataform.data')\">";
	}
*/
	if ($mode) {
		$text .= Size_Select();
		$text .= Color_Select();
	}

	return $text;
}

function Color_Select() {
	$text = "<!-- Start of Color selector -->
	<div style='margin-left: 0px; margin-right: 0px; width: 221px; position: relative; z-index: 1000; float: right; display: none' id='col_selector' onclick=\"this.style.display='none'\">
	<div style='position: absolute; bottom: 30px; right: 145px; width: 221px'>";

	$text .= "<script type='text/javascript'>
	//<![CDATA[
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
	var tdblk = '<td style=\'background-color: #000000; cursor: default; height: 10px; width: 10px;\'></td>';
	var g = 1;
	var s = 0;
	
	function td_render(color) {
		return '<td style=\'background-color: #' + color + '; height: 10px; width: 10px;\' onclick=\"addtext(\'[color=#' + color + '][/color]\')\"></td>';
	}

	for (i=0; i < coloursrgb.length; i++) { 
		for (j=0; j < coloursrgb.length; j++) { 
			for (k=0; k < coloursrgb.length; k++) { 
				maxtddiv++; 
				if (maxtddiv % maxtd == 0) { 
					if (rowswitch) {
						if (notr < 5){
							rows1 += '</tr><tr>' + td_render(coloursgrey[g]) + tdblk;
							g++;
						}
						rowswitch = 0;
						notr++;
					}else{
						rows2 += '</tr><tr>' + td_render(colourssol[s]) + tdblk;
						s++;
						rowswitch = 1;
					}
					maxtddiv = 0; 
				}
				rowline = td_render(coloursrgb[j] + coloursrgb[k] + coloursrgb[i]);
				if (rowswitch) {
					rows1 += rowline;
				}else{
					rows2 += rowline;
				}
			}
		}
	}
	document.write('<table cellspacing=\'1\' cellpadding=\'0\' style=\'cursor: hand; cursor: pointer; background-color: #000; width: 100%; border: 0px\'><tr>');
	document.write(td_render(coloursgrey[0]) + tdblk + rows1 + rows2);
	document.write('</tr></table>');
	//]]>
	</script>";

	$text .="</div>
	</div>
	<!-- End of Color selector -->";

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