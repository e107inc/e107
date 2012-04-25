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
	
	public $total = 0;
	public $amount = 3;
	public $from = 0;
	public $curCat = null;
	public $sliderCat = 1;
	public $slideMode = FALSE;
			
	function sc_gallery_caption($parm='')
	{
		$tp = e107::getParser();
		$text = "<a class='gallery-caption' title='".$tp->toAttribute($this->var['media_caption'])."' href='".e107::getParser()->replaceConstants($this->var['media_url'],'abs')."' rel='lightbox[Gallery2]' >";
		$text .= $this->var['media_caption'];
		$text .= "</a>";
		return $text;
	}
	
	function sc_gallery_thumb($parm='')
	{
		$tp = e107::getParser();
		$class = ($this->slideMode == TRUE) ? 'gallery-slideshow-thumb' : 'gallery-thumb';
		$att = ($parm) ?$parm : 'aw=190&ah=150';
		$text = "<a class='".$class."' title='".$tp->toAttribute($this->var['media_caption'])."' href='".e107::getParser()->replaceConstants($this->var['media_url'],'abs')."'  rel='lightbox[Gallery]' >";
		$text .= "<img class='".$class."' src='".e107::getParser()->thumbUrl($this->var['media_url'],$att)."' alt='' />";
		$text .= "</a>";
		return $text;	
	}
	
	function sc_gallery_cat_title($parm='')
	{
		$tp = e107::getParser();
		$text = "<a href='".e_SELF."?cat=".$this->var['media_cat_category']."'>";
		$text .= $tp->toHtml($this->var['media_cat_title']);
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
	
	function sc_gallery_slideshow($parm='')
	{
		$this->sliderCat = ($parm) ? intval($parm) : vartrue(e107::getPlugPref('gallery','slideshow_category'),1);

		$template 	= e107::getTemplate('gallery','gallery','SLIDESHOW_WRAPPER');		
		return e107::getParser()->parseTemplate($template);
	}
	
	function sc_gallery_slides($parm)
	{
		$this->slideMode = TRUE;
		$amount = ($parm) ? intval($parm) : vartrue(e107::getPlugPref('gallery','slideshow_perslide'),3);
		$tp = e107::getParser();
		$list = e107::getMedia()->getImages('gallery_'.$this->sliderCat);
		$item_template 	= e107::getTemplate('gallery','gallery','SLIDESHOW_SLIDE_ITEM');		
		
		$count = 1;
		foreach($list as $row)
		{
			$this->setParserVars($row);	
					
			$inner .= ($count == 1) ?  "\n\n<!-- SLIDE ".$count." -->\n<div class='slide'>\n" : "";
			$inner .= "\n\t".$tp->parseTemplate($item_template,TRUE)."\n";
			$inner .= ($count == $amount) ? "\n</div>\n\n" : "";
						
			if($count == $amount)
			{
				$count = 1; 
			}
			else
			{
				$count++;
			}
			
		}
		
		$inner .= ($count != 1) ? "</div><!-- END SLIDES -->" : "";
		return $inner;
	}
	
	
}
?>