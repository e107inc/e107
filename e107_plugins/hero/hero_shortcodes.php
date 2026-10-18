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

		$bullet = $this->var['hero_bullets'][$this->count];

		if(!empty($parm['raw']))
		{
			return e107::getParser()->attributeValue($bullet['icon']);
		}

		return e107::getParser()->toIcon($this->iconOf($bullet));
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

		return e107::getParser()->attributeValue($this->var['hero_bullets'][$this->count]['icon_style']);
   
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
		$tp = e107::getParser();
		$bullet = varset($this->var['hero_bullets'][$this->count], array());

		if($this->trustOf($bullet, 'text') >= 1)
		{
			return $tp->toHTML(varset($bullet['text']), true, 'BODY');
		}

		return $tp->toHTML($tp->attributeValue(varset($bullet['text'])), true, 'BODY,scparse_off');
	}


	public function sc_hero_animation()
	{
		if(empty($this->var['hero_bullets'][$this->count]['animation']))
		{
			return null;
		}

		return e107::getParser()->attributeValue($this->var['hero_bullets'][$this->count]['animation']);
	}

	public function sc_hero_animation_delay()
	{
		if(empty($this->var['hero_bullets'][$this->count]['animation_delay']))
		{
			return null;
		}

		return "animation-delay-".e107::getParser()->attributeValue($this->var['hero_bullets'][$this->count]['animation_delay']);
	}

	/**
	* @example {hero_TITLE}
	* @example {hero_TITLE: enwrap=strong} // replace [ ] chars with <strong> tags.
	*/
	public function sc_hero_title($parm=null)
	{
		return $this->enwrap(e107::getParser()->toHTML(varset($this->var['hero_title']), false, 'TITLE,scparse_off,defs_off'), $parm);
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
		return $this->enwrap(e107::getParser()->toHTML(varset($this->var['hero_description']), false, 'TITLE,scparse_off,defs_off'), $parm);
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

		return e107::getParser()->toIcon($this->iconOf($this->var['hero_button1']), $parm);
	}

	public function sc_hero_button1_label($parm=null)
	{
		if(empty($this->var['hero_button1']['label']))
		{
			return null;
		}

		return $this->buttonLabel('hero_button1');
	}
	
	public function sc_hero_button1_url($parm=null)
	{
		return $this->buttonUrl('hero_button1');
	}

	public function sc_hero_button1_class($parm=null)
	{
		return e107::getParser()->attributeValue(varset($this->var['hero_button1']['class']));
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

		return e107::getParser()->toIcon($this->iconOf($this->var['hero_button2']), $parm);
	}

	public function sc_hero_button2_label($parm=null)
	{
		return $this->buttonLabel('hero_button2');
	}
	
	public function sc_hero_button2_url($parm=null)
	{
		return $this->buttonUrl('hero_button2');
	}

	public function sc_hero_button2_class($parm=null)
	{
		return e107::getParser()->attributeValue(varset($this->var['hero_button2']['class']));
	}

	/**
	 * @param string $button 'hero_button1' or 'hero_button2'
	 * @return string the button's label: as an HTML title for an author who may post HTML, otherwise as text with any shortcode in it parsed
	 */
	private function buttonLabel($button)
	{
		$tp = e107::getParser();
		$part = varset($this->var[$button], array());

		if($this->trustOf($part, 'label') >= 1)
		{
			return $tp->toHTML(varset($part['label']), false, 'TITLE,defs_off');
		}

		return $tp->attributeValue($tp->parseTemplate(varset($part['label'])));
	}

	/**
	 * @param string $button 'hero_button1' or 'hero_button2'
	 * @return string the button's address, with any shortcode in it parsed, encoded for an href; for an author who may post
	 *                HTML any scheme but javascript:, data: and vbscript:, for anyone else only the schemes
	 *                {@see e_parse::toUrlAttribute()} keeps; empty otherwise
	 */
	private function buttonUrl($button)
	{
		$tp = e107::getParser();
		$part = varset($this->var[$button], array());
		$url = $tp->parseTemplate(varset($part['url']));

		if($this->trustOf($part, 'url') < 1)
		{
			return $tp->toUrlAttribute($url);
		}

		$url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$scheme = strtolower(preg_replace('/[\x00-\x20\x7F]/', '', $url));

		if(preg_match('/&#|&[a-z][a-z0-9]*;/i', $url) || preg_match('#^(javascript|data|vbscript):#', $scheme))
		{
			return '';
		}

		return $tp->attributeValue(str_replace(array('"', "'", '<', '>', '`'), array('%22', '%27', '%3C', '%3E', '%60'), $url));
	}

	/**
	 * @param array $part a bullet or a button as stored
	 * @return string the icon to draw: as written for an author with script access, a quote stored as an entity inside
	 *                an attribute value kept as one, otherwise encoded so only a name or path draws
	 */
	private function iconOf($part)
	{
		$icon = varset($part['icon']);

		if($this->trustOf($part, 'icon') === 2)
		{
			return strtr($icon, array('&quot;' => '"', '&#039;' => "'", '&amp;quot;' => '&quot;', '&amp;#039;' => '&#039;'));
		}

		return e107::getParser()->attributeValue($icon);
	}

	/**
	 * @param array $part a bullet or a button as stored
	 * @param string $key one of its values
	 * @return int|null what the author of that value could post when it was saved, or null for one saved before that was recorded
	 */
	private function trustOf($part, $key)
	{
		$trust = (isset($part['__trust__']) && is_array($part['__trust__']) && isset($part['__trust__'][$key])) ? $part['__trust__'][$key] : null;

		return in_array($trust, array(0, 1, 2), true) ? $trust : null;
	}


	

}