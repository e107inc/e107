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
	private $attFull = null;
	
	function init()
	{
		$this->downloadable = e107::getPlugPref('gallery','downloadable');	
		$pop_w 				= vartrue(e107::getPlugPref('gallery','pop_w'),1024);
		$pop_h 				= vartrue(e107::getPlugPref('gallery','pop_h'),768);		
		$this->attFull 		= array('w'=>$pop_w, 'h'=>$pop_h, 'x'=>1, 'crop'=>0); // 'w='.$pop_w.'&h='.$pop_h.'&x=1';
	}
			
	function sc_gallery_caption($parm='')
	{
		$tp = e107::getParser();
		$text = "<a class='gallery-caption' title='".$tp->toAttribute($this->var['media_caption'])."' href='".$tp->thumbUrl($this->var['media_url'], $this->attFull)."' data-gal='prettyPhoto[slide]' >";     // Erase  rel"lightbox.Gallery2"  - Write "prettyPhoto[slide]"
		$text .= $this->var['media_caption'];
		$text .= "</a>";
		return $text;
	}
	
	function sc_gallery_description($parm='')
	{
		$tp = e107::getParser();
		return $tp->toHTML($this->var['media_description'], true, 'BODY');
	}
	
	function sc_gallery_breadcrumb($parm='')
	{
		$breadcrumb = array();
		
		$breadcrumb[] = array('text'=> LAN_PLUGIN_GALLERY_TITLE, 'url'=> e107::getUrl()->create('gallery', $this->var));
		
		if(vartrue($this->curCat))
		{
			$breadcrumb[] = array('text'=> $this->sc_gallery_cat_title('title'), 'url'=> e107::getUrl()->create('gallery/index/list', $this->var));
		}
		
		return e107::getForm()->breadcrumb($breadcrumb);
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
		
		$w 			= vartrue($parms['w']) ? $parms['w'] : $tp->thumbWidth(); // 190; // 160;
		$h 			= vartrue($parms['h']) ? $parms['h'] : $tp->thumbHeight(); // 130;	
		
		$class 		= ($this->slideMode == TRUE) ? 'gallery-slideshow-thumb img-responsive img-rounded' : varset($parms['class'],'gallery-thumb img-responsive');
	//	$rel 		= ($this->slideMode == TRUE) ? 'lightbox.SlideGallery' : 'lightbox.Gallery';
		$rel 		= ($this->slideMode == TRUE) ? 'prettyPhoto[slide]' : 'prettyPhoto[gal]';

		//$att        = array('aw'=>$w, 'ah'=>$h, 'x'=>1, 'crop'=>1);
		$caption    = $tp->toAttribute($this->var['media_caption']) ;
		$att        = array('w'=>$w, 'h'=>$h, 'class'=>$class, 'alt'=>$caption, 'x'=>1, 'crop'=>1);

		
		$srcFull = $tp->thumbUrl($this->var['media_url'], $this->attFull);

		if(vartrue($parms['actualPreview'])) 
		{
			$srcFull = $tp->replaceConstants($this->var['media_url'], 'full');
		}
		
		if(isset($parms['thumburl'])) return $srcFull;
		elseif(isset($parms['thumbsrc'])) return $tp->thumbUrl($this->var['media_url'],$att);
		elseif(isset($parms['imageurl'])) return $tp->replaceConstants($this->var['media_url'], 'full');
		

		$description = ($this->downloadable) ? " <a class='btn btn-xs btn-default btn-mini e-tip' title='Right-click > Save Link As' href='".$srcFull."'>Download</a>" : "";
		$description .= $tp->toAttribute($this->var['media_description']);
		
		$text = "<a class='".$class."' title=\"".$description."\" href='".$srcFull."'  data-gal='{$rel}'  >";
		$text .= $tp->toImage($this->var['media_url'],$att);
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
		
		$w 			= vartrue($parms['w']) ? $parms['w'] : 300; // 260;
		$h 			= vartrue($parms['h']) ? $parms['h'] : 200; // 180;	
		$att 		= 'aw='.$w.'&ah='.$h.'&x=1'; // 'aw=190&ah=150';
		
		$url = e107::getUrl()->create('gallery/index/list', $this->var);
		
		if(isset($parms['thumbsrc'])) return e107::getParser()->thumbUrl($this->var['media_cat_image'],$att);		
		
		$text = "<a class='thumbnail' href='".$url."'>";
		$text .= "<img class='img-responsive' data-src='holder.js/".$w."x".$h."' src='".e107::getParser()->thumbUrl($this->var['media_cat_image'],$att)."' alt='' />";
		$text .= "</a>";
		return $text;		
	}
	
	function sc_gallery_nextprev($parm='')
	{
		// we passs both fields, the router will convert one of them to 'cat' variable, based on the current URL config
		$url = 'route::gallery/index/list?media_cat_category='.$this->curCat.'--AMP--media_cat_sef='.$this->var['media_cat_sef'].'--AMP--frm=--FROM--::full=1';
		$parm = 'total='.$this->total.'&amount='.$this->amount.'&current='.$this->from.'&url='.rawurlencode($url); // .'&url='.$url;
		$text .= e107::getParser()->parseTemplate("{NEXTPREV=".$parm."}");
		return $text;	
	}
	
	function sc_gallery_slideshow($parm='')
	{
		$this->sliderCat = ($parm) ? $parm : vartrue(e107::getPlugPref('gallery','slideshow_category'),1);

		$tmpl 	= e107::getTemplate('gallery','gallery');	
		$template = array_change_key_case($tmpl);
			
		return e107::getParser()->parseTemplate($template['slideshow_wrapper']);
	}
	
	/**
	 * Display a Grid of thumbnails - useful for home pages. 
	 * Amount per row differs according to device, so they are not set here, only the amount.  
	 * @example {GALLERY_PORTFOLIO: placeholder=1&category=2}
	 */
	function sc_gallery_portfolio($parms='')
	{
		$ns 	= e107::getRender();		
		$parm 	= eHelper::scParams($parms);
		$cat 	= ($parm['category']) ? $parm['category'] : vartrue(e107::getPlugPref('gallery','slideshow_category'), 1); //TODO Separate pref?

		$tmpl 	= e107::getTemplate('gallery','gallery');		
		$limit 	= vartrue($parm['limit'], 6);
		
		$list = e107::getMedia()->getImages('gallery_'.$cat.'|gallery_image_'.$cat, 0, $limit);
	
		if(count($list) < 1 && vartrue($parm['placeholder']))
		{
			$list = array();

			for ($i=0; $i < $limit; $i++) 
			{
				$list[] = array('media_url'=>'');	
			}	
		}
		
		//NOTE: Using tablerender() allows the theme developer to set the number of columns etc using col-xx-xx 
		foreach($list as $val)
		{
			$this->var = $val;
			$text .= $ns->tablerender('', $this->sc_gallery_thumb('class=gallery_thumb img-responsive img-home-portfolio'),'gallery_portfolio',true);	
		}
		
		return $text;

	}
	
	
	
	/**
	 * All possible parameters
	 * {GALLERY_SLIDES=4|limit=16&template=MY_SLIDESHOW_SLIDE_ITEM}
	 * first parameter is always number of slides, default is 3
	 * limit - (optional) total limit of pcitures to be shown
	 * template - (optional) template - name of template to be used for parsing the slideshow item
	 */
	function sc_gallery_slides($parm)
	{
		$tp 				= e107::getParser();
		$this->slideMode 	= TRUE;
		$parms 				= eHelper::scDualParams($parm);
		$amount 			= $parms[1] ? intval($parms[1]) : 3; // vartrue(e107::getPlugPref('gallery','slideshow_perslide'),3);
		$parms 				= $parms[2];
		$limit 				= (integer) vartrue($parms['limit'], 16);
		$list 				= e107::getMedia()->getImages('gallery_'.$this->sliderCat.'|gallery_image_'.$this->sliderCat,0,$limit);
		$tmpl 				= e107::getTemplate('gallery','gallery');
		$tmpl				= array_change_key_case($tmpl); // change template key to lowercase (BC fix)
		$tmpl_key			= vartrue($parms['template'],'slideshow_slide_item');
		$item_template 		= $tmpl[$tmpl_key]; // e107::getTemplate('gallery','gallery', vartrue($parms['template'], 'SLIDESHOW_SLIDE_ITEM'));
		$catList 			= e107::getMedia()->getCategories('gallery');		
		$cat 				= $catList['gallery_'.$this->sliderCat];

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