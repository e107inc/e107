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
 * $Source: /cvs_backup/e107_0.8/e107_files/shortcode/batch/bbcode_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

include_once(e_HANDLER.'shortcode_handler.php');
//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_ren_help.php');
e107::coreLan('ren_help');

$codes = array('bb', 'bb_help', 'bb_preimagedir');
// register_shortcode('bbcode_shortcodes', $codes);

class bbcode_shortcodes extends e_shortcode
{

	// The BBcode Buttons. 
	function bb_format($id)
	{
		 
		$text = "<select class='e-bb bbcode_buttons e-pointer' id='{$id}' title='Format text' onchange=\"addtext(this.value);this.value=''\">
			<option value=''>Format</option>		
			<option value='[p][/p]'>Paragraph</option>
			<option value='[h2][/h2]'>Heading</option>
			<option value='[block][/block]'>Block</option>
			<option value='[blockquote][/blockquote]'>Quote</option>
			<option value='[code][/code]'>Code</option>
			</select>";
			
		return $text;
		
	}
	
	function bb_table($id)
	{
	
	//	$data = "[table]\n[tr]\n\t[td]Cell 1[/td]\n\t[td]Cell 2[/td]\n[/tr]\n[/table]"; // works with jquery, but not onclick. 
		$data = "[table][tr][td]Cell 1[/td][td]Cell 2[/td][/tr][/table]";
		$event = $this->getEvent('addtext',$data,'Insert a table',1);
		$text = "<a {$event} class='e-bb' id='{$id}' data-function='insert' href='#{$this->var['tagid']}'  data-bbcode='{$data}'>";
		$text .= "<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/table.png' alt='' title='Insert a table' /></a>";
		return $text;
	}
	
	function bb_newpage($id)
	{
	//	$data = "[table]\n[tr]\n\t[td]Cell 1[/td]\n\t[td]Cell 2[/td]\n[/tr]\n[/table]"; // works with jquery, but not onclick. 
	//	$data = "[newpage]";
	//	$event = $this->getEvent('addtext',$data,LANHELP_34,1);
		$event = '';
		$text = "<a {$event} class='e-bb e-tabs-add' id='{$id}' data-url='".e_SELF."?mode=dialog&action=dialog&iframe=1' data-function='add' href='#{$this->var['tagid']}'  data-bbcode='{$data}'>";
		$text .= "<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/newpage.png' alt='' title='".LANHELP_34."'  /></a>";
		return $text;
	}
	
	
	
	function bb_list($id)
	{

		$data = "[list][*]Item 1[*]Item 2[/list]";
	//	$data = "[list]\n[*]Item 1\n[*]Item 2\n[/list]"; // works with jquery, but not onclick. 
		$event = $this->getEvent('addtext',$data,LANHELP_36);
		$text = "<a {$event} class='e-bb' id='{$id}' data-function='insert' href='#{$this->var['tagid']}' data-bbcode='{$data}'>";
		$text .= "<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/list.png' alt='' title='".nl2br(LANHELP_36)."' /></a>";
		return $text;
	}
	
	function bb_youtube($id)
	{
		$data = "[youtube]*[/youtube]";
		$event = $this->getEvent('addinput',$data,LANHELP_48);
		$text = "<a {$event} class='e-bb' id='{$id}' data-function='input' href='#{$this->var['tagid']}'  data-bbcode='{$data}'>";
		$text .="<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/youtube.png' alt='' title='".nl2br(LANHELP_48)."' /></a>";
		return $text;
	}
	
	function bb_link($id)
	{
		$data = "[link=*]*[/link]";
		$event = $this->getEvent('addinput',$data,LANHELP_35);
		$text = "<a {$event} class='e-bb ' id='{$id}' data-function='input' href='#{$this->var['tagid']}'  data-bbcode='{$data}'>\n";
		$text .="<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/link.png' alt='' title='".nl2br(LANHELP_23)."' /></a>";
		return $text;
	}
	
	function bb_preimage($id)
	{
		
		if($this->var['tagid'] == 'data_') // BC work-around for duplicate IDs. 
		{
			$tag =  "data";
		}
		else
		{
			list($tag,$tmp) = explode("--",$this->var['tagid']); // works with $frm->bbarea to detect textarea from first half of tag. 
		}
		$text = "<a class='e-dialog' id='{$id}' href='".e_ADMIN."image.php?mode=main&action=dialog&for=".$this->var['template']."&tagid=".$tag."&iframe=1&bbcode=img'  >";
		$text .= "<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/preimage.png' title='".LANHELP_45."' alt='' />";
		$text .= "</a>\n";
		return $text;
	}
	
	function bb_prefile($id)
	{
		if($this->var['tagid'] == 'data_') // BC work-around for duplicate IDs. 
		{
			$tag =  "data";
		}
		else
		{
			list($tag,$tmp) = explode("--",$this->var['tagid']); // works with $frm->bbarea to detect textarea from first half of tag. 
		}
		$text = "<a class='e-dialog' id='{$id}' href='".e_ADMIN."image.php?mode=dialog&action=list&for=_common_file&tagid=".$tag."&iframe=1&bbcode=file'  >";
		$text .= "<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/prefile.png' title='".LANHELP_39."' alt='' />";
		$text .= "</a>\n";
		return $text;
	}	
	
	function bb_fontsize($id) // FIXME CSS issues 
	{
		
		 $data = "size";
		 $formid = $id."_";

		 $event = $this->getEvent('expandit',$formid, LANHELP_22);
		$text = "<a {$event} class='e-bb e-expandit'  onclick=\"expandit('{$this->var['tagid']}')\" data-function='show' href='#{$this->var['tagid']}' title='".LANHELP_22."' data-bbcode='{$data}'>
		<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/fontsize.png' alt='' title='".LANHELP_22."' /></a>";
		
		
		$text .="<!-- Start of Size selector -->
		<div id='{$this->var['tagid']}' class='e-hideme col-selection' style='position:relative;top:30px;left:200px' >";
		$text .="<div style='position:relative;bottom:30px; left:125px; width:100px'>";
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

		


	function bb_fontcol($id) // JS in this breaks ajax loading.  
	{
		return '';
		// $bbcode['fontcol'] = array("e-expandit","col_selector_".$rand, LANHELP_21,"fontcol.png","Color_Select",'col_selector_'.$rand);
	
		$formid = $id."_";
		 $event = $this->getEvent('expandit',$formid, LANHELP_22);
		 
		 
		$text = "<a {$event} class='e-bb' id='{id}' data-function='show' href='#{$this->var['tagid']}' title='".LANHELP_22."' data-bbcode='{$data}'>
		<img class='bbcode_buttons e-pointer' src='".e_IMAGE_ABS."bbcode/fontcol.png' alt='' title='".LANHELP_21."' /></a>";
		
	//	return $text;
		
		$text .= "<!-- Start of Color selector -->
	<div id='{$formid}' style='width: 221px; position: absolute; left:340px; top:60px;  margin-right:auto; margin-left:auto; display:none; z-index: 1000;  onclick=\"expandit('{$formid}')\" >
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
		return '<td style=\'background-color: #' + color + '; height: 10px; width: 10px;\' onmousedown=\"addtext(\'[color=#' + color + '][/color]\');expandit(\'{$formid}\')\"><\/td>';
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




	
	// shouldn't be needed when js css selectors are enabled. 
	function getEvent($func,$func_var,$hint_diz,$emote = '')
	{
		if($emote)
		{
			$emote = ",".$emote;	
		}
		$text = "onclick=\"{$func}('".$func_var."'{$emote})\" ";
		$bbcode_help = $this->var['hint_func'];
		$bbcode_tag = $this->var['tagid'];
		$_helptxt	= $hint_diz;
	//	onclick="addtext('[justify][/justify]')" onmouseout="help('','admin')" onmouseover="help('Justify align: [justify]This text will be justified[/justify]','admin')">
		$text .= ($this->var['hint_active'] ? "onmouseout=\"{$bbcode_help}('','{$bbcode_tag}')\" onmouseover=\"{$bbcode_help}('".$_helptxt."','{$bbcode_tag}')\"" : "" );
		return $text;	
	}
	
	
	
			
	
	function sc_bb($parm)
	{
		if(method_exists($this,"bb_".$parm)) // start of the big cleanup. 
		{
			$meth = "bb_".$parm;
			$mes = e107::getMessage();
		//	$mes->debug("Loaded BB: ".$parm);
			$unique = $this->var['template']."--".$parm; // works in conjunction with media-manager category
			return "\n\n<!-- {$parm} -->\n".$this->$meth($unique);
		}
		
		// NOTE: everything below here could be replaced with separate 'bb_xxxx' methods if need be. (see above)
	
		
		//FIXME - cachevars/getcachedvars!
		global $pref, $eplug_bb, $bbcode_func, $bbcode_help, $bbcode_filedir, $bbcode_imagedir, $bbcode_helpactive, $bbcode_helptag, $register_bb;

	//	if(defsettrue('e_WYSIWYG')){ return; }

		$bbcode_func = ($bbcode_func) ? $bbcode_func : "addtext";
		$bbcode_help  = ($bbcode_help) ? $bbcode_help : "help";
		$bbcode_tag  = ($bbcode_helptag != 'helpb') ? ",'$bbcode_helptag'" : "";

		$rand = rand(1000,9999);
		$imagedir_display = str_replace('../','',$bbcode_imagedir);

		if($parm == 'emotes')
		{
			if ($pref['comments_emoticons'] && $pref['smiley_activate'] && !defsettrue('e_WYSIWYG'))
			{
				$bbcode['emotes'] = array("expandit","emoticon_selector_".$rand, LANHELP_44, "emotes.png", "Emoticon_Select", "emoticon_selector_".$rand);
			}
			else
			{	// If emotes disabled, don't return anything (without this we return an empty image reference)
				return '';
			}
		}

		// Format: $bbcode['UNIQUE_NAME'] = array(ONCLICK_FUNC, ONCLICK_VAR, HELPTEXT, ICON, INCLUDE_FUNC, INCLUDE_FUNCTION_VAR);

		$bbcode['newpage'] = array($bbcode_func,"[newpage]", LANHELP_34, "newpage.png");
		$bbcode['link'] = array('addinput',"[link=".LANHELP_35."][/link]", LANHELP_23,"link.png");
		$bbcode['h'] = array($bbcode_func,"[h][/h]", LANHELP_50,"heading.png"); // FIXME bbcode icon
		$bbcode['p'] = array($bbcode_func,"[p][/p]", LANHELP_49,"paragraph.png"); // FIXME bbcode icon
		$bbcode['b'] = array($bbcode_func,"[b][/b]", LANHELP_24,"bold.png");
		$bbcode['i'] = array($bbcode_func,"[i][/i]", LANHELP_25,"italic.png");
		$bbcode['u'] = array($bbcode_func,"[u][/u]", LANHELP_26,"underline.png");
		$bbcode['justify'] = array($bbcode_func,"[justify][/justify]", LANHELP_53,"center.png"); // FIXME bbcode icon
		$bbcode['center'] = array($bbcode_func,"[center][/center]", LANHELP_28,"center.png");
		$bbcode['left'] = array($bbcode_func,"[left][/left]", LANHELP_29,"left.png");
		$bbcode['right'] = array($bbcode_func,"[right][/right]", LANHELP_30,"right.png");
		$bbcode['bq'] = array($bbcode_func,"[blockquote][/blockquote]", LANHELP_31,"blockquote.png");
		$bbcode['code'] = array($bbcode_func,"[code][/code]", LANHELP_32,"code.png");
		$bbcode['list'] = array($bbcode_func,"[list][/list]", LANHELP_36,"list.png");
		$bbcode['img'] = array($bbcode_func,"[img][/img]", LANHELP_27,"image.png");
		$bbcode['flash'] = array($bbcode_func,"[flash=width,height][/flash]", LANHELP_47,"flash.png");
		$bbcode['youtube'] = array($bbcode_func,"[youtube][/youtube]", LANHELP_48,"youtube.png");
		$bbcode['sanitised'] = array('', '', '');
		$bbcode['format'] = array('dropdown', '[format]', 'da',"<select><option>hello</option></select>");
		
		$bbcode['nobr'] = array($bbcode_func,"[nobr][/nobr]", LANHELP_51, "nobr.png"); // FIXME bbcode icon
		$bbcode['br'] = array($bbcode_func,"[br]", LANHELP_52, "br.png"); // FIXME bbcode icon
		$bbcode['block'] = array($bbcode_func,"[block][/block]", LANHELP_54,"block.png"); // FIXME bbcode icon, interactive interface, theme hooks

		$bbcode['fontsize'] = array("expandit","size_selector_".$rand, LANHELP_22,"fontsize.png","Size_Select",'size_selector_'.$rand);
		$bbcode['fontcol'] = array("e-expandit","col_selector_".$rand, LANHELP_21,"fontcol.png","Color_Select",'col_selector_'.$rand);
		$bbcode['preimage'] = array("e-dialog","preimage_selector_".$rand, LANHELP_45.$imagedir_display,"preimage.png","PreImage_Select","preimage_selector_".$rand);
		$bbcode['prefile'] = array("expandit","prefile_selector_".$rand, LANHELP_39,"prefile.png","PreFile_Select",'prefile_selector_'.$rand);

		if(!isset($iconpath[$parm]))
		{
			$iconpath[$parm] =  (file_exists(THEME."bbcode/bold.png") ? THEME_ABS."bbcode/" : e_IMAGE_ABS."bbcode/");
			$iconpath[$parm] .= $bbcode[$parm][3];
		}



		if (!empty($register_bb))
		{
			foreach($register_bb as $key=>$val) // allow themes to plug in to it.
			{
				if($val[0]=="")
				{
					$val[0] = $bbcode_func;
				}
				$bbcode[$key] = $val;
				$iconpath[$key] = $val[3];
			}
		}


		if (!empty($eplug_bb))
		{
			foreach($eplug_bb as $val)  // allow plugins to plug into it.
			{
				if(!$val) continue;
				extract($val); 
				//	echo "$onclick $onclick_var $helptext $icon <br />";
				$bbcode[$name] = array($onclick,$onclick_var,$helptext,$icon,$function,$function_var);
				$iconpath[$name] = $icon;
			}
		}

		$pre = "\n";
		$post = "\n";

		$_onclick_func = (isset($bbcode[$parm][0])) ? $bbcode[$parm][0] : $bbcode_func;
		$_onclick_var = (isset($bbcode[$parm][1])) ? $bbcode[$parm][1] : '';
		$_helptxt = (isset($bbcode[$parm][2])) ? $bbcode[$parm][2] : '';
		$_function = (isset($bbcode[$parm][4])) ? $bbcode[$parm][4] : '';
		$_function_var = (isset($bbcode[$parm][5])) ? $bbcode[$parm][5] : '';

		if($_onclick_func == 'e-dialog')
		{  //  $tagid = "news-body";
			$pre = "\n<a href='".e_ADMIN."image.php?mode=main&action=dialog&for=news&tagid=".$tagid."&iframe=1&bbcode=1' class='e-dialog' >";
			$post = "</a>\n";	
		}
		

		

		if($bbcode[$parm])  // default - insert text.
		{
			$text = $pre;
			$text .= "<img class='bbcode bbcode_buttons e-pointer' src='".$iconpath[$parm]."' alt='' title=\"".nl2br($_helptxt)."\" onclick=\"{$_onclick_func}('".$_onclick_var."')\" ".($bbcode_helpactive ? "onmouseout=\"{$bbcode_help}(''{$bbcode_tag})\" onmouseover=\"{$bbcode_help}('".$_helptxt."'{$bbcode_tag})\"" : "" )." />";
			$text .= $post;
		}
	
		

		if($_function)
		{

			$text .= ($bbcode_helpactive && $_helptxt && !$iconpath[$parm]) ? "<span onmouseout=\"{$bbcode_help}(''{$bbcode_tag})\" onmouseover=\"{$bbcode_help}('".$_helptxt."'{$bbcode_tag})\" >" : "";
			$text .= call_user_func($_function, $_function_var);
			$text .= ($bbcode_helpactive && $_helptxt && !$iconpath[$parm]) ? "</span>" : "";
		}

		return $text;
	}

	function sc_bb_help($parm)
	{
		return ''; // Use tooltips instead. 
		/*
		global $bbcode_helpactive,$bbcode_helptag, $bbcode_helpsize;
		if($parm) $bbcode_helptag = $parm;
		elseif(!varset($bbcode_helptag))  $bbcode_helptag = 'helpb';
		if($bbcode_helpsize) $bbcode_helpsize = ' '.$bbcode_helpsize;
		$bbcode_helpactive = TRUE;
		*/
		//FIXME - better bb help
		
		$bbcode_helptag 	= ($this->var['tagid']) ? $this->var['tagid'] : 'data_';
		$bbcode_helpsize 	= $this->var['size'];
		$bbcode_helpactive 	= $this->var['hint_active'];
		
		return "<input id='{$bbcode_helptag}' class='helpbox {$bbcode_helpsize}' type='text' name='{$bbcode_helptag}' size='90' readonly='readonly' />";
	}

	function sc_bb_preimagedir($parm)
	{
		if(defsettrue('e_WYSIWYG')) { return; }
		global $bbcode_imagedir;
		$bbcode_imagedir = $parm;
		return;
	}
}
?>