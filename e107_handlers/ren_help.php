<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @DEPRECATED FILE
 */

if (!defined('e107_INIT')) { exit; }

// IMPORTANT : Entire file is deprecated. see: bbcode_shortcodes.php and bbcode_hanlder.php 
// Left in place only for BC. 
/**
 *  @DEPRECATED use $frm->bbarea() instead. 
 */
function ren_help($mode = 1, $addtextfunc = "addtext", $helpfunc = "help")
{
    // ren_help() is deprecated - use display_help().
    return display_help("helpb", $mode, $addtextfunc, $helpfunc = "help");
}




// DONE - full rewrite, EVERYTHING - bbcode class (php + JS), core callbacks, tooltip help, optimize
/**
 * @DEPRECATED use $frm->bbarea() instead. 
 */
function display_help($tagid="helpb", $mode = 1, $addtextfunc = "addtext", $helpfunc = "help", $helpsize = '')
{
	
	$options = array('trigger' => $addtextfunc );
	
	return e107::getBB()->renderButtons($mode,'data',$options); // guessing the name of the textarea as 'data' no indicator unfortunately. 
	// may cause pre-image and pre-file selector issues. 
	
		
  //  if(defsettrue('e_WYSIWYG')) { return; }
	
	/*
	global $tp, $pref, $eplug_bb, $bbcode_func, $register_bb, $bbcode_help, $bbcode_helpactive, $bbcode_helptag, $bbcode_helpsize;
	$bbcode_helpsize = $helpsize;

	$bbcode_func = $addtextfunc;
 	$bbcode_help = $helpfunc;
    $bbcode_helptag = $tagid;
	
	// $arr = get_defined_vars();
	// print_a($arr);

    // load the template
	if(is_readable(THEME."bbcode_template.php"))
	{
		include(THEME."bbcode_template.php");
	}
	else
	{
		include(e_CORE."templates/bbcode_template.php");
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
	$temp['page']		= $BBCODE_TEMPLATE_CPAGE;
	$temp['maintenance']= $BBCODE_TEMPLATE_ADMIN;
	$temp['comment'] 	= $BBCODE_TEMPLATE;
	$temp['signature'] 	= $BBCODE_TEMPLATE_SIGNATURE;

	if(isset($temp[$mode]))
	{
        $BBCODE_TEMPLATE = $temp[$mode];
	}

	$visible = deftrue('e_WYSIWYG') ? "style='display:none'" : "";

    if(is_readable(e_CORE."shortcodes/batch/bbcode_shortcodes.php"))
	{
		
		$sc = e107::getScBatch('bbcode');
		
		if($tagid == 'data') // BC fix. 
		{
			$tagid = 'data_';	
		}
		
		$data = array(
			'tagid'			=> $tagid,
			'template'		=> $mode,
			'trigger'		=> $addtextfunc,
			'hint_func'		=> $helpfunc,
			'hint_active'	=> $bbcode_helpactive,
			'size'			=> $helpsize
		);
				
		$sc->setVars($data);	
		
  		return "<div id='bbcode-panel-".$tagid."' class='mceToolbar bbcode-panel' {$visible}>".$tp->parseTemplate($BBCODE_TEMPLATE)."</div>";
	}
	else
	{
    	return "ERROR: ".e_CORE."shortcodes/batch/bbcode_shortcodes.php IS NOT READABLE.";
	}
	*/
	
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
	<div style='width: 221px; position: absolute; left:340px; top:60px;  margin-right:auto; margin-left:auto; display:none; z-index: 1000; id='{$formid}' onclick=\"expandit(this)\" >
	<div  style='border:1px solid black; position: absolute;  top:30px;  width: 221px; '>";

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


function PreImage_Select($formid='preimage_selector')
{
	global $bbcode_imagedir;
		
	$med = e107::getMedia();
	
	$text ="<!-- Start of PreImage selector -->";
	
	$text .= $med->imageSelect($bbcode_imagedir,$formid);
	
	return $text; 
}




// Parameter '$bbcode_filedir' removed - not used in code.
function PreFile_Select($formid='prefile_selector')
{
	require_once(e_HANDLER."userclass_class.php");
	global $IMAGES_DIRECTORY, $fl;

	$sql = e107::getDb();
	$fl = e107::getFile();
	
	$filelist = array();
	$downloadList = array();

	/*$sql->db_Select('download', '*', 'download_class != '.e_UC_NOBODY);
	while ($row = $sql->db_Fetch()) {
		extract($row);
		if($download_url)
		{
			$filelist[] = array('id' => $download_id, 'name' => $download_name, 'url' => $download_url, 'class' => $download_class);
			$downloadList[] = $download_url;
		}
	}*/

	$tmp = $fl->get_files(e_FILE.'downloads/');
	foreach($tmp as $value)
	{
		if(!in_array($value['fname'], $downloadList))
		{
			$filelist[] = array('id' => 0, 'name' => $value['fname'], 'url' => $value['fname']);
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
	// require_once(e_HANDLER."emote.php");
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

