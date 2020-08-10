<?php
// $Id$
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 *    e107 website system - Shortcodes
 */
if (!defined('e107_INIT')) { exit; }   

 include_once(e_HANDLER.'shortcode_handler.php');
// $rss_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);



class rss_menu_shortcodes extends e_shortcode
{




	function sc_rss_feed()
	{
		global $row, $tp;
	//	$url2 = e_PLUGIN."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".2".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
		$url2 = e107::url('rss_menu','rss', $row);
		return "<a href='".$url2."'>".$tp->toHTML($row['rss_name'], TRUE)."</a>";
	}

	function sc_rss_icon()
	{
		global $row, $tp;
	//	$url2 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".2".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
		$url2 = e107::url('rss_menu','rss', $row);
		return "<a href='".$url2."'>".RSS_ICON."</a>";
	}

	function sc_rss_text()
	{
		global $row, $tp;
		return $tp->toHTML($row['rss_text'], TRUE, "defs");
	}

	function sc_rss_types()
	{
		global $row, $tp;
	//	$url1 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".1".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
	//	$url2 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".2".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
	//	$url3 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".3".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
	//	$url4 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".4".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');

		$url2 = e107::url('rss_menu','rss', $row);
		$url4 = e107::url('rss_menu','atom', $row);


		if(deftrue('BOOTSTRAP')) // v2.x
		{
			$text = "
			<div>
				<a class='btn btn-sm btn-default'  href='".e107::url('rss_menu', 'rss', $row)."' title='RSS 2.0'>".$tp->toGlyph('fa-rss')." RSS</a>
				<a class='btn btn-sm btn-default'  href='".e107::url('rss_menu', 'atom', $row)."' title='ATOM'>".$tp->toGlyph('fa-rss')." Atom</a>
			</div>";

			return $text;
		}



		$text = "";
	//	$text .= "<a href='".$url1."' class='rss'><img src='".e_PLUGIN_ABS."rss_menu/images/rss1.png' class='icon' alt='RSS 0.92' /></a>";
		$text .= "<a href='".$url2."' class='rss'><img src='".e_PLUGIN_ABS."rss_menu/images/rss2.png' class='icon' alt='RSS 2.0' /></a>";
	//	$text .= "<a href='".$url3."' class='rss'><img src='".e_PLUGIN_ABS."rss_menu/images/rss3.png' class='icon' alt='RDF' /></a>";
		$text .= "<a href='".$url4."' class='rss'><img src='".e_PLUGIN_ABS."rss_menu/images/rss4.png' class='icon' alt='ATOM' /></a>";
		return $text;
	}















	//##### ADMIN --------------------------------------------------





	function sc_rss_admin_caption($parm='')
	{
		global $sort;
		list($field,$txt) = explode(",",$parm);
		$txt = constant($txt);
		return "<a href='".e_SELF."?list.{$field}.".($sort == "desc" ? "asc" : "desc")."'>".$txt."</a>\n";
	}

	function sc_rss_admin_id()
	{
		global $row;
		return $row['rss_id'];
	}

	function sc_rss_admin_name()
	{
		global $row;
		return $row['rss_name'];
	}

	function sc_rss_admin_path()
	{
		global $row;
		return $row['rss_path'];
	}

	function sc_rss_admin_url()
	{
		global $row;
		return "<a href='".e_PLUGIN."rss_menu/rss.php?".e_LANQRY.$row['rss_url']."'>".$row['rss_url']."</a>";
	}

	function sc_rss_admin_topicid()
	{
		global $row;
		return $row['rss_topicid'];
	}

	function sc_rss_admin_limit()
	{
		global $row, $rs;
		$id = $row['rss_id'];
		$frm = e107::getForm();
		 return $frm->number("limit[$id]",$row['rss_limit']);
		return "<input class='tbox' type='text' name=\"limit[$id]\" title=\"".RSS_LAN05."\" value='".intval($row['rss_limit'])."' size='3' maxlength='3' />";
	}

	function sc_rss_admin_limitbutton()
	{
		$frm = e107::getForm();
		return $frm->admin_button('update_limit',LAN_UPDATE,'update');
	}

	function sc_rss_admin_options()
	{
		global $row, $tp;
		$delname = $row['rss_name'];
		$delid = $row['rss_id'];
		$options = "
		<a href='".e_SELF."?create.edit.".$row['rss_id']."' >".ADMIN_EDIT_ICON."</a>
		<input type='image' title=\"".LAN_DELETE."\" name='delete[{$delid}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL ." [".LAN_ID.": ".$delid." : ".$delname."]\\n\\n")."')\"/>";
		return $options;
	}

	function sc_rss_admin_form_name()
	{
		global $row;
		return "<input class='tbox' type='text' name='rss_name' size='74' value=\"".$row['rss_name']."\" />\n";
	}

	function sc_rss_admin_form_url()
	{
		global $row,$PLUGINS_DIRECTORY;
		return SITEURL.$PLUGINS_DIRECTORY."rss_menu/rss.php?".e_LANQRY." <input class='tbox' type='text' name='rss_url' size='10' value=\"".$row['rss_url']."\" maxlength='50' /> .{".LAN_TYPE."}.{".RSS_LAN_ADMIN_12."}";
	}

	function sc_rss_admin_form_topicid()
	{
		global $row;
		return "<input class='tbox' type='text' name='rss_topicid' size='74' value=\"".$row['rss_topicid']."\" maxlength='250' />";
	}

	function sc_rss_admin_form_path()
	{
		global $row;
		return "<input class='tbox' type='text' name='rss_path' size='74' value=\"".$row['rss_path']."\" maxlength='250' />";
	}

	function sc_rss_admin_form_text()
	{
		global $row;
		return "<textarea class='tbox' name='rss_text' cols='74' rows='5' >".$row['rss_text']."</textarea>\n";
	}

	function sc_rss_admin_form_class()
	{
		global $row;
		$vals = array(RSS_LAN_ADMIN_21,RSS_LAN_ADMIN_22,RSS_LAN_ADMIN_23);
		$text = "<select class='tbox' name='rss_class'>";
		foreach($vals as $key=>$val)
		{
			$sel = ($row['rss_class'] == $key) ? " selected='selected'" : "";
			$text .= "<option value='{$key}'{$sel}>$val</option>\n";
		}
		$text .= "</select>";
		return $text;
	}

	function sc_rss_admin_form_limit()
	{
		global $row;
		$frm = e107::getForm();

		return $frm->number('rss_limit',$row['rss_limit'],3);
		return "<input class='tbox' name='rss_limit' size='3' title=\"".RSS_LAN05."\" value='".intval($row['rss_limit'])."' maxlength='3' />";
	}


	function sc_rss_admin_form_createbutton()
	{
		global $row;
		$qs = explode(".", e_QUERY);
		$frm = e107::getForm();

		if(isset($qs[1]) && $qs[1] == "edit" && isset($qs[2]) && is_numeric($qs[2]) )
		{
			$text = "<input type='hidden' name='rss_datestamp' value='".$row['rss_datestamp']."' />
			<input type='hidden' name='rss_id' value='".$row['rss_id']."' />";

		    $text .= $frm->admin_button('update_rss',LAN_UPDATE,'update');

		}
		else
		{
		    $text = $frm->admin_button('create_rss',LAN_CREATE,'submit');

		}
		return $text;
	}

	function sc_rss_admin_import_check()
	{
		global $feed, $rs, $tp, $i;
		if($feed['description'])
		{
			$feed['text'] = $feed['description'];
		}

		$text  = "<input id='import-".$i."' type='checkbox' name='importid[$i]' value='1' />";
		$text .= "<input type='hidden' name='name[$i]' value='".$tp->toForm($feed['name'])."' />";
		$text .= "<input type='hidden' name='url[$i]' value='".$tp->toForm($feed['url'])."' />";
		$text .= "<input type='hidden' name='topic_id[$i]' value='".$tp->toForm($feed['topic_id'])."' />";
		$text .= "<input type='hidden' name='path[$i]' value='".$tp->toForm($feed['path'])."' />";
		$text .= "<input type='hidden' name='text[$i]' value='".$tp->toForm($feed['text'])."' />";
		$text .= "<input type='hidden' name='class[$i]' value='".$tp->toForm($feed['class'])."' />";
		$text .= "<input type='hidden' name='limit[$i]' value='".intval($feed['limit'])."' />";
		return $text;
	}

	function sc_rss_admin_import_path()
	{
		global $feed, $i;
		return "<label for='import-".$i."'>".$feed['path']."</label>";
	}

	function sc_rss_admin_import_name()
	{
		global $feed, $i;
		return "<label for='import-".$i."'>".$feed['name']."</label>";
	}

	function sc_rss_admin_import_text()
	{
		global $feed;
		return ($feed['description'])  ? $feed['description'] : $feed['text'];
	}

	function sc_rss_admin_import_url()
	{
		global $feed;
		return $feed['url'];
	}

	function sc_rss_admin_import_topicid()
	{
		global $feed;
		return $feed['topic_id'];
	}

}

	$rss_shortcodes = new rss_menu_shortcodes;
