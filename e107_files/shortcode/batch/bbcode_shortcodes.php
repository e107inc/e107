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
|     $Revision: 1.1 $
|     $Date: 2006-07-06 03:28:50 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
if(!$BBCODE_TEMPLATE)
{
	if(is_readable(THEME."bbcode_template.php"))
	{
    	include_once(THEME."bbcode_template.php");
	}
	else
	{
		include_once(e_THEME."templates/bbcode_template.php");
	}
}
include_once(e_HANDLER.'shortcode_handler.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_ren_help.php");
$bbcode_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);

/*
SC_BEGIN BB

global $pref, $bbcode_func, $bbcode_help, $bbcode_filedir, $bbcode_imagedir, $bbcode_helpactive, $bbcode_helptag;

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

// Format: bbcode[UNIQUE_ID] = array(INSERTTEXT,HELPTEXT,ICON,FUNCTION,FUNCTION-PARM);

$bbcode['newpage'] = array("[newpage]", LANHELP_34, "newpage.png");
$bbcode['link'] = array("[link=".LANHELP_35."][/link]", LANHELP_23,"link.png");
$bbcode['b'] = array("[b][/b]", LANHELP_24,"bold.png");
$bbcode['i'] = array("[i][/i]", LANHELP_25,"italic.png");
$bbcode['u'] = array("[u][/u]", LANHELP_26,"underline.png");
$bbcode['center'] = array("[center][/center]", LANHELP_28,"center.png");
$bbcode['left'] = array("[left][/left]", LANHELP_29,"left.png");
$bbcode['right'] = array("[right][/right]", LANHELP_30,"right.png");
$bbcode['bq'] = array("[blockquote][/blockquote]", LANHELP_31,"blockquote.png");
$bbcode['code'] = array("[code][/code]", LANHELP_32,"code.png");
$bbcode['list'] = array("[list][/list]", LANHELP_36,"list.png");
$bbcode['img'] = array("[img][/img]", LANHELP_27,"image.png");
$bbcode['flash'] = array("[flash=width,height][/flash]", LANHELP_47,"flash.png");

$bbcode['fontsize'] = array("[size][/size]", LANHELP_22,"fontsize.png","Size_Select",'size_selector_'.$rand);
$bbcode['fontcol'] = array("[color][/color]", LANHELP_21,"fontcol.png","Color_Select",'col_selector_'.$rand);
$bbcode['preimage'] = array("[img][/img]", LANHELP_45.$imagedir_display,"preimage.png","PreImage_Select","preimage_selector_".$rand);
$bbcode['prefile'] = array("[file][/file]", LANHELP_39,"prefile.png","PreFile_Select",'prefile_selector_'.$rand);


$iconpath =  (file_exists(THEME."bbcode/bold.png") ? THEME."bbcode/" : e_IMAGE."generic/bbcode/");
$function = $bbcode[$parm][3];
$formid = $bbcode[$parm][4];

if($function)  // onclick call a function.
{
		$text = "<img class='bbcode' src='".$iconpath.$bbcode[$parm][2]."' alt='' title='".$bbcode[$parm][1]."' onclick=\"expandit('{$formid}')\" ".($bbcode_helpactive ? "onmouseout=\"{$bbcode_help}(''{$bbcode_tag})\" onmouseover=\"{$bbcode_help}('".$bbcode[$parm][1]."'{$bbcode_tag})\"" : "" )." />\n";
		$text .= $function($formid);
}
elseif($bbcode[$parm])  // default - insert text.
{
	$text = "\n<img class='bbcode' src='".$iconpath.$bbcode[$parm][2]."' alt='' title='".$bbcode[$parm][1]."' onclick=\"{$bbcode_func}('".$bbcode[$parm][0]."')\" ".($bbcode_helpactive ? "onmouseout=\"{$bbcode_help}(''{$bbcode_tag})\" onmouseover=\"{$bbcode_help}('".$bbcode[$parm][1]."'{$bbcode_tag})\"" : "" )." />\n";
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



SC_BEGIN BB_IMAGEDIR
	if(e_WYSIWYG){	return;	}
	global $bbcode_imagedir;
	$bbcode_imagedir = $parm;
	return;
SC_END


*/
?>