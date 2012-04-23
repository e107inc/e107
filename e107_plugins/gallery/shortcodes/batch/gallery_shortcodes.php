<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if (!defined('e107_INIT')) { exit; }


class gallery_shortcodes extends e_shortcode
{		
	function sc_gallery_caption($parm='')
	{
		$text = "<a href='".e107::getParser()->replaceConstants($this->eParserVars['media_url'])."' rel='external shadowbox' >";
		$text .= $this->eParserVars['media_caption'];
		$text .= "</a>";
		return $text;
	}
	
	function sc_gallery_thumb($parm='')
	{
		$att = ($parm) ?$parm : 'aw=190&ah=150';
		$text = "<a href='".e107::getParser()->replaceConstants($this->eParserVars['media_url'])."' rel='external shadowbox' >";
		$text .= "<img src='".e107::getParser()->thumbUrl($this->eParserVars['media_url'],$att)."' alt='' />";
		$text .= "</a>";
		return $text;	
	}
	
	function sc_gallery_cat_title($parm='')
	{
		$tp = e107::getParser();
		$text = "<a href='".e_SELF."?cat=".$this->eParserVars['media_cat_category']."'>";
		$text .= $tp->toHtml($this->eParserVars['media_cat_title']);
		$text .= "</a>";
		return $text;	
	}
	
	function sc_gallery_cat_thumb($parm='')
	{
		$att = ($parm) ?$parm : 'aw=190&ah=150';
		$text = "<a href='".e_SELF."?cat=".$this->eParserVars['media_cat_category']."'>";
		$text .= "<img src='".e107::getParser()->thumbUrl($this->eParserVars['media_cat_image'],$att)."' alt='' />";
		$text .= "</a>";
		return $text;		
	}
	
	
}
