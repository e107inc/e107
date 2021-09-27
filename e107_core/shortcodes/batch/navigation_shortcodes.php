<?php

require_once(__DIR__.'/navigation_shortcodes_legacy.php');

	/**
	 * Navigation Shortcodes
	 *
	 */
	class navigation_shortcodes extends e_shortcode
	{
		use navigation_shortcodes_legacy;
		
		public $template;
		public $counter;
		public $active;
		public $depth = 0;
		public $navClass;
		private $activeSubFound = false;

		/**
		 * As set by {NAVIGATION: class=xxxx}
		 * @example {NAV_CLASS}
		 * @param null $parm
		 * @return mixed
		 */
		function sc_nav_class($parm = null)
		{
			return $this->navClass;
		}


		/**
		 *
		 * @return string 'active' on the active link.
		 * @example {NAV_LINK_ACTIVE}
		 */
		function sc_nav_link_active($parm = null)
		{

			if($this->active == true)
			{
				return 'active';
			}

			// check if it's the first link.. (eg. anchor mode) and nothing activated. 
			return ($this->counter == 1) ? 'active' : '';

		}

		/**
		 * Return the primary_id number for the current link
		 *
		 * @return integer
		 */
		function sc_nav_link_id($parm = null)
		{

			return (int) $this->var['link_id'];
		}

		function sc_nav_link_depth($parm = null)
		{

			unset($parm);

			return isset($this->var['link_depth']) ? (int) $this->var['link_depth'] : $this->depth;
		}


		function setDepth($val)
		{

			$this->depth = (int) $val;
		}


		/**
		 * Return the name of the current link
		 *
		 * @return string
		 * @example {NAV_LINK_NAME}
		 */
		function sc_nav_link_name($parm = null)
		{

			if(empty($this->var['link_name']))
			{
				return null;
			}

			if(strpos($this->var['link_name'], 'submenu.') === 0) // BC Fix.
			{
				list($tmp, $tmp2, $link) = explode('.', $this->var['link_name'], 3);
				unset($tmp, $tmp2);
			}
			else
			{
				$link = $this->var['link_name'];
			}

			return e107::getParser()->toHTML($link, false, 'defs');
		}


		/**
		 * Return the parent of the current link
		 *
		 * @return integer
		 */
		function sc_nav_link_parent($parm = null)
		{
			return (int) $this->var['link_parent'];
		}


		function sc_nav_link_identifier($parm = null)
		{

			return isset($this->var['link_identifier']) ? $this->var['link_identifier'] : '';
		}

		/**
		 * Return the URL of the current link
		 *
		 * @return string
		 */
		function sc_nav_link_url($parm = null)
		{

			$tp = e107::getParser();

			if(!empty($this->var['link_owner']) && !empty($this->var['link_sefurl']))
			{
				return e107::url($this->var['link_owner'], $this->var['link_sefurl']);
			}

			if(strpos($this->var['link_url'], e_HTTP) === 0)
			{
				$url = "{e_BASE}" . substr($this->var['link_url'], strlen(e_HTTP));
			}
			elseif($this->var['link_url'][0] !== "{" && strpos($this->var['link_url'], "://") === false)
			{
				$url = "{e_BASE}" . $this->var['link_url']; // Add e_BASE to links like: 'news.php' or 'contact.php' 	
			}
			else
			{
				$url = $this->var['link_url'];
			}

			$url = $tp->replaceConstants($url, 'full', true);

			if(strpos($url, "{") !== false)
			{
				$url = $tp->parseTemplate($url); // BC Fix shortcode in URL support - dynamic urls for multilanguage.
			}

			return $url;
		}

		/*
			function sc_nav_link_sub_oversized($parm='')
			{
				$count = count($this->var['link_sub']);
		
				if(!empty($parm) && $count > $parm)
				{
					return 'oversized';
				}
		
				return $count;
		
			}
		*/

		/**
		 * Returns only the anchor target in the URL if one is found.
		 * @example {NAV_LINK_TARGET}
		 * @param array $parm
		 * @return null|string
		 */
		function sc_nav_link_target($parm = null)
		{

			if(strpos($this->var['link_url'], '#') !== false)
			{
				list($tmp, $segment) = explode('#', $this->var['link_url'], 2);

				return '#' . $segment;

			}

			return '#';
		}

		/**
		 * Optional link attributes. onclick, rel etc.
		 * @param null $parm
		 * @example {NAV_LINK_OPEN}
		 * @return string
		 */
		function sc_nav_link_open($parm = null)
		{

			$type = $this->var['link_open'] ? (int) $this->var['link_open'] : 0;

			### 0 - same window, 1 - target blank, 4 - 600x400 popup, 5 - 800x600 popup
			### TODO - JS modal (i.e. bootstrap)

			$text = '';
			$rel = '';

			switch($type)
			{
				case 1:
					$text = ' target="_blank"';
					$rel = (strpos($this->var['link_url'], 'http') !== false) ? 'noopener noreferrer'  : '';
					break;

				case 4:
					$text = " onclick=\"open_window('" . $this->var['link_url'] . "',600,400); return false;\"";
					break;

				case 5:
					$text = " onclick=\"open_window('" . $this->var['link_url'] . "',800,600); return false;\"";
					break;
			}

			if(!empty($this->var['link_rel']))
			{
				$rel = str_replace(',', ' ', $this->var['link_rel']);
			}

			if(!empty($rel))
			{
				$text .= " rel='".$rel."'";
			}

			return $text;
		}




		/**
		 * Return the link icon of the current link
		 * @example {NAV_LINK_ICON}
		 * @return string
		 */
		function sc_nav_link_icon($parm = null)
		{

			$tp = e107::getParser();

			if(empty($this->var['link_button']))
			{
				return '';
			}

			//	if($icon = $tp->toGlyph($this->var['link_button']))
			//	{
			//		return $icon;	
			//	}
			//	else 
			{
				//$path = e107::getParser()->replaceConstants($this->var['link_button'], 'full', TRUE);	
				return $tp->toIcon($this->var['link_button'], array('fw' => true, 'space' => ' ', 'legacy' => "{e_IMAGE}icons/"));
				// return "<img class='icon' src='".$path."' alt=''  />";	
			}

		}


		/**
		 * Return the link description of the current link
		 * @example {NAV_LINK_DESCRIPTION}
		 * @return string
		 */
		function sc_nav_link_description($parm = null)
		{

			$toolTipEnabled = e107::pref('core', 'linkpage_screentip', false);

			if($toolTipEnabled == false || empty($this->var['link_description']))
			{
				return null;
			}


			return e107::getParser()->toAttribute($this->var['link_description']);
		}


		/**
		 * Return the parsed sublinks of the current link
		 * @example {NAV_LINK_SUB}
		 * @return string
		 */
		function sc_nav_link_sub($parm = null)
		{

			if(empty($this->var['link_sub']))
			{
				return false;
			}

			if(is_string($this->var['link_sub'])) // html override option.
			{
				//	e107::getDebug()->log($this->var);

				return $this->var['link_sub'];
			}

			$this->depth++;

			// Assume it's an array.

			$startTemplate = is_array($this->var['link_sub']) && !empty($this->var['link_sub'][0]['link_sub']) && isset($this->template['submenu_lowerstart']) ? $this->template['submenu_lowerstart'] : $this->template['submenu_start'];
			$endTemplate = is_array($this->var['link_sub']) && !empty($this->var['link_sub'][0]['link_sub']) && isset($this->template['submenu_lowerstart']) ? $this->template['submenu_lowerend'] : $this->template['submenu_end'];



			$text = e107::getParser()->parseTemplate(str_replace(array('{LINK_SUB}','{NAV_SUB}'), '', $startTemplate), true, $this);

			foreach($this->var['link_sub'] as $val)
			{
				$active = (e107::getNav()->isActive($val, $this->activeSubFound, true)) ? "_active" : "";

				$this->setVars($val);    // isActive is allowed to alter data
				$tmpl = !empty($val['link_sub']) ? varset($this->template['submenu_loweritem' . $active]) : varset($this->template['submenu_item' . $active]);
				$text .= e107::getParser()->parseTemplate($tmpl, true, $this);
				if($active)
				{
					$this->activeSubFound = true;
				}
			}

			$text .= e107::getParser()->parseTemplate(str_replace(array('{LINK_SUB}','{NAV_SUB}'), '', $endTemplate), true, $this);

			return $text;
		}

		/**
		 * Return a generated anchor for the current link.
		 *
		 * @param unused
		 * @return string - a generated anchor for the current link.
		 * @example {NAV_LINK_ANCHOR}
		 */
		function sc_nav_link_anchor($parm = null)
		{

			return $this->var['link_name'] ? '#' . e107::getForm()->name2id($this->var['link_name']) : '';
		}
	}