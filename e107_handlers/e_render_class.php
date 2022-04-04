<?php


	/**
	 * Used for rendering content via the theme tablestyle() method.
	 * Previously known as 'e107table'
	 * @package e107
	 */
	class e_render
	{

		public  $eMenuCount   = 0;
		public  $eMenuArea;
		public  $eMenuTotal   = array();
		public  $eSetStyle;
		private $themeClass   = 'theme';  // v2.3.0+
		private $legacyThemeClass;
		private $adminThemeClass;
		public  $adminarea    = false;
		private $uniqueId     = null;
		private $content      = array();
		private $contentTypes = array('header', 'footer', 'text', 'title', 'image', 'list');
		private $mainRenders  = array(); // all renderered with style = 'default' or 'main'.
		private $styleCount   = array();
		private $thm;


		/**
		 * @param $adminarea
		 * @return void
		 */
		public function _init($adminarea=false)
		{
			$this->adminarea = (bool) $adminarea;

			$this->legacyThemeClass = e107::getPref('sitetheme') . '_theme'; // disabled at the moment.
			$this->adminThemeClass = e107::getPref('admintheme') . '_admintheme';    // Check for a class.

			$this->load();
		}

		// Called in header_default.php.

		/**
		 * @return void|null
		 */
		public function init()
		{

			if(empty($this->thm) || !method_exists($this->thm, 'init'))
			{
				if(deftrue('FONTAWESOME'))
				{
					e107::getParser()->setFontAwesome(FONTAWESOME);
				}
				if(deftrue('BOOTSTRAP'))
				{
					e107::getParser()->setBootstrap(BOOTSTRAP);
				}
				return null;
			}

			ob_start(); // don't allow init() to echo.
			$this->thm->init();
			ob_end_clean();
		}

		/**
		 * Load theme class if necessary.
		 *
		 * @return null
		 */
		private function load()
		{

			if(!empty($this->thm))
			{
				return null;
			}

			if(($this->adminarea === true))
			{
				/** @var e_theme_render $thm */

				if(class_exists($this->adminThemeClass))
				{
					$this->thm = new $this->adminThemeClass();
				}
				else
				{
					echo "<h3>COULDN'T FIND ".$this->adminThemeClass." CLASS</h3>";
				}

			}
			elseif(class_exists($this->themeClass)) // v2.3.0+
			{

				if(ADMIN && $this->hasLegacyCode()) // debug - no translation needed.
				{
					echo "<div class='alert alert-danger'>Please place all theme code inside the <b>theme</b> class. </div>";
				}

				/** @var e_theme_render $thm */
				$this->thm = new $this->themeClass();

				if(ADMIN && !$this->thm instanceof e_theme_render)
				{
					// debug - no need to translate.
					echo "<div class='alert alert-danger'>class <b>" . $this->themeClass . "</b> is missing 'implements e_theme_render'. Make sure there is an init() method also!</div>";
				}
			}
			elseif(class_exists($this->legacyThemeClass)) // legacy v2.x
			{
				/** @var e_theme_render $thm */
				$this->thm = new $this->legacyThemeClass();
			}

			return null;
		}


		/**
		 * Return content options for the main render that uses {SETSTYLE=default} or {SETSTYLE=main}
		 *
		 * @return array
		 */
		private function getMainRender()
		{

			if(isset($this->mainRenders[0]))
			{
				return $this->mainRenders[0];
			}

			return array();

		}


		/**
		 * Return the first caption rendered with {SETSTYLE=default} or {SETSTYLE=main}
		 *
		 * @return string|null
		 */
		public function getMainCaption()
		{

			if(isset($this->mainRenders[0]['caption']))
			{
				return $this->mainRenders[0]['caption'];
			}

			return null;
		}


		/**
		 * @return array
		 */
		function getMagicShortcodes()
		{

			$ret = array();

			$val = $this->getMainRender();

			$types = array('caption') + $this->contentTypes;

			foreach($types as $var)
			{
				$sc = '{---' . strtoupper($var) . '---}';
				$ret[$sc] = isset($val[$var]) ? (string) $val[$var] : null;
			}

			if($tmp = e107::callMethod('theme_shortcodes', 'sc_caption', varset($val['caption'])))
			{
				$ret['{---CAPTION---}'] = $tmp;
			}

			$bread = e107::breadcrumb();

			if($tmp = e107::callMethod('theme_shortcodes', 'sc_breadcrumb', $bread))
			{
				$ret['{---BREADCRUMB---}'] = $tmp;
			}
			else
			{
				$ret['{---BREADCRUMB---}'] = e107::getForm()->breadcrumb($bread, true);
			}

			return $ret;

		}

		/**
		 * Set the style mode for use in tablestyle() method/function
		 *
		 * @param string $style
		 */
		public function setStyle($style)
		{

			$this->eSetStyle = (string) $style;
		}

		/**
		 * Set a unique id for use in tablestyle() method/function
		 *
		 * @param string $id
		 * @return e_render
		 */
		public function setUniqueId($id)
		{

			$this->uniqueId = !empty($id) ? eHelper::dasherize($id) : null;

			return $this;
		}


		/**
		 * Set Advanced Page/Menu content (beyond just $caption and $text)
		 *
		 * @param string|array $type header|footer|text|title|image|list
		 * @param string       $val
		 * @return bool|e_render
		 */
		public function setContent($type, $val)
		{

			if(is_array($type))
			{
				foreach($this->contentTypes as $t)
				{
					$this->content[$t] = (string) $type[$t];
				}
			}


			if(!in_array($type, $this->contentTypes, true))
			{
				return false;
			}

			if($this->uniqueId !== null)
			{
				$key = $this->uniqueId;
			}
			else
			{
				$key = '_generic_';
				e107::getDebug()->log("Possible issue: Missing a Unique Tablerender ID. Use \$ns->setUniqueId() in the plugin script prior to setContent(). See 'source code' for more information."); // debug only, no LAN.
			}

			$this->content[$key][$type] = (string) $val;

			return $this;
		}


		/**
		 * Return the value of custom content
		 *
		 * @param string $type header|footer|text|title|image|list
		 * @return array
		 */
		public function getContent($type = '')
		{

			$key = ($this->uniqueId !== null) ? $this->uniqueId : '_generic_';

			if(empty($type))
			{
				return $this->content[$key];
			}


			return $this->content[$key][$type];

		}


		/**
		 * Return the current value of {SETSTYLE}
		 *
		 * @return mixed
		 */
		public function getStyle()
		{

			return $this->eSetStyle;
		}


		/**
		 * Return the currenty set uniqueId.
		 *
		 * @return mixed
		 */
		public function getUniqueId()
		{

			return $this->uniqueId;
		}


		/**
		 * @param string  $caption caption text
		 * @param string  $text
		 * @param string  $mode    unique identifier
		 * @param boolean $return  : return the html instead of echo it.
		 * @return null
		 */
		public function tablerender($caption, $text, $mode = 'default', $return = false)
		{

			$override_tablerender = e107::getSingleton('override', e_HANDLER . 'override_class.php')->override_check('tablerender');

			if($override_tablerender)
			{
				$result = $override_tablerender($caption, $text, $mode, $return);

				if($result === 'return')
				{
					return '';
				}
				extract($result);
			}


			if($return)
			{
				if(!empty($text) && $this->eMenuArea)
				{
					$this->eMenuCount++;
				}

				ob_start();
				$this->tablestyle($caption, $text, $mode);

				return ob_get_clean();

			}

			if(!empty($text) && $this->eMenuArea)
			{
				$this->eMenuCount++;
			}

			$this->tablestyle($caption, $text, $mode);

			return '';
		}


		/**
		 * @return bool
		 */
		private function hasLegacyCode()
		{

			$legacy = ['VIEWPORT', 'THEME_DISCLAIMER', 'IMODE', 'HTMLTAG', 'BODYTAG', 'COMMENTLINK', 'OTHERNEWS_LIMIT',
				'PRE_EXTENDEDSTRING', 'COMMENTOFFSTRING', 'CORE_CSS', 'TRACKBACKSTRING', 'TRACKBACKBEFORESTRING'];

			foreach($legacy as $const)
			{
				if(defined($const))
				{
					return true;
				}
			}

			return false;

		}


		/**
		 * Output the styled template.
		 *
		 * @param $caption
		 * @param $text
		 * @param $mode
		 */
		private function tablestyle($caption, $text, $mode)
		{
			$text = (string) $text;

			// Automatic list detection .
			$isList = (strncmp(ltrim($text), '<ul', 3) === 0);
			$this->setContent('list', $isList);

			$options = $this->getContent();

			$options['uniqueId'] = (string) $this->uniqueId;
			$options['menuArea'] = (int) $this->eMenuArea;
			$options['menuCount'] = $this->eMenuCount;
			$options['menuTotal'] = (int) varset($this->eMenuTotal[$this->eMenuArea]);
			$options['setStyle'] = (string) $this->eSetStyle;

			$options['caption'] = e107::getParser()->toText($caption);

			if($this->eSetStyle === 'default' || $this->eSetStyle === 'main')
			{
				$this->mainRenders[] = $options;
			}

			if(!empty($this->eSetStyle))
			{
				if(!isset($this->styleCount[$this->eSetStyle]))
				{
					$this->styleCount[$this->eSetStyle] = 0;
				}

				$this->styleCount[$this->eSetStyle]++;
			}

			$options['styleCount'] = varset($this->styleCount[$this->eSetStyle]);


			//XXX Optional feature may be added if needed - define magic shortcodes inside $thm class. eg. function msc_custom();

			if(!empty($this->thm) && is_object($this->thm))
			{
				$this->thm->tablestyle($caption, $text, $mode, $options);
			}
			elseif(function_exists('tablestyle'))
			{
				tablestyle($caption, $text, $mode, $options);
			}

			$key = ($this->uniqueId !== null) ? $this->uniqueId : '_generic_';
			$this->content[$key] = array();
			$this->uniqueId = null;

		}


	}