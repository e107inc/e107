<?php
	

// Hero Shortcodes file

if (!defined('e107_INIT')) { exit; }

class plugin_hero_hero_shortcodes extends e_shortcode
{
	public $count = 0;

	/**
	* {hero_ID}
	*/
	public function sc_hero_id($parm=null)
	{
		return $this->var['hero_id'];
	}

	public function sc_hero_media($parm=null)
	{
		if(empty($this->var['hero_media']))
		{
			return null;
		}

		if(empty($parm['w']) )
		{
			$parm['w'] = e107::getParser()->thumbWidth();
		}

		if(empty($parm['h']) )
		{
			$parm['h'] = e107::getParser()->thumbHeight();
		}



		return e107::getMedia()->previewTag($this->var['hero_media'], $parm);
	//	return e107::getParser()->replaceConstants($this->var['hero_media'], 'full');
	}

	public function sc_hero_bgimage($parm=null)
	{
		if(empty($this->var['hero_bg']))
		{
			return 'none';
		}

		if($url = e107::getParser()->replaceConstants($this->var['hero_bg'], 'full'))
		{
			return 'url('.$url.')';
		}

		return 'none';
	}

	public function sc_hero_carousel_indicators($parm=null)
	{
		$target = !empty($parm['target']) ? $parm['target'] : 'carousel-hero';
		$class = !empty($parm['class']) ? $parm['class'] : '';
		$total = (int) vartrue($this->var['hero_total_slides'], 0);

		if(empty($total))
		{
			return "(No Slides Found)"; // debug info
		}

		$loop = range(0,$total-1);

		$text = '';
		$bs5 = '';
		foreach($loop as $c)
		{
			$active = ($c == 0) ? 'active' : '';
			$current = ($c == 0) ? " aria-current='true'" : '';

			$text .= '<li data-target="#'.$target.'" data-slide-to="'.$c.'" data-bs-slide-to="'.$c.'" class="'.$active.'" '.$current.'></li>';
			$bs5 .= ' <button type="button" data-bs-target="#'.$target.'" data-bs-slide-to="'.$c.'" aria-label="Slide '.$c.'" class="'.$active.'" '.$current.'></button>';

			$text .= "\n";
		}

		if(defset('BOOTSTRAP') === 5)
		{
			$start = '<div class="carousel-indicators '.$class.'">';
			$text = $bs5;
			$end = '</div>';
		}
		else
		{
			$start = '<ol class="carousel-indicators '.$class.'">';
			$end = '</ol>';
		}

		 return $start.$text.$end;


	}

	public function sc_hero_slide_active($parm=null)
	{
		return varset($this->var['hero_slide_active']);
	}

	public function sc_hero_slide_interval($parm=null)
	{
		return e107::pref('hero', 'slide_interval', 7500);
	}

  /* {hero_ICON} returs <i class="fa fa-stumbleupon-circle"><!-- --></i> */
  /* {hero_ICON: raw=1}	returns database value, not able to use in template */
  
	public function sc_hero_icon($parm=null)
	{
		if(empty($this->var['hero_bullets'][$this->count]['icon']))
		{
			return null;
		}

		if(!empty($parm['raw']))
		{
			return $this->var['hero_bullets'][$this->count]['icon'];
		}

		return e107::getParser()->toIcon($this->var['hero_bullets'][$this->count]['icon']);
	}

	/**
	 * Returns success, info, primary, warning and danger strings.
	 * @param null $parm
	 * @return string|null
	 */
	public function sc_hero_icon_style($parm=null)
	{
		if(empty($this->var['hero_bullets'][$this->count]['icon_style']))
		{
			return null;
		}

		return $this->var['hero_bullets'][$this->count]['icon_style'];
   
   } 
  
	public function sc_hero_count()
	{
		return $this->count;
	}

/*	public function sc_hero_url()
	{
		return $this->var['hero_bullets'][$this->count]['url'];
	}*/

	public function sc_hero_text()
	{
		$count = $this->count;
		return e107::getParser()->toHTML($this->var['hero_bullets'][$count]['text'],true,'BODY');
	}


	public function sc_hero_animation()
	{
		if(empty($this->var['hero_bullets'][$this->count]['animation']))
		{
			return null;
		}

		return $this->var['hero_bullets'][$this->count]['animation'];
	}

	public function sc_hero_animation_delay()
	{
		if(empty($this->var['hero_bullets'][$this->count]['animation_delay']))
		{
			return null;
		}

		return "animation-delay-".$this->var['hero_bullets'][$this->count]['animation_delay'];
	}

	/**
	* @example {hero_TITLE}
	* @example {hero_TITLE: enwrap=strong} // replace [ ] chars with <strong> tags.
	*/
	public function sc_hero_title($parm=null)
	{
		return $this->enwrap($this->var['hero_title'],$parm);
	}
	
	private function enwrap($text, $parm=null)
	{
		if(empty($text))
		{
			return null;
		}

		$repl = array();

		$class = !empty($parm['class']) ? " class='".$parm['class']."'" : "";

		if(!empty($parm['enwrap']))
		{
			$tag = $parm['enwrap'];
			$repl = array("<".$tag.$class.">","</".$tag.">");
		}

		if(!empty($repl))
		{
			$srch = array("[","]");
			return str_replace($srch,$repl,$text);

		}

		return $text;

	}

	/**
	* @example {hero_DESCRIPTION}
	* @example {hero_DESCRIPTION: enwrap=span&class=text-info} // replace [ ] chars with <span> tags and apply text-info class.
	*/
	public function sc_hero_description($parm=null)
	{
		return $this->enwrap($this->var['hero_description'],$parm);
	}
	


	/**
	* {HERO_BULLETS}
	*//*
	public function sc_hero_bullets($parm=null)
	{
		return $this->var['hero_bullets'];
	}
	*/

	/**
	* {hero_BUTTON1_xxxx}
	*/
	public function sc_hero_button1_icon($parm=null)
	{
		if(empty($this->var['hero_button1']['icon']))
		{
			return null;
		}

		return e107::getParser()->toIcon($this->var['hero_button1']['icon'],$parm);
	}

	public function sc_hero_button1_label($parm=null)
	{
		if(empty($this->var['hero_button1']['label']))
		{
			return null;
		}

		return e107::getParser()->parseTemplate($this->var['hero_button1']['label']);
	}
	
	public function sc_hero_button1_url($parm=null)
	{
		return e107::getParser()->parseTemplate($this->var['hero_button1']['url']);
	}

	public function sc_hero_button1_class($parm=null)
	{
		return $this->var['hero_button1']['class'];
	}


	/**
	* {hero_BUTTON2_xxxx}
	*/

	public function sc_hero_button2_icon($parm=null)
	{
		if(empty($this->var['hero_button2']['icon']))
		{
			return null;
		}

		return e107::getParser()->toIcon($this->var['hero_button2']['icon'],$parm);
	}

	public function sc_hero_button2_label($parm=null)
	{
		return e107::getParser()->parseTemplate($this->var['hero_button2']['label']);
	}
	
	public function sc_hero_button2_url($parm=null)
	{
		return e107::getParser()->parseTemplate($this->var['hero_button2']['url']);
	}

	public function sc_hero_button2_class($parm=null)
	{
		return $this->var['hero_button2']['class'];
	}


	

}