<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if (!defined('e107_INIT')) { exit; }

/*
// SEE e_shortcode.php instead. 
  
 
class gallery_shortcodes extends e_shortcode
{
	
	public $total = 0;
	public $amount = 3;
	public $from = 0;
	public $curCat = null;
			
	function sc_gallery_caption($parm='')
	{
		$tp = e107::getParser();
		$text = "<a title='".$tp->toAttribute($this->var['media_caption'])."' href='".e107::getParser()->replaceConstants($this->var['media_url'],'abs')."' rel='lightbox[Gallery2]' >";
		$text .= $this->var['media_caption'];
		$text .= "</a>";
		return $text;
	}
	
	function sc_gallery_thumb($parm='')
	{
		$tp = e107::getParser();
		$att = ($parm) ?$parm : 'aw=190&ah=150';
		$text = "<a title='".$tp->toAttribute($this->var['media_caption'])."' href='".e107::getParser()->replaceConstants($this->var['media_url'],'abs')."'  rel='lightbox[Gallery]' >";
		$text .= "<img src='".e107::getParser()->thumbUrl($this->var['media_url'],$att)."' alt='' />";
		$text .= "</a>";
		return $text;	
	}
	
	function sc_gallery_cat_title($parm='')
	{
		$tp = e107::getParser();
		$text = "<a href='".e_SELF."?cat=".$this->var['media_cat_category']."'>";
		$text .= $tp->toHTML($this->var['media_cat_title']);
		$text .= "</a>";
		return $text;	
	}
	
	function sc_gallery_cat_thumb($parm='')
	{
		$att = ($parm) ?$parm : 'aw=190&ah=150';
		$text = "<a href='".e_SELF."?cat=".$this->var['media_cat_category']."'>";
		$text .= "<img src='".e107::getParser()->thumbUrl($this->var['media_cat_image'],$att)."' alt='' />";
		$text .= "</a>";
		return $text;		
	}
	
	function sc_gallery_nextprev($parm='')
	{
		$url = e_SELF."?cat=".$this->curCat."--AMP--frm=--FROM--";
		$parm = 'total='.$this->total.'&amount='.$this->amount.'&current='.$this->from.'&url='.$url; // .'&url='.$url;
		$text .= e107::getParser()->parseTemplate("{NEXTPREV=".$parm."}");
		return $text;	
	}
	
	
}
*/
