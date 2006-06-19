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
|     $Revision: 1.46 $
|     $Date: 2006-06-19 11:00:45 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_ren_help.php");
@include_once(e_LANGUAGEDIR."English/lan_ren_help.php");
function ren_help($mode = 1, $addtextfunc = "addtext", $helpfunc = "help") {

	//        $mode == TRUE : fontsize and colour dialogs are rendered
	//        $mode == 2 : no helpbox

	$rand = rand(1000,9999);

	if (strstr(e_SELF, "content") || strstr(e_SELF, "cpage")) {
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

	$img[0] = "newpage.png";
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

	$text = "";
	foreach($code as $key=>$bbcode){
		if($key == 12){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('col_selector_".$rand."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else if($key == 13){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('size_selector_".$rand."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else{
		  $text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"{$addtextfunc}('".$bbcode[1]."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}
	}


	$text .= Size_Select('size_selector_'.$rand);
	$text .= Color_Select('col_selector_'.$rand);
	return $text;
}

// New Function - display_help() [ ren_help() is deprecated. ]

function display_help($tagid="helpb", $mode = 1, $addtextfunc = "addtext", $helpfunc = "help") {

	global $pref;
	//        $mode == TRUE : fontsize and colour dialogs are rendered
	//        $mode == 2 : no helpbox
	//        $mode == 'news' : preuploaded news images dialog is rendered
	$rand = rand(1000,9999);
	if (strstr(e_SELF, "content") || strstr(e_SELF, "cpage")) {
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
	if ($mode == 'news') {
		$code[14] = array("preimage", "[img][/img]", LANHELP_37);
		$code[15] = array("prefile", "[file][/file]", LANHELP_39);
	}

	//check emotes
	$emotes=FALSE;
	if($pref['comments_emoticons'] && $pref['smiley_activate'] && (!$pref['wysiwyg'] || !check_class($pref['post_html'])) ){
		$emotes=TRUE;
	}

	if($emotes===TRUE){
		$code[16] = array("emotes", "", LANHELP_44);
	}

	$img[0] = "newpage.png";
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
	if ($mode == 'news') {
		$img[14] = "preimage.png";
		$img[15] = "prefile.png";
	}
	if($emotes===TRUE){
		$img[16] = "emotes.png";
	}

	$imgpath = (file_exists(THEME."bbcode/bold.png") ? THEME."bbcode/" : e_IMAGE."generic/bbcode/");

	$text = "";
	foreach($code as $key=>$bbcode){
		if($key == 12){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('col_selector_".$rand."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else if($key == 13){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('size_selector_".$rand."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else if($key == 14){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('preimage_selector_".$rand."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else if($key == 15){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('prefile_selector_".$rand."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else if($key == 16){
			$text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"expandit('emoticon_selector_".$rand."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}else{
		  $text .= "<img class='bbcode' src='".$imgpath.$img[$key]."' alt='' title='".$bbcode[2]."' onclick=\"{$addtextfunc}('".$bbcode[1]."')\" ".($mode != 2 ? "onmouseout=\"{$helpfunc}('')\" onmouseover=\"{$helpfunc}('".$bbcode[2]."')\"" : "" )." />\n";
		}
	}

	if ($mode) {
		$text .= Size_Select('size_selector_'.$rand);
		$text .= Color_Select('col_selector_'.$rand);
		if ($mode == 'news') {
			$text .= PreImage_Select('preimage_selector_'.$rand);
			$text .= PreFile_Select('prefile_selector_'.$rand);
		}
	}
	
	if($emotes===TRUE){
		require_once(e_HANDLER."emote.php");
		$text .= Emoticon_Select('emoticon_selector_'.$rand);
	}


	return $text;
}

function Color_Select($formid='col_selector') {

	$text = "<!-- Start of Color selector -->
	<div style='margin-left: 0px; margin-right: 0px; width: 221px; position: relative; z-index: 1000; float: right; display: none' id='{$formid}' onclick=\"this.style.display='none'\">
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
	var tdblk = '<td style=\'background-color: #000000; cursor: default; height: 10px; width: 10px;\'><\/td>';
	var g = 1;
	var s = 0;

	function td_render(color) {
		return '<td style=\'background-color: #' + color + '; height: 10px; width: 10px;\' onmousedown=\"addtext(\'[color=#' + color + '][/color]\')\"><\/td>';
	}

	for (i=0; i < coloursrgb.length; i++) {
		for (j=0; j < coloursrgb.length; j++) {
			for (k=0; k < coloursrgb.length; k++) {
				maxtddiv++;
				if (maxtddiv % maxtd == 0) {
					if (rowswitch) {
						if (notr < 5){
							rows1 += '<\/tr><tr>' + td_render(coloursgrey[g]) + tdblk;
							g++;
						}
						rowswitch = 0;
						notr++;
					}else{
						rows2 += '<\/tr><tr>' + td_render(colourssol[s]) + tdblk;
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
	document.write('<\/tr><\/table>');
	//]]>
	</script>";

	$text .="</div>
	</div>
	<!-- End of Color selector -->";

	return $text;
}


function Size_Select($formid='size_selector') {
	$text ="<!-- Start of Size selector -->
	<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$formid}'>";
	$text .="<div style='position:absolute; bottom:30px; right:125px'>";
	$text .= "<table class='fborder' style='background-color: #fff'>
	<tr><td class='forumheader3'>
	<select class='tbox' name='preimageselect' onchange=\"addtext(this.value); expandit('{$formid}')\">
	<option value=''>".LANHELP_41."</option>";

	$sizes = array(7,8,9,10,11,12,14,15,18,20,22,24,26,28,30,36);
	foreach($sizes as $s){
		$text .= "<option value='[size=".$s."][/size]'>".$s."px</option>\n";
	}
	$text .="</select></td></tr>	\n </table></div>
	</div>\n<!-- End of Size selector -->";
	return $text;
}

function PreImage_Select($formid='preimage_selector') {

	global $IMAGES_DIRECTORY, $fl;
	if(!is_object($fl)){
        require_once(e_HANDLER."file_class.php");
		$fl = new e_file;
	}

	$rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*');
	$imagelist = $fl->get_files(e_IMAGE."newspost_images/","",$rejecthumb);
    sort($imagelist);
	$text ="<!-- Start of PreImage selector -->
	<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$formid}'>";
	$text .="<div style='position:absolute; bottom:30px; right:100px'>";
	$text .= "<table class='fborder' style='background-color: #fff'>
	<tr><td class='forumheader3' style='white-space: nowrap'>";

	if(!count($imagelist))
			{
				$text .= LAN_NEWS_43;
			}
			else
			{
				$text .= "<select class='tbox' name='preimageselect' onchange=\"addtext(this.value); expandit('{$formid}')\">
				<option value=''>".LANHELP_42."</option>";
				foreach($imagelist as $image)
				{
					if(strstr($image['fname'], "thumb"))
					{
						$fi = str_replace("thumb_", "", $image['fname']);
						if(file_exists(e_IMAGE."newspost_images/".$fi))
						{
							// thumb and main image found
							$text .= "<option value=\"[link=".$IMAGES_DIRECTORY."newspost_images/".$fi."][img]{E_IMAGE}newspost_images/".$image['fname']."[/img][/link]\">".$image['fname']." (".LANHELP_38.")</option>\n
							";
						}
						else
						{
							$text .= "<option value=\"[img]{E_IMAGE}newspost_images/".$image['fname']."[/img]\">".$image['fname']."</option>\n
							";
						}
					}
					else
					{
						$text .= "<option value=\"[img]{E_IMAGE}newspost_images/".$image['fname']."[/img]\">".$image['fname']."</option>\n
						";
					}
				}
				$text .="</select>";
			}
	$text .="</td></tr>	\n </table></div>
	</div>\n<!-- End of PreImage selector -->\n";
	return $text;
}

function PreFile_Select($formid='prefile_selector') {
	global $IMAGES_DIRECTORY, $fl, $sql;
		$rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*');
		$imagelist = $fl->get_files(e_IMAGE."newspost_images/","",$rejecthumb);
		sort($imagelist); 
		$filelist = array();
		$downloadList = array();

		$sql->db_Select("download", "*", "download_class != ".e_UC_NOBODY);
		while ($row = $sql->db_Fetch()) {
			extract($row);
			if($download_url)
			{
				$filelist[] = array("id" => $download_id, "name" => $download_name, "url" => $download_url, "class" => $download_class);
				$downloadList[] = $download_url;
			}
		}

		$tmp = $fl->get_files(e_FILE."downloads/","",$rejecthumb);
		foreach($tmp as $value)
		{
			if(!in_array($value['fname'], $downloadList))
			{
				$filelist[] = array("id" => 0, "name" => $value['fname'], "url" => $value['fname']);
			}
		}
	$text ="<!-- Start of PreFile selector -->
	<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$formid}'>";
	$text .="<div style='position:absolute; bottom:30px; right:75px'>";
	$text .= "<table class='fborder' style='background-color: #fff'>
	<tr><td class='forumheader3' style='white-space: nowrap'>";


	if(!count($filelist))
	{
		$text .= LANHELP_40;
	}
	else
	{
		$text .= "<select class='tbox' name='prefileselect' onchange=\"addtext(this.value); expandit('{$formid}')\">
				<option value=''>".LANHELP_43."</option>";
		foreach($filelist as $file)
		{
					if(isset($file['class']))
					{
						$ucinfo = "^".$file['class'];
						$ucname = r_userclass_name($file['class']);
					}
					else
					{
						$ucinfo = "";
						$ucname = r_userclass_name(0);
					}

					if($file['id'])
					{
						$text .= "<option value=\"[file=request.php?".$file['id']."{$cinfo}]".$file['name']."[/file]\">".$file['name']." - $ucname</option>\n";
											}
					else
					{
						$text .= "<option value=\"[file=request.php?".$file['url']."{$cinfo}]".$file['name']."[/file]\">".$file['name']." - $ucname</option>\n";
					}

		}
		$text .="</select>";
	}

	$text .="</td></tr>	\n </table></div>
	</div>\n<!-- End of PreFile selector -->\n";
	return $text;
}

function Emoticon_Select($formid='emoticon_selector') {
	$text ="<!-- Start of Emoticon selector -->
	<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$formid}'>
		<div style='position:absolute; bottom:30px; right:75px; width:221px; overflow:auto;'>
			<table class='fborder' style='background-color:#fff;'>
			<tr><td class='forumheader3'>
			".r_emote()."
			</td></tr></table>
		</div>
	</div>\n<!-- End of Emoticon selector -->\n";
	return $text;
}

?>