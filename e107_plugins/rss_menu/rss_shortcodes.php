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



class rss_menu_shortcodes extends e_shortcode
{
	private $tp;

	function __construct()
	{
		$this->tp = e107::getParser();
	}

	function sc_rss_feed()
	{
		$url2 = e107::url('rss_menu','rss', $this->var);
		return "<a href='".$url2."'>".$this->tp->toHTML($this->var['rss_name'], TRUE)."</a>";
	}

	function sc_rss_icon()
	{
		$url2 = e107::url('rss_menu','rss', $this->var);
		return "<a href='".$url2."'>".defset('RSS_ICON')."</a>";
	}

	function sc_rss_text()
	{
		return $this->tp->toHTML($this->var['rss_text'], TRUE, "defs");
	}

	function sc_rss_types()
	{
		$url2 = e107::url('rss_menu','rss', $this->var);
		$url4 = e107::url('rss_menu','atom', $this->var);

		if(deftrue('BOOTSTRAP')) // v2.x
		{
			$text = "
			<div>
				<a class='btn btn-sm btn-default'  href='".$url2."' title='RSS 2.0'>".$this->tp->toGlyph('fa-rss')." RSS</a>
				<a class='btn btn-sm btn-default'  href='".$url4."' title='ATOM'>".$this->tp->toGlyph('fa-rss')." Atom</a>
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


	function sc_rss_admin_import_check()
	{
		global $rs, $i;
		if(!empty($this->var['description']))
		{
			$this->var['text'] = $this->var['description'];
		}

		$text  = "<input id='import-".$i."' type='checkbox' name='importid[$i]' value='1' />";
		$text .= "<input type='hidden' name='name[$i]' value='".$this->tp->toForm($this->var['name'])."' />";
		$text .= "<input type='hidden' name='url[$i]' value='".$this->tp->toForm($this->var['url'])."' />";
		$text .= "<input type='hidden' name='topic_id[$i]' value='".$this->tp->toForm($this->var['topic_id'])."' />";
		$text .= "<input type='hidden' name='path[$i]' value='".$this->tp->toForm($this->var['path'])."' />";
		$text .= "<input type='hidden' name='text[$i]' value='".$this->tp->toForm($this->var['text'])."' />";
		$text .= "<input type='hidden' name='class[$i]' value='".$this->tp->toForm($this->var['class'])."' />";
		$text .= "<input type='hidden' name='limit[$i]' value='".intval($this->var['limit'])."' />";
		return $text;
	}

	function sc_rss_admin_import_path()
	{
		global $i;
		return "<label for='import-".$i."'>".$this->var['path']."</label>";
	}

	function sc_rss_admin_import_name()
	{
		global $i;
		return "<label for='import-".$i."'>".$this->var['name']."</label>";
	}

	function sc_rss_admin_import_text()
	{
		return !empty($this->var['description'])  ? $this->var['description'] : $this->var['text'];
	}

	function sc_rss_admin_import_url()
	{
		return $this->var['url'];
	}

	function sc_rss_admin_import_topicid()
	{
		return $this->var['topic_id'];
	}

}

//	$rss_shortcodes = new rss_menu_shortcodes;
