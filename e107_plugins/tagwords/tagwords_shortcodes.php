<?php
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$tagwords_shortcodes = $tp->e_sc->parse_scbatch(__FILE__);
/*

SC_BEGIN TAG_SEARCH
	global $tp, $tag;
	
	$value = (isset($_GET['q']) ? $tp->toForm($_GET['q']) : '');
	switch($sc_mode)
	{
		case 'menu':
			if(varsettrue($tag->pref['tagwords_menu_view_search'])!=1)
			{
				return;
			}
			$id = 'tagwords_searchform_menu';
			
			return "
			<form id='".$id."' name='".$id."' method='get' action='".e_PLUGIN."tagwords/tagwords.php'>
			<input class='tbox' style='width:100px;' type='text'  name='q' size='35' value='".$value."' maxlength='50' />
			<input class='button' type='submit' name='se' value='".LAN_TAG_SEARCH_2."' />
			</form>";
			break;
		
		case 'search':
			if(varsettrue($tag->pref['tagwords_view_search'])!=1)
			{
				return;
			}
			$id = 'tagwords_searchform';
			return "
			<form id='".$id."' name='".$id."' method='get' action='".e_PLUGIN."tagwords/tagwords.php'>
			<input class='tbox' style='width:100px;' type='text'  name='q' size='35' value='".$value."' maxlength='50' />
			<input class='button' type='submit' name='s' value='".LAN_TAG_SEARCH_2."' />
			</form>";
			break;
		
		default:
			if(varsettrue($tag->pref['tagwords_view_search'])!=1)
			{
				return;
			}
			return "
			<input class='tbox' style='width:100px;' type='text'  name='q' size='35' value='".$value."' maxlength='50' />
			<input class='button' type='submit' name='s' value='".LAN_TAG_SEARCH_2."' />";
			break;
	}

SC_END

SC_BEGIN TAG_AREA_HEADING
	global $tag;
	return varsettrue($tag->area->settings['caption']);
SC_END

SC_BEGIN TAG_LINK
	global $tag;
	switch($sc_mode)
	{
		case 'menu':
			return "<a href='".e_PLUGIN."tagwords/tagwords.php'>".LAN_TAG_MENU_1."</a>";
			break;
		case 'home':
			return "<a href='".e_PLUGIN."tagwords/tagwords.php'>".LAN_TAG_7."</a>";
			break;
		default:
			if(method_exists($tag->area, 'getLink'))
			{
				return $tag->area->getLink($tag->id);
			}
			break;
	}
SC_END

SC_BEGIN TAG_CLOUD
	global $tag;
	switch($sc_mode)
	{
		case 'menu':
			return $tag->TagCloud('menu');
			break;
		case 'list':
			return $tag->TagCloudList();
			break;
		default:
			return $tag->TagCloud();
			break;
	}
SC_END

SC_BEGIN TAG_WORD
	global $tag, $tp;
	switch($sc_mode)
	{
		case 'result':
			return "<b>".$tag->num."</b> ".($tag->num==1 ? LAN_TAG_8 : LAN_TAG_9)." '<b>".$tp->toHTML($_GET['q'],TRUE)."</b>'";
			break;
		case 'form':
		default:
			return $tag->word;
			break;
	}
SC_END

SC_BEGIN TAG_NUMBER
	global $tag;
	switch($sc_mode)
	{
		case 'list':
			return $tag->number;
			break;
		case 'menu':
			if(varsettrue($tag->pref['tagwords_menu_view_freq'])==1)
			{
				return $tag->number;
			}
			break;
		default:
			if(varsettrue($tag->pref['tagwords_view_freq'])==1)
			{
				return $tag->number;
			}
			break;
	}
SC_END

SC_BEGIN TAG_SORT
	global $tag;

	$sort=FALSE;
	if(varsettrue($tag->pref['tagwords_view_sort'])==1)
	{
		$s = varset($_GET['sort'],'');
		switch($s)
		{
			case 'alpha':
				$sel = 'alpha';
				break;
			case 'freq':
				$sel = 'freq';
				break;
			default:
				$sel = '';
				break;
		}

		$text = "
		<select id='sort' name='sort' class='tbox'>
			<option value=''>".LAN_TAG_19."</option>
			<option value='alpha' ".($sel=='alpha' ? "selected='selected'" : '')." >".LAN_TAG_10."</option>
			<option value='freq' ".($sel=='freq' ? "selected='selected'" : '')." >".LAN_TAG_11."</option>
		</select>";
		return $text;
	}
	return;
SC_END

SC_BEGIN TAG_TYPE
	global $tag;

	$type=FALSE;
	if(varsettrue($tag->pref['tagwords_view_style'])==1)
	{
		$t = varset($_GET['type'],'');
		switch($t)
		{
			case 'cloud':
				$sel = 'cloud';
				break;
			case 'list':
				$sel = 'list';
				break;
			default:
				$sel = '';
				break;
		}

		$text = "
		<select id='type' name='type' class='tbox'>
			<option value=''>".LAN_TAG_20."</option>
			<option value='cloud' ".($sel=='cloud' ? "selected='selected'" : '')." >".LAN_TAG_13."</option>
			<option value='list' ".($sel=='list' ? "selected='selected'" : '')." >".LAN_TAG_12."</option>
		</select>";
		return $text;
	}
	return;
SC_END

SC_BEGIN TAG_AREA
	global $tag;
	if(varsettrue($tag->pref['tagwords_view_area'])==1)
	{
		$text = "
		<select id='area' name='area' class='tbox'>
			<option value='' >".LAN_TAG_15."</option>";
			foreach($tag->tagwords as $area)
			{
				if(array_key_exists($area,$tag->pref['tagwords_activeareas']))
				{
					$name = "e_tagwords_{$area}";
					$selected = (varsettrue($_GET['area'])==$area ? "selected=selected" : '');
					$text .= "<option value='".$area."' ".$selected." >".$tag->$name->settings['caption']."</option>";
				}
			}
		$text .= "
		</select>";
		return $text;
	}
	return;
SC_END

SC_BEGIN TAG_BUTTON
	return "<input class='button' type='submit' name='so' value='".LAN_TAG_SEARCH_3."' />";
SC_END

SC_BEGIN TAG_OPTIONS
	global $tp, $tag;
	if( varsettrue($tag->pref['tagwords_view_search'])==1 || varsettrue($tag->pref['tagwords_view_sort'])==1 || varsettrue($tag->pref['tagwords_view_style'])==1 || varsettrue($tag->pref['tagwords_view_area'])==1 )
	{
		return $tp->parseTemplate($tag->template['options'], FALSE, $tag->shortcodes);
	}
SC_END

//##### ADMIN OPTIONS -------------------------

SC_BEGIN TAG_OPT_MIN
	global $tag;
	$id = ($sc_mode=='menu' ? 'tagwords_menu_min' : 'tagwords_min');
	return "<input class='tbox' type='text' id='".$id."' name='".$id."' value='".$tag->pref[$id]."' size='3' maxlength='3' />";
SC_END

SC_BEGIN TAG_OPT_CLASS
	global $tag;
	$id = 'tagwords_class';
	return r_userclass($id,$tag->pref[$id],"","admin,public,guest,nobody,member,classes");
SC_END

SC_BEGIN TAG_OPT_DEFAULT_SORT
	global $tag;
	$id = ($sc_mode=='menu' ? 'tagwords_menu_default_sort' : 'tagwords_default_sort');
	return "<label><input type='radio' name='".$id."' value='1' ".($tag->pref[$id] ? "checked='checked'" : "")." /> ".LAN_TAG_OPT_5."</label>&nbsp;&nbsp;
	<label><input type='radio' name='".$id."' value='0' ".($tag->pref[$id] ? "" : "checked='checked'")." /> ".LAN_TAG_OPT_6."</label>";
SC_END

SC_BEGIN TAG_OPT_DEFAULT_STYLE
	global $tag;
	$id = 'tagwords_default_style';
	return "<label><input type='radio' name='".$id."' value='1' ".($tag->pref[$id] ? "checked='checked'" : "")." /> ".LAN_TAG_OPT_8."</label>&nbsp;&nbsp;
	<label><input type='radio' name='".$id."' value='0' ".($tag->pref[$id] ? "" : "checked='checked'")." /> ".LAN_TAG_OPT_9."</label>";
SC_END

SC_BEGIN TAG_OPT_VIEW_SORT
	global $tag;
	$id = 'tagwords_view_sort';
	$sel = ($tag->pref[$id] ? "checked='checked'" : "");
	return "
	<label for='".$id."'>
		<input type='checkbox' name='".$id."' id='".$id."' value='1' ".$sel." /> ".LAN_TAG_OPT_12."
	</label>";
SC_END

SC_BEGIN TAG_OPT_VIEW_STYLE
	global $tag;
	$id = 'tagwords_view_style';
	$sel = ($tag->pref[$id] ? "checked='checked'" : "");
	return "
	<label for='".$id."'>
		<input type='checkbox' name='".$id."' id='".$id."' value='1' ".$sel." /> ".LAN_TAG_OPT_13."
	</label>";
SC_END

SC_BEGIN TAG_OPT_VIEW_AREA
	global $tag;
	$id = 'tagwords_view_area';
	$sel = ($tag->pref[$id] ? "checked='checked'" : "");
	return "
	<label for='".$id."'>
		<input type='checkbox' name='".$id."' id='".$id."' value='1' ".$sel." /> ".LAN_TAG_OPT_14."
	</label>";
SC_END

SC_BEGIN TAG_OPT_VIEW_SEARCH
	global $tag;
	$id = ($sc_mode=='menu' ? 'tagwords_menu_view_search' : 'tagwords_view_search');
	$sel = ($tag->pref[$id] ? "checked='checked'" : "");
	return "
	<label for='".$id."'>
		<input type='checkbox' name='".$id."' id='".$id."' value='1' ".$sel." /> ".LAN_TAG_OPT_19."
	</label>";
SC_END

SC_BEGIN TAG_OPT_VIEW_FREQ
	global $tag;
	if($sc_mode=='menu')
	{
		$id = 'tagwords_menu_view_freq';
	}
	else
	{
		$id = 'tagwords_view_freq';
	}
	$sel = ($tag->pref[$id] ? "checked='checked'" : "");
	return "
	<label for='".$id."'>
		<input type='checkbox' name='".$id."' id='".$id."' value='1' ".$sel." /> ".LAN_TAG_OPT_20."
	</label>";
SC_END

SC_BEGIN TAG_OPT_CAPTION
	global $tp, $tag;
	$id = 'tagwords_menu_caption';
	return "<input class='tbox' type='text' id='".$id."' name='".$id."' value='".$tp->toForm($tag->pref[$id],"","defs")."' size='30' maxlength='100' />";
SC_END

SC_BEGIN TAG_OPT_SEPERATOR
	global $tp, $tag;
	$id = 'tagwords_word_seperator';
	return "<input class='tbox' type='text' id='".$id."' name='".$id."' value='".$tp->toForm($tag->pref[$id])."' size='3' maxlength='10' />";
SC_END

SC_BEGIN TAG_OPT_ACTIVEAREAS
	global $tag;
	$id = 'tagwords_activeareas';
	$text = "";
	foreach($tag->tagwords as $area)
	{
		$sel = (array_key_exists($area,$tag->pref[$id]) ? "checked='checked'" : '');
		$name = "e_tagwords_{$area}";

		$text .= "
		<label for='".$id."[".$area."]'>
		<input type='checkbox' name='".$id."[".$area."]' id='".$id."[".$area."]' value='1' ".$sel." />
		".$tag->$name->settings['caption']."
		</label><br />";

	}
	return $text;
SC_END

SC_BEGIN TAG_OPT_BUTTON
	return "<input class='button' type='submit' name='updatesettings' value='".LAN_UPDATE."' />";
SC_END

*/

?>