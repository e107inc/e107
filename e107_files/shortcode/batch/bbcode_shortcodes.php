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
|     $Source: /cvs_backup/e107_0.7/e107_files/shortcode/batch/bbcode_shortcodes.php,v $
|     $Revision: 1.7 $
|     $Date: 2006-07-08 02:21:51 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

include_once(e_HANDLER.'shortcode_handler.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_ren_help.php");
global $register_bb;
$bbcode_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);

/*
SC_BEGIN BB

global $pref, $eplug_bb, $bbcode_func, $bbcode_help, $bbcode_filedir, $bbcode_imagedir, $bbcode_helpactive, $bbcode_helptag, $register_bb;

if(e_WYSIWYG){ return; }

$bbcode_func = ($bbcode_func) ? $bbcode_func : "addtext";
$bbcode_help  = ($bbcode_help) ? $bbcode_help : "help";
$bbcode_tag  = ($bbcode_helptag != 'helpb') ? ",'$bbcode_helptag'" : "";

$rand = rand(1000,9999);
$imagedir_display = str_replace("../","",$bbcode_imagedir);

if($parm == "emotes" && $pref['comments_emoticons'] && $pref['smiley_activate'] && !e_WYSIWYG)
{
	$bbcode['emotes'] = array('', LANHELP_44, "emotes.png", "Emoticon_Select", "emoticon_selector_".$rand);
}

// Format: $bbcode['UNIQUE_NAME'] = array(ONCLICK_FUNC, ONCLICK_VAR, HELPTEXT, ICON, INCLUDE_FUNC, INCLUDE_FUNCTION_VAR);

$bbcode['newpage'] = array($bbcode_func,"[newpage]", LANHELP_34, "newpage.png");
$bbcode['link'] = array($bbcode_func,"[link=".LANHELP_35."][/link]", LANHELP_23,"link.png");
$bbcode['b'] = array($bbcode_func,"[b][/b]", LANHELP_24,"bold.png");
$bbcode['i'] = array($bbcode_func,"[i][/i]", LANHELP_25,"italic.png");
$bbcode['u'] = array($bbcode_func,"[u][/u]", LANHELP_26,"underline.png");
$bbcode['center'] = array($bbcode_func,"[center][/center]", LANHELP_28,"center.png");
$bbcode['left'] = array($bbcode_func,"[left][/left]", LANHELP_29,"left.png");
$bbcode['right'] = array($bbcode_func,"[right][/right]", LANHELP_30,"right.png");
$bbcode['bq'] = array($bbcode_func,"[blockquote][/blockquote]", LANHELP_31,"blockquote.png");
$bbcode['code'] = array($bbcode_func,"[code][/code]", LANHELP_32,"code.png");
$bbcode['list'] = array($bbcode_func,"[list][/list]", LANHELP_36,"list.png");
$bbcode['img'] = array($bbcode_func,"[img][/img]", LANHELP_27,"image.png");
$bbcode['flash'] = array($bbcode_func,"[flash=width,height][/flash]", LANHELP_47,"flash.png");

$bbcode['fontsize'] = array("expandit","size_selector_".$rand, LANHELP_22,"fontsize.png","Size_Select",'size_selector_'.$rand);
$bbcode['fontcol'] = array("expandit","col_selector_".$rand, LANHELP_21,"fontcol.png","Color_Select",'col_selector_'.$rand);
$bbcode['preimage'] = array("expandit","preimage_selector_".$rand, LANHELP_45.$imagedir_display,"preimage.png","PreImage_Select","preimage_selector_".$rand);
$bbcode['prefile'] = array("expandit","prefile_selector_".$rand, LANHELP_39,"prefile.png","PreFile_Select",'prefile_selector_'.$rand);

if(!isset($iconpath[$parm]))
{
	$iconpath[$parm] =  (file_exists(THEME."bbcode/bold.png") ? THEME."bbcode/" : e_IMAGE."generic/bbcode/");
    $iconpath[$parm] .= $bbcode[$parm][3];
}


foreach($register_bb as $key=>$val) // allow themes to plug in to it.
{
	if($val[0]=="")
	{
    	$val[0] = $bbcode_func;
	}
	$bbcode[$key] = $val;
	$iconpath[$key] = $val[3];
}


foreach($eplug_bb as $key=>$val)  // allow plugins to plug into it.
{
	extract($val);
   //	echo "$onclick $onclick_var $helptext $icon <br />";
    $bbcode[$name] = array($onclick,$onclick_var,$helptext,$icon,$function,$function_var);
	$iconpath[$name] = $icon;
}


$_onclick_func = ($bbcode[$parm][0]) ? $bbcode[$parm][0] : $bbcode_func;
$_onclick_var = $bbcode[$parm][1];
$_helptxt = $bbcode[$parm][2];
$_function = $bbcode[$parm][4];
$_function_var = $bbcode[$parm][5];


if($bbcode[$parm])  // default - insert text.
{
	$text = "\n<img class='bbcode bbcode_buttons' style='cursor:pointer' src='".$iconpath[$parm]."' alt='' title='".$helptxt."' onclick=\"{$_onclick_func}('".$_onclick_var."')\" ".($bbcode_helpactive ? "onmouseout=\"{$bbcode_help}(''{$bbcode_tag})\" onmouseover=\"{$bbcode_help}('".$_helptxt."'{$bbcode_tag})\"" : "" )." />\n";
}

if($_function)
{

	$text .= ($bbcode_helpactive && $_helptxt && !$iconpath[$parm]) ? "<span onmouseout=\"{$bbcode_help}(''{$bbcode_tag})\" onmouseover=\"{$bbcode_help}('".$_helptxt."'{$bbcode_tag})\" >" : "";
	$text .= $_function($_function_var);
	$text .= ($bbcode_helpactive && $_helptxt && !$iconpath[$parm]) ? "</span>" : "";
}

return $text;

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


function Color_Select($formid='col_selector') {

	$text = "<!-- Start of Color selector -->
	<div style='margin-left: 0px; margin-right: 0px; width: 221px; position: relative; z-index: 1000; float: right; display: none' id='{$formid}' onclick=\"this.style.display='none'\" >
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


function PreImage_Select($formid) {
	global $fl, $tp, $bbcode_imagedir;

	$path = ($bbcode_imagedir) ?  $bbcode_imagedir : e_IMAGE."newspost_images/";
	$formid = ($formid) ? ($formid) : "preimage_selector";


	if(!is_object($fl)){
        require_once(e_HANDLER."file_class.php");
		$fl = new e_file;
	}

	$rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*');
	$imagelist = $fl->get_files($path,"",$rejecthumb,2);
    sort($imagelist);

	$text ="<!-- Start of PreImage selector -->
	<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$formid}'>";
	$text .="<div style='position:absolute; bottom:30px; right:100px'>";
	$text .= "<table class='fborder' style='background-color: #fff'>
	<tr><td class='forumheader3' style='white-space: nowrap'>";

	if(!count($imagelist))
			{

				$text .= LANHELP_46."<b>".str_replace("../","",$path)."</b>";
			}
			else
			{
				$text .= "<select class='tbox' name='preimageselect' onchange=\"addtext(this.value); expandit('{$formid}')\">
				<option value=''>".LANHELP_42."</option>";
				foreach($imagelist as $image)
				{
					$e_path = $tp->createConstants($image['path'],1);
					$showpath = str_replace($path,"",$image['path']);
					if(strstr($image['fname'], "thumb"))
					{
						$fi = str_replace("thumb_", "", $image['fname']);
						if(file_exists($path.$fi))
						{
							// thumb and main image found
							$text .= "<option value=\"[link=".$e_path.$fi."][img]".$e_path.$image['fname']."[/img][/link]\">".$showpath.$image['fname']." (".LANHELP_38.")</option>\n
							";
						}
						else
						{
							$text .= "<option value=\"[img]".$e_path.$image['fname']."[/img]\">".$showpath.$image['fname']."</option>\n
							";
						}
					}
					else
					{
						$text .= "<option value=\"[img]".$e_path.$image['fname']."[/img]\">".$showpath.$image['fname']."</option>\n";
					}
				}
				$text .="</select>";
			}
	$text .="</td></tr>	\n </table></div>
	</div>\n<!-- End of PreImage selector -->\n";
	return $text;
}

function PreFile_Select($formid='prefile_selector',$bbcode_filedir) {
	require_once(e_HANDLER."userclass_class.php");
	global $IMAGES_DIRECTORY, $fl, $sql;
		$rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*');

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
	require_once(e_HANDLER."emote.php");
	$text ="<!-- Start of Emoticon selector -->
	<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$formid}' onclick=\"this.style.display='none'\" >
		<div style='position:absolute; bottom:30px; right:75px; width:221px; height:133px; overflow:auto;'>
			<table class='fborder' style='background-color:#fff;'>
			<tr><td class='forumheader3'>
			".r_emote()."
			</td></tr></table>
		</div>
	</div>\n<!-- End of Emoticon selector -->\n";
	return $text;
}




SC_END




SC_BEGIN BB_HELP
	if(e_WYSIWYG){	return;	}
	global $bbcode_helpactive,$bbcode_helptag;
	$bbcode_helptag = ($parm) ? $parm : "helpb";
	$bbcode_helpactive = TRUE;
	return "<input id='{$bbcode_helptag}' class='helpbox' type='text' name='{$bbcode_helptag}' size='100' style='width:95%'/>\n";
SC_END



SC_BEGIN BB_PREIMAGEDIR
	if(e_WYSIWYG){	return;	}
	global $bbcode_imagedir;
	$bbcode_imagedir = $parm;
	return;
SC_END


*/
?>