<?php
/*
* e107 website system
*
* Copyright (C) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Text processing and parsing functions
*
*/

if (!defined('e107_INIT')) { exit(); }

// Directory for the hard-coded utf-8 handling routines
define('E_UTF8_PACK', e_HANDLER.'utf8/');

define("E_NL", chr(2));

class e_parse extends e_parser
{
	/**
	 * Determine how to handle utf-8.
	 *    0 = 'do nothing'
	 *    1 = 'use mb_string'
	 *    2 = emulation
	 *
	 * @var integer
	 */
	protected $utfAction;

	// Shortcode processor - see __get()
	//var $e_sc;

	// BBCode processor
	var $e_bb;

	// Profanity filter
	var $e_pf;

	// Emote filter
	var $e_emote;

	// 'Hooked' parsers (array)
	var $e_hook;

	var $search = array('&amp;#039;', '&#039;', '&#39;', '&quot;', 'onerror', '&gt;', '&amp;quot;', ' & ');

	var $replace = array("'", "'", "'", '"', 'one<i></i>rror', '>', '"', ' &amp; ');

	// Set to TRUE or FALSE once it has been calculated
	var $e_highlighting;

	// Highlight query
	var $e_query;
	
	public $thumbWidth = 100;
	
	public $thumbHeight = 0;
	
	public $thumbCrop = 0;

	private $thumbEncode = 0;

	private $staticCount = 0;

	// BBcode that contain preformatted code.
	private $preformatted = array('html', 'markdown');


	// Set up the defaults
	var $e_optDefault = array(
		// default context: reflects legacy settings (many items enabled)
		'context' 		=> 'OLDDEFAULT',
		//
		'fromadmin' 	=> FALSE,

		// Enable emote display
		'emotes'		=> TRUE,

		// Convert defines(constants) within text.
		'defs' 			=> FALSE,

		// replace all {e_XXX} constants with their e107 value - 'rel' or 'abs'
		'constants' 	=> FALSE,

		// Enable hooked parsers
		'hook'			=> TRUE,

		// Allow scripts through (new for 0.8)
		'scripts'		=> TRUE,

		// Make links clickable
		'link_click'	=> TRUE,

		// Substitute on clickable links (only if link_click == TRUE)
		'link_replace'	=> TRUE,

		// Parse shortcodes - TRUE enables parsing

		'parse_sc' 		=> FALSE,
		// remove HTML tags.
		'no_tags' 		=> FALSE,

		// Restore entity form of quotes and such to single characters - TRUE disables
		'value'			=> FALSE,

		// Line break compression - TRUE removes newline characters
		'nobreak' 		=> FALSE,

		// Retain newlines - wraps to \n instead of <br /> if TRUE (for non-HTML email text etc)
		'retain_nl' 	=> FALSE
		);

	// Super modifiers override default option values
	var	$e_SuperMods = array(
				//text is part of a title (e.g. news title)
				'TITLE' =>
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'emotes'=>FALSE, 'defs'=>TRUE, 'parse_sc'=>TRUE
						),
				'TITLE_PLAIN' =>
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'emotes'=>FALSE, 'defs'=>TRUE, 'parse_sc'=>TRUE, 'no_tags' => TRUE
						),
				//text is user-entered (i.e. untrusted) and part of a title (e.g. forum title)
				'USER_TITLE' =>
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'scripts' => FALSE, 'emotes'=>FALSE, 'hook'=>FALSE
						),
				// text is 'body' of email or similar - being sent 'off-site' so don't rely on server availability
				'E_TITLE' =>
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'defs'=>TRUE, 'parse_sc'=>TRUE, 'emotes'=>FALSE, 'scripts' => FALSE, 'link_click' => FALSE
						),
				// text is part of the summary of a longer item (e.g. content summary)
				'SUMMARY' =>
					array(
						'defs'=>TRUE, 'constants'=>'full', 'parse_sc'=>TRUE
						),
				// text is the description of an item (e.g. download, link)
				'DESCRIPTION' =>
					array(
						'defs'=>TRUE, 'constants'=>'full', 'parse_sc'=>TRUE
						),
				// text is 'body' or 'bulk' text (e.g. custom page body, content body)
				'BODY' =>
					array(
						'defs'=>TRUE, 'constants'=>'full', 'parse_sc'=>TRUE
						),
				// text is parsed by the Wysiwyg editor. eg. TinyMce
				'WYSIWYG' =>
					array(
							'hook' => false, 'link_click' => false, 'link_replace' => false, 'retain_nl' => true
						),
				// text is user-entered (i.e. untrusted)'body' or 'bulk' text (e.g. custom page body, content body)
				'USER_BODY' =>
					array(
						'constants'=>'full', 'scripts' => FALSE, 'nostrip'=>FALSE
						),
				// text is 'body' of email or similar - being sent 'off-site' so don't rely on server availability
				'E_BODY' =>
					array(
						'defs'=>TRUE, 'constants'=>'full', 'parse_sc'=>TRUE, 'emotes'=>FALSE, 'scripts' => FALSE, 'link_click' => FALSE
						),
				// text is text-only 'body' of email or similar - being sent 'off-site' so don't rely on server availability
				'E_BODY_PLAIN' =>
					array(
						'defs'=>TRUE, 'constants'=>'full', 'parse_sc'=>TRUE, 'emotes'=>FALSE, 'scripts' => FALSE, 'link_click' => FALSE, 'retain_nl' => TRUE, 'no_tags' => TRUE
						),
				// text is the 'content' of a link (A tag, etc)
				'LINKTEXT' =>
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'emotes'=>FALSE, 'hook'=>FALSE, 'defs'=>TRUE, 'parse_sc'=>TRUE
						),
				// text is used (for admin edit) without fancy conversions or html.
				'RAWTEXT' =>
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'emotes'=>FALSE, 'hook'=>FALSE, 'no_tags'=>TRUE
						)
		);

	// Individual modifiers change the current context
	var $e_Modifiers = array(
				'emotes_off'	=> array('emotes' => FALSE),
				'emotes_on'		=> array('emotes' => TRUE),
				'no_hook'		=> array('hook' => FALSE),
				'do_hook'		=> array('hook' => TRUE),
				// New for 0.8
				'scripts_off'	=> array('scripts' => FALSE),
				// New for 0.8
				'scripts_on'	=> array('scripts' => TRUE),
				'no_make_clickable' => array('link_click' => FALSE),
				'make_clickable' => array('link_click' => TRUE),
				'no_replace' 	=> array('link_replace' => FALSE),
				// Replace text of clickable links (only if make_clickable option set)
				'replace' 		=> array('link_replace' => TRUE),
				// No path replacement
				'consts_off'	=> array('constants' => FALSE),
				// Relative path replacement
				'consts_rel'	=> array('constants' => 'rel'),
				// Absolute path replacement
				'consts_abs'	=> array('constants' => 'abs'),
				// Full path replacement
				'consts_full'	=> array('constants' => 'full'),
				// No shortcode parsing
				'scparse_off'	=> array('parse_sc'	=> FALSE),

				'scparse_on'	=> array('parse_sc'	=> TRUE),
				// Strip tags
				'no_tags' 		=> array('no_tags' 	=> TRUE),
				// Leave tags
				'do_tags' 		=> array('no_tags' 	=> FALSE),

				'fromadmin'		=> array('fromadmin' => TRUE),
				'notadmin'		=> array('fromadmin' => FALSE),
				// entity replacement
				'er_off'		=> array('value' => FALSE),
				'er_on'			=> array('value' => TRUE),
				// Decode constant if exists
				'defs_off'		=> array('defs' => FALSE),
				'defs_on'		=> array('defs' => TRUE),

				'dobreak'		=> array('nobreak' => FALSE),
				'nobreak'		=> array('nobreak' => TRUE),
				// Line break using \n
				'lb_nl'			=> array('retain_nl' => TRUE),
				// Line break using <br />
				'lb_br'			=> array('retain_nl' => FALSE),

				// Legacy option names below here - discontinue later
				'retain_nl'		=> array('retain_nl' => TRUE),
				'defs'			=> array('defs' => TRUE),
				'parse_sc'		=> array('parse_sc'	=> TRUE),
				'constants'		=> array('constants' => 'rel'),
				'value'			=> array('value' => TRUE),
				'wysiwyg'		=> array('wysiwyg'=>TRUE)
		);


	/**
	 * Constructor - keep it public for backward compatibility
	 still some new e_parse() in the core
	 *
	 */
	public function __construct()
	{
		// initialise the type of UTF-8 processing methods depending on PHP version and mb string extension
		parent::__construct();


		$this->init();
		$this->initCharset();

		// Preprocess the supermods to be useful default arrays with all values
		foreach ($this->e_SuperMods as $key => $val)
		{
			// precalculate super defaults
			$this->e_SuperMods[$key] = array_merge($this->e_optDefault , $this->e_SuperMods[$key]);
			$this->e_SuperMods[$key]['context'] = $key;
		}
	}


	/**
	 * Initialise the type of UTF-8 processing methods depending on PHP version and mb string extension.
	 *
	 * NOTE: can't be called until CHARSET is known
	 but we all know that it is UTF-8 now
	 *
	 * @return void
	 */
	private function initCharset()
	{
		// Start by working out what, if anything, we do about utf-8 handling.
		// 'Do nothing' is the simple option
		$this->utfAction = 0;
// CHARSET is utf-8
//		if(strtolower(CHARSET) == 'utf-8')
//		{
			if(version_compare(PHP_VERSION, '6.0.0') < 1)
			{
				// Need to do something here
				if(extension_loaded('mbstring'))
				{
					// Check for function overloading
					$temp = ini_get('mbstring.func_overload');
					// Just check the string functions - will be non-zero if overloaded
					if(($temp & MB_OVERLOAD_STRING) == 0)
					{
						// Can use the mb_string routines
						$this->utfAction = 1;
					}
					// Set the default encoding, so we don't have to specify every time
					mb_internal_encoding('UTF-8');
				}
				else
				{
					// Must use emulation - will probably be slow!
					$this->utfAction = 2;
					require_once(E_UTF8_PACK.'utils/unicode.php');
					// Always load the core routines - bound to need some of them!
					require_once(E_UTF8_PACK.'native/core.php');
				}
			}
//		}
	}


	/**
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strlen strlen PHP function.
	 * Returns the length of the given string.
	 *
	 * @param string $str The UTF-8 encoded string being measured for length.
	 * @return integer The length (amount of UTF-8 characters) of the string on success, and 0 if the string is empty.
	 */
	public function ustrlen($str)
	{
		switch($this->utfAction)
		{
			case 0:
				return strlen($str);
			case 1:
				return mb_strlen($str);
		}
		// Default case shouldn't happen often
		// Save a call - invoke the function directly
		return strlen(utf8_decode($str));
	}


	/**
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strtolower strtolower PHP function.
	 * Make a string lowercase.
	 *
	 * @param string $str The UTF-8 encoded string to be lowercased.
	 * @return string Specified string with all alphabetic characters converted to lowercase.
	 */
	public function ustrtolower($str)
	{
		switch($this->utfAction)
		{
			case 0:
				return strtolower($str);
			case 1:
				return mb_strtolower($str);
		}
		// Default case shouldn't happen often
		return utf8_strtolower($str);
	}


	/**
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strtoupper strtoupper PHP function.
	 * Make a string uppercase.
	 *
	 * @param string $str The UTF-8 encoded string to be uppercased.
	 * @return string Specified string with all alphabetic characters converted to uppercase.
	 */
	public function ustrtoupper($str)
	{
		switch($this->utfAction)
		{
			case 0:
				return strtoupper($str);
			case 1:
				return mb_strtoupper($str);
		}
		// Default case shouldn't happen often
		return utf8_strtoupper($str);
	}


	/**
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strpos strpos PHP function.
	 * Find the position of the first occurrence of a case-sensitive UTF-8 encoded string.
	 * Returns the numeric position (offset in amount of UTF-8 characters)
	 *  of the first occurrence of needle in the haystack string.
	 *
	 * @param string $haystack The UTF-8 encoded string being searched in.
	 * @param integer $needle The UTF-8 encoded string being searched for.
	 * @param integer $offset [optional] The optional offset parameter allows you to specify which character in haystack to start searching.
	 * 				 The position returned is still relative to the beginning of haystack.
	 * @return integer|boolean Returns the position as an integer. If needle is not found, the function will return boolean FALSE.
	 */
	public function ustrpos($haystack, $needle, $offset = 0)
	{
		switch($this->utfAction)
		{
			case 0:
				return strpos($haystack, $needle, $offset);
			case 1:
				return mb_strpos($haystack, $needle, $offset);
		}
		return utf8_strpos($haystack, $needle, $offset);
	}


	/**
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strrpos strrpos PHP function.
	 * Find the position of the last  occurrence of a case-sensitive UTF-8 encoded string.
	 * Returns the numeric position (offset in amount of UTF-8 characters)
	 *  of the last occurrence of needle in the haystack string.
	 *
	 * @param string $haystack The UTF-8 encoded string being searched in.
	 * @param integer $needle The UTF-8 encoded string being searched for.
	 * @param integer $offset [optional] - The optional offset parameter allows you to specify which character in haystack to start searching.
	 * 				 The position returned is still relative to the beginning of haystack.
	 * @return integer|boolean Returns the position as an integer. If needle is not found, the function will return boolean FALSE.
	 */
	public function ustrrpos($haystack, $needle, $offset = 0)
	{
		switch($this->utfAction)
		{
			case 0:
				return strrpos($haystack, $needle, $offset);
			case 1:
				return mb_strrpos($haystack, $needle, $offset);
		}
		return utf8_strrpos($haystack, $needle, $offset);
	}


	/**
	 * Unicode (UTF-8) analogue of standard @link http://php.net/stristr stristr PHP function.
	 * Returns all of haystack starting from and including the first occurrence of needle to the end.
	 *
	 * @param string $haystack The UTF-8 encoded string to search in.
	 * @param mixed $needle If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
	 * @param integer $length [optional] (PHP 5.3+) If TRUE, returns the part of the haystack before the first occurrence of the needle (excluding needle).
	 * @return string Returns the matched substring. If needle is not found, returns FALSE.
	 */
	public function ustristr($haystack, $needle, $before_needle = false)
	{
		switch($this->utfAction)
		{
			case 0:
				return stristr($haystack, $needle, $before_needle);
			case 1:
				//return mb_substr($haystack, $needle, $before_needle);
				return mb_stristr($haystack, $needle, $before_needle);
		}
		// No utf8 pack backup
		return stristr($haystack, $needle, $before_needle);
	}
	
	/**
	 * Unicode (UTF-8) analogue of standard @link http://php.net/substr substr PHP function.
	 * Returns the portion of string specified by the start and length parameters.
	 *
	 * NOTE: May be subtle differences in return values dependent on which routine is used.
	 *  Native substr() routine can return FALSE. mb_substr() and utf8_substr() just return an empty string.
	 *
	 * @param string $str The UTF-8 encoded string.
	 * @param integer $start Start of portion to be returned. Position is counted in amount of UTF-8 characters from the beginning of str.
	 * 				First character's position is 0. Second character position is 1, and so on.
	 * @param integer $length [optional] If length is given, the string returned will contain at most length characters beginning from start
	 * 				(depending on the length of string). If length is omitted, the rest of string from start will be returned.
	 * @return string The extracted UTF-8 encoded part of input string.
	 */
	public function usubstr($str, $start, $length = NULL)
	{
		switch($this->utfAction)
		{
			case 0:
				return substr($str, $start, $length);
			case 1:
				if(is_null($length))
				{
					return mb_substr($str, $start);
				}
				else
				{
					return mb_substr($str, $start, $length);
				}
		}
		return utf8_substr($str, $start, $length);
	}

	/**
	 * Converts the supplied text (presumed to be from user input) to a format suitable for storing in a database table.
	 *
	 * @param string $data
	 * @param boolean $nostrip [optional] Assumes all data is GPC ($_GET, $_POST, $_COOKIE) unless indicate otherwise by setting this var to TRUE.
	 * 				If magic quotes is enabled on the server and you do not tell toDB() that the data is non GPC then slashes will be stripped when they should not be.
	 * @param boolean $no_encode [optional] This parameter should nearly always be FALSE. It is used by the save_prefs() function to preserve HTML content within prefs even when
	 * 				the save_prefs() function has been called by a non admin user / user without html posting permissions.
	 * @param boolean|string $mod [optional] model = admin-ui usage. The 'no_html' and 'no_php' modifiers blanket prevent HTML and PHP posting regardless of posting permissions. (used in logging)
	 *		The 'pReFs' value is for internal use only, when saving prefs, to prevent sanitisation of HTML.
	 * @param boolean $original_author [optional]
	 * @return string
	 * @todo complete the documentation of this essential method
	 */
	public function toDB($data, $nostrip =false, $no_encode = false, $mod = false, $parm = null)
	{
		$core_pref = e107::getConfig();

		if (is_array($data))
		{
			$ret = array();
			foreach ($data as $key => $var)
			{
				//Fix - sanitize keys as well
				$ret[$this->toDB($key, $nostrip, $no_encode, $mod, $original_author)] = $this->toDB($var, $nostrip, $no_encode, $mod, $original_author);
			}
			return $ret;
		}



		if (MAGIC_QUOTES_GPC == true && $nostrip == false)
		{
			$data = stripslashes($data);
		}

		if ($mod !== 'pReFs') //XXX We're not saving prefs.
		{

			$data = $this->preFilter($data); // used by bb_xxx.php toDB() functions. bb_code.php toDB() allows us to properly bypass HTML cleaning below.

		//	if(strlen($data) != strlen(strip_tags($data))) // html tags present. // strip_tags()  doesn't function doesnt look for unclosed '>'.
			if(($this->isHtml($data)) && strpos($mod, 'no_html') === false)
			{
				$this->isHtml = true;
				$data = $this->cleanHtml($data); // sanitize all html.

				$data = str_replace(array('%7B','%7D'),array('{','}'),$data); // fix for {e_XXX} paths.

			//	$data = urldecode($data); //XXX Commented out :  NO LONGER REQUIRED. symptom of cleaning the HTML - urlencodes src attributes containing { and } .eg. {e_BASE}

			}
			else // caused double-encoding of '&'
			{
				//$data = str_replace('<','&lt;',$data);
				//$data = str_replace('>','&gt;',$data);
			}

			if (!check_class($core_pref->get('post_html', e_UC_MAINADMIN)))
			{
				$data = strip_tags($data); // remove tags from cleaned html.
				$data = str_replace(array('[html]','[/html]'),'',$data);
			}


			//  $data = html_entity_decode($data, ENT_QUOTES, 'utf-8');	// Prevent double-entities. Fix for [code]  - see bb_code.php toDB();
		}



		if (check_class($core_pref->get('post_html'))) /*$core_pref->is('post_html') && XXX preformecd by cleanHtml() */
		{
			$no_encode = true;
		}
				
		if($parm !== null && is_numeric($parm) && !check_class($core_pref->get('post_html'), '', $parm))
		{
			$no_encode = false;
		}

		if ($no_encode === true && strpos($mod, 'no_html') === false)
		{
			$search = array('$', '"', "'", '\\', '<?');
			$replace = array('&#036;', '&quot;', '&#039;', '&#092;', '&lt;?');
			$ret = str_replace($search, $replace, $data);
		}
		else // add entities for everything. we want to save the code.
		{

			$data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
			$data = str_replace('\\', '&#092;', $data);


			$ret = preg_replace("/&amp;#(\d*?);/", "&#\\1;", $data);
		}
		
		// XXX - php_bbcode has been deprecated. 
		if ((strpos($mod, 'no_php') !== false) || !check_class($core_pref->get('php_bbcode')))
		{
			$ret = preg_replace("#\[(php)#i", "&#91;\\1", $ret);
		}

		// Don't allow hooks to mess with prefs.
		if($mod !== 'model')
		{
			return $ret;
		}


		/**
		 * e_parse hook
		 */
		$eParseList = $core_pref->get('e_parse_list');
		if(!empty($eParseList))
		{

			$opts = array(
				'nostrip'   => $nostrip,
				'noencode'  => $no_encode,
				'type'      => $parm['type'],
				'field'     => $parm['field']
			);

			foreach($eParseList as $plugin)
			{
				$hookObj = e107::getAddon($plugin, 'e_parse');
				if($tmp = e107::callMethod($hookObj, 'toDB', $ret, $opts))
				{
					$ret = $tmp;
				}

			}

		}


		return $ret;
	}



	/**
	 *	Check for umatched 'dangerous' HTML tags
	 *		(these can destroy page layout where users are able to post HTML)
	 * @DEPRECATED
	 *	@param string $data
	 *	@param string $tagList - if empty, uses default list of input tags. Otherwise a CSV list of tags to check (any type)
	 *
	 *	@return boolean TRUE if an unopened closing tag found
	 *					FALSE if nothing found
	 */
	function htmlAbuseFilter($data, $tagList = '')
	{
		
		if ($tagList == '')
		{
			$checkTags = array('textarea', 'input', 'td', 'tr', 'table');
		}
		else
		{
			$checkTags = explode(',', $tagList);
		}
		$tagArray = array_flip($checkTags);
		foreach ($tagArray as &$v) { $v = 0; };		// Data fields become zero; keys are tag names.
		$data = strtolower(preg_replace('#\[code\].*?\[\/code\]#i', '', $data));            // Ignore code blocks. All lower case simplifies the rest
		$matches = array();
		if (!preg_match_all('#<(\/|)([^<>]*?[^\/])>#', $data, $matches, PREG_SET_ORDER))
		{
			//echo "No tags found<br />";
			return TRUE;				// No tags found; so all OK
		}
		//print_a($matches);
		foreach ($matches as $m)
		{
			// $m[0] is the complete tag; $m[1] is '/' or empty; $m[2] is the tag and any attributes
			list ($tag) = explode(' ', $m[2], 2);
			if (!isset($tagArray[$tag])) continue;			// Not a tag of interest
			if ($m[1] == '/')
			{	// Closing tag
				if ($tagArray[$tag] == 0) 
				{
					//echo "Close before open: {$tag}<br />";
					return TRUE;		// Closing tag before we've had an opening tag
				}
				$tagArray[$tag]--;		// Obviously had at least one opening tag
			}
			else
			{	// Opening tag
				$tagArray[$tag]++;
			}
		}
		//print_a($tagArray);
		foreach ($tagArray as $t)
		{
			if ($t > 0) return TRUE;		// More opening tags than closing tags
		}
		return FALSE;						// OK now
	}




	/**
	 * @DEPRECATED XXX TODO Remove this horrible thing which adds junk to a db. 
	 *	Checks a string for potentially dangerous HTML tags, including malformed tags
	 *
	 */
	public function dataFilter($data, $mode='bbcode')
	{
					

		$ans = '';
		$vetWords = array('<applet', '<body', '<embed', '<frame', '<script','%3Cscript',
						 '<frameset', '<html', '<iframe', '<style', '<layer', '<link',
						 '<ilayer', '<meta', '<object', '<plaintext', 'javascript:',
						 'vbscript:','data:text/html');
		
		$ret = preg_split('#(\[code.*?\[/code.*?])#mis', $data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

		foreach ($ret as $s)
		{
			if (substr($s, 0, 5) != '[code')
			{
				$vl = array();
				$t = html_entity_decode(rawurldecode($s), ENT_QUOTES, CHARSET);
				$t = str_replace(array("\r", "\n", "\t", "\v", "\f", "\0"), '', $t);
				$t1 = strtolower($t);
				foreach ($vetWords as $vw)
				{
					if (strpos($t1, $vw) !== FALSE)
					{
						$vl[] = $vw;		// Add to list of words found
					}
					if (substr($vw, 0, 1) == '<')
					{
						$vw = '</'.substr($vw, 1);
						if (strpos($t1, $vw) !== FALSE)
						{
							$vl[] = $vw;		// Add to list of words found
						}
					}
				}
				// More checks here
				if (count($vl))
				{	// Do something
					$s = preg_replace_callback('#('.implode('|', $vl).')#mis', array($this, 'modtag'), $t);
				}
			}
			$s = preg_replace('#(?:onmouse.+?|onclick|onfocus)\s*?\=#', '[sanitised]$0[/sanitised]', $s);
			$s = preg_replace_callback('#base64([,\(])(.+?)([\)\'\"])#mis', array($this, 'proc64'), $s);
			$ans .= $s;
		}
		
		if($mode == 'link' && count($vl))
		{
			return "#sanitized";
		}
		
		return $ans;
	}


	/**
	 * Check base-64 encoded code
	 */
	private function proc64($match)
	{
		$decode = base64_decode($match[2]);
		return 'base64'.$match[1].base64_encode($this->dataFilter($decode)).$match[3];
	}


	// XXX REmove ME. 
	private function modTag($match)
	{
		$ans = '';
		if (isset($match[1]))
		{
			$chop = intval(strlen($match[1]) / 2);
			$ans = substr($match[1], 0, $chop).'##xss##'.substr($match[1], $chop);
		}
		else
		{
			$ans = '?????';
		}
		return '[sanitised]'.$ans.'[/sanitised]';

	}



	/**
	 *	Processes data as needed before its written to the DB.
	 *	Currently gives bbcodes the opportunity to do something
	 *
	 *	@param $data string - data about to be written to DB
	 *	@return string - modified data
	 */
	public function preFilter($data)
	{
		if (!is_object($this->e_bb))
		{
			require_once(e_HANDLER.'bbcode_handler.php');
			$this->e_bb = new e_bbcode;
		}
		$ret = $this->e_bb->parseBBCodes($data, USERID, 'default', 'PRE');			// $postID = logged in user here
		return $ret;
	}




	function toForm($text)
	{

		if(empty($text)) // fix - handle proper 0, Space etc values.
		{
			return $text;
		}


		if(is_string($text) && substr($text,0,6) == '[html]')
		{
			// $text = $this->toHtml($text,true);
			$search = array('&quot;','&#039;','&#092;', '&',); // '&' must be last.
			$replace = array('"',"'","\\", '&amp;');

		//	return htmlspecialchars_decode($text);
			$text = str_replace($search,$replace,$text);
		//	return $text;
			//$text  = htmlentities($text,ENT_NOQUOTES, "UTF-8");

		//	return $text;

		}
	//	return htmlentities($text);

		$search = array('&#036;', '&quot;', '<', '>');
		$replace = array('$', '"', '&lt;', '&gt;');
		$text = str_replace($search, $replace, $text);
		if (e107::wysiwyg() !== true && is_string($text))
		{
			// fix for utf-8 issue with html_entity_decode(); ???
			$text = urldecode($text);
		//	$text = str_replace("&nbsp;", " ", $text);
		}
		return $text;
	}


	function post_toForm($text)
	{
		if(is_array($text))
		{
			foreach ($text as $key=>$value)
			{
				$text[$this->post_toForm($key)] = $this->post_toForm($value);
			}
			return $text;
		}
		if(MAGIC_QUOTES_GPC == TRUE)
		{
			$text = stripslashes($text);
		}
		return str_replace(array("'", '"', "<", ">"), array("&#039;", "&quot;", "&lt;", "&gt;"), $text);
	}


	function post_toHTML($text, $original_author = FALSE, $extra = '', $mod = FALSE)
	{
		$text = $this->toDB($text, FALSE, FALSE, $mod, $original_author);
		return $this->toHTML($text, TRUE, $extra);
	}

	/**
	 * @param $text - template to parse.
	 * @param boolean $parseSCFiles - parse core 'single' shortcodes
	 * @param object|array $extraCodes - shortcode class containing sc_xxxxx methods or an array of key/value pairs or legacy shortcode content (eg. content within .sc)
	 * @param object $eVars - XXX more info needed.
	 * @return string
	 */
	function parseTemplate($text, $parseSCFiles = true, $extraCodes = null, $eVars = null)
	{

		if(!is_bool($parseSCFiles))
		{
			trigger_error("\$parseSCFiles in parseTemplate() was given incorrect data");
		}

		return e107::getScParser()->parseCodes($text, $parseSCFiles, $extraCodes, $eVars);
	}

	
	/**
	 * Check if we are using the simple-Parse array format, or a legacy .sc format which contains 'return ' 
	 * @param array $extraCodes
	 */
	private function isSimpleParse($extraCodes)
	{
		
		if(!is_array($extraCodes))
		{
			return false;	
		}
		
		foreach ($extraCodes as $sc => $code)
		{
			if(preg_match('/return(.*);/',$code)) // still problematic. 'return;' Might be used in common speech.
			{
				return false;
			}
			else
			{
				return true;
			}
		/*	if(!strpos($code, 'return '))
			{
				return true;
			}
			else 
			{
				return false;
			}*/
		}		
	}



	/**
	 * Simple parser
	 *
	 * @param string $template
	 * @param e_vars|array $vars
	 * @param string $replaceUnset string to be used if replace variable is not set, false - don't replace
	 * @return string parsed content
	 */
	function simpleParse($template, $vars, $replaceUnset='')
	{
		$this->replaceVars = $vars;
		$this->replaceUnset = $replaceUnset;
		return preg_replace_callback("#\{([a-zA-Z0-9_]+)\}#", array($this, 'simpleReplace'), $template);
	}

	protected function simpleReplace($tmp) 
	{

		$unset = ($this->replaceUnset !== false ? $this->replaceUnset : $tmp[0]);

		if(is_array($this->replaceVars))
		{
            $this->replaceVars = new e_vars($this->replaceVars);
			//return ($this->replaceVars[$key] !== null ? $this->replaceVars[$key]: $unset);
		}
		$key = $tmp[1]; // PHP7 fix.
		return ($this->replaceVars->$key !== null ? $this->replaceVars->$key : $unset); // Doesn't work.
	}


	function htmlwrap($str, $width, $break = "\n", $nobreak = "a", $nobr = "pre", $utf = FALSE)
	{
		/*
		Pretty well complete rewrite to try and handle utf-8 properly.
		Breaks each utf-8 'word' every $width characters max. If possible, breaks after 'safe' characters.
		$break is the character inserted to flag the break.
		$nobreak is a list of tags within which word wrap is to be inactive
		*/

		//TODO handle htmlwrap somehow
		//return $str;

		// Don't wrap if non-numeric width
		$width = intval($width);
		// And trap stupid wrap counts
		if ($width < 6)
			return $str;

		// Transform protected element lists into arrays
		$nobreak = explode(" ", strtolower($nobreak));

		// Variable setup
		$intag = FALSE;
		$innbk = array();
		$drain = "";

		// List of characters it is "safe" to insert line-breaks at
		// It is not necessary to add < and > as they are automatically implied
		$lbrks = "/?!%)-}]\\\"':;&";

		// Is $str a UTF8 string?
		if ($utf || strtolower(CHARSET) == 'utf-8')
		{
			// 0x1680, 0x180e, 0x2000-0x200a, 0x2028, 0x205f, 0x3000 are 'non-ASCII' Unicode UCS-4 codepoints - see http://www.unicode.org/Public/UNIDATA/UnicodeData.txt
			// All convert to 3-byte utf-8 sequences:
			// 0x1680	0xe1	0x9a	0x80
			// 0x180e	0xe1	0xa0	0x8e
			// 0x2000	0xe2	0x80	0x80
			//   -
			// 0x200a	0xe2	0x80	0x8a
			// 0x2028	0xe2	0x80	0xa8
			// 0x205f	0xe2	0x81	0x9f
			// 0x3000	0xe3	0x80	0x80
			$utf8 = 'u';
			$whiteSpace = '#([\x20|\x0c]|[\xe1][\x9a][\x80]|[\xe1][\xa0][\x8e]|[\xe2][\x80][\x80-\x8a,\xa8]|[\xe2][\x81][\x9f]|[\xe3][\x80][\x80]+)#';
			// Have to explicitly enumerate the whitespace chars, and use non-utf-8 mode, otherwise regex fails on badly formed utf-8
		}
		else
		{
			$utf8 = '';
			// For non-utf-8, can use a simple match string
			$whiteSpace = '#(\s+)#';
		}


		// Start of the serious stuff - split into HTML tags and text between
		$content = preg_split('#(<.*?'.'>)#mis', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		foreach($content as $value)
		{
			if ($value[0] == "<")
			{
				// We are within an HTML tag
				// Create a lowercase copy of this tag's contents
				$lvalue = strtolower(substr($value, 1, -1));
				if ($lvalue)
				{
					// Tag of non-zero length
					// If the first character is not a / then this is an opening tag
					if ($lvalue[0] != "/")
					{
						// Collect the tag name
						preg_match("/^(\w*?)(\s|$)/", $lvalue, $t);

						// If this is a protected element, activate the associated protection flag
						if(in_array($t[1], $nobreak))
							array_unshift($innbk, $t[1]);
					}
					else
					{
						// Otherwise this is a closing tag
						// If this is a closing tag for a protected element, unset the flag
						if (in_array(substr($lvalue, 1), $nobreak))
						{
							reset($innbk);
							while (list($key, $tag) = each($innbk))
							{
								if (substr($lvalue, 1) == $tag)
								{
									unset($innbk[$key]);
									break;
								}
							}
							$innbk = array_values($innbk);
						}
					}
				}
				else
				{
					// Eliminate any empty tags altogether
					$value = '';
				}
				// Else if we're outside any tags, and with non-zero length string...
			}
			elseif ($value)
			{
				// If unprotected...
				if (!count($innbk))
				{
					// Use the ACK (006) ASCII symbol to replace all HTML entities temporarily
					$value = str_replace("\x06", "", $value);
					preg_match_all("/&([a-z\d]{2,7}|#\d{2,5});/i", $value, $ents);
					$value = preg_replace("/&([a-z\d]{2,7}|#\d{2,5});/i", "\x06", $value);
					//			echo "Found block length ".strlen($value).': '.substr($value,20).'<br />';
					// Split at spaces - note that this will fail if presented with invalid utf-8 when doing the regex whitespace search
					//			$split = preg_split('#(\s)#'.$utf8, $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
					$split = preg_split($whiteSpace, $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
					$value = '';
					foreach ($split as $sp)
					{
						//			echo "Split length ".strlen($sp).': '.substr($sp,20).'<br />';
						$loopCount = 0;
						while (strlen($sp) > $width)
						{
							// Enough characters that we may need to do something.
							$pulled = '';
							if ($utf8)
							{
								// Pull out a piece of the maximum permissible length
								if (preg_match('#^((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$width.'})(.{0,1}).*#s',$sp,$matches) == 0)
								{
									// Make any problems obvious for now
									$value .= '[!<b>invalid utf-8: '.$sp.'<b>!]';
									$sp = '';
								}
								elseif (empty($matches[2]))
								{
									// utf-8 length is less than specified - treat as a special case
									$value .= $sp;
									$sp = '';
								}
								else
								{
									// Need to find somewhere to break the string
									for($i = strlen($matches[1]) - 1; $i >= 0; $i--)
									{
										if(strpos($lbrks, $matches[1][$i]) !== FALSE)
											break;
									}
									if($i < 0)
									{
										// No 'special' break character found - break at the word boundary
										$pulled = $matches[1];
									}
									else
									{
										$pulled = substr($sp, 0, $i + 1);
									}
								}
								$loopCount++;
								if ($loopCount > 20)
								{
									// Make any problems obvious for now
									$value .= '[!<b>loop count exceeded: '.$sp.'</b>!]';
									$sp = '';
								}
							}
							else
							{
								for ($i = min($width, strlen($sp)); $i > 0; $i--)
								{
									// No speed advantage to defining match character
									if (strpos($lbrks, $sp[$i-1]) !== FALSE)
										break;
								}
								if ($i == 0)
								{
									// No 'special' break boundary character found - break at the word boundary
									$pulled = substr($sp, 0, $width);
								}
								else
								{
									$pulled = substr($sp, 0, $i);
								}
							}
							if ($pulled)
							{
								$value .= $pulled.$break;
								// Shorten $sp by whatever we've processed (will work even for utf-8)
								$sp = substr($sp, strlen($pulled));
							}
						}
						// Add in any residue
						$value .= $sp;
					}
					// Put captured HTML entities back into the string
					foreach ($ents[0] as $ent)
						$value = preg_replace("/\x06/", $ent, $value, 1);
				}
			}
			// Send the modified segment down the drain
			$drain .= $value;
		}
		// Return contents of the drain
		return $drain;
	}

	/**
	 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
	 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
	 *
	 * Truncate a HTML string
	 *
	 * Cuts a string to the length of $length and adds the value of $ending if the text is longer than length.
	 *
	 * @param string  $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param string $ending It will be used as Ending and appended to the trimmed string.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @return string Trimmed string.
	 */
	function html_truncate($text, $length = 100, $ending = '...', $exact = true)
	{
		if($this->ustrlen(preg_replace('/<.*?>/', '', $text)) <= $length)
		{
			return $text;
		}
		$totalLength = 0;
		$openTags = array();
		$truncate = '';
		preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);

		foreach($tags as $tag)
		{
			if(!$tag[2] || !preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/si', $tag[2]))
			{
				if(preg_match('/<[\w]+[^>]*>/s', $tag[0]))
				{
					array_unshift($openTags, $tag[2]);
				}
				else if(preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag))
				{
					$pos = array_search($closeTag[1], $openTags);
					if($pos !== false)
					{
						array_splice($openTags, $pos, 1);
					}
				}
			}
			$truncate .= $tag[1];
			$contentLength = $this->ustrlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));

			if($contentLength + $totalLength > $length)
			{
				$left = $length - $totalLength;
				$entitiesLength = 0;
				if(preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE))
				{
					foreach($entities[0] as $entity)
					{
						if($entity[1] + 1 - $entitiesLength <= $left)
						{
							$left--;
							$entitiesLength += $this->ustrlen($entity[0]);
						}
						else
						{
							break;
						}
					}
				}

				$truncate .= $this->usubstr($tag[3], 0, $left + $entitiesLength);
				break;
			}
			else
			{
				$truncate .= $tag[3];
				$totalLength += $contentLength;
			}
			if($totalLength >= $length)
			{
				break;
			}
		}
		if(!$exact)
		{
			$spacepos = $this->ustrrpos($truncate, ' ');
			if(isset($spacepos))
			{
				$bits = $this->usubstr($truncate, $spacepos);
				preg_match_all('/<\/([a-z]+)>/i', $bits, $droppedTags, PREG_SET_ORDER);
				if(!empty($droppedTags))
				{
					foreach($droppedTags as $closingTag)
					{
						if(!in_array($closingTag[1], $openTags))
						{
							array_unshift($openTags, $closingTag[1]);
						}
					}
				}
				$truncate = $this->usubstr($truncate, 0, $spacepos);
			}
		}
		$truncate .= $ending;
		foreach($openTags as $tag)
		{
			$truncate .= '</' . $tag . '>';
		}
		return $truncate;
	}

	/**
	 * Truncate a HTML string to a maximum length $len ­ append the string $more if it was truncated
	 *
	 * @param string $text String to process
	 * @param integer $len [optional] Length of characters to be truncated - default 200
	 * @param string $more [optional] String which will be added if truncation - default ' ... '
	 * @return string
	 */
	public function html_truncate_old ($text, $len = 200, $more = ' ... ')
	{
		$pos = 0;
		$curlen = 0;
		$tmp_pos = 0;
		$intag = FALSE;
		while($curlen < $len && $curlen < strlen($text))
		{
			switch($text {$pos} )
			{
				case "<":
					if($text {$pos + 1} == "/")
					{
						$closing_tag = TRUE;
					}
					$intag = TRUE;
					$tmp_pos = $pos - 1;
					$pos++;
				break;


				case ">":
					if($text {$pos - 1} == "/")
					{
						$closing_tag = TRUE;
					}
					if($closing_tag == TRUE)
					{
						$tmp_pos = 0;
						$closing_tag = FALSE;
					}
					$intag = FALSE;
					$pos++;
				break;


				case "&":
					if($text {$pos + 1} == "#")
					{
						$end = strpos(substr($text, $pos, 7), ";");
						if($end !== FALSE)
						{
							$pos += ($end + 1);
							if(!$intag)
							{
								$curlen++;
							}
						break;
						}
					}
					else
					{
						$pos++;
						if(!$intag)
						{
							$curlen++;
						}
					break;
					}
				default:
					$pos++;
					if(!$intag)
					{
						$curlen++;
					}
				break;
			}
		}
		$ret = ($tmp_pos > 0 ? substr($text, 0, $tmp_pos+1) : substr($text, 0, $pos));
		if($pos < strlen($text))
		{
			$ret = $ret.$more;
		}
		return $ret;
	}


	/**
	 * Truncate a string of text to a maximum length $len ­ append the string $more if it was truncated
	 * Uses current CHARSET ­ for utf-8, returns $len characters rather than $len bytes
	 *
	 * @param string $text ­ string to process
	 * @param integer $len ­ length of characters to be truncated
	 * @param string $more ­ string which will be added if truncation
	 * @return string
	 */
	public function text_truncate($text, $len = 200, $more = ' ... ')
	{
		// Always valid

		if($this->ustrlen($text) <= $len)
		{
			return $text;
		}

		$text = html_entity_decode($text,ENT_QUOTES,'utf-8');

		if(function_exists('mb_strimwidth'))
		{
			return mb_strimwidth($text, 0, $len, $more);
		}
		
		$ret = $this->usubstr($text, 0, $len);

		// search for possible broken html entities
		// - if an & is in the last 8 chars, removing it and whatever follows shouldn't hurt
		// it should work for any characters encoding

		$leftAmp = $this->ustrrpos($this->usubstr($ret, -8), '&');
		if($leftAmp)
		{
			$ret = $this->usubstr($ret, 0, $this->ustrlen($ret) - 8 + $leftAmp);
		}

		return $ret.$more;

	}


	function textclean ($text, $wrap = 100)
	{
		$text = str_replace("\n\n\n", "\n\n", $text);
		$text = $this->htmlwrap($text, $wrap);
		$text = str_replace(array('<br /> ', ' <br />', ' <br /> '), '<br />', $text);
		/* we can remove any linebreaks added by htmlwrap function as any \n's will be converted later anyway */
		return $text;
	}


	// Test for text highlighting, and determine the text highlighting transformation
	// Returns TRUE if highlighting is active for this page display
	function checkHighlighting()
	{
		global $pref;

		if (!defined('e_SELF'))
		{
			// Still in startup, so can't calculate highlighting
			return FALSE;
		}

		if(!isset($this->e_highlighting))
		{
			$this->e_highlighting = FALSE;
			$shr = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
			if($pref['search_highlight'] && (strpos(e_SELF, 'search.php') === FALSE) && ((strpos($shr, 'q=') !== FALSE) || (strpos($shr, 'p=') !== FALSE)))
			{
				$this->e_highlighting = TRUE;
				if(!isset($this->e_query))
				{
					$query = preg_match('#(q|p)=(.*?)(&|$)#', $shr, $matches);
					$this->e_query = str_replace(array('+', '*', '"', ' '), array('', '.*?', '', '\b|\b'), trim(urldecode($matches[2])));
				}
			}
		}
		return $this->e_highlighting;
	}


	/**
	 * Replace text represenation of website urls and email addresses with clickable equivalents.
	 * @param string $text
	 * @param string $type email|url
	 * @param array $opts options. (see below)
	 * @param string $opts['sub'] substitute text within links
	 * @param bool $opts['ext'] load link in new window (not for email)
	 * @return string
	 */
	private function makeClickable($text='', $type='email', $opts=array())
	{

		if(empty($text))
		{
			return '';
		}

		$textReplace = (!empty($opts['sub'])) ? $opts['sub'] : '';

		if(substr($textReplace,-6) === '.glyph')
		{
			$textReplace = $this->toGlyph($textReplace,'');
		}

		switch($type)
		{
			default:
			case "email":

				preg_match_all("#(?:[\n\r ]|^)?([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", $text, $match);

				if(!empty($match[0]))
				{

					$srch = array();
					$repl = array();

					foreach($match[0] as $eml)
					{
						$email = trim($eml);
						$srch[] = $email;
						$repl[] = $this->emailObfuscate($email,$textReplace);
					}
					$text = str_replace($srch,$repl,$text);
				}
				break;

			case "url":

				$linktext = (!empty($textReplace)) ? $textReplace : '\\2';
				$external = (!empty($opts['ext'])) ? 'rel="external"' : '';

				$text = preg_replace("#(^|[\s]|&nbsp;)([\w]+?:\/\/(?:[\w-%]+?)(?:\.[\w-%]+?)+.*?)(?=$|[\s[\]<]|\.\s|\.$|,\s|,$|&nbsp;)#is", "\\1<a class=\"e-url\" href=\"\\2\" ".$external.">".$linktext."</a>", $text);
				$text = preg_replace("#(^|[\s])((?:www|ftp)(?:\.[\w-%]+?){2}.*?)(?=$|[\s[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a class=\"e-url\" href=\"http://\\2\" ".$external.">".$linktext."</a>", $text);

				break;

		}

		return $text;



	}



	function parseBBCodes($text, $postID)
	{
		if (!is_object($this->e_bb))
		{
			require_once(e_HANDLER.'bbcode_handler.php');
			$this->e_bb = new e_bbcode;
		}


		$text = $this->e_bb->parseBBCodes($text, $postID);

		return $text;
	}













	/**
	 * Converts the text (presumably retrieved from the database) for HTML output.
	 *
	 * @param string $text
	 * @param boolean $parseBB [optional]
	 * @param string $modifiers [optional] TITLE|SUMMARY|DESCRIPTION|BODY|RAW|LINKTEXT etc.
	 *		Comma-separated list, no spaces allowed
	 *		first modifier must be a CONTEXT modifier, in UPPER CASE.
	 *		subsequent modifiers are lower case - see $this->e_Modifiers for possible values
	 * @param mixed $postID [optional]
	 * @param boolean $wrap [optional]
	 * @return string
	 * @todo complete the documentation of this essential method
	 */
	public function toHTML($text, $parseBB = FALSE, $modifiers = '', $postID = '', $wrap = FALSE)
	{
		if($text == '')
		{
			return $text;
		}

		$pref = e107::getPref();

		global $fromadmin;

		// Set default modifiers to start
		$opts = $this->e_optDefault;


		// Now process any modifiers that are specified
		if ($modifiers)
		{
			$aMods = explode(',', $modifiers);

			// If there's a supermodifier, it must be first, and in uppercase
			$psm = trim($aMods[0]);
			if (isset($this->e_SuperMods[$psm]))
			{
				// Supermodifier found - override default values where necessary
				$opts = array_merge($opts,$this->e_SuperMods[$psm]);
				$opts['context'] = $psm;
				unset($aMods[0]);
			}

			// Now find any regular modifiers; use them to modify the context
			// (there should only be one or two out of the list of possibles)
			foreach ($aMods as $mod)
			{
				// Slight concession to varying coding styles - stripping spaces is a waste of CPU cycles!
				$mod = trim($mod);
				if (isset($this->e_Modifiers[$mod]))
				{
					// This is probably quicker than array_merge
					// - especially as usually only one or two loops
					foreach ($this->e_Modifiers[$mod] as $k => $v)
					{
						// Update our context-specific options
						$opts[$k] = $v;
					}
				}
			}
		}

		// Turn off a few things if not enabled in options
		if(empty($pref['smiley_activate']))
		{
			$opts['emotes'] = false;
		}

		if(empty($pref['make_clickable']))
		{
			$opts['link_click'] = false;
		}

		if(empty($pref['link_replace']))
		{
			$opts['link_replace'] = false;
		}

		if($this->isHtml($text)) //BC FIx for when HTML is saved without [html][/html]
		{
			$opts['nobreak'] = true;
			$text = trim($text);
		}

		$fromadmin = $opts['fromadmin'];

		// Convert defines(constants) within text. eg. Lan_XXXX - must be the entire text string (i.e. not embedded)
		// The check for '::' is a workaround for a bug in the Zend Optimiser 3.3.0 and PHP 5.2.4 combination
		// - causes crashes if '::' in site name

		if($opts['defs'] && (strlen($text) < 35) && ((strpos($text, '::') === FALSE) && defined(trim($text))))
		{
			$text = constant(trim($text)); // don't return yet, words could be hooked with linkwords etc.
		}

		if ($opts['no_tags'])
		{
			$text = strip_tags($text);
		}
		
		if (MAGIC_QUOTES_GPC === true) // precaution for badly saved data.
		{
			$text = stripslashes($text);
		}


		// Make sure we have a valid count for word wrapping
		if (!$wrap && !empty($pref['main_wordwrap']))
		{
			$wrap = $pref['main_wordwrap'];
		}
//		$text = " ".$text;


		// Now get on with the parsing
		$ret_parser = '';
		$last_bbcode = '';
		// So we can change them on each loop
		$saveOpts = $opts;
		
		
		
		if ($parseBB == false)
		{
			$content = array($text);
		}
		else
		{
			// Split each text block into bits which are either within one of the 'key' bbcodes, or outside them
			// (Because we have to match end words, the 'extra' capturing subpattern gets added to output array. We strip it later)
			$content = preg_split('#(\[(table|html|php|code|scode|hide).*?\[/(?:\\2)\])#mis', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		}


		// Use $full_text variable so its available to special bbcodes if required
		foreach ($content as $full_text)
		{
			$proc_funcs = TRUE;
			$convertNL = TRUE;

			// We may have 'captured' a bbcode word - strip it if so
			if ($last_bbcode == $full_text)
			{
				$last_bbcode = '';
				$proc_funcs = FALSE;
				$full_text = '';
			}
			else
			{
				// Set the options for this pass
				$opts = $saveOpts;

				// Have to have a good test in case a 'non-key' bbcode starts the block
				// - so pull out the bbcode parameters while we're there
				if (($parseBB !== FALSE) && preg_match('#(^\[(table|html|php|code|scode|hide)(.*?)\])(.*?)(\[/\\2\]$)#is', $full_text, $matches ))
				{
					// It's one of the 'key' bbcodes
					// Usually don't want 'normal' processing if its a 'special' bbcode
					$proc_funcs = FALSE;
					// $matches[0] - complete block from opening bracket of opening tag to closing bracket of closing tag
					// $matches[1] - complete opening tag (inclusive of brackets)
					// $matches[2] - bbcode word
					// $matches[3] - parameter, including '='
					// $matches[4] - bit between the tags (i.e. text to process)
					// $matches[5] - closing tag
					// In case we decide to load a file
					
					$bbPath 		= e_CORE.'bbcodes/';
					$bbFile 		= strtolower(str_replace('_', '', $matches[2]));
					$bbcode 		= '';
					$className 		= '';
					$full_text 		= '';
					$code_text 		= $matches[4];
					$parm 			= $matches[3] ? substr($matches[3],1) : '';
					$last_bbcode 	= $matches[2];
	
					switch ($matches[2])
					{
						case 'php' :
							// Probably run the output through the normal processing functions - but put here so the PHP code can disable if desired
							$proc_funcs = TRUE;

							// This is just the contents of the php.bb file pulled in - its short, so will be quicker
			//				$search = array("&quot;", "&#039;", "&#036;", '<br />', E_NL, "-&gt;", "&lt;br /&gt;");
			//				$replace = array('"', "'", "$", "\n", "\n", "->", "<br />");
							// Shouldn't have any parameter on this bbcode
							// Not sure whether checks are necessary now we've reorganised
			//				if (!$matches[3]) $bbcode = str_replace($search, $replace, $matches[4]);
							// Because we're bypassing most of the initial parser processing, we should be able to just reverse the effects of toDB() and execute the code
							// [SecretR] - avoid php code injections, missing php.bb will completely disable user posted php blocks
							$bbcode = file_get_contents($bbPath.$bbFile.'.bb');
							if (!$matches[3])
							{
								$code_text = html_entity_decode($matches[4], ENT_QUOTES, 'UTF-8');
							}
							break;

						case 'html' : // This overrides and deprecates html.bb
							$proc_funcs = TRUE;


						//	$code_text = str_replace("\r\n", " ", $code_text);
						//	$code_text = html_entity_decode($code_text, ENT_QUOTES, CHARSET);
						//	$code_text = str_replace('&','&amp;',$code_text); // validation safe.
							$html_start = "<!-- bbcode-html-start -->"; // markers for html-to-bbcode replacement. 
							$html_end	= "<!-- bbcode-html-end -->";
							$full_text = str_replace(array("[html]","[/html]"), "",$code_text); // quick fix.. security issue?

							$full_text = $this->parseBBCodes($full_text, $postID); // parse any embedded bbcodes eg. [img]
							$full_text = $this->replaceConstants($full_text,'abs'); // parse any other paths using {e_....
							$full_text = $html_start.$full_text.$html_end;
							$full_text = $this->parseBBTags($full_text); // strip <bbcode> tags. 
							$opts['nobreak'] = true;
							$parseBB = false; // prevent further bbcode processing.


							break;

						case 'table' : // strip <br /> from inside of <table>		
						
							$convertNL = FALSE;
						//	break;

						case 'hide' :
							$proc_funcs = TRUE;

						default :		// Most bbcodes will just execute their normal file
							// @todo should we cache these bbcodes? require_once should make class-related codes quite efficient
							if (file_exists($bbPath.'bb_'.$bbFile.'.php'))
							{	// Its a bbcode class file
								require_once($bbPath.'bb_'.$bbFile.'.php');
								$className = 'bb_'.$last_bbcode;

								$this->bbList[$last_bbcode] = new $className();
							}
							elseif(file_exists($bbPath.$bbFile.'.bb'))
							{
								$bbcode = file_get_contents($bbPath.$bbFile.'.bb');
							}
					}   // end - switch ($matches[2])

					if ($className)
					{
						$tempCode = new $className();

						$full_text = $tempCode->bbPreDisplay($matches[4], $parm);
					}
					elseif ($bbcode)
					{	// Execute the file
						$full_text = eval($bbcode);			// Require output of bbcode to be returned
						// added to remove possibility of nested bbcode exploits ...
						//   (same as in bbcode_handler - is it right that it just operates on $bbcode_return and not on $bbcode_output? - QUERY XXX-02
					}
					if(strpos($full_text, '[') !== FALSE)
					{
						$exp_search = array('eval', 'expression');
						$exp_replace = array('ev<b></b>al', 'expres<b></b>sion');
						$bbcode_return = str_replace($exp_search, $exp_replace, $full_text);
					}
				}
			}


			// Do the 'normal' processing - in principle, as previously - but think about the order.
			if ($proc_funcs && !empty($full_text)) // some more speed
			{
				// Split out and ignore any scripts and style blocks. With just two choices we can match the closing tag in the regex
				$subcon = preg_split('#((?:<s)(?:cript[^>]+>.*?</script>|tyle[^>]+>.*?</style>))#mis', $full_text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
				foreach ($subcon as $sub_blk)
				{

					if(strpos($sub_blk,'<script') === 0) // Strip scripts unless permitted
					{
						if($opts['scripts'])
						{
							$ret_parser .= html_entity_decode($sub_blk, ENT_QUOTES);
						}
					}
					elseif(strpos($sub_blk,'<style') === 0)
					{
						// Its a style block - just pass it through unaltered - except, do we need the line break stuff? - QUERY XXX-01
						if(defined('DB_INF_SHOW'))
						{
							echo "Processing stylesheet: {$sub_blk}<br />";
						}

						$ret_parser .= $sub_blk;
					}
					else
					{
						// Do 'normal' processing on a chunk


						// Could put tag stripping in here
					
/*
						//	Line break compression - filter white space after HTML tags - among other things, ensures HTML tables display properly
						// Hopefully now achieved by other means
						if ($convertNL && !$opts['nobreak'])
						{
							$sub_blk = preg_replace("#>\s*[\r]*\n[\r]*#", ">", $sub_blk);
						}
*/

						//	Link substitution
						// Convert URL's to clickable links, unless modifiers or prefs override
						if ($opts['link_click'])
						{
							if ($opts['link_replace'] && defset('ADMIN_AREA') !== true)
							{

								$link_text = $pref['link_text'];
								$email_text = ($pref['email_text']) ? $this->replaceConstants($pref['email_text']) : LAN_EMAIL_SUBS;

								$sub_blk = $this->makeClickable($sub_blk, 'url', array('sub'=> $link_text,'ext'=>$pref['links_new_window']));
								$sub_blk = $this->makeClickable($sub_blk, 'email', array('sub'=> $email_text));
							}
							else
							{

								$sub_blk = $this->makeClickable($sub_blk, 'url', array('ext'=>true));
								$sub_blk = $this->makeClickable($sub_blk, 'email');

							}
						}


						// Convert emoticons to graphical icons, if enabled
						if ($opts['emotes'])
						{
							if (!is_object($this->e_emote))
							{
							//	require_once(e_HANDLER.'emote_filter.php');
								$this->e_emote = new e_emoteFilter;
							}
							$sub_blk = $this->e_emote->filterEmotes($sub_blk);
						}


						// Reduce newlines in all forms to a single newline character (finds '\n', '\r\n', '\n\r')
						if (!$opts['nobreak'])
						{
							if ($convertNL && ($this->preformatted($sub_blk) === false)) // eg. html or markdown
							{
								// We may need to convert to <br /> later
								$sub_blk = preg_replace("#[\r]*\n[\r]*#", E_NL, $sub_blk);
							}
							else
							{
								// Not doing any more - its HTML or Markdown so keep it as is.
								$sub_blk = preg_replace("#[\r]*\n[\r]*#", "\n", $sub_blk);
							}
						}


						//	Entity conversion
						// Restore entity form of quotes and such to single characters, except for text destined for tag attributes or JS.
						if($opts['value'])
						{
							// output used for attribute values.
							$sub_blk = str_replace($this->replace, $this->search, $sub_blk);
						}
						else
						{
							// output not used for attribute values.
							$sub_blk = str_replace($this->search, $this->replace, $sub_blk);
						}


						//   BBCode processing (other than the four already done, which shouldn't appear at all in the text)
						if ($parseBB !== FALSE)
						{
							if (!is_object($this->e_bb))
							{
								require_once(e_HANDLER.'bbcode_handler.php');
								$this->e_bb = new e_bbcode;
							}
							if ($parseBB === TRUE)
							{
								// 'Normal' or 'legacy' processing
								if($modifiers == "WYSIWYG")
								{
									$sub_blk = $this->e_bb->parseBBCodes($sub_blk, $postID, 'wysiwyg');	
								}
								else 
								{
									$sub_blk = $this->e_bb->parseBBCodes($sub_blk, $postID);
								}
								
							}
							elseif ($parseBB === 'STRIP')
							{
								// Need to strip all BBCodes
								$sub_blk = $this->e_bb->parseBBCodes($sub_blk, $postID, 'default', TRUE);
							}
							else
							{
								// Need to strip just some BBCodes
								$sub_blk = $this->e_bb->parseBBCodes($sub_blk, $postID, 'default', $parseBB);
							}
						}


						// replace all {e_XXX} constants with their e107 value. modifier determines relative/absolute conversion
						// (Moved to after bbcode processing by Cameron)
						if ($opts['constants'])
						{
							$sub_blk = $this->replaceConstants($sub_blk, $opts['constants']);		// Now decodes text values
						}


						// profanity filter
						if (!empty($pref['profanity_filter']))
						{
							if (!is_object($this->e_pf))
							{
							//	require_once(e_HANDLER."profanity_filter.php");
								$this->e_pf = new e_profanityFilter;
							}
							$sub_blk = $this->e_pf->filterProfanities($sub_blk);
						}


						//	Shortcodes
						// Optional short-code conversion
						if ($opts['parse_sc'])
						{
							$sub_blk = $this->parseTemplate($sub_blk, TRUE);
						}


						/**
						 * / @deprecated
						 */
						if ($opts['hook']) //Run any hooked in parsers
						{
							if ( varset($pref['tohtml_hook']))
							{
								//Process the older tohtml_hook pref (deprecated)
								foreach(explode(",", $pref['tohtml_hook']) as $hook)
								{
									if (!is_object($this->e_hook[$hook]))
									{
										if(is_readable(e_PLUGIN.$hook."/".$hook.".php"))
										{
											require_once(e_PLUGIN.$hook."/".$hook.".php");
											$hook_class = "e_".$hook;
											$this->e_hook[$hook] = new $hook_class;
										}

									}
									$sub_blk = $this->e_hook[$hook]->$hook($sub_blk,$opts['context']);
								}
							}

							/**
						    * / @deprecated
						    */
							if(isset($pref['e_tohtml_list']) && is_array($pref['e_tohtml_list']))
							{
								foreach($pref['e_tohtml_list'] as $hook)
								{
									if (!is_object($this->e_hook[$hook]))
									{
										if(is_readable(e_PLUGIN.$hook."/e_tohtml.php"))
										{
											require_once(e_PLUGIN.$hook."/e_tohtml.php");
											$hook_class = "e_tohtml_".$hook;
											$this->e_hook[$hook] = new $hook_class;
										}
									}

									if(is_object( $this->e_hook[$hook]))
									{
										$sub_blk = $this->e_hook[$hook]->to_html($sub_blk, $opts['context']);
									}
								}
							}

						/**
						* / Preferred 'hook'
						*/
						if(!empty($pref['e_parse_list']))
						{
							foreach($pref['e_parse_list'] as $plugin)
							{
								$hookObj = e107::getAddon($plugin,'e_parse');
								if($tmp = e107::callMethod($hookObj, 'toHTML', $sub_blk, $opts['context']))
								{
									$sub_blk = $tmp;
								}

							}

						}





				}


						// 	Word wrap
						if ($wrap && !$opts['nobreak'])
						{
							$sub_blk = $this->textclean($sub_blk, $wrap);
						}


						//	Search highlighting
						if ($opts['emotes'])			// Why??
						{
							if ($this->checkHighlighting())
							{
								$sub_blk = $this->e_highlight($sub_blk, $this->e_query);
							}
						}

						
						

						if($convertNL == true)
						{
							// Default replaces all \n with <br /> for HTML display
							$nl_replace = '<br />';
							if ($opts['nobreak'])
							{
								$nl_replace = '';
							}
							elseif ($opts['retain_nl'])
							{
								$nl_replace = "\n";
							}
							
							$sub_blk = str_replace(E_NL, $nl_replace, $sub_blk);
						}

						$ret_parser .= $sub_blk;
					}	// End of 'normal' processing for a block of text

				}		// End of 'foreach() on each block of non-script text

			}		// End of 'normal' parsing (non-script text)
			else
			{
				// Text block that needed no processing at all
				$ret_parser .= $full_text;
			}
		}

		// Quick Fix - Remove trailing <br /> on block-level elements (eg. div, pre, table, etc. )
		$srch = array();
		$repl = array();
		
		foreach($this->blockTags as $val)
		{
			$srch[] = "</".$val."><br />";	
			$repl[]	= "</".$val.">";
		}
		
		$ret_parser = str_replace($srch, $repl, $ret_parser);

		return trim($ret_parser);
	}


	/**
	 * Check if a string begins with a preformatter flag.
	 * @param $str
	 * @return bool
	 */
	private function preformatted($str)
	{
		foreach($this->preformatted as $type)
		{
			$code = '['.$type.']';
			if(strpos($str, $code) === 0)
			{
				return true;
			}

		}

		return false;
	}





	function toASCII($text)
	{

		$char_map = array(
			// Latin
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
			'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
			'ß' => 'ss',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
			'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
			'ÿ' => 'y',
			// Latin symbols
			'©' => '(c)',
			// Greek
			'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
			'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
			'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			'Ϋ' => 'Y',
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
			// Turkish
			'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
			'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
			// Russian
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
			'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
			'Я' => 'Ya',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',
			// Ukrainian
			'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
			'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
			// Czech
			'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
			'Ž' => 'Z',
			'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
			'ž' => 'z',
			// Polish
			'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
			'Ż' => 'Z',
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			'ż' => 'z',
			// Latvian
			'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
			'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
			'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
			'š' => 's', 'ū' => 'u', 'ž' => 'z'
		);

		return str_replace(array_keys($char_map), $char_map, $text);

	}



	/**
	 * Use it on html attributes to avoid breaking markup . 
	 * @example echo "<a href='#' title='".$tp->toAttribute($text)."'>Hello</a>"; 
	 */
	function toAttribute($text)
	{
		// URLs posted without HTML access may have an &amp; in them.

		// Xhtml compliance.
		$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

		if(!preg_match('/&#|\'|"|<|>/s', $text))
		{
			$text = $this->replaceConstants($text);
			return $text;
		}
		else
		{
			return $text;
		}
	}


	/**
	 * Convert text blocks which are to be embedded within JS
	 *
	 * @param string|array $stringarray
	 * @return string
	 */
	public function toJS($stringarray)
	{
		$search = array("\r\n", "\r", "<br />", "'");
		$replace = array("\\n", "", "\\n", "\'");
		$stringarray = str_replace($search, $replace, $stringarray);
		$stringarray = strip_tags($stringarray);

		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);

		return strtr($stringarray, $trans_tbl);
	}


	/**
	 * Converts a PHP variable into its JavaScript equivalent.
	 * We use HTML-safe strings, with several characters escaped.
	 *
	 * @param mixed $var
	 *  PHP variable.
	 * @param bool $force_object
	 *  True: Outputs an object rather than an array when a non-associative
	 *  array is used. Especially useful when the recipient of the output
	 *  is expecting an object and the array is empty.
	 *
	 * @return string
	 */
	public function toJSON($var, $force_object = false)
	{
		// The PHP version cannot change within a request.
		static $php530;

		if(!isset($php530))
		{
			$php530 = version_compare(PHP_VERSION, '5.3.0', '>=');
		}

		if($php530)
		{
			if($force_object === true)
			{
				// Encode <, >, ', &, and " using the json_encode() options parameter.
				return json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_FORCE_OBJECT);
			}

			// Encode <, >, ', &, and " using the json_encode() options parameter.
			return json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
		}

		return $this->toJSONhelper($var);
	}


	/**
	 * Encodes a PHP variable to HTML-safe JSON for PHP versions below 5.3.0.
	 *
	 * @param mixed $var
	 * @return string
	 */
	public function toJSONhelper($var)
	{
		switch(gettype($var))
		{
			case 'boolean':
				return $var ? 'true' : 'false'; // Lowercase necessary!

			case 'integer':
			case 'double':
				return $var;

			case 'resource':
			case 'string':
				// Always use Unicode escape sequences (\u0022) over JSON escape
				// sequences (\") to prevent browsers interpreting these as
				// special characters.
				$replace_pairs = array(
					// ", \ and U+0000 - U+001F must be escaped according to RFC 4627.
					'\\'           => '\u005C',
					'"'            => '\u0022',
					"\x00"         => '\u0000',
					"\x01"         => '\u0001',
					"\x02"         => '\u0002',
					"\x03"         => '\u0003',
					"\x04"         => '\u0004',
					"\x05"         => '\u0005',
					"\x06"         => '\u0006',
					"\x07"         => '\u0007',
					"\x08"         => '\u0008',
					"\x09"         => '\u0009',
					"\x0a"         => '\u000A',
					"\x0b"         => '\u000B',
					"\x0c"         => '\u000C',
					"\x0d"         => '\u000D',
					"\x0e"         => '\u000E',
					"\x0f"         => '\u000F',
					"\x10"         => '\u0010',
					"\x11"         => '\u0011',
					"\x12"         => '\u0012',
					"\x13"         => '\u0013',
					"\x14"         => '\u0014',
					"\x15"         => '\u0015',
					"\x16"         => '\u0016',
					"\x17"         => '\u0017',
					"\x18"         => '\u0018',
					"\x19"         => '\u0019',
					"\x1a"         => '\u001A',
					"\x1b"         => '\u001B',
					"\x1c"         => '\u001C',
					"\x1d"         => '\u001D',
					"\x1e"         => '\u001E',
					"\x1f"         => '\u001F',
					// Prevent browsers from interpreting these as as special.
					"'"            => '\u0027',
					'<'            => '\u003C',
					'>'            => '\u003E',
					'&'            => '\u0026',
					// Prevent browsers from interpreting the solidus as special and
					// non-compliant JSON parsers from interpreting // as a comment.
					'/'            => '\u002F',
					// While these are allowed unescaped according to ECMA-262, section
					// 15.12.2, they cause problems in some JSON parsers.
					"\xe2\x80\xa8" => '\u2028', // U+2028, Line Separator.
					"\xe2\x80\xa9" => '\u2029', // U+2029, Paragraph Separator.
				);

				return '"' . strtr($var, $replace_pairs) . '"';

			case 'array':
				// Arrays in JSON can't be associative. If the array is empty or if it
				// has sequential whole number keys starting with 0, it's not associative
				// so we can go ahead and convert it as an array.
				if(empty($var) || array_keys($var) === range(0, sizeof($var) - 1))
				{
					$output = array();
					foreach($var as $v)
					{
						$output[] = $this->toJSONhelper($v);
					}
					return '[ ' . implode(', ', $output) . ' ]';
				}
				break;

			// Otherwise, fall through to convert the array as an object.
			case 'object':
				$output = array();
				foreach($var as $k => $v)
				{
					$output[] = $this->toJSONhelper(strval($k)) . ':' . $this->toJSONhelper($v);
				}
				return '{' . implode(', ', $output) . '}';

			default:
				return 'null';
		}
	}


	/**
	 * Convert Text for RSS/XML use.
	 *
	 * @param string $text
	 * @param boolean $tags [optional]
	 * @return string
	 */
	function toRss($text, $tags = false)
	{
		if($tags != true)
		{
			$text = $this -> toHTML($text, true);
			$text = strip_tags($text);
		}

		$text = $this->toEmail($text);

		$search = array("&amp;#039;", "&amp;#036;", "&#039;", "&#036;", e_BASE, "href='request.php","<!-- bbcode-html-start -->","<!-- bbcode-html-end -->");
		$replace = array("'", '$', "'", '$', SITEURL, "href='".SITEURL."request.php", '', '' );
		$text = str_replace($search, $replace, $text);

		$text = $this->ampEncode($text);

		if($tags == true && ($text))
		{
			$text = "<![CDATA[".$text."]]>";
		}

		return $text;
	}


	/**
	 * Clean and Encode Ampersands '&' for output to browser.
	 * @param string $text
	 * @return mixed|string
	 */
	function ampEncode($text='')
	{
		// Fix any left-over '&'
		$text = str_replace('&amp;', '&', $text); //first revert any previously converted.
		$text = str_replace('&', '&amp;', $text);

		return $text;
	}


	/**
	 * Convert any string back to plain text.
	 * @param $text
	 * @return mixed|string
	 */
	function toText($text)
	{

		if($this->isBbcode($text) === true) // convert any bbcodes to html
		{
			$text = $this->toHtml($text,true);
		}

		if($this->isHtml($text) === true) // strip any html.
		{
			$text = $this->toHtml($text,true);
			$text = strip_tags($text);
		}

		$search = array("&amp;#039;", "&amp;#036;", "&#039;", "&#036;", "&#092;", "&amp;#092;");
		$replace = array("'", '$', "'", '$', "\\", "\\");
		$text = str_replace($search, $replace, $text);
		return $text;
	}


	/**
	 * Set the dimensions of a thumbNail (generated by thumbUrl)
	 */
	public function setThumbSize($w=null,$h=null,$crop=null)
	{
		if($w !== null)
		{
			$this->thumbWidth = intval($w);	
		}
		
		if($h !== null)
		{
			$this->thumbHeight = intval($h);	
		}	
		
		if($crop !== null)
		{
			$this->thumbCrop = intval($crop);	
		}				
		
	}

	public function thumbEncode($val = null)
	{

		if($val !== null)
		{
			$this->thumbEncode = intval($val);
			return null;
		}

		return $this->thumbEncode;
	}


	/**
	 * Retrieve img tag width and height attributes for current thumbnail.
	 * @return string
	 */
	public function thumbDimensions($type = 'single')
	{
		if(!empty($this->thumbCrop) && !empty($this->thumbWidth) && !empty($this->thumbHeight)) // dimensions are known.
		{
			return ($type === 'double') ? 'width="'.$this->thumbWidth.'" height="'.$this->thumbHeight.'"' : "width='".$this->thumbWidth."' height='".$this->thumbHeight."'";
		}

		return null;
	}


	/**
	 * Set or Get the value of the thumbNail Width. 
	 * @param $width (optional)
	 */
	public function thumbWidth($width=null)
	{
		if($width !== null)
		{
			$this->thumbWidth = intval($width);
		}
		
		return $this->thumbWidth;		
	}

	/**
	 * Set or Get the value of the thumbNailbCrop.
	 * @param bool $status = true/false
	 */
	public function thumbCrop($status=false)
	{
		if($status !== false)
		{
			$this->thumbCrop = intval($status);
		}

		return $this->thumbCrop;
	}



	/**
	 * Set or Get the value of the thumbNail height. 
	 * @param $height (optional)
	 */
	public function thumbHeight($height= null)
	{
		if($height !== null)
		{
			$this->thumbHeight = intval($height);
		}
		
		return $this->thumbHeight;	
		
	}

	/**
	 * Generated a Thumb Cache File Name from path and options.
	 * @param string $path
	 * @param array $options
	 * @return null|string
	 */
	public function thumbCacheFile($path, $options=null, $log=null)
	{
		if(empty($path))
		{
			return null;
		}

		if(is_string($options))
		{
			parse_str($options,$options);
		}

		$path = str_replace($this->getUrlConstants('raw'), $this->getUrlConstants('sc'), $path);
		$path = $this->replaceConstants(str_replace('..', '', $path));

		$filename   = basename($path);
		$tmp        = explode('.',$filename);
		$ext        = end($tmp);
		$len        = strlen($ext) + 1;
		$start      = substr($filename,0,- $len);


		// cleanup.
		$newOpts = array(
			'w'     => (string) intval($options['w']),
			'h'     => (string) intval($options['h']),
			'aw'    => (string) intval($options['aw']),
			'ah'    => (string) intval($options['ah']),
			'c'     => strtoupper(vartrue($options['c'],'0'))
		);

		if($log !== null)
		{
			file_put_contents(e_LOG.$log, "\n++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n\n", FILE_APPEND);
			$message = $path."\n".print_r($newOpts,true)."\n\n\n";
			file_put_contents(e_LOG.$log, $message, FILE_APPEND);

		//	file_put_contents(e_LOG.$log, "\t\tFOUND!!\n\n\n", FILE_APPEND);
		}


		if(!empty($options['aw']))
		{
			$options['w'] = $options['aw'];
		}

		if(!empty($options['ah']))
		{
			$options['h'] = $options['ah'];
		}


		$size = varset($options['w'],0).'x'.varset($options['h'],0);

		$thumbQuality = e107::getPref('thumbnail_quality',65);

		$cache_str = md5(serialize($newOpts).$path. $thumbQuality);

		$pre = 'thumb_';
		$post = '.cache.bin';

	//	$cache_str = http_build_query($newOpts,null,'_'); // testing files.

		if(defined('e_MEDIA_STATIC')) // experimental - subject to change.
		{
			$pre = '';
			$post = '';
		}

		$fname = $pre.strtolower($start.'_'.$cache_str.'_'.$size.'.'.$ext).$post;

		return $fname;
	}



	private function staticCount($val=false)
	{

		$count = $this->staticCount;

		if($val === 0)
		{
			$this->staticCount = 0;
		}
		elseif($val !== false)
		{
			$this->staticCount = $this->staticCount + (int) $val;
		}

		return (int) $count;

	}


	/**
	 * @todo Move to e107_class ?
	 * @param string $path - absolute path
	 * @return string - static path.
	 */
	public function staticUrl($path=null, $opts=array())
	{
		if(!defined('e_HTTP_STATIC') || deftrue('e_ADMIN_AREA'))
		{
			// e107::getDebug()->log("e_HTTP_STATIC not defined");
			if($path === null)
			{
				return !empty($opts['full']) ? SITEURL : e_HTTP;
			}
			else
			{
				return $path;
			}
		}


		$staticArray = e_HTTP_STATIC;

		if(is_array($staticArray))
		{
			$cnt = count($staticArray);
			$staticCount = $this->staticCount();
			if($staticCount > ($cnt -1))
			{
				$staticCount = 0;
				$this->staticCount(0);
			}

			$http = !empty($staticArray[$staticCount]) ? $staticArray[$staticCount] : e_HTTP;

		}
		else
		{
			$http = e_HTTP_STATIC;
		}

		$this->staticCount(1);

		if(empty($path))
		{
			return $http;
		}

		$base = '';

		$srch = array(
		//
			e_PLUGIN_ABS,
			e_THEME_ABS,
			e_WEB_ABS,
			e_CACHE_IMAGE_ABS,
		);


		$repl = array(

			$http.$base.e107::getFolder('plugins'),
			$http.$base.e107::getFolder('themes'),
			$http.$base.e107::getFolder('web'),
			$http.$base.str_replace('../', '', e_CACHE_IMAGE),
		);

		$ret = str_replace($srch,$repl,$path);

		if(strpos($ret, 'http') !== 0) // if not converted, check media folder also. 
		{
			$ret = str_replace(e_MEDIA_ABS,$http.$base.e107::getFolder('media'),$ret);
		}

		return $ret;

	}


	/**
	 * Generate an auto-sized Image URL.
	 * @param $url - path to image or leave blank for a placeholder. eg. {e_MEDIA}folder/my-image.jpg
	 * @param array $options - width and height, but leaving this empty and using $this->thumbWidth() and $this->thumbHeight() is preferred. ie. {SETWIDTH: w=x&y=x}
	 * @param int $options ['w'] width (optional)
	 * @param int $options ['h'] height (optional)
	 * @param bool|string $options ['crop'] true/false or A(auto) or T(op) or B(ottom) or C(enter) or L(eft) or R(right)
	 * @param string $options ['scale'] '2x' (optional)
	 * @param bool $options ['x'] encode/mask the url parms (optional)
	 * @param bool $options ['nosef'] when set to true disabled SEF Url being returned (optional)
	 * @param bool $raw set to true when the $url does not being with an e107 variable ie. "{e_XXXX}" eg. {e_MEDIA} (optional)
	 * @param bool $full when true returns full http:// url. (optional)
	 * @return string
	 */
	public function thumbUrl($url=null, $options = array(), $raw = false, $full = false)
	{
		$this->staticCount++; // increment counter.

		if(strpos($url,"{e_") === 0) // Fix for broken links that use {e_MEDIA} etc.
		{
			//$url = $this->replaceConstants($url,'abs');	
			// always switch to 'nice' urls when SC is used	
			$url = str_replace($this->getUrlConstants('sc'), $this->getUrlConstants('raw'), $url);	
		}
				
		if(is_string($options))
		{
			parse_str($options, $options);
		}

		if(!empty($options['scale'])) // eg. scale the width height 2x 3x 4x. etc.
		{
			$options['return'] = 'src';
			$options['size'] = $options['scale'];
			unset($options['scale']);
			return $this->thumbSrcSet($url,$options);
		}


		
		if(strstr($url,e_MEDIA) || strstr($url,e_SYSTEM)) // prevent disclosure of 'hashed' path. 
		{
			$raw = true; 	
		}

		if($raw) $url = $this->createConstants($url, 'mix');
		
		$baseurl = ($full ? SITEURL : e_HTTP).'thumb.php?';

		if(defined('e_HTTP_STATIC'))
		{
			$baseurl = $this->staticUrl().'thumb.php?';
		}
        
		$thurl = 'src='.urlencode($url).'&amp;';

	//	e107::getDebug()->log("Thumb: ".basename($url). print_a($options,true), E107_DBG_BASIC);

		if(!empty($options) && (isset($options['w']) || isset($options['aw']) || isset($options['h'])))
		{
			$options['w']       = varset($options['w']);
			$options['h']       = varset($options['h']);
			$options['crop']    = (isset($options['aw']) || isset($options['ah'])) ? 1 : varset($options['crop']);
			$options['aw']      = varset($options['aw']);
			$options['ah']      = varset($options['ah']);
			$options['x']       = varset($options['x']);
		}
		else
		{
			$options['w']       = $this->thumbWidth;
			$options['h']       = $this->thumbHeight;
			$options['crop']    = $this->thumbCrop;
			$options['aw']      = null;
			$options['ah']      = null;
			$options['x']       = $this->thumbEncode;

		}


		if(!empty($options['crop']))
		{
			if(!empty($options['aw']) || !empty($options['ah']))
			{
				$options['w']	= $options['aw'] ;
				$options['h']	= $options['ah'] ;
			}

			$thurl .= 'aw='.intval($options['w']).'&amp;ah='.intval($options['h']);

			if(!is_numeric($options['crop']))
			{
				$thurl .= '&amp;c='.$options['crop'];
				$options['nosef'] = true;
			}

		}
		else
		{

			$thurl .= 'w='.intval($options['w']).'&amp;h='.intval($options['h']);

		}


		if(defined('e_MEDIA_STATIC')) // experimental - subject to change.
		{
			$opts = str_replace('&amp;', '&', $thurl);

			$staticFile = $this->thumbCacheFile($url, $opts);



			if(!empty($staticFile) && is_readable(e_CACHE_IMAGE.$staticFile))
			{
				$staticImg = $this->staticUrl(e_CACHE_IMAGE_ABS.$staticFile);
			//	var_dump($staticImg);
				return $staticImg;
			}

		//	echo "<br />static-not-found: ".$staticFile;

			$options['nosef'] = true;
			$options['x'] = null;
			// file_put_contents(e_LOG."thumb.log", "\n++++++++++++++++++++++++++++++++++\n\n", FILE_APPEND);
		}


		if(e_MOD_REWRITE_MEDIA == true && empty($options['nosef']) )// Experimental SEF URL support.
		{
			$options['full'] = $full;
			$options['ext'] = substr($url,-3);
			$options['thurl'] = $thurl;
		//	$options['x'] = $this->thumbEncode();

			if($sefUrl = $this->thumbUrlSEF($url,$options))
			{
				return $sefUrl;
			}
		}

		if(!empty($options['x'] ))//base64 encode url
		{
			$thurl = 'id='.base64_encode($thurl);
		}

		return $baseurl.$thurl;
	}



	/**
	 * Split a thumb.php url into an array which can be parsed back into the thumbUrl method. .
	 * @param $src
	 * @return array
	 */
	function thumbUrlDecode($src)
	{
		list($url,$qry) = explode("?",$src);

		$ret = array();

		if(strstr($url,"thumb.php") && !empty($qry)) // Regular
		{
			parse_str($qry,$val);
			$ret = $val;
		}
		elseif(preg_match('/media\/img\/(a)?([\d]*)x(a)?([\d]*)\/(.*)/',$url,$match)) // SEF
		{
			$wKey = $match[1].'w';
			$hKey = $match[3].'h';

			$ret = array(
				'src'=> 'e_MEDIA_IMAGE/'.$match[5],
				$wKey => $match[2],
				$hKey => $match[4]
			);
		}
		elseif(preg_match('/theme\/img\/(a)?([\d]*)x(a)?([\d]*)\/(.*)/', $url, $match)) // Theme-image SEF Urls
		{
			$wKey = $match[1].'w';
			$hKey = $match[3].'h';

			$ret = array(
				'src'=> 'e_THEME/'.$match[5],
				$wKey => $match[2],
				$hKey => $match[4]
			);

		}
		elseif(defined('TINYMCE_DEBUG'))
		{
			print_a("thumbUrlDecode: No Matches");

		}


		return $ret;
	}



	/**
	 * Experimental: Generate a Thumb URL for use in the img srcset attribute.
	 * @param string $src eg. {e_MEDIA_IMAGE}myimage.jpg
	 * @param int|str $width - desired size in px or '2x' or '3x' or null for all or array (
	 * @return string
	 */
	function thumbSrcSet($src='', $width=null)
	{
		$multiply = null;

		if(is_array($width))
		{
			$parm = $width;
			$multiply = $width['size'];
			$encode = (!empty($width['x'])) ? $width['x'] : false;
			$width = $width['size'];
		}


	//	$encode =  $this->thumbEncode();;
		if($width == null || $width=='all')
		{
			$links = array();
			$mag = ($width == null) ? array(1, 2) : array(160,320,460,600,780,920,1100);
			foreach($mag as $v)
			{
				$w = ($this->thumbWidth * $v);
				$h =  ($this->thumbHeight * $v);

				$att = (!empty($this->thumbCrop)) ? array('aw' => $w, 'ah' => $h) : array('w' => $w, 'h' => $h);
				$att['x'] = $encode;

				$add = ($width == null) ? " ".$v."x" : " ".$v."w";
				$links[] = $this->thumbUrl($src, $att).$add; // " w".$width; //
			}

			return implode(", ",$links);

		}
		elseif($multiply === '2x' || $multiply === '3x' || $multiply === '4x')
		{

			if(empty($parm['w']) && isset($parm['h']))
			{
				$parm['h'] = ($parm['h'] * $multiply) ;
				return $this->thumbUrl($src, $parm)." ".$parm['h']."h ".$multiply;
			}

			$width = (!empty($parm['w']) || !empty($parm['h'])) ? (intval($parm['w']) * $multiply) : (intval($this->thumbWidth) * $multiply);
			$height = (!empty($parm['h']) || !empty($parm['w'])) ? (intval($parm['h']) * $multiply) : (intval($this->thumbHeight) * $multiply);

		}
		else
		{
			$height = (($this->thumbHeight * $width) / $this->thumbWidth);

		}



		if(!isset($parm['aw']))
		{
			$parm['aw'] = null;
		}

		if(!isset($parm['ah']))
		{
			$parm['ah'] = null;
		}

		if(!isset($parm['x']))
		{
			$parm['x'] = null;
		}

		if(!isset($parm['crop']))
		{
			$parm['crop'] = null;
		}

		$parms = array('w'=>$width,'h'=>$height,'crop'=> $parm['crop'],'x'=>$parm['x'], 'aw'=>$parm['aw'],'ah'=>$parm['ah']);

	//	$parms = !empty($this->thumbCrop) ? array('aw' => $width, 'ah' => $height, 'x'=>$encode) : array('w'  => $width,	'h'  => $height, 'x'=>$encode	);

		// $parms['x'] = $encode;

		if(!empty($parm['return']) && $parm['return'] === 'src')
		{
			return $this->thumbUrl($src, $parms);
		}

		return $this->thumbUrl($src, $parms)." ".$width."w";


	}


	public function thumbUrlScale($src,$parm)
	{



	}

	/**
	 * Used by thumbUrl when SEF Image URLS is active. @see e107.htaccess
	 * @param $url
	 * @param array $options
	 * @return string
	 */
	private function thumbUrlSEF($url='', $options=array())
	{
		if(!empty($options['full']))
		{
			$base = SITEURL;
		}
		else
		{
			$base = (!empty($options['ebase'])) ? '{e_BASE}' : e_HTTP;
		}

		if(defined('e_HTTP_STATIC'))
		{
			$base = $this->staticUrl();
		}
	//	$base = (!empty($options['full'])) ? SITEURL : e_HTTP;

		if(!empty($options['x'])  && !empty($options['ext'])) // base64 encoded. Build URL for:  RewriteRule ^media\/img\/([-A-Za-z0-9+/]*={0,3})\.(jpg|gif|png)?$ thumb.php?id=$1
		{
			$ext = strtolower($options['ext']);
			return $base.'media/img/'.base64_encode($options['thurl']).'.'.str_replace("jpeg", "jpg", $ext);
		}
		elseif(strstr($url, 'e_MEDIA_IMAGE')) // media images.
		{
			$sefPath = 'media/img/';
			$clean = array('{e_MEDIA_IMAGE}','e_MEDIA_IMAGE/');
		}
		elseif(strstr($url, 'e_AVATAR')) // avatars
		{
			$sefPath = 'media/avatar/';
			$clean = array('{e_AVATAR}','e_AVATAR/');
		}
		elseif(strstr($url, 'e_THEME')) // theme folder images.
		{
			$sefPath = 'theme/img/';
			$clean = array('{e_THEME}','e_THEME/');
		}
		else
		{
			// e107::getDebug()->log("SEF URL False: ".$url);
			return false;
		}

		// Build URL for ReWriteRule ^media\/img\/(a)?([\d]*)x(a)?([\d]*)\/(.*)?$ thumb.php?src=e_MEDIA_IMAGE/$5&$1w=$2&$3h=$4
		$sefUrl =  $base.$sefPath;

		if(vartrue($options['aw']) || vartrue($options['ah']))
		{
			$sefUrl .= 'a'.intval($options['aw']) .'xa'. intval($options['ah']);
		}
		elseif(!empty($options['crop']))
		{

			if(!is_numeric($options['crop']))
			{
				$sefUrl .= strtolower($options['crop']).intval($options['w']) .'x'.strtolower($options['crop']). intval($options['h']);
			}
			else
			{
				$sefUrl .= 'a'.intval($options['w']) .'xa'. intval($options['h']);
			}


		}
		else
		{
			$sefUrl .= intval($options['w']) .'x'. intval($options['h']);
		}

		$sefUrl .= '/';
		$sefUrl .= str_replace($clean,'',$url);

		return $sefUrl;

	}

	/**
	 * Help for converting to more safe URLs
	 * e.g. {e_MEDIA_FILE}path/to/video.flv => e_MEDIA_FILE/path/to/video.flv
	 *
	 * @todo support for ALL URL shortcodes (replacement methods)
	 * @param string $type sc|raw|rev|all
	 * @return array
	 */
	public function getUrlConstants($type = 'sc')
	{
		// sub-folders first!
		static $array = array(
			'e_MEDIA_FILE/' 	=> '{e_MEDIA_FILE}',
			'e_MEDIA_VIDEO/' 	=> '{e_MEDIA_VIDEO}',
			'e_MEDIA_IMAGE/' 	=> '{e_MEDIA_IMAGE}',
			'e_MEDIA_ICON/' 	=> '{e_MEDIA_ICON}',
			'e_AVATAR/' 		=> '{e_AVATAR}',
			'e_AVATAR_DEFAULT/' => '{e_AVATAR_DEFAULT}',
			'e_AVATAR_UPLOAD/' => '{e_AVATAR_UPLOAD}',
			'e_WEB_JS/' 		=> '{e_WEB_JS}',
			'e_WEB_CSS/' 		=> '{e_WEB_CSS}',
			'e_WEB_IMAGE/' 		=> '{e_WEB_IMAGE}',
			'e_IMPORT/' 		=> '{e_IMPORT}',
		//	'e_WEB_PACK/' 		=> '{e_WEB_PACK}',

			'e_BASE/' 			=> '{e_BASE}',
			'e_ADMIN/' 			=> '{e_ADMIN}',
			'e_IMAGE/' 			=> '{e_IMAGE}',
			'e_THEME/' 			=> '{e_THEME}',
			'e_PLUGIN/' 		=> '{e_PLUGIN}',
			'e_HANDLER/' 		=> '{e_HANDLER}', // BC
			'e_MEDIA/' 			=> '{e_MEDIA}',
			'e_WEB/' 			=> '{e_ADMIN}',
	//		'THEME/'			=> '{THEME}',
		);
		
		
		switch ($type)
		{
			case 'sc':
				return array_values($array);
			break;

			case 'raw':
				return array_keys($array);
			break;

			case 'rev':
				return array_reverse($array, true);
			break;

			case 'all':
				return $array;
			break;
		}
		return array();
	}


	function getEmotes()
	{
		return $this->e_emote->emotes;		
	}


	/**
	 * Replace e107 path constants
	 * Note: only an ADMIN user can convert {e_ADMIN}
	 * TODO - runtime cache of search/replace arrays (object property) when $mode !== ''
	 * @param string $text
	 * @param string $mode [optional]	abs|full "full" = produce absolute URL path, e.g. http://sitename.com/e107_plugins/etc
	 * 									'abs' = produce truncated URL path, e.g. e107plugins/etc
	 * 									"" (default) = URL's get relative path e.g. ../e107_plugins/etc
	 * @param mixed $all [optional] 	if TRUE, then when $mode is "full" or TRUE, USERID is also replaced...
	 * 									when $mode is "" (default), ALL other e107 constants are replaced
	 * @return string
	 */
	public function replaceConstants($text, $mode = '', $all = FALSE)
	{
		if(is_array($text))
		{
			$new = array();
			foreach($text as $k=>$v)
			{
				$new[$k] = $this->replaceConstants($v,$mode,$all);
			}

			return $new;
		}

		if($mode != "")
		{
			$e107 = e107::getInstance();

			$replace_relative = array(
				$e107->getFolder('media_files'),
				$e107->getFolder('media_video'),
				$e107->getFolder('media_image'),
				$e107->getFolder('media_icon'),
				$e107->getFolder('avatars'),
				$e107->getFolder('web_js'),
				$e107->getFolder('web_css'),
				$e107->getFolder('web_image'),
				//$e107->getFolder('web_pack'),
				e_IMAGE_ABS,
				e_THEME_ABS,
				$e107->getFolder('images'),
				$e107->getFolder('plugins'),
				$e107->getFolder('files'),
				$e107->getFolder('themes'),
			//	$e107->getFolder('downloads'),
				$e107->getFolder('handlers'),
				$e107->getFolder('media'),
				$e107->getFolder('web'),
				$e107->site_theme ? $e107->getFolder('themes').$e107->site_theme.'/' : '',
				defset('THEME_ABS'),
				(ADMIN ? $e107->getFolder('admin') : ''),
				'',
				$e107->getFolder('core'),
				$e107->getFolder('system'),
			);

			switch ($mode)
			{
				case 'abs':
					$replace_absolute = array(
						e_MEDIA_FILE_ABS,
						e_MEDIA_VIDEO_ABS,
						e_MEDIA_IMAGE_ABS,
						e_MEDIA_ICON_ABS,
						e_AVATAR_ABS,
						e_JS_ABS,
						e_CSS_ABS,
						e_WEB_IMAGE_ABS,
				//		e_PACK_ABS,
						e_IMAGE_ABS,
						e_THEME_ABS,
						e_IMAGE_ABS,
						e_PLUGIN_ABS,
						e_FILE_ABS,
						e_THEME_ABS,
				//		e_DOWNLOAD_ABS, //impossible when download is done via php.
						'', // handlers - no ABS path available
						e_MEDIA_ABS,
						e_WEB_ABS,
						defset('THEME_ABS'),
						defset('THEME_ABS'),
						(ADMIN ? e_ADMIN_ABS : ''),
						$e107->server_path,
						'', // no e_CORE absolute path
						'', // no e_SYSTEM absolute path
					);
				break;

				case 'full':
					$replace_absolute = array(
						SITEURLBASE.e_MEDIA_FILE_ABS,
						SITEURLBASE.e_MEDIA_VIDEO_ABS,
						SITEURLBASE.e_MEDIA_IMAGE_ABS,
						SITEURLBASE.e_MEDIA_ICON_ABS,
						SITEURLBASE.e_AVATAR_ABS,
						SITEURLBASE.e_JS_ABS,
						SITEURLBASE.e_CSS_ABS,
						SITEURLBASE.e_WEB_IMAGE_ABS,
				//		SITEURLBASE.e_PACK_ABS,
						SITEURLBASE.e_IMAGE_ABS,
						SITEURLBASE.e_THEME_ABS,
						SITEURLBASE.e_IMAGE_ABS,
						SITEURLBASE.e_PLUGIN_ABS,
						SITEURLBASE.e_FILE_ABS, // deprecated
						SITEURLBASE.e_THEME_ABS,
						//SITEURL.$e107->getFolder('downloads'),
						'', //  handlers - no ABS path available
						SITEURLBASE.e_MEDIA_ABS,
						SITEURLBASE.e_WEB_ABS,
						defset('THEME_ABS') ? SITEURLBASE.THEME_ABS : '',
						defset('THEME_ABS') ? SITEURLBASE.THEME_ABS : '',
						(ADMIN ? SITEURLBASE.e_ADMIN_ABS : ''),
						SITEURL,
						'', // no e_CORE absolute path
						'', // no e_SYSTEM absolute path
					);
				break;
			}
			// sub-folders first!
			$search = array(
				'{e_MEDIA_FILE}',
				'{e_MEDIA_VIDEO}',
				'{e_MEDIA_IMAGE}',
				'{e_MEDIA_ICON}',
				'{e_AVATAR}',
				'{e_WEB_JS}',
				'{e_WEB_CSS}',
				'{e_WEB_IMAGE}',
		//		'{e_WEB_PACK}',
				"{e_IMAGE_ABS}",
				"{e_THEME_ABS}",
				"{e_IMAGE}",
				"{e_PLUGIN}",
				"{e_FILE}",
				"{e_THEME}",
				//,"{e_DOWNLOAD}"
				"{e_HANDLER}",
				"{e_MEDIA}",
				"{e_WEB}",
				"{THEME}",
				"{THEME_ABS}",
				"{e_ADMIN}",
				"{e_BASE}",
				"{e_CORE}",
				"{e_SYSTEM}",
			);

			/*if (ADMIN)
			{
				$replace_relative[] = $e107->getFolder('admin');
				$replace_absolute[] = SITEURL.$e107->getFolder('admin');
				$search[] = "{e_ADMIN}";
			}*/

			if ($all)
			{
				if (USER)
				{  // Can only replace with valid number for logged in users
					$replace_relative[] = USERID;
					$replace_absolute[] = USERID;
				}
				else
				{
					$replace_relative[] = '';
					$replace_absolute[] = '';
				}
				$search[] = "{USERID}";
			}

			// current THEME
			/*if(!defined('THEME'))
			{
				//if not already parsed by doReplace
				$text = str_replace(array('{THEME}', '{THEME_ABS}'), '', $text);
			}
			else
			{
				$replace_relative[] = THEME;
				$replace_absolute[] = THEME_ABS;
				$search[] = "{THEME}";
				$replace_relative[] = THEME;
				$replace_absolute[] = THEME_ABS;
				$search[] = "{THEME_ABS}";
			}*/

			$replace = ((string)$mode == "full" || (string)$mode=='abs' ) ? $replace_absolute : $replace_relative;
			return str_replace($search,$replace,$text);
		}

//		$pattern = ($all ? "#\{([A-Za-z_0-9]*)\}#s" : "#\{(e_[A-Z]*)\}#s");
		$pattern = ($all ? '#\{([A-Za-z_0-9]*)\}#s' : '#\{(e_[A-Z]*(?:_IMAGE|_VIDEO|_FILE|_CONTENT|_ICON|_AVATAR|_JS|_CSS|_PACK|_DB|_ABS){0,1})\}#s');
		$text = preg_replace_callback($pattern, array($this, 'doReplace'), $text);

		if(!defined('THEME'))
		{
			//if not already parsed by doReplace
			$text = str_replace(array('{THEME}', '{THEME_ABS}'), '', $text);
		}
		else
		{
			$srch = array('{THEME}', '{THEME_ABS}');
			$repl = array(THEME, THEME_ABS);
			$text = str_replace($srch, $repl, $text);
		}

		return $text;
	}


	function doReplace($matches)
	{
		if(defined($matches[1]) && (deftrue('ADMIN') || strpos($matches[1], 'ADMIN') === FALSE))
		{
			return constant($matches[1]);
		}
		return $matches[1];
	}

	/**
	 * Create and substitute e107 constants in passed URL
	 *
	 * @param string $url
	 * @param integer $mode 0-folders, 1-relative ('rel'), 2-absolute ('abs'), 3-full ('full') (with domain), 4-absolute & relative ('mix') (combination of 1,2,3)
	 * @return string
	 */
	public function createConstants($url, $mode = 0)
	{
		
		//FIXME - create constants for absolute paths and site URL's
		if (!is_numeric($mode))
		{
			switch ($mode)
			{
				case 'rel' : $mode = 1; break;
				case 'abs' : $mode = 2; break;
				case 'full' : $mode = 3; break;
				case 'mix' : $mode = 4; break;
				case 'nice': $mode = 5; break;
			}
		}
		$e107 = e107::getInstance();
		switch($mode)
		{
			case 0: // folder name only.
				$tmp = array(
					'{e_MEDIA_FILE}'	=> $e107->getFolder('media_files'),
					'{e_MEDIA_VIDEO}'	=> $e107->getFolder('media_videos'),
					'{e_MEDIA_IMAGE}'	=> $e107->getFolder('media_images'),
					'{e_MEDIA_ICON}'	=> $e107->getFolder('media_icons'),
					'{e_AVATAR}'		=> $e107->getFolder('avatars'),
					'{e_WEB_JS}'		=> $e107->getFolder('web_js'),
					'{e_WEB_CSS}'		=> $e107->getFolder('web_css'),
					'{e_WEB_IMAGE}'		=> $e107->getFolder('web_images'),
			//		'{e_WEB_PACK}'		=> $e107->getFolder('web_packs'),

					'{e_IMAGE}' 	=> $e107->getFolder('images'),
					'{e_PLUGIN}'	=> $e107->getFolder('plugins'),
					'{e_FILE}'		=> $e107->getFolder('files'),
					'{e_THEME}'		=> $e107->getFolder('themes'),
					'{e_DOWNLOAD}'	=> $e107->getFolder('downloads'),
					'{e_ADMIN}'		=> $e107->getFolder('admin'),
					'{e_HANDLER}'	=> $e107->getFolder('handlers'),
					'{e_MEDIA}'		=> $e107->getFolder('media'),
					'{e_WEB}'		=> $e107->getFolder('web'),
					'{e_UPLOAD}'	=> $e107->getFolder('uploads'),
					);
					
			break;


			
			case 1: // relative path only
				$tmp = array(
					'{e_MEDIA_FILE}'	=> e_MEDIA_FILE,
					'{e_MEDIA_VIDEO}'	=> e_MEDIA_VIDEO,
					'{e_MEDIA_IMAGE}'	=> e_MEDIA_IMAGE,
					'{e_MEDIA_ICON}'	=> e_MEDIA_ICON,
					'{e_AVATAR}'		=> e_AVATAR,
					'{e_IMPORT}'		=> e_IMPORT,
					'{e_WEB_JS}'		=> e_WEB_JS,
					'{e_WEB_CSS}'		=> e_WEB_CSS,
					'{e_WEB_IMAGE}'		=> e_WEB_IMAGE,
				//	'{e_WEB_PACK}'		=> e_WEB_PACK,

					'{e_IMAGE}'		=> e_IMAGE,
					'{e_PLUGIN}'	=> e_PLUGIN,
					'{e_FILE}'		=> e_FILE,
					'{e_THEME}'		=> e_THEME,
					'{e_DOWNLOAD}'	=> e_DOWNLOAD,
					'{e_ADMIN}'		=> e_ADMIN,
					'{e_HANDLER}'	=> e_HANDLER,
					'{e_MEDIA}'		=> e_MEDIA,
					'{e_WEB}'		=> e_WEB,
					'{e_UPLOAD}'	=> e_UPLOAD,
				);
			break;

			case 2: // absolute path only
				$tmp = array(
					'{e_MEDIA_FILE}'	=> e_MEDIA_FILE_ABS,
					'{e_MEDIA_VIDEO}'	=> e_MEDIA_VIDEO_ABS,
					'{e_MEDIA_IMAGE}'	=> e_MEDIA_IMAGE_ABS,
					'{e_MEDIA_ICON}'	=> e_MEDIA_ICON_ABS,
					'{e_AVATAR}'		=> e_AVATAR_ABS,
					'{e_WEB_JS}'		=> e_JS_ABS,
					'{e_WEB_CSS}'		=> e_CSS_ABS,
					'{e_WEB_IMAGE}'		=> e_WEB_IMAGE_ABS,
			//		'{e_WEB_PACK}'		=> e_PACK_ABS,

					'{e_IMAGE}'		=> e_IMAGE_ABS,
					'{e_PLUGIN}'	=> e_PLUGIN_ABS,
					'{e_FILE}'		=> e_FILE_ABS, // deprecated
					'{e_THEME}'		=> e_THEME_ABS,
					'{e_DOWNLOAD}'	=> e_HTTP.'request.php?',// FIXME - we need solution!
					'{e_ADMIN}'		=> e_ADMIN_ABS,
					//'{e_HANDLER}'	=> e_HANDLER_ABS, - no ABS path available
					'{e_MEDIA}'		=> e_MEDIA_ABS,
					'{e_WEB}'		=> e_WEB_ABS,
					'{e_BASE}'		=> e_HTTP,
				);
			break;

			case 3: // full path (e.g http://domain.com/e107_images/)
				$tmp = array(
					'{e_MEDIA_FILE}'	=> SITEURLBASE.e_MEDIA_FILE_ABS,
					'{e_MEDIA_VIDEO}'	=> SITEURLBASE.e_MEDIA_VIDEO_ABS,
					'{e_MEDIA_IMAGE}'	=> SITEURLBASE.e_MEDIA_IMAGE_ABS,
					'{e_MEDIA_ICON}'	=> SITEURLBASE.e_MEDIA_ICON_ABS,
					'{e_AVATAR}'		=> SITEURLBASE.e_AVATAR_ABS,
					'{e_WEB_JS}'		=> SITEURLBASE.e_JS_ABS,
					'{e_WEB_CSS}'		=> SITEURLBASE.e_CSS_ABS,
					'{e_WEB_IMAGE}'		=> SITEURLBASE.e_WEB_IMAGE_ABS,
			//		'{e_WEB_PACK}'		=> SITEURLBASE.e_PACK_ABS,

					'{e_IMAGE}'		=> SITEURLBASE.e_IMAGE_ABS,
					'{e_PLUGIN}'	=> SITEURLBASE.e_PLUGIN_ABS,
					'{e_FILE}'		=> SITEURLBASE.e_FILE_ABS, // deprecated
					'{e_THEME}'		=> SITEURLBASE.e_THEME_ABS,
					'{e_DOWNLOAD}'	=> SITEURLBASE.e_HTTP.'request.php?',// FIXME - we need solution!
					'{e_ADMIN}'		=> SITEURLBASE.e_ADMIN_ABS,
					//'{e_HANDLER}'	=> e_HANDLER_ABS, - no ABS path available
					'{e_MEDIA}'		=> SITEURLBASE.e_MEDIA_ABS,
					'{e_WEB}'		=> SITEURLBASE.e_WEB_ABS,
					'{e_BASE}'		=> SITEURL,
				);
			break;

			case 4: // absolute & relative paths
				$url = $this->createConstants($url, 3);
				$url = $this->createConstants($url, 2);
				$url = $this->createConstants($url, 1);
				return $url;
			break;

			case 5: // nice urls - e.g. e_MEDIA_VIDEO/mystream.flv
				$url = $this->createConstants($url, 4);
				return str_replace($this->getUrlConstants('sc'), $this->getUrlConstants('raw'), $url);
			break;

			default:
				$tmp = array();
			break;
		}

		$hasCDN = strpos($url, '//') === 0;

		foreach($tmp as $key=>$val)
		{
			// Fix - don't break the CDN '//cdn.com' URLs
			if ($hasCDN && $val === '/') {
				continue;
			}

			$len = strlen($val);
			if(substr($url, 0, $len) == $val)
			{
				// replace the first instance only
				return substr_replace($url, $key, 0, $len);
			}
		}

		return $url;
	}


	//FIXME - $match not used?
	function e_highlight($text, $match)
	{
		$tags = array();
		preg_match_all('#<[^>]+>#', $text, $tags);
		$text = preg_replace('#<[^>]+>#', '<|>', $text);
		$text = preg_replace('#(\b".$match."\b)#i', '<span class="searchhighlight">\\1</span>', $text);
		foreach ($tags[0] as $tag)
		{
			$text = preg_replace('#<\|>#', $tag, $text, 1);
		}
		return $text;
	}

	
	

	/**
	 * Convert Text to a suitable format for use in emails. eg. relative links will be replaced with full links etc. 
	 * @param string $text
	 * @param boolean $posted - if the text has been posted. (uses stripslashes etc)
	 * @param string $mods - flags for text transformation. 
	 */
	public function toEmail($text, $posted = "", $mods = "parse_sc, no_make_clickable")
	{
		if ($posted === TRUE)
		{
			if (MAGIC_QUOTES_GPC)
			{
				$text = stripslashes($text);
			}
			$text = preg_replace('#\[(php)#i', '&#91;\\1', $text);
		}

		$text = (strtolower($mods) != "rawtext") ? $this->replaceConstants($text, "full") : $text;

		if($this->isHtml($text))
		{
			$text = str_replace(array("[html]","[/html]"), "", $text);
			$text = html_entity_decode( $text, ENT_COMPAT, 'UTF-8');
		}
		else
		{

			$text = $this->toHTML($text, true, $mods);
		}

		return $text;
	}



	/**
	 * Given an email address, returns a link including with obfuscated text.
	 * e-email css in e107.css inserts the user/domain data for display.
	 *
	 * @param string $email
	 * @param string $words [optional] text to display
	 * @param null $subject [optional] default subject for email.
	 * @return string
	 */
	function emailObfuscate($email, $words = null, $subject =null)
	{
		if(strpos($email, '@') === false)
		{
			return '';
		}

		if ($subject)
		{
			$subject = '?subject='.$subject;
		}

		list($name, $address) = explode('@', $email, 2);

		if(empty($words))
		{
			$words = "&#64;";
			$user = "data-user='".$this->obfuscate($name)."'";
			$dom =  "data-dom='".$this->obfuscate($address)."'";
		}
		else
		{
			$user = '';
			$dom = '';
		}

		$url = "mailto:".$email.$subject;

		$safe = $this->obfuscate($url);

		return "<a class='e-email' {$user} {$dom} rel='external' href='".$safe."'>".$words.'</a>';
	}



	/**
	 * Obfuscate text from bots using Randomized encoding.
	 * @param $text
	 * @return string
	 */
	public function obfuscate($text)
	{
		$ret = '';
		foreach (str_split($text) as $letter)
		{
			switch (rand(1, 3))
			{
				// HTML entity code
				case 1:
					$ret .= '&#'.ord($letter).';';
				break;

				// Hex character code
				case 2:
					$ret .= '&#x'.dechex(ord($letter)).';';
				break;

				// Raw (no) encoding
				case 3:
					$ret .= $letter;
			}
		}

		return $ret;
	}



	
	public function __get($name)
	{
		switch($name)
		{
			case 'e_sc':
				$ret = e107::getScParser();
			break;


			default:
				trigger_error('$e107->$'.$name.' not defined', E_USER_WARNING);
				return NULL;
			break;
		}


		$this->$name = $ret;
		return $ret;
	}
}


/**
 * New v2 Parser 
 * Start Fresh and Build on it over time to become eventual replacement to e_parse. 
 * Cameron's DOM-based parser. 
 */
class e_parser
{
    /**
     * @var DOMDocument
     */
    public $domObj                = null;
	public $isHtml                  = false;
    protected $removedList        = array();
    protected $nodesToDelete      = array();
    protected $nodesToConvert     = array();
    protected $nodesToDisableSC = array();
    protected $pathList           = array();
    protected $allowedAttributes  = array(
                                    'default'   => array('id', 'style', 'class'),
                                    'img'       => array('id', 'src', 'style', 'class', 'alt', 'title', 'width', 'height'),
                                    'a'         => array('id', 'href', 'style', 'class', 'title', 'target'),
                                    'script'	=> array('type', 'src', 'language', 'async'),
                                    'iframe'	=> array('id', 'src', 'frameborder', 'class', 'width', 'height', 'style'),
	                                'input'     => array('type','name','value','class','style'),
	                                'form'      => array('action','method','target'),
	                                'audio'     => array('src','controls', 'autoplay', 'loop', 'muted', 'preload' ),
	                                'video'     => array('autoplay', 'controls', 'height', 'loop', 'muted', 'poster', 'preload', 'src', 'width'),
	                                'td'        => array('id', 'style', 'class', 'colspan', 'rowspan'),
	                                'th'        => array('id', 'style', 'class', 'colspan', 'rowspan'),
	                                'col'       => array('id', 'span', 'class','style'),
		                            'embed'     => array('id', 'src', 'style', 'class', 'wmode', 'type', 'title', 'width', 'height'),
									'x-bbcode'  => array('alt'),
                                  );

    protected $badAttrValues     = array('javascript[\s]*?:','alert\(','vbscript[\s]*?:','data:text\/html', 'mhtml[\s]*?:', 'data:[\s]*?image');

    protected $replaceAttrValues = array(
        'default' => array()
    );

    protected $allowedTags        = array('html', 'body','div','a','img','table','tr', 'td', 'th', 'tbody', 'thead', 'colgroup', 'b',
                                        'i', 'pre','code', 'strong', 'u', 'em','ul', 'ol', 'li','img','h1','h2','h3','h4','h5','h6','p',
                                        'div','pre','section','article', 'blockquote','hgroup','aside','figure','figcaption', 'abbr','span', 'audio', 'video', 'br',
                                        'small', 'caption', 'noscript', 'hr', 'section', 'iframe', 'sub', 'sup', 'cite', 'x-bbcode'
                                   );
    protected $scriptTags 		= array('script','applet','form','input','button', 'embed', 'object', 'ins', 'select','textarea'); //allowed when $pref['post_script'] is enabled.
	
	protected $blockTags		= array('pre','div','h1','h2','h3','h4','h5','h6','blockquote'); // element includes its own line-break. 


    private $scriptAccess      = false; // nobody.

	/**
	 * e_parser constructor.
	 */
	public function __construct()
    {

		$this->init();
         /*
        $meths = get_class_methods('DomDocument');
        sort($meths);
        print_a($meths);
        */        
    }  

    /**
     * Used by e_parse to start
     */
    function init()
    {
        $this->domObj = new DOMDocument();


    }

	/**
	 * Add Allowed Tags.
	 * @param string
	 */
	public function addAllowedTag($tag)
	{
		$this->allowedTags[] = $tag;
	}


	/**
	 * @param $tag - html tag.
	 * @param $attArray - array of attributes. eg. array('style', 'id', 'class') etc.
	 */
	public function addAllowedAttribute($tag, $attArray)
	{
		$this->allowedAttributes[$tag] = (array) $attArray;
	}


	/**
     * Set Allowed Tags. 
     * @param $array 
     */
    public function setAllowedTags($array=array())
    {
        $this->allowedTags = $array;    
    }

	/**
	 * Set Script Access
	 * @param $val int e_UC_MEMBER, e_UC_NOBODY, e_UC_MAINADMIN or userclass number.
	 */
	public function setScriptAccess($val)
	{
		$this->scriptAccess = $val;
	}

	public function getAllowedTags()
	{
		return $this->allowedTags;

	}


	public function getScriptAccess()
	{
		return $this->scriptAccess;
	}

	/**
     * Set Allowed Attributes. 
     * @param $array 
     */
    public function setAllowedAttributes($array=array())
    {
        $this->allowedAttributes = $array;    
    } 

     /**
     * Set Script Tags.
     * @param $array
     */
    public function setScriptTags($array=array())
    {
        $this->scriptTags = $array;
    }

	/**
	 * Add leading zeros to a number. eg. 3 might become 000003
	 * @param $num integer 
	 * @param $numDigits - total number of digits
	 * @return number with leading zeros. 
	 */
	public function leadingZeros($num,$numDigits)
	{
		return sprintf("%0".$numDigits."d",$num);
	}

	/**
	 * Generic variable translator for LAN definitions.
	 * @param $lan - string LAN
	 * @param string | array $vals - either a single value, which will replace '[x]' or an array with key=>value pairs.
	 * @example $tp->lanVars("My name is [x] and I own a [y]", array('x'=>"John", 'y'=>"Cat"));
	 * @example $tp->lanVars("My name is [x] and I own a [y]", array("John","Cat"));
	 * @return string
	 */
	function lanVars($lan, $vals, $bold=false)
	{
		
		$array = (!is_array($vals)) ? array('x'=>$vals) : $vals;

		$search = array();
		$replace = array();

		$defaults = array('x', 'y', 'z');

		foreach($array as $k=>$v)
		{
			if(is_numeric($k)) // convert array of numeric to x,y,z
			{
				$k = $defaults[$k];
			}

			$search[] = "[".$k."]";
			$replace[] = ($bold===true) ? "<strong>".$v."</strong>" : $v;
		}
		
		return str_replace($search, $replace, $lan);
	}
    
	/**
	 * Return an Array of all specific tags found in an HTML document and their attributes.  
	 * @param $html - raw html code
	 * @param $taglist - comma separated list of tags to search or '*' for all. 
	 * @param $header - if the $html includes the html head or body tags - it should be set to true. 
	 */
	public function getTags($html, $taglist='*', $header = false)
	{
		
		if($header == false)
		{
			$html = "<html><body>".$html."</body></html>";	
		}	
		
		$doc = $this->domObj;   
		       
		$doc->preserveWhiteSpace = true;
		libxml_use_internal_errors(true);
        $doc->loadHTML($html);
	
		$tg = explode(",", $taglist);
		$ret = array();
		
		foreach($tg as $find)
		{
	        $tmp = $doc->getElementsByTagName($find);
			
			 
			foreach($tmp as $k=>$node)
			{
				$tag = $node->nodeName;
				$inner = $node->C14N();
				 $inner = str_replace("&#xD;","",$inner);
				
				foreach ($node->attributes as $attr)
	            {
					$name = $attr->nodeName;
	           		$value = $attr->nodeValue; 
					$ret[$tag][$k][$name] = $value; 
				}
				
				$ret[$tag][$k]['@value'] = $inner; 
				
					
			}
		}
		
		if($header == false)
		{
			unset($ret['html'],$ret['body']);
		}	
		
		
		return $ret;
	}
	
	
	
	/**
	 * Parse xxxxx.glyph file to bootstrap glyph format.
	 * @param string $text
	 * @param array|string $options
	 * @param bool $options['size'] 2x, 3x, 4x, or 5x
	 * @param bool $options['fw'] Fixed-Width
	 * @param bool $options['spin'] Spin
	 * @param int $options['rotate'] Rotate in Degrees.
	 * @example $tp->toGlyph('fa-spinner', 'spin=1');
	 * @example $tp->toGlyph('fa-spinner', array('spin'=>1));
	 * @example $tp->toGlyph('fa-shield', array('rotate'=>90, 'size'=>'2x'));
	 */
	public function toGlyph($text, $options=" ")
	{

		if(empty($text))
		{
			return false;
		}

		if(is_array($options))
		{
			$parm = $options;
			$options = varset($parm['space'],'');
		}
		elseif(strpos($options,'='))
		{
			parse_str($options,$parm);
			$options = varset($parm['space'],'');
		}
		else
		{
			$parm = array();
		}

		if(substr($text,0,2) === 'e-') 	// e107 admin icon.
		{
			$size = (substr($text,-3) === '-32') ? 'S32' : 'S16';

			if(substr($text,-3) === '-24')
			{
				$size = 'S24';
			}

			return "<i class='".$size." ".$text."'></i>";
		}

		// Get Glyph names.
	//	$bs3 = e107::getMedia()->getGlyphs('bs3','');
	//	$fa4 = e107::getMedia()->getGlyphs('fa4','');



		list($id) = explode('.glyph',$text,2);
	//	list($type, $tmp2) = explode("-",$text,2);

	//	return $cls;

	//	$removePrefix = array('glyphicon-','icon-','fa-');

	//	$id = str_replace($removePrefix, "", $cls);


		$spin       = null;
		$rotate     = null;
		$fixedW     = null;
		$prefix     = 'glyphicon glyphicon-'; // fallback
		$size       = null;
		$tag        = 'i';

	//	return print_r($fa4,true);
/*
		if(deftrue('FONTAWESOME') &&  in_array($id ,$fa4)) // Contains FontAwesome 3 set also.
		{
			$prefix = 'fa fa-';
			$size 	= (vartrue($parm['size'])) ?  ' fa-'.$parm['size'] : '';
			$tag 	= 'i';
			$spin   = !empty($parm['spin']) ? ' fa-spin' : '';
			$rotate = !empty($parm['rotate']) ? ' fa-rotate-'.intval($parm['rotate']) : '';
			$fixedW = !empty($parm['fw']) ? ' fa-fw' : "";
		}
		elseif(deftrue("BOOTSTRAP"))
		{
			if(BOOTSTRAP === 3 && in_array($id ,$bs3))
			{
				$prefix = 'glyphicon glyphicon-';
				$tag = 'span';
			}
			else
			{
		//		$prefix = 'icon-';
				$tag = 'i';
			}

			$size = '';

		}
		*/
		if(strpos($text, 'fa-') === 0) // Font-Awesome
		{
			$prefix = 'fa ';
			$size 	= (vartrue($parm['size'])) ?  ' fa-'.$parm['size'] : '';
			$tag 	= 'i';
			$spin   = !empty($parm['spin']) ? ' fa-spin' : '';
			$rotate = !empty($parm['rotate']) ? ' fa-rotate-'.intval($parm['rotate']) : '';
			$fixedW = !empty($parm['fw']) ? ' fa-fw' : "";
		}
		elseif(strpos($text, 'glyphicon-') === 0) // Bootstrap 3
		{
			$prefix = 'glyphicon ';
			$tag = 'span';

		}
		elseif(strpos($text, 'icon-') === 0) // Bootstrap 2
		{
			if(deftrue('BOOTSTRAP') != 2) // bootrap 2 icon but running bootstrap3.
			{
				$prefix = 'glyphicon ';
				$tag = 'span';
				$id = str_replace("icon-", "glyphicon-", $id);
			}
			else
			{
				$prefix = '';
				$tag = 'i';
			}

		}
		elseif($custom = e107::getThemeGlyphs()) // Custom Glyphs
		{
			foreach($custom as $glyphConfig)
			{
				if(strpos($text, $glyphConfig['prefix']) === 0)
				{
					$prefix = $glyphConfig['class'] . " ";
					$tag = $glyphConfig['tag'];
					continue;
				}
			}

		}
		

		$idAtt = (!empty($parm['id'])) ? "id='".$parm['id']."' " : '';
		$style = (!empty($parm['style'])) ? "style='".$parm['style']."' " : '';
		$class = (!empty($parm['class'])) ? $parm['class']." " : '';

		$text = "<".$tag." {$idAtt}class='".$class.$prefix.$id.$size.$spin.$rotate.$fixedW."' {$style}><!-- --></".$tag.">" ;
		$text .= ($options !== false) ? $options : "";

		return $text;


	}


	/**
	 * @param $text
	 * @return string
	 */
	public function toBadge($text)
	{
		return "<span class='badge'>".$text."</span>";
	}


	/**
	 * @param $text
	 * @return string
	 */
	public function toLabel($text, $type = null)
	{
		if($type === null)
		{
			$type = 'default';
		}

		$tmp = explode(",",$text);

		$opt = array();
		foreach($tmp as $v)
		{
			$opt[] = "<span class='label label-".$type."'>".$v."</span>";
		}

		return implode(" ",$opt);
	}

	/**
	 * Take a file-path and convert it to a download link.
	 * @param $text
	 * @return string
	 */
	public function toFile($text, $parm=array())
	{
		$srch = array(
			'{e_MEDIA_FILE}' => 'e_MEDIA_FILE/',
			'{e_PLUGIN}' => 'e_PLUGIN/'
		);

		$link = e_HTTP."request.php?file=". str_replace(array_keys($srch), $srch,$text);

		if(!empty($parm['raw']))
		{
			return $link;
		}

		return "<a href='".$link."'>-attachment-</a>"; //TODO Add pref for this.
	}

	/**
	 * Render an avatar based on supplied user data or current user when missing. 
	 * @param @array  - user data from e107_user. 
	 * @return <img> tag of avatar.  
	 */
	public function toAvatar($userData=null, $options=array())
	{
		$tp 		= e107::getParser();
		$width 		= !empty($options['w']) ? intval($options['w']) : $tp->thumbWidth;
		$height 	= ($tp->thumbHeight !== 0) ? $tp->thumbHeight : "";		
		$linkStart  = '';
		$linkEnd    =  '';

		if(!empty($options['h']))
		{
			$height = intval($options['h']);
		}


		if($userData === null && USERID)
		{
			$userData = array();
			$userData['user_id']    = USERID;
			$userData['user_image']	= USERIMAGE;
			$userData['user_name']	= USERNAME;
			$userData['user_currentvisit'] = USERCURRENTVISIT;
		}

		
		$image = (!empty($userData['user_image'])) ? varset($userData['user_image']) : null;

		$genericImg = $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg","w=".$width."&h=".$height,true);
		
		if (!empty($image)) 
		{
			
			if(strpos($image,"://")!==false) // Remove Image
			{
				$img = $image;	
			}
			elseif(substr($image,0,8) == "-upload-")
			{
				
				$image = substr($image,8); // strip the -upload- from the beginning. 
				$img = (file_exists(e_AVATAR_UPLOAD.$image))  ? $tp->thumbUrl(e_AVATAR_UPLOAD.$image,"w=".$width."&h=".$height) : $genericImg;
			}
			elseif(file_exists(e_AVATAR_DEFAULT.$image))  // User-Uplaoded Image
			{
				$img =	$tp->thumbUrl(e_AVATAR_DEFAULT.$image,"w=".$width."&h=".$height);		
			}
			else // Image Missing. 
			{
				
				$img = $genericImg;
			}
		}
		else // No image provided - so send generic. 
		{
			$img = $genericImg;
		}

		if(($img == $genericImg) && !empty($userData['user_id'] ) && (($userData['user_id'] == USERID)) && !empty($options['link']))
		{
			$linkStart = "<a class='e-tip' title=\"".LAN_EDIT."\" href='".e107::getUrl()->create('user/myprofile/edit')."'>";
			$linkEnd = "</a>";
		}
		
		$title = (ADMIN) ? $image : $tp->toAttribute($userData['user_name']);
		$shape = (!empty($options['shape'])) ? "img-".$options['shape'] : "img-rounded rounded";


		if(!empty($options['type']) && $options['type'] === 'url')
		{
			return $img;
		}


		$heightInsert = empty($height) ? '' : "height='".$height."'";
		$id = (!empty($options['id'])) ? "id='".$options['id']."' " : "";

		$classOnline = (!empty($userData['user_currentvisit']) && intval($userData['user_currentvisit']) > (time() - 300)) ? " user-avatar-online" : '';

		$class = !empty($options['class']) ? $options['class'] : $shape." user-avatar";

		$text = $linkStart;
		$text .= "<img ".$id."class='".$class.$classOnline."' alt=\"".$title."\" src='".$img."'  width='".$width."' ".$heightInsert." />";
		$text .= $linkEnd;
	//	return $img;
		return $text;
		
	}


	
	/**
	 * Display an icon. 
	 * @param string $icon 
	 * @example $tp->toIcon("{e_IMAGES}icons/something.png"); 
	 */
	public function toIcon($icon='',$parm = array())
	{

		if(empty($icon))
		{
			return null;
		}

		if(strpos($icon,'e_MEDIA_IMAGE')!==false)
		{
		//	return "<div class='alert alert-danger'>Use \$tp->toImage() instead of toIcon() for ".$icon."</div>"; // debug info only.
		}

		if(substr($icon,0,3) == '<i ') // if it's html (ie. css sprite) return the code.
		{
			return $icon;
		}
				
		$ext = pathinfo($icon, PATHINFO_EXTENSION);
		$dimensions = null;
		
		if(!$ext || $ext == 'glyph') // Bootstrap or Font-Awesome. 
		{
			return $this->toGlyph($icon,$parm);
		}
		
		if(strpos($icon,'e_MEDIA')!==FALSE)
		{
			$path = $this->thumbUrl($icon);
			$dimensions = $this->thumbDimensions();
		}
		elseif($icon[0] === '{')
		{
			$path = $this->replaceConstants($icon,'full');		
		}
		elseif(!empty($parm['legacy']))
		{
			$legacyList = (!is_array($parm['legacy'])) ? array($parm['legacy']) : $parm['legacy'];

			foreach($legacyList as $legPath)
			{
				$legacyPath = $legPath.$icon;
				$filePath = $this->replaceConstants($legacyPath);

				if(is_readable($filePath))
				{
					$path = $this->replaceConstants($legacyPath,'full');
					break;
				}

			}

			if(empty($path))
			{
				$log = e107::getAdminLog();
				$log->addDebug('Broken Icon Path: '.$icon."\n".print_r(debug_backtrace(null,2), true), false)->save('IMALAN_00');
				e107::getDebug()->log('Broken Icon Path: '.$icon);
				return null;
			}
			
		}
		else 
		{
			$path = $icon;
		}


		$alt = (!empty($parm['alt'])) ? $this->toAttribute($parm['alt']) : basename($path);
		$class = (!empty($parm['class'])) ? $parm['class'] : 'icon';
		
		return "<img class='".$class."' src='".$path."' alt='".$alt."' ".$dimensions." />";
	}


	/**
	 * Render an <img> tag.
	 * @param string $file
	 * @param array $parm  legacy|w|h|alt|class|id|crop
	 * @param array $parm['legacy'] Usually a legacy path like {e_FILE}
	 * @return string
	 * @example $tp->toImage('welcome.png', array('legacy'=>{e_IMAGE}newspost_images/','w'=>200));
	 */
	public function toImage($file, $parm=array())
	{

		if(strpos($file,'e_AVATAR')!==false)
		{
			return "<div class='alert alert-danger'>Use \$tp->toAvatar() instead of toImage() for ".$file."</div>"; // debug info only.

		}

		if(empty($file) && empty($parm['placeholder']))
		{
			return null;
		}

		if(!empty($file))
		{
			$srcset     = null;
			$path       = null;
			$file       = trim($file);
			$ext        = pathinfo($file, PATHINFO_EXTENSION);
			$accepted   = array('jpg','gif','png','jpeg');


			if(!in_array($ext,$accepted))
			{
				return null;
			}
		}

		$tp  = $this;

	//		e107::getDebug()->log($file);
	//	e107::getDebug()->log($parm);

		if(strpos($file,'http')===0)
		{
			$path = $file;
		}
		elseif(strpos($file,'e_MEDIA')!==false || strpos($file,'e_THEME')!==false || strpos($file,'e_PLUGIN')!==false || strpos($file,'{e_IMAGE}')!==false) //v2.x path.
		{

			if(!isset($parm['w']) && !isset($parm['h']))
			{
				$parm['w']      = $tp->thumbWidth();
				$parm['h']      = $tp->thumbHeight();
				$parm['crop']   = $tp->thumbCrop();
				$parm['x']      = $tp->thumbEncode();
			}

			unset($parm['src']);
			$path = $tp->thumbUrl($file,$parm);


			if(empty($parm['w']) && empty($parm['h']))
			{
				$parm['srcset'] = false;
			}
			elseif(!isset($parm['srcset']))
			{
				$srcSetParm = $parm;
				$srcSetParm['size'] = ($parm['w'] < 100) ? '4x' : '2x';
				$parm['srcset'] = $tp->thumbSrcSet($file, $srcSetParm);
			}

		}
		elseif($file[0] === '{') // Legacy v1.x path. Example: {e_PLUGIN}myplugin/images/fixedimage.png
		{
			$path = $tp->replaceConstants($file,'abs');
		}
		elseif(!empty($parm['legacy'])) // Search legacy path for image in a specific folder. No path, only file name provided.
		{

			$legacyPath = rtrim($parm['legacy'],'/').'/'.$file;
			$filePath = $tp->replaceConstants($legacyPath);

			if(is_readable($filePath))
			{
				$path = $tp->replaceConstants($legacyPath,'abs');
			}
			else
			{
				$log = e107::getAdminLog();
				$log->addDebug('Broken Image Path: '.$legacyPath."\n".print_r(debug_backtrace(null,2), true), false)->save('IMALAN_00');
				e107::getDebug()->log("Broken Image Path: ".$legacyPath);
			}

		}
		else // usually http://....
		{
			$path = $file;
		}

		if(empty($path) && !empty($parm['placeholder']))
		{
			$path = $tp->thumbUrl($file,$parm);
		}

		$id     = (!empty($parm['id']))     ? "id=\"".$parm['id']."\" " :  ""  ;
		$class  = (!empty($parm['class']))  ? $parm['class'] : "img-responsive img-fluid";
		$alt    = (!empty($parm['alt']))    ? $tp->toAttribute($parm['alt']) : basename($file);
		$style  = (!empty($parm['style']))  ? "style=\"".$parm['style']."\" " :  ""  ;
		$srcset = (!empty($parm['srcset'])) ? "srcset=\"".$parm['srcset']."\" " : "";
		$width  = (!empty($parm['w']))      ? "width=\"".intval($parm['w'])."\" " : "";
		$height = (!empty($parm['h']))      ? "height=\"".intval($parm['h'])."\" " : "";

		return "<img {$id}class='{$class}' src='".$path."' alt=\"".$alt."\" ".$srcset.$width.$height.$style." />";

	}


	/**
	 * Check if a string contains bbcode.
	 * @param $text
	 * @return bool
	 */
	function isBBcode($text)
	{
		if(preg_match('#(?<=<)\w+(?=[^<]*?>)#', $text))
		{
			return false;
		}

		$bbsearch = array('[/img]','[/h]', '[/b]', '[/link]', '[/right]', '[/center]', '[/flash]', '[/code]', '[/table]');

		foreach($bbsearch as $v)
		{
			if(strpos($text,$v)!==false)
			{
				return true;
			}

		}

		return false;


	}


	/**
	 * Check if a string is HTML
	 * @param $text
	 * @return bool
	 */
	function isHtml($text)
	{

		if(strpos($text,'[html]'))
		{
			return true;
		}

		if($this->isBBcode($text))
		{
			return false;
		}

		if(preg_match('#(?<=<)\w+(?=[^<]*?>)#', $text))
		{
			return true;
		}

		return false;


	}


	/**
	 * Check if string is json and parse or return false.
	 * @param $text
	 * @return bool|mixed return false if not json, and json values if true.
	 */
	public function isJSON($text)
	{
		if(!is_string($text))
		{
			return false;
		}

		 if(substr($text,0,1) === '{' || substr($text,0,1) === '[') // json
	    {
	        $dat = json_decode($text, true);

	        if(json_last_error() !=  JSON_ERROR_NONE)
	        {
		        //   e107::getDebug()->log("Json data found");
	           return false;
	        }

	        return $dat;
	    }

		return false;

	}



	/**
	 * Checks if string is valid UTF-8.
	 *
	 * Try to detect UTF-8 using mb_detect_encoding(). If mb string extension is
	 * not installed, we try to use a simple UTF-8-ness checker using a regular
	 * expression originally created by the W3C. But W3C's function scans the
	 * entire strings and checks that it conforms to UTF-8.
	 *
	 * @see http://w3.org/International/questions/qa-forms-utf-8.html
	 *
	 * So this function is faster and less specific. It only looks for non-ascii
	 * multibyte sequences in the UTF-8 range and also to stop once it finds at
	 * least one multibytes string. This is quite a lot faster.
	 *
	 * @param $string string  string being checked.
	 * @return bool  Returns true if $string is valid UTF-8 and false otherwise.
	 */
	public function isUTF8($string)
	{
		if (function_exists('mb_check_encoding'))
		{
			return (mb_check_encoding($string, 'UTF-8'));
		}

		return (bool) preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);

	}






	/**
	 * Check if a file is an video or not. 
	 * @param $file string
	 * @return boolean
	 */
	function isVideo($file)
	{
		$ext = pathinfo($file,PATHINFO_EXTENSION);
			
		return ($ext === 'youtube' || $ext === 'youtubepl') ? true : false;
		
	}

	/**
	 * Check if a file is an image or not.
	 * @param $file string
	 * @return boolean
	 */
	function isImage($file)
	{
		if(substr($file,0,3)=="{e_")
		{
			$file = e107::getParser()->replaceConstants($file);
		}


		$ext = pathinfo($file,PATHINFO_EXTENSION);

		return ($ext === 'jpg' || $ext === 'png' || $ext === 'gif' || $ext === 'jpeg') ? true : false;
	}

	
	/**
	 * Display a Video file. 
	 * @param string $file - format: id.type eg. x123dkax.youtube 
	 * @param boolean $thumbnail  - set to 'tag' to return an image thumbnail and 'src' to return the src url or 'video' for a small video thumbnail. 
	 */
	function toVideo($file, $parm=array())
	{
		if(empty($file))
		{
			return false;
		}

		list($id,$type) = explode(".",$file,2);

		$thumb = vartrue($parm['thumb']);


		$pref = e107::getPref();
		$ytpref = array();
		foreach($pref as $k=>$v) // Find all Youtube Prefs. 
		{
			if(substr($k,0,8) === 'youtube_')
			{
				$key = substr($k,8);
				$ytpref[$key] = $v;
			}	
		}

		unset($ytpref['bbcode_responsive']); // do not include in embed code.

		if(!empty($ytpref['cc_load_policy']))
		{
			$ytpref['cc_lang_pref'] = e_LAN; // switch captions with chosen user language.
		}

		$ytqry = http_build_query($ytpref);

		$defClass = (deftrue('BOOTSTRAP')) ? "embed-responsive embed-responsive-16by9" : "video-responsive"; // levacy backup.


		if($type === 'youtube')
		{
		//	$thumbSrc = "https://i1.ytimg.com/vi/".$id."/0.jpg";
			$thumbSrc = "https://i1.ytimg.com/vi/".$id."/mqdefault.jpg";
			$video =  '<iframe class="embed-responsive-item" width="560" height="315" src="//www.youtube.com/embed/'.$id.'?'.$ytqry.'" style="background-size: 100%;background-image: url('.$thumbSrc.');border:0px" allowfullscreen></iframe>';

		
			if($thumb === 'tag')
			{
				return "<img class='img-responsive img-fluid' src='".$thumbSrc."' alt='Youtube Video' style='width:".vartrue($parm['w'],'80')."px'/>";
			}
			
			if($thumb === 'email')
			{
				$thumbSrc = "http://i1.ytimg.com/vi/".$id."/maxresdefault.jpg"; // 640 x 480
				$filename = 'temp/yt-thumb-'.md5($id).".jpg";
				$filepath = e_MEDIA.$filename;
				$url 	= 'http://youtu.be/'.$id;
				
				if(!file_exists($filepath))
				{
					e107::getFile()->getRemoteFile($thumbSrc, $filename,'media');	
				}
								
				return "<a href='".$url."'><img class='video-responsive video-thumbnail' src='{e_MEDIA}".$filename."' alt='".LAN_YOUTUBE_VIDEO."' title='".LAN_CLICK_TO_VIEW."' />
				<div class='video-thumbnail-caption'><small>".LAN_CLICK_TO_VIEW."</small></div></a>";
			}
			
			if($thumb === 'src')
			{
				return $thumbSrc;
			}


			
			if($thumb === 'video')
			{
				return '<div class="'.$defClass.' video-thumbnail thumbnail">'.$video.'</div>';
			}
			
			return '<div class="'.$defClass.' '.vartrue($parm['class']).'">'.$video.'</div>';
		}


		if($type === 'youtubepl')
		{

			if($thumb === 'tag')
			{
				$thumbSrc =  e107::getMedia()->getThumb($id);

				if(empty($thumbSrc))
				{
					$thumbSrc = e_IMAGE_ABS."generic/playlist_120.png";
				}
				return "<img class='img-responsive img-fluid' src='".$thumbSrc."' alt='".LAN_YOUTUBE_PLAYLIST."' style='width:".vartrue($parm['w'],'80')."px'/>";

			}

			if($thumb === 'src')
			{
				$thumb = e107::getMedia()->getThumb($id);
				if(!empty($thumb))
				{
					return $thumb;
				}
				else
				{
					// return "https://cdn0.iconfinder.com/data/icons/internet-2-2/64/youtube_playlist_videos_vid_web_online_internet-256.png";
					return e_IMAGE_ABS."generic/playlist_120.png";
				}
			}

			$video = '<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list='.$id.'" style="border:0" allowfullscreen></iframe>';
			return '<div class="'.$defClass.' '.vartrue($parm['class']).'">'.$video.'</div>';
		}
				
		if($type === 'mp4') //TODO FIXME
		{
			return '
			<div class="video-responsive">
			<video width="320" height="240" controls>
			  <source src="'.$file.'" type="video/mp4">
		
			  Your browser does not support the video tag.
			</video>
			</div>';	
		}
		
		
		
		return false;
	}
	
	
	
	/**
	 * Display a Date in the browser. 
	 * Includes support for 'livestamp' (http://mattbradley.github.io/livestampjs/)
	 * @param integer $datestamp - unix timestamp
	 * @param string $format - short | long | relative 
	 * @return HTML with converted date. 
	 */
	public function toDate($datestamp = null, $format='short')
	{
		if(!is_numeric($datestamp)){ return null; }

		return '<span data-livestamp="'.$datestamp.'">'.e107::getDate()->convert($datestamp, $format).'</span>';	
	}
	

		
	
	
	
	/** 
	 * Parse new <x-bbcode> tags into bbcode output.
	 * @param bool $retainTags : when you want to replace html and retain the <bbcode> tags wrapping it.
	 * @return string html
	 */
	function parseBBTags($text,$retainTags = false)
	{
		$stext = str_replace("&quot;", '"', $text);

		$bbcodes = $this->getTags($stext, 'x-bbcode');

		foreach($bbcodes as $v)
		{
			foreach($v as $val)
			{
				$tag = base64_decode($val['alt']);
				$repl = ($retainTags == true) ? '$1'.$tag.'$2' : $tag;
			//	$text = preg_replace('/(<x-bbcode[^>]*>).*(<\/x-bbcode>)/i',$repl, $text);
				$text = preg_replace('/(<x-bbcode alt=(?:&quot;|")'.$val['alt'].'(?:&quot;|")>).*(<\/x-bbcode>)/i',$repl, $text);

			}	
		}

		return $text;
	}



    /**
     * Perform and render XSS Test Comparison
     */
    public function test($text='',$advanced = false)
    {
      //  $tp = e107::getParser();
        $sql = e107::getDb();
        $tp = e107::getParser();

	    if(empty($text))
	    {
		    $text = <<<TMPL
[html]<p><strong>bold print</strong></p>
<pre class="prettyprint linenums">&lt;a href='#'&gt;Something&lt;/a&gt;</pre>
<p>Some text's and things.</p>
<p>&nbsp;</p>
<p><a href="/test.php?w=9&amp;h=12">link</a></p>
<p>日本語 简体中文</p>
<p>&nbsp;</p>
[/html]
TMPL;
	    }

	    //   $text .= '[code=inline]<b class="something">Something</b>[/code]日本語 ';

        // -------------------- Encoding ----------------

		$acc = $this->getScriptAccess();
		$accName = e107::getUserclass()->uc_get_classname($acc);

		echo "<h2>e107 Parser Test <small>with script access by <span class='label label-warning'>".$accName."</span></small></h2>";
		echo"<h3>User-input <small>(eg. from \$_POST)</small></h3>";

	    print_a($text);

	    $dbText = $tp->toDB($text,true);

		echo "<h3>User-input &gg; toDB() ";

		if($this->isHtml == true)
		{
			echo "<small>detected as <span class='label label-warning'>HTML</span></small>";
		}
		else
		{
			echo "<small>detected as <span class='label label-info'>Plain text</span></small>";
		}

		echo "</h3>";

	    print_a($dbText);


	    if(!empty($advanced))
	    {
			echo "<div class='alert alert-warning'>";
		    $dbText2 = $tp->toDB($text, true, false, 'no_html');
		    echo "<h3>User-input &gg; toDb(\$text, true, false, 'no_html')</h3>";
		    print_a($dbText2);

		    echo "<div class='alert alert-warning'>";
		    $dbText3 = $tp->toDB($text, false, false, 'pReFs');
		    echo "<h3>User-input &gg; toDb(\$text, false, false, 'pReFs')</h3>";
		    print_a($dbText3);

		   // toClean
		    $filter3 = $tp->filter($text, 'wds');
		    echo "<h3>User-input &gg; filter(\$text, 'wds')</h3>";
		    print_a( $filter3);

		    // Filter by String.
		    $filter1 = $tp->filter($text,'str');
		    echo "<h3>User-input &gg; filter(\$text, 'str')</h3>";
		    print_a($filter1);

		    // Filter by Encoded.
		    $filter2 = $tp->filter($text,'enc');
		    echo "<h3>User-input &gg; filter(\$text, 'enc')</h3>";
		    print_a($filter2);


		    // toAttribute
		    $toAtt = $tp->toAttribute($text);
		    echo "<h3>User-input &gg; toAttribute(\$text)</h3>";
		    print_a($toAtt);

		    // toEmail
		    $toEmail = $tp->toEmail($dbText);
		    echo "<h3>User-input &gg; toEmail(\$text) <small>from DB</small></h3>";
		    print_a($toEmail);

		    // toEmail
		    $toRss = $tp->toRss($text);
		    echo "<h3>User-input &gg; toRss(\$text)</h3>";
		    print_a($toRss);

		    echo "</div>";



	    }

	    echo "<h3>toDB() &gg; toHtml()</h3>";
		$html = $tp->toHtml($dbText,true);
	    print_a($html);

	    echo "<h3>toDB &gg; toHtml() <small>(rendered)</small></h3>";
	    echo $html;

	    echo "<h3>toDB &gg; toForm()</h3>";
		$toForm = $tp->toForm($dbText);
	    $toFormRender = e107::getForm()->open('test');
	    $toFormRender .= "<textarea cols='100' style='width:100%;height:300px' >".$toForm."</textarea>";
	    $toFormRender .= e107::getForm()->close();

		echo  $toFormRender;


		 echo "<h3>toDB &gg; bbarea</h3>";
	    echo e107::getForm()->bbarea('name',$toForm);

		if(!empty($advanced))
		{

			echo "<h3>Allowed Tags</h3>";
			print_a($this->allowedTags);


		    echo "<h3>Converted Paths</h3>";
		    print_a($this->pathList);

		    echo "<h3>Removed Tags and Attributes</h3>";
		    print_a($this->removedList);

		    echo "<h3>Nodes to Convert</h3>";
			print_a($this->nodesToConvert);

			  echo "<h3>Nodes to Disable SC</h3>";
			print_a($this->nodesToDisableSC);
		}

	    similar_text($text, html_entity_decode( $toForm, ENT_COMPAT, 'UTF-8'),$perc);
	    $scoreStyle = ($perc > 98) ? 'label-success' : 'label-danger';
	    echo "<h3><span class='label ".$scoreStyle."'>Similarity:  ".number_format($perc)."%</span></h3>";

		echo "<table class='table table-bordered'>


		<tr>
			<th style='width:50%'>User-input</th>
			<th style='width:50%'>toForm() output</th>
		</tr>
		<tr>
			<td>".print_a($text,true)."</td>
			<td>". $toFormRender."</td>
		</tr>

		</table>";
	  /*  <tr>
			<td>".print_a(json_encode($text),true)."</td>
			<td>". print_a(json_encode(html_entity_decode( $toForm, ENT_COMPAT, 'UTF-8')),true)."</td>
		</tr>*/

	//    print_a($text);

return;

//return;
        // ---------------------------------


		$html = $text;

        
      //  $html = $this->getXss();
                   
        echo "<h2>Unprocessed XSS</h2>";
        // echo $html; // Remove Comment for a real mess! 
        print_a($html);
 
        echo "<h2>Standard v2 Parser</h2>";
        echo "<h3>\$tp->dataFilter()</h3>";
        // echo $tp->dataFilter($html); // Remove Comment for a real mess! 
        $sql->db_Mark_Time('------ Start Parser Test -------');
        print_a($tp->dataFilter($html));
        $sql->db_Mark_Time('tp->dataFilter');
         
        echo "<h3>\$tp->toHtml()</h3>";
        // echo $tp->dataFilter($html); // Remove Comment for a real mess! 
        print_a($tp->toHTML($html));
        $sql->db_Mark_Time('tp->toHtml');     
        
        echo "<h3>\$tp->toDB()</h3>";
        // echo $tp->dataFilter($html); // Remove Comment for a real mess!
        $todb = $tp->toDB($html);
        print_a( $todb);
        $sql->db_Mark_Time('tp->toDB');

	    echo "<h3>\$tp->toForm() with toDB input.</h3>";
       print_a( $tp->toForm($todb));
        
        echo "<h2>New Parser</h2>"; 
        echo "<h3>Processed</h3>";
        $cleaned = $this->cleanHtml($html, true);  // false = don't check html pref.
        print_a($cleaned);
        $sql->db_Mark_Time('new Parser');    
      //  $sql->db_Mark_Time('------ End Parser Test -------');
        echo "<h3>Processed &amp; Rendered</h3>";
        echo $cleaned;
        
        echo "<h2>New Parser - Data</h2>"; 
        echo "<h3>Converted Paths</h3>";
        print_a($this->pathList);
                   
        echo "<h3>Removed Tags and Attributes</h3>";
        print_a($this->removedList);
        
         //   print_a($p); 
    }



	/**
	 * Filters/Validates using the PHP5 filter_var() method.
	 * @param $text
	 * @param $type string str|int|email|url|w|wds|file
	 * @return string | boolean | array
	 */
	function filter($text, $type='str',$validate=false)
	{
		if(empty($text))
		{
			return $text;
		}

		if($type === 'w') // words only.
		{
			return preg_replace('/[^\w]/',"",$text);
		}

		if($type === 'wds') // words, digits and spaces only.
		{
			return preg_replace('/[^\w\d ]/',"",$text);
		}

		if($type === 'file')
		{
			return preg_replace('/[^\w\d_\.-]/',"",$text);
		}


		if($validate == false)
		{
			$filterTypes = array(
				'int'   => FILTER_SANITIZE_NUMBER_INT,
				'str'   => FILTER_SANITIZE_STRING, // no html.
				'email' => FILTER_SANITIZE_EMAIL,
				'url'   => FILTER_SANITIZE_URL,
				'enc'   => FILTER_SANITIZE_ENCODED
			);
		}
		else
		{
			$filterTypes = array(
				'int'   => FILTER_VALIDATE_INT,
				'email' => FILTER_VALIDATE_EMAIL,
				'ip'    => FILTER_VALIDATE_IP,
				'url'   => FILTER_VALIDATE_URL,

			);
		}

		if(is_array($text))
		{
			return filter_var_array($text, $filterTypes[$type]);
		}


		return filter_var($text, $filterTypes[$type]);

	}


    /**
     * Process and clean HTML from user input.
     * TODO Html5 tag support.
     * @param string $html raw HTML
     * @param boolean $checkPref
     * @return string
     */
    public function cleanHtml($html='', $checkPref = true)
    {
        if(empty($html)){ return ''; }

		$html = str_replace('&nbsp;', '@nbsp;', $html); // prevent replacement of &nbsp; with spaces.


        if(strpos($html, "<body")===false) // HTML Fragment
		{
       		$html = '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>'; 
		}
		else  // Full HTML page. 
		{
		//	$this->allowedTags[] = 'head';
		//	$this->allowedTags[] = 'body';
		//	$this->allowedTags[] = 'title';
			//$this->allowedTags[] = 'meta';
		}
         
		if(!is_object($this->domObj))
		{
			$this->init();	
		}

		if($this->scriptAccess === false)
		{
	        $this->scriptAccess = e107::getConfig()->get('post_script', e_UC_MAINADMIN); // Pref to Allow <script> tags11;
		}

		if(check_class($this->scriptAccess))
        {
            $this->allowedTags = array_merge($this->allowedTags, $this->scriptTags);
        }

		
        // Set it up for processing.
	//    libxml_use_internal_errors(true); // hides errors.
        $doc  = $this->domObj;
	    libxml_use_internal_errors(true);
    //    @$doc->loadHTML($html);
	    if(function_exists('mb_convert_encoding'))
	    {
			$html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
	    }

		@$doc->loadHTML($html);

		// $doc->encoding = 'UTF-8';

     //   $doc->resolveExternals = true;
        
    //    $tmp = $doc->getElementsByTagName('*');   
    
       	$this->nodesToConvert 	= array(); // required. 
		$this->nodesToDelete 	= array(); // required. 
		$this->removedList		= array();

		$tmp = $doc->getElementsByTagName('*');

        /** @var DOMElement $node */
        foreach($tmp as $node)
        {
            $path = $node->getNodePath();

		//	echo "<br />Path = ".$path;
        //   $tag = strval(basename($path));


	        if(strpos($path,'/code') !== false || strpos($path,'/pre') !== false) //  treat as html.
            {
                $this->pathList[] = $path;
            //     $this->nodesToConvert[] =  $node->parentNode; // $node;
                $this->nodesToDisableSC[] = $node;
                continue;
            }


            $tag = preg_replace('/([a-z0-9\[\]\/]*)?\/([\w\-]*)(\[(\d)*\])?$/i', "$2", $path);
            if(!in_array($tag, $this->allowedTags))
            {

                $this->removedList['tags'][] = $tag;
                $this->nodesToDelete[] = $node; 
                continue;
            }

            foreach ($node->attributes as $attr)
            {
                $name = $attr->nodeName;
                $value = $attr->nodeValue;

                $allow = varset($this->allowedAttributes[$tag], $this->allowedAttributes['default']);
                $removeAttributes = array();

                if(!in_array($name, $allow))
                {

                    if(strpos($name,'data-') === 0 && $this->scriptAccess == true)
                    {
                        continue;
                    }

                    $removeAttributes[] = $name;
                    //$node->removeAttribute($name);
                    $this->removedList['attributes'][] = $name. " from <".$tag.">";
                    continue;
                }

                if($this->invalidAttributeValue($value)) // Check value against blacklisted values.
                {
					//$node->removeAttribute($name);
                    $node->setAttribute($name, '#---sanitized---#');
					$this->removedList['sanitized'][] = $tag.'['.$name.']';    
                }
                else
                {
                    $_value = $this->secureAttributeValue($name, $value);

                    $node->setAttribute($name, $_value);
                    if($_value !== $value)
                    {
                        $this->removedList['sanitized'][] = $tag.'['.$name.'] converted "'.$value.'" -> "'.$_value.'"';
                    }
                }
            }

            // required - removing attributes in a loop breaks the loop
            if(!empty($removeAttributes))
            {
	            foreach ($removeAttributes as $name)
	            {
	                $node->removeAttribute($name);
	            }
            }


        }
        
        // Remove some stuff. 
        foreach($this->nodesToDelete as $node)
        {
            $node->parentNode->removeChild($node);
        }  

		// Disable Shortcodes in pre/code

       foreach($this->nodesToDisableSC as $key => $node)
       {
		    $value = $node->C14N();

		    if(empty($value))
		    {
		        continue;
		    }

		    $value = str_replace("&#xD;", "\r", $value);

		    if($node->nodeName === 'pre')
		    {
		        $value = preg_replace('/^<pre[^>]*>/', '', $value);
		        $value = str_replace("</pre>", "", $value);
		        $value = str_replace('<br></br>', PHP_EOL, $value);

		    }

		    if($node->nodeName === 'code')
		    {
		        $value = preg_replace('/^<code[^>]*>/', '', $value);
		        $value = str_replace("</code>", "", $value);
		        $value = str_replace("<br></br>", PHP_EOL, $value);
		    }

		    $value = str_replace('{', '{{{', $value); // temporarily change {e_XXX} to {{{e_XXX}}}
		    $value = str_replace('}', '}}}', $value); // temporarily change {e_XXX} to {{{e_XXX}}}

		    $newNode = $doc->createElement($node->nodeName);
		    $newNode->nodeValue = $value;

		    if($class = $node->getAttribute('class'))
		    {
		        $newNode->setAttribute('class',$class);
		    }

	        if($style = $node->getAttribute('style'))
		    {
		        $newNode->setAttribute('style',$style);
		    }

		    $node->parentNode->replaceChild($newNode, $node);
       }



        // Convert <code> and <pre> Tags to Htmlentities.
        /* TODO XXX Still necessary? Perhaps using bbcodes only?
        foreach($this->nodesToConvert as $node)  
        {
            $value = $node->C14N();

            $value = str_replace("&#xD;","",$value);

        //    print_a("WOWOWO");
            
            if($node->nodeName == 'pre')
            {
                $value = substr($value,5);
                $end = strrpos($value,"</pre>");
                $value = substr($value,0,$end);
            }
            
            if($node->nodeName == 'code')
            {
                $value = substr($value,6);
                $end = strrpos($value,"</code>");
                $value = substr($value,0,$end);
            }
            
            $value = htmlentities(htmlentities($value)); // Needed
            $node->nodeValue = $value;
        }
		*/

        $cleaned = $doc->saveHTML($doc->documentElement); // $doc->documentElement fixes utf-8/entities issue. @see http://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly

		$cleaned = str_replace('@nbsp;', '&nbsp;',  $cleaned); // prevent replacement of &nbsp; with spaces. - convert back.


		$cleaned = str_replace('{{{','&#123;', $cleaned); // convert shortcode temporary triple-curly braces back to entities.
         $cleaned = str_replace('}}}','&#125;', $cleaned); // convert shortcode temporary triple-curly braces back to entities.

        $cleaned = str_replace(array('<body>','</body>','<html>','</html>','<!DOCTYPE html>','<meta charset="UTF-8">','<?xml version="1.0" encoding="utf-8"?>'),'',$cleaned); // filter out tags. 



     //   $cleaned = html_entity_decode($cleaned, ENT_QUOTES, 'UTF-8');
        
        return trim($cleaned);
    }

    public function secureAttributeValue($attribute, $value)
    {
        $search = isset($this->replaceAttrValues[$attribute]) ? $this->replaceAttrValues[$attribute] : $this->replaceAttrValues['default'];
        if(!empty($search))
        {
            $value = str_replace($search, '', $value);
        }
        return $value;
    }
 
 
    /**
     * Check for Invalid Attribute Values
     * @param $value string
     * @return true/false
     */   
    function invalidAttributeValue($value)
    {
    	
    	
        foreach($this->badAttrValues as $v) // global list because a bad value is bad regardless of the attribute it's in. ;-)
        {
            if(preg_match('/'.$v.'/i',$value)==true)
            {
				$this->removedList['blacklist'][]	= "Match found for '{$v}' in '{$value}'";
            	
                return true;    
            }   
            
        }
        
        return false;    
    }   
    
       
    
    /**
     * XSS HTML code to test against
     */
    public function getXss()
    {

$html = <<<EOF
Internationalization Test: 
ภาษาไทย <br />
日本語 <br />
简体中文 <br />
<a href='somewhere.html' src='invalidatrribute' >Test</a>
A GOOD LINK: <a href='http://mylink.php'>Some Link</a>
<a href='javascript: something' src='invalidatrribute' >Test regex</a>
<img href='invalidattribute' src='myimage.jpg' />
<frameset onload=alert(1) data-something=where>
<table background="javascript:alert(1)"><tr><td><a href="something.php" onclick="alert(1)">Hi there</a></td></tr></table>
<div>
<!--<img src="--><img src=x onerror=alert(1)//">
<comment><img src="</comment><img src=x onerror=alert(1)//">
<ul>
<li style=list-style:url() onerror=alert(1)></li> <div style=content:url(data:image/svg+xml,%3Csvg/%3E);visibility:hidden onload=alert(1)></div>
</ul>
</div>
</frameset>
<head><base href="javascript://"/></head><body><a href="/. /,alert(1)//#">XXX</a></body>
<SCRIPT FOR=document EVENT=onreadystatechange>alert(1)</SCRIPT>
<OBJECT CLASSID="clsid:333C7BC4-460F-11D0-BC04-0080C7055A83"><PARAM NAME="DataURL" VALUE="javascript:alert(1)"></OBJECT>
<b <script>alert(1)//</script>0</script></b>
<div id="div1"><input value="``onmouseover=alert(1)"></div> <div id="div2"></div><
script>document.getElementById("div2").innerHTML = document.getElementById("div1").innerHTML;</script>
Some example text<br />
<b>This is bold</b><br />
<i>This is italic</i><br />
<small>Some small text</small>
<pre>This is pre-formatted
        <script>alert('something')</script>
        <b>Bold Stuff</b>
        <pre>something</pre>
        <code>code</code>
        <b>BOLD</b>
        function myfunction()
        {
            
        }
 </pre>
<code>
        function myfunction()
        {
            
        }

<script>alert('something')</script>
</code>
<svg><![CDATA[><image xlink:href="]]><img src=xx:x onerror=alert(2)//"></svg>
<style><img src="</style><img src=x onerror=alert(1)//">
<x '="foo"><x foo='><img src=x onerror=alert(1)//'> <!-- IE 6-9 --> <! '="foo"><x foo='><img src=x onerror=alert(2)//'> <? '="foo"><x foo='><img src=x onerror=alert(3)//'>
<embed src="javascript:alert(1)"></embed> // O10.10↓, OM10.0↓, GC6↓, FF <img src="javascript:alert(2)"> <image src="javascript:alert(2)"> // IE6, O10.10↓, OM10.0↓ <script src="javascript:alert(3)"></script> // IE6, O11.01↓, OM10.1↓
<div style=width:1px;filter:glow onfilterchange=alert(1)>x</div>
<object allowscriptaccess="always" data="test.swf"></object>
[A] <? foo="><script>alert(1)</script>"> <! foo="><script>alert(1)</script>"> </ foo="><script>alert(1)</script>"> [B] <? foo="><x foo='?><script>alert(1)</script>'>"> [C] <! foo="[[[x]]"><x foo="]foo><script>alert(1)</script>"> [D] <% foo><x foo="%><script>alert(1)</script>">
<iframe src=mhtml:http://html5sec.org/test.html!xss.html></iframe> <iframe src=mhtml:http://html5sec.org/test.gif!xss.html></iframe>
<html> <body> <b>some content without two new line \n\n</b> Content-Type: multipart/related; boundary="******"<b>some content without two new line</b> --****** Content-Location: xss.html Content-Transfer-Encoding: base64 PGlmcmFtZSBuYW1lPWxvIHN0eWxlPWRpc3BsYXk6bm9uZT48L2lmcmFtZT4NCjxzY3JpcHQ+DQp1 cmw9bG9jYXRpb24uaHJlZjtkb2N1bWVudC5nZXRFbGVtZW50c0J5TmFtZSgnbG8nKVswXS5zcmM9 dXJsLnN1YnN0cmluZyg2LHVybC5pbmRleE9mKCcvJywxNSkpO3NldFRpbWVvdXQoImFsZXJ0KGZy YW1lc1snbG8nXS5kb2N1bWVudC5jb29raWUpIiwyMDAwKTsNCjwvc2NyaXB0PiAgICAg --******-- </body> </html>
<!-- IE 5-9 --> <div id=d><x xmlns="><iframe onload=alert(1)"></div> <script>d.innerHTML+='';</script> <!-- IE 10 in IE5-9 Standards mode --> <div id=d><x xmlns='"><iframe onload=alert(2)//'></div> <script>d.innerHTML+='';</script>
<img[a][b]src=x[d]onerror[c]=[e]"alert(1)">
<a href="[a]java[b]script[c]:alert(1)">XXX</a>
<img src="x` `<script>alert(1)</script>"` `>
<img src onerror /" '"= alt=alert(1)//">
<title onpropertychange=alert(1)></title><title title=></title>
<!-- IE 5-8 standards mode --> <a href=http://foo.bar/#x=`y></a><img alt="`><img src=xx:x onerror=alert(1)></a>"> <!-- IE 5-9 standards mode --> <!a foo=x=`y><img alt="`><img src=xx:x onerror=alert(2)//"> <?a foo=x=`y><img alt="`><img src=xx:x onerror=alert(3)//">
<!--[if]><script>alert(1)</script --> <!--[if<img src=x onerror=alert(2)//]> -->
<script> Blabla </script>
<script src="/\example.com\foo.js"></script> // Safari 5.0, Chrome 9, 10 <script src="\\example.com\foo.js"></script> // Safari 5.0
<object id="x" classid="clsid:CB927D12-4FF7-4a9e-A169-56E4B8A75598"></object> <object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" onqt_error="alert(1)" style="behavior:url(#x);"><param name=postdomevents /></object>
<!-- `<img/src=xx:xx onerror=alert(1)//--!>
<xmp> <% </xmp> <img alt='%></xmp><img src=xx:x onerror=alert(1)//'> <script> x='<%' </script> %>/ alert(2) </script> XXX <style> *['<!--']{} </style> -->{} *{color:red}</style>
<a style="-o-link:'javascript:alert(1)';-o-link-source:current">X</a>
<style>p[foo=bar{}*{-o-link:'javascript:alert(1)'}{}*{-o-link-source:current}*{background:red}]{background:green};</style>
<div style="font-family:'foo[a];color:red;';">XXX</div>
<form id="test"></form><button form="test" formaction="javascript:alert(1)">X</button>
<input onfocus=write(1) autofocus>
<video poster=javascript:alert(1)//></video>
<video>somemovei.mp4</video>
<body onscroll=alert(1)><br><br><br><br><br><br>...<br><br><br><br><input autofocus>

<article id="something">Some text goes here</article>


EOF;

return $html;            
            
    }
        
    
    
    
}



class e_emotefilter
{
	private $search         = array();
	private $replace        = array();
	public $emotes;
	private $singleSearch   = array();
	private $singleReplace  = array();
	 
	function __construct()
	{		
		$pref = e107::getPref();
		
		if(empty($pref['emotepack']))
		{	
			$pref['emotepack'] = "default";
			e107::getConfig('emote')->clearPrefCache('emote');
			e107::getConfig('core')->set('emotepack','default')->save(false,true,false);
		}

		$this->emotes = e107::getConfig("emote")->getPref();

		if(empty($this->emotes))
		{
			return;
		}

		$base = defined('e_HTTP_STATIC') && is_string(e_HTTP_STATIC)  ? e_HTTP_STATIC : SITEURLBASE;

		foreach($this->emotes as $key => $value)
		{

		  $value = trim($value);

		  if ($value)
		  {	// Only 'activate' emote if there's a substitution string set


			$key = preg_replace("#!(\w{3,}?)$#si", ".\\1", $key);
			// Next two probably to sort out legacy issues - may not be required any more
		//	$key = preg_replace("#_(\w{3})$#", ".\\1", $key);

			  $key = str_replace("!", "_", $key);

			  $filename = e_IMAGE."emotes/" . $pref['emotepack'] . "/" . $key;


			  
			  $fileloc = $base.e_IMAGE_ABS."emotes/" . $pref['emotepack'] . "/" . $key;

			  $alt = str_replace(array('.png','.gif', '.jpg'),'', $key);

			  if(file_exists($filename))
			  {
			        $tmp = explode(" ", $value);
					foreach($tmp as $code)
					{
						$img                = "<img class='e-emoticon' src='".$fileloc."' alt=\"".$alt."\"  />";

				        $this->search[]     = "\n".$code;
				        $this->replace[]    = "\n".$img;

						$this->search[]     = " ".$code;
				        $this->replace[]    = " ".$img;

				        $this->search[]     = ">".$code; // Fix for emote within html.
				        $this->replace[]    = ">".$img;

				        $this->singleSearch[] = $code;
				        $this->singleReplace[] = $img;

					}


			  /*
				if(strstr($value, " "))
				{
					$tmp = explode(" ", $value);
					foreach($tmp as $code)
					{
						$this->search[] = " ".$code;
						$this->search[] = "\n".$code;

						$this->replace[] = " <img class='e-emoticon' src='".$fileloc."' alt=\"".$alt."\"  /> ";
						$this->replace[] = "\n <img class='e-emoticon' src='".$fileloc."'alt=\"".$alt."\"   /> ";
					}
					unset($tmp);
				}
				else
				{
					if($value)
					{
						$this->search[] = " ".$value;
						$this->search[] = "\n".$value;

						$this->replace[] = " <img class='e-emoticon' src='".$fileloc."' alt=\"".$alt."\"   /> ";
						$this->replace[] = "\n <img class='e-emoticon' src='".$fileloc."' alt=\"".$alt."\"   /> ";
					}
				}*/
			  }
		  }
		  else
		  {
			unset($this->emotes[$key]);
		  }


		}

	//	print_a($this->regSearch);
	//	print_a($this->regReplace);

	}


	function filterEmotes($text)
	{

		if(empty($text))
		{
			return '';
		}

		if(!empty($this->singleSearch) && (strlen($text) < 12) && in_array($text, $this->singleSearch)) // just one emoticon with no space, line-break or html tags around it.
		{
			return str_replace($this->singleSearch,$this->singleReplace,$text);
		}

		return str_replace($this->search, $this->replace, $text);

	}

	 
	function filterEmotesRev($text)
	{
		return str_replace($this->replace, $this->search, $text);
	}
}


class e_profanityFilter 
{
	var $profanityList;

	function __construct()
	{
		global $pref;

		$words = explode(",", $pref['profanity_words']);
        $word_array = array();
		foreach($words as $word) 
		{
			$word = trim($word);
			if($word != "")
			{
				$word_array[] = $word;
				if (strpos($word, '&#036;') !== FALSE)
				{
					$word_array[] = str_replace('&#036;', '\$', $word);		// Special case - '$' may be 'in clear' or as entity
				}
			}
		}
		if(count($word_array))
		{
			$this->profanityList = str_replace('#','\#',implode("\b|\b", $word_array));		// We can get entities in the string - confuse the regex delimiters
		}
		unset($words);
		return TRUE;
	}

	function filterProfanities($text) 
	{
		global $pref;
		if (!$this->profanityList) 
		{
			return $text;
		}
		if ($pref['profanity_replace']) 
		{
			return preg_replace("#\b".$this->profanityList."\b#is", $pref['profanity_replace'], $text);
		} 
		else 
		{
			return preg_replace_callback("#\b".$this->profanityList."\b#is", array($this, 'replaceProfanities'), $text);
		}
	}

	function replaceProfanities($matches) 
	{
		/*!
		@function replaceProfanities callback
		@abstract replaces vowels in profanity words with stars
		@param text string - text string to be filtered
		@result filtered text
		*/

		return preg_replace("#a|e|i|o|u#i", "*" , $matches[0]);
	}
}


/**
 * Backwards Compatibility Class textparse
 */
class textparse {

	function editparse($text, $mode = "off")
	{
		if(E107_DBG_DEPRECATED)
		{
			e107::getDebug()->logDeprecated();
		}

		return e107::getParser()->toForm($text);
	}

	function tpa($text, $mode = '', $referrer = '', $highlight_search = false, $poster_id = '')
	{
		if(E107_DBG_DEPRECATED)
		{
			e107::getDebug()->logDeprecated();
		}

		return e107::getParser()->toHTML($text, true, $mode, $poster_id);
	}

	function tpj($text)
	{

		if(E107_DBG_DEPRECATED)
		{
			e107::getDebug()->logDeprecated();
		}

		return $text;
	}

	function formtpa($text, $mode = '')
	{

		if(E107_DBG_DEPRECATED)
		{
			e107::getDebug()->logDeprecated();
		}

		return e107::getParser()->toDB($text);
	}

	function formtparev($text)
	{

		if(E107_DBG_DEPRECATED)
		{
			e107::getDebug()->logDeprecated();
		}

		return e107::getParser()->toForm($text);
	}

}