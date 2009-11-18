<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/ren_help.php,v $
 * $Revision: 1.15 $
 * $Date: 2009-11-18 01:04:43 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }



function ren_help($mode = 1, $addtextfunc = "addtext", $helpfunc = "help")
{
    // ren_help() is deprecated - use display_help().
    return display_help("helpb", $mode, $addtextfunc, $helpfunc = "help");
}




// FIXME - full rewrite, EVERYTHING - bbcode class (php + JS), core callbacks, tooltip help, optimize
function display_help($tagid="helpb", $mode = 1, $addtextfunc = "addtext", $helpfunc = "help", $helpsize = '')
{
    if(defsettrue('e_WYSIWYG')) { return; }
	global $tp, $pref, $eplug_bb, $bbcode_func, $register_bb, $bbcode_help, $bbcode_helpactive, $bbcode_helptag, $bbcode_helpsize;
	$bbcode_helpsize = $helpsize;

	$bbcode_func = $addtextfunc;
 	$bbcode_help = $helpfunc;
    $bbcode_helptag = $tagid;

    // load the template
	if(is_readable(THEME."bbcode_template.php"))
	{
		include(THEME."bbcode_template.php");
	}
	else
	{
		include(e_THEME."templates/bbcode_template.php");
	}

	if($mode != 2 && $mode != "forum")
	{
    	$bbcode_helpactive = TRUE;
	}

    // Load the Plugin bbcode AFTER the templates, so they can modify or replace.
	if  (!empty($pref['e_bb_list']))
	{
		foreach($pref['e_bb_list'] as $val)
		{
			if(is_readable(e_PLUGIN.$val."/e_bb.php"))
			{
				require(e_PLUGIN.$val."/e_bb.php");
			}
		}
	}

	$temp = array();
    $temp['news'] 		= $BBCODE_TEMPLATE_NEWSPOST;
	$temp['submitnews']	= $BBCODE_TEMPLATE_SUBMITNEWS;
	$temp['extended']	= $BBCODE_TEMPLATE_NEWSPOST;
	$temp['admin']		= $BBCODE_TEMPLATE_ADMIN;
	$temp['mailout']	= $BBCODE_TEMPLATE_MAILOUT;
	$temp['cpage']		= $BBCODE_TEMPLATE_CPAGE;
	$temp['maintenance']= $BBCODE_TEMPLATE_ADMIN;
	$temp['comment'] 	= "{BB_HELP}<br />".$BBCODE_TEMPLATE;

	if(isset($temp[$mode]))
	{
        $BBCODE_TEMPLATE = $temp[$mode];
	}
    if(is_readable(e_FILE."shortcode/batch/bbcode_shortcodes.php"))
	{
  		require_once(e_FILE."shortcode/batch/bbcode_shortcodes.php");
  		return $tp->parseTemplate($BBCODE_TEMPLATE);
	}
	else
	{
    	return "ERROR: ".e_FILE."shortcode/batch/bbcode_shortcodes.php IS NOT READABLE.";
	}

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
	var i, j, k;

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
	document.write('<table cellspacing=\'1\' cellpadding=\'0\' style=\'cursor: pointer; background-color: #000; width: 100%; border: 0px\'><tr>');
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

//	$rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*');
	$imagelist = $fl->get_files($path,'','standard',2);
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
				$text .= "<select class='tbox' name='preimageselect' onchange=\"addtext(this.value, true); expandit('{$formid}')\">
				<option value=''>".LANHELP_42."</option>";
				foreach($imagelist as $image)
				{
					$e_path = $tp->createConstants($image['path'],1);
					$showpath = str_replace($path,'',$image['path']);
					$text .= "<option value=\"[img]".$e_path.$image['fname']."[/img]\">".$showpath.$image['fname']."</option>\n";
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
//		$rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*');

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

		$tmp = $fl->get_files(e_FILE."downloads/");
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
				$text .= "<option value=\"[file={e_BASE}request.php?".$file['id']."{$ucinfo}]".htmlspecialchars($file['name'])."[/file]\">".htmlspecialchars($file['name'])." - {$ucname}</option>\n";
			}
			else
			{
				$text .= "<option value=\"[file={e_BASE}request.php?".htmlspecialchars($file['url'])."{$ucinfo}]".htmlspecialchars($file['name'])."[/file]\">".htmlspecialchars($file['name'])." - {$ucname}</option>\n";
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

?>