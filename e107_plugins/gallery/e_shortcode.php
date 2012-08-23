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
	public $slideCount = 1;
	private $downloadable = FALSE;
	
	function init()
	{
		$this->downloadable = e107::getPlugPref('gallery','downloadable');	
	}
			
	function sc_gallery_caption($parm='')
	{
		$tp = e107::getParser();
		$text = "<a class='gallery-caption' title='".$tp->toAttribute($this->var['media_caption'])."' href='".e107::getParser()->replaceConstants($this->var['media_url'],'abs')."' rel='lightbox.Gallery2' >";
		$text .= $this->var['media_caption'];
		$text .= "</a>";
		return $text;
	}
	
	function sc_gallery_description($parm='')
	{
		$tp = e107::getParser();
		return $tp->toHTML($this->var['media_description'], true, 'BODY');
	}
	
	/**
	 * All possible parameters
	 * {GALLERY_THUMB=w=200&h=200&thumburl&thumbsrc&imageurl&orig}
	 * w and h - optional width and height of the thumbnail
	 * thumburl - return only the URL of the destination image (large one)
	 * thumbsrc - url to the thumb, as it's written in the src attribute of the image
	 * imageurl - full path to the destination image (no proxy)
	 * actualPreview - large preview will use the original path to the image (no proxy)
	 */
	function sc_gallery_thumb($parm='')
	{
		$tp 		= e107::getParser();	
		$parms 		= eHelper::scParams($parm);
		
		$w 			= vartrue($parms['w']) ? $parms['w'] : 190;
		$h 			= vartrue($parms['h']) ? $parms['h'] : 150;	
		
		$class 		= ($this->slideMode == TRUE) ? 'gallery-slideshow-thumb' : 'gallery-thumb';
		$rel 		= ($this->slideMode == TRUE) ? 'lightbox.SlideGallery' : 'lightbox.Gallery';
		$att 		= 'aw='.$w.'&ah='.$h.'&x=1'; // 'aw=190&ah=150';
		
		$pop_w 		= vartrue(e107::getPlugPref('gallery','pop_w'),1024);
		$pop_h 		= vartrue(e107::getPlugPref('gallery','pop_h'),768);		
		$attFull 	= 'w='.$pop_w.'&h='.$pop_h.'&x=1';
		$srcFull = $tp->thumbUrl($this->var['media_url'], $attFull);
		if(isset($parm['actualPreview']))
		{
			$srcFull = $tp->replaceConstants($this->var['media_url'], 'full');
		}
		
		if(isset($parms['thumburl'])) return $srcFull;
		elseif(isset($parms['thumbsrc'])) return $tp->thumbUrl($this->var['media_url'],$att);
		elseif(isset($parms['imageurl'])) return $tp->replaceConstants($this->var['media_url'], 'full');
		
		$caption = $tp->toAttribute($this->var['media_caption']) ;	
		$caption .= ($this->downloadable) ? " <a class='e-tip smalltext' title='Right-click > Save Link As' href='".$srcFull."'>Download</a>" : "";
		
		$text = "<a class='".$class."' title=\"".$caption."\" href='".$srcFull."'  rel='{$rel}' >";
		$text .= "<img class='".$class."' src='".$tp->thumbUrl($this->var['media_url'],$att)."' alt='' />";
		$text .= "</a>";
		
		return $text;	
	}
	
	function sc_gallery_cat_title($parm='')
	{
		$tp = e107::getParser();
		$url = e107::getUrl()->create('gallery/index/list', $this->var); 
		if($parm == 'title') return $tp->toHtml($this->var['media_cat_title'], false, 'TITLE');
		$text = "<a href='".$url."'>";
		$text .= $tp->toHtml($this->var['media_cat_title'], false, 'TITLE');
		$text .= "</a>";
		return $text;	
	}
	
	function sc_gallery_cat_url($parm='')
	{
		return e107::getUrl()->create('gallery/index/list', $this->var); 
	}
	
	function sc_gallery_cat_description($parm='')
	{
		$tp = e107::getParser();
		return $tp->toHTML($this->var['media_cat_diz'], true, 'BODY');
	}
	
	function sc_gallery_baseurl()
	{
		return e107::getUrl()->create('gallery'); 
	}
	
	function sc_gallery_cat_thumb($parm='')
	{
		$parms = eHelper::scParams($parm);
		
		$w 			= vartrue($parms['w']) ? $parms['w'] : 190;
		$h 			= vartrue($parms['h']) ? $parms['h'] : 150;	
		$att 		= 'aw='.$w.'&ah='.$h.'&x=1'; // 'aw=190&ah=150';
		
		$url = e107::getUrl()->create('gallery/index/list', $this->var);
		
		if(isset($parms['thumbsrc'])) return e107::getParser()->thumbUrl($this->var['media_cat_image'],$att);
		
		$text = "<a href='".$url."'>";
		$text .= "<img src='".e107::getParser()->thumbUrl($this->var['media_cat_image'],$att)."' alt='' />";
		$text .= "</a>";
		return $text;		
	}
	
	function sc_gallery_nextprev($parm='')
	{
		// we passs both fields, the router will convert one of them to 'cat' variable, based on the current URL config
		$url = 'url::gallery/index/list?media_cat_category='.$this->curCat.'--AMP--media_cat_title='.$this->var['media_cat_title'].'--AMP--frm=--FROM--::full=1';
		$parm = 'total='.$this->total.'&amount='.$this->amount.'&current='.$this->from.'&url='.rawurlencode($url); // .'&url='.$url;
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
		$amount = ($parm) ? intval($parm) : 3; // vartrue(e107::getPlugPref('gallery','slideshow_perslide'),3);
		$tp = e107::getParser();
		$limit = varset($gp['slideshow_limit'],16);
		$list = e107::getMedia()->getImages('gallery_'.$this->sliderCat,0,$limit);
		$item_template 	= e107::getTemplate('gallery','gallery','SLIDESHOW_SLIDE_ITEM');		
		$cat = $this->catList[$this->sliderCat];
		
		$count = 1;
		foreach($list as $row)
		{
			$this->setVars($row)
				->addVars($cat);	
					
			$inner .= ($count == 1) ?  "\n\n<!-- SLIDE ".$count." -->\n<div class='slide' id='gallery-item-".$this->slideCount."'>\n" : "";
			$inner .= "\n\t".$tp->parseTemplate($item_template,TRUE)."\n";
			$inner .= ($count == $amount) ? "\n</div>\n\n" : "";
						
			if($count == $amount)
			{
				$count = 1; 
				$this->slideCount++;
			}
			else
			{
				$count++;
			}		
		}

		$inner .= ($count != 1) ? "</div><!-- END SLIDES -->" : "";
		return $inner;
	}
	
	function sc_gallery_jumper($parm)
	{
		// echo "SlideCount=".$this->slideCount; 
		if($this->slideCount ==1 ){ return "gallery-jumper must be loaded after Gallery-Slides"; }
			
		$text = '';
		for($i=1; $i < ($this->slideCount); $i++)
		{
			$val = ($parm == 'space') ? "&nbsp;" : $i;					
			$text .= '<a href="#" class="gallery-slide-jumper" id="gallery-jumper-'.$i.'">'.$val.'</a>';			
		}
	
		return $text;						
								
	}
}
?>