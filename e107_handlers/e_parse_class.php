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

if (!defined('e107_INIT'))
{
	exit();
}


define('E_NL', chr(2));


/**
 *
 */
class e_parse
{

	/**
	 * Determine how to handle utf-8.
	 *    0 = 'do nothing'
	 *    1 = 'use mb_string'
	 *    2 = emulation
	 *
	 * @var int
	 */
	private $multibyte = false; // previously $utfAction

	private $pref; // core prefs used in toHTML.

	private static $hideMarkers = 0; // sets of [hide] markers handed out by setAsideHideBlocks(), so that no two renders share one

	/** @var string a [code] block as the bbcode parser pairs one, the number its tags carry captured second */
	private static $codeBlockPattern = '#(\[code(?![a-z_])(\d*)(?!\d)[^\]\n]*\].*?\[/code(?![a-z_])\2(?!\d)[^\]\n]*\])#is';

	/**
	 * Matches one HTML entity, the unit both truncators count as a single character.
	 *
	 * @var string
	 */
	private $htmlEntityRegexp = '&[0-9a-zA-Z]{2,8};|&#[0-9]{1,7};|&#[xX][0-9a-fA-F]{1,6};';

	/**
	 * The most bytes one such unit can take: a ten-byte entity, as no character is longer.
	 *
	 * @var int
	 */
	private $longestVisibleUnit = 10;

	/**
	 * Matches one well-formed UTF-8 character of two bytes or more.
	 *
	 * @var string
	 */
	private $utf8MultibyteRegexp = '[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2}';

	// 'Hooked' parsers (array)
	private $e_hook = array();

	public $search = array('&amp;#039;', '&#039;', '&#39;', '&quot;', 'onerror', '&gt;', '&amp;quot;', ' & ');

	public $replace = array("'", "'", "'", '"', 'one<i></i>rror', '>', '"', ' &amp; ');

	/** @var array|null see {@see e_parse::bodyEntities()} */
	private $bodyEntities = null;

	// Set to TRUE or FALSE once it has been calculated
	protected $e_highlighting;

	// Highlight query
	protected $e_query;

	private $thumbWidth = 100;

	private $thumbHeight = 0;

	private $thumbCrop = 0;

	private $thumbEncode = 0;

	private $staticCount = 0;

	protected $staticUrl;

	protected $staticUrlMap = [];

	/** @var array Stored relative paths - used by replaceConstants() */
	private $relativePaths = [];

	/** @var bool Whether a shortcode pass will run over the bbcodes being parsed, for {@see e_parse::toBBCodeUrlAttribute()} */
	private static $bbcodeShortcodes = false;

	/** @var string[] The constants {@see e_parse::toAttribute()} may expand: folders the web server serves, never a request or session value */
	private static $webPathConstants = array(
		'e_AVATAR', 'e_AVATAR_ABS',
		'e_BASE',
		'e_BOOTSTRAP',
		'e_CSS', 'e_CSS_ABS',
		'e_DOCS_ABS',
		'e_FILE', 'e_FILE_ABS',
		'e_HELP_ABS',
		'e_HTTP',
		'e_IMAGE', 'e_IMAGE_ABS',
		'e_JS', 'e_JS_ABS',
		'e_MEDIA', 'e_MEDIA_ABS',
		'e_MEDIA_FILE',
		'e_MEDIA_ICON',
		'e_MEDIA_IMAGE',
		'e_MEDIA_VIDEO',
		'e_PLUGIN', 'e_PLUGIN_ABS',
		'e_THEME', 'e_THEME_ABS',
		'e_WEB', 'e_WEB_ABS',
		'e_WEB_CSS',
		'e_WEB_IMAGE',
		'e_WEB_JS',
	);

	/** @var string[] The further constants {@see e_parse::replaceConstants()} expands by default: the admin area, and folders a site may keep outside the web root */
	private static $serverPathConstants = array(
		'e_ADMIN', 'e_ADMIN_ABS',
		'e_BACKUP',
		'e_CACHE', 'e_CACHE_CONTENT', 'e_CACHE_DB', 'e_CACHE_IMAGE',
		'e_CORE',
		'e_DOCS',
		'e_DOWNLOAD',
		'e_HANDLER',
		'e_HELP',
		'e_IMPORT',
		'e_LANGUAGEDIR',
		'e_LOG',
		'e_SYSTEM',
		'e_TEMP',
		'e_UPLOAD',
	);

	/** @var string Matches an {e_...} placeholder replaceConstants() may expand by default */
	private static $constantPlaceholder = '#\{(e_[A-Z]*(?:_IMAGE|_VIDEO|_FILE|_CONTENT|_ICON|_AVATAR|_JS|_CSS|_PACK|_DB|_ABS){0,1})\}#s';


	// BBcode that contain preformatted code.
	private $preformatted = array('html', 'markdown');


	// Set up the defaults
	private $e_optDefault = array(
		// default context: reflects legacy settings (many items enabled)
		'context'      => 'OLDDEFAULT',
		//
		'fromadmin'    => false,

		// Enable emote display
		'emotes'       => true,

		// Convert defines(constants) within text.
		'defs'         => false,

		// replace all {e_XXX} constants with their e107 value - 'rel' or 'abs'
		'constants'    => false,

		// Enable hooked parsers
		'hook'         => true,

		// Allow scripts through (new for 0.8)
		'scripts'      => true,

		// Make links clickable
		'link_click'   => true,

		// Substitute on clickable links (only if link_click == TRUE)
		'link_replace' => true,

		// Parse shortcodes - TRUE enables parsing
		'parse_sc'     => false,

		// remove HTML tags.
		'no_tags'      => false,

		// Restore entity form of quotes and such to single characters - TRUE disables
		'value'        => false,

		// Line break compression - TRUE removes newline characters
		'nobreak'      => false,

		// Retain newlines - wraps to \n instead of <br /> if TRUE (for non-HTML email text etc)
		'retain_nl'    => false
	);

	// Super modifiers override default option values
	private $e_SuperMods = array(
		//text is part of a title (e.g. news title)
		'TITLE'        =>
			array(
				'nobreak' => true, 'retain_nl' => true, 'link_click' => false, 'emotes' => false, 'defs' => true, 'parse_sc' => true
			),
		'TITLE_PLAIN'  =>
			array(
				'nobreak' => true, 'retain_nl' => true, 'link_click' => false, 'emotes' => false, 'defs' => true, 'parse_sc' => true, 'no_tags' => true
			),
		//text is user-entered (i.e. untrusted) and part of a title (e.g. forum title)
		'USER_TITLE'   =>
			array(
				'nobreak' => true, 'retain_nl' => true, 'link_click' => false, 'scripts' => false, 'emotes' => false, 'hook' => false
			),
		// text is 'body' of email or similar - being sent 'off-site' so don't rely on server availability
		'E_TITLE'      =>
			array(
				'nobreak' => true, 'retain_nl' => true, 'defs' => true, 'parse_sc' => true, 'emotes' => false, 'scripts' => false, 'link_click' => false
			),
		// text is part of the summary of a longer item (e.g. content summary)
		'SUMMARY'      =>
			array(
				'defs' => true, 'constants' => 'full', 'parse_sc' => true
			),
		// text is the description of an item (e.g. download, link)
		'DESCRIPTION'  =>
			array(
				'defs' => true, 'constants' => 'full', 'parse_sc' => true
			),
		// text is 'body' or 'bulk' text (e.g. custom page body, content body)
		'BODY'         =>
			array(
				'defs' => true, 'constants' => 'full', 'parse_sc' => true
			),
		// text is parsed by the Wysiwyg editor. eg. TinyMce
		'WYSIWYG'      =>
			array(
				'hook' => false, 'link_click' => false, 'link_replace' => false, 'retain_nl' => true
			),
		// text is user-entered (i.e. untrusted)'body' or 'bulk' text (e.g. custom page body, content body)
		'USER_BODY'    =>
			array(
				'constants' => 'full', 'scripts' => false, 'nostrip' => false
			),
		// text is 'body' of email or similar - being sent 'off-site' so don't rely on server availability
		'E_BODY'       =>
			array(
				'defs' => true, 'constants' => 'full', 'parse_sc' => true, 'emotes' => false, 'scripts' => false, 'link_click' => false
			),
		// text is text-only 'body' of email or similar - being sent 'off-site' so don't rely on server availability
		'E_BODY_PLAIN' =>
			array(
				'defs' => true, 'constants' => 'full', 'parse_sc' => true, 'emotes' => false, 'scripts' => false, 'link_click' => false, 'retain_nl' => true, 'no_tags' => true
			),
		// text is the 'content' of a link (A tag, etc)
		'LINKTEXT'     =>
			array(
				'nobreak' => true, 'retain_nl' => true, 'link_click' => false, 'emotes' => false, 'hook' => false, 'defs' => true, 'parse_sc' => true
			),
		// text is used (for admin edit) without fancy conversions or html.
		'RAWTEXT'      =>
			array(
				'nobreak' => true, 'retain_nl' => true, 'link_click' => false, 'emotes' => false, 'hook' => false, 'no_tags' => true
			),
		'NODEFAULT'    =>
			array('context' => false, 'fromadmin' => false, 'emotes' => false, 'defs' => false, 'constants' => false, 'hook' => false,
			      'scripts' => false, 'link_click' => false, 'link_replace' => false, 'parse_sc' => false, 'no_tags' => false, 'value' => false,
			      'nobreak' => false, 'retain_nl' => false
			)
	);

	// Individual modifiers change the current context
	private $e_Modifiers = array(
		'emotes_off'        => array('emotes' => false),
		'emotes_on'         => array('emotes' => true),
		'no_hook'           => array('hook' => false),
		'do_hook'           => array('hook' => true),
		// New for 0.8
		'scripts_off'       => array('scripts' => false),
		// New for 0.8
		'scripts_on'        => array('scripts' => true),
		'no_make_clickable' => array('link_click' => false),
		'make_clickable'    => array('link_click' => true),
		'no_replace'        => array('link_replace' => false),
		// Replace text of clickable links (only if make_clickable option set)
		'replace'           => array('link_replace' => true),
		// No path replacement
		'consts_off'        => array('constants' => false),
		// Relative path replacement
		'consts_rel'        => array('constants' => 'rel'),
		// Absolute path replacement
		'consts_abs'        => array('constants' => 'abs'),
		// Full path replacement
		'consts_full'       => array('constants' => 'full'),
		// No shortcode parsing
		'scparse_off'       => array('parse_sc' => false),

		'scparse_on' => array('parse_sc' => true),
		// Strip tags
		'no_tags'    => array('no_tags' => true),
		// Leave tags
		'do_tags'    => array('no_tags' => false),

		'fromadmin' => array('fromadmin' => true),
		'notadmin'  => array('fromadmin' => false),
		// entity replacement
		'er_off'    => array('value' => false),
		'er_on'     => array('value' => true),
		// Decode constant if exists
		'defs_off'  => array('defs' => false),
		'defs_on'   => array('defs' => true),

		'dobreak'   => array('nobreak' => false),
		'nobreak'   => array('nobreak' => true),
		// Line break using \n
		'lb_nl'     => array('retain_nl' => true),
		// Line break using <br />
		'lb_br'     => array('retain_nl' => false),

		// Legacy option names below here - discontinue later
		'retain_nl' => array('retain_nl' => true),
		'defs'      => array('defs' => true),
		'parse_sc'  => array('parse_sc' => true),
		'constants' => array('constants' => 'rel'),
		'value'     => array('value' => true),
		'wysiwyg'   => array('wysiwyg' => true)
	);

	/**
	 * @var DOMDocument
	 */
	private $domObj;
	private $isHtml = false;

	private $bootstrap;
	private $fontawesome;

	private $modRewriteMedia;

	private $removedList      = array();
	private $nodesToDelete    = array();
	private $nodesToConvert   = array();
	private $nodesToDisableSC = array();
	private $pathList         = array();

	private $allowedAttributes = array();


	private $badAttrValues = array();

	private $replaceAttrValues = array();

	private $allowedTags = array();
	private $scriptTags  = array();

	private $scriptAttributes = array();

	private $blockTags = array();

	private $scriptAccess = false; // nobody.

	/** @var bool raised by {@see e_parse::toDBWithoutScript()} for the length of its {@see e_parse::toDB()} call */
	private $storingWithoutScript = false;

	private $replaceVars;
	private $replaceUnset;

	/**
	 * Constructor - keep it public for backward compatibility
	 * still some new e_parse() in the core
	 *
	 */
	public function __construct()
	{

		// initialise the type of UTF-8 processing methods depending on PHP version and mb string extension
		$this->domObj = new DOMDocument('1.0', 'utf-8');
		$this->init();
		$this->compileAttributeDefaults();

	}

	/**
	 * @param string $type
	 * @return array
	 */
	public function getModifierList($type = '')
	{
		if ($type === 'super')
		{
			return $this->e_SuperMods;
		}

		return $this->e_Modifiers;
	}


	/**
	 * Initialise the type of UTF-8 processing methods depending on PHP version and mb string extension.
	 * Note: mb string is required during installation of e107.
	 * NOTE: can't be called until CHARSET is known
	 * but we all know that it is UTF-8 now
	 *
	 * @return void|null
	 */
	public function setMultibyte($bool)
	{

		if ($bool === false)
		{
			$this->multibyte = false;

			return null;
		}

		if (extension_loaded('mbstring'))
		{
			$this->multibyte = true;
			mb_internal_encoding('UTF-8');
		}
	}


	/**
	 * Returns the length of the given string.
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strlen strlen PHP function.
	 *
	 * @param string $str The UTF-8 encoded string being measured for length.
	 * @return int The length (amount of UTF-8 characters) of the string on success, and 0 if the string is empty.
	 */
	public function ustrlen($str)
	{
		if ($this->multibyte)
		{
			return mb_strlen($str);
		}

		return strlen($str);

		//	return strlen(utf8_decode($str));
	}


	/**
	 * Make a string lowercase.
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strtolower strtolower PHP function.
	 *
	 * @param string $str The UTF-8 encoded string to be lowercased.
	 * @return string Specified string with all alphabetic characters converted to lowercase.
	 */
	public function ustrtolower($str)
	{
		if ($this->multibyte)
		{
			return mb_strtolower($str);
		}

		return strtolower($str);
	}


	/**
	 * Make a string uppercase.
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strtoupper strtoupper PHP function.
	 *
	 * @param string $str The UTF-8 encoded string to be uppercased.
	 * @return string Specified string with all alphabetic characters converted to uppercase.
	 */
	public function ustrtoupper($str)
	{

		if ($this->multibyte)
		{
			return mb_strtoupper($str);
		}

		return strtoupper($str);

	}


	/**
	 * Find the position of the first occurrence of a case-sensitive UTF-8 encoded string.
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strpos strpos PHP function.
	 *
	 * Returns the numeric position (offset in amount of UTF-8 characters)
	 *  of the first occurrence of needle in the haystack string.
	 *
	 * @param string  $haystack The UTF-8 encoded string being searched in.
	 * @param int     $needle   The UTF-8 encoded string being searched for.
	 * @param int     $offset   [optional] The optional offset parameter allows you to specify which character in haystack to start searching.
	 *                          The position returned is still relative to the beginning of haystack.
	 * @return int|boolean Returns the position as an int. If needle is not found, the function will return boolean FALSE.
	 */
	public function ustrpos($haystack, $needle, $offset = 0)
	{

		if ($this->multibyte)
		{
			return mb_strpos($haystack, $needle, $offset);
		}

		return strpos($haystack, $needle, $offset);
	}


	/**
	 * Find the position of the last  occurrence of a case-sensitive UTF-8 encoded string.
	 * Unicode (UTF-8) analogue of standard @link http://php.net/strrpos strrpos PHP function.
	 * Returns the numeric position (offset in amount of UTF-8 characters)
	 *  of the last occurrence of needle in the haystack string.
	 *
	 * @param string  $haystack The UTF-8 encoded string being searched in.
	 * @param int     $needle   The UTF-8 encoded string being searched for.
	 * @param int     $offset   [optional] - The optional offset parameter allows you to specify which character in haystack to start searching.
	 *                          The position returned is still relative to the beginning of haystack.
	 * @return int|boolean Returns the position as an int. If needle is not found, the function will return boolean FALSE.
	 */
	public function ustrrpos($haystack, $needle, $offset = 0)
	{
		if ($this->multibyte)
		{
			return mb_strrpos($haystack, $needle, $offset);
		}

		return strrpos($haystack, $needle, $offset);
	}


	/**
	 * Returns all of haystack starting from and including the first occurrence of needle to the end.
	 * Unicode (UTF-8) analogue of standard @link http://php.net/stristr stristr PHP function.
	 *
	 * @param string $haystack      The UTF-8 encoded string to search in.
	 * @param mixed  $needle        If needle is not a string, it is converted to an int and applied as the ordinal value of a character.
	 * @param bool   $before_needle [optional] (PHP 5.3+) If TRUE, returns the part of the haystack before the first occurrence of the needle (excluding needle).
	 * @return string Returns the matched substring. If needle is not found, returns FALSE.
	 */
	public function ustristr($haystack, $needle, $before_needle = false)
	{

		if ($this->multibyte)
		{
			return mb_stristr($haystack, $needle, $before_needle);
		}

		return stristr($haystack, $needle, $before_needle);

	}

	/**
	 * Returns the portion of string specified by the start and length parameters.
	 * Unicode (UTF-8) analogue of standard @link http://php.net/substr substr PHP function.
	 *
	 * @param string  $str    The UTF-8 encoded string.
	 * @param int     $start  Start of portion to be returned. Position is counted in amount of UTF-8 characters from the beginning of str.
	 *                        First character's position is 0. Second character position is 1, and so on.
	 * @param int     $length [optional] If length is given, the string returned will contain at most length characters beginning from start
	 *                        (depending on the length of string). If length is omitted, the rest of string from start will be returned.
	 * @return string The extracted UTF-8 encoded part of input string.
	 */
	public function usubstr($str, $start, $length = null)
	{

		if ($this->multibyte)
		{
			return mb_substr($str, $start, $length);
		}

		if ($length === null)
		{
			$length = strlen($str);
		}

		return (string) substr($str, $start, $length);

	}

	/**
	 * Convert submitted content into the form e107 stores: clean the HTML, apply
	 * the site's HTML and PHP posting permissions, and entity-encode the rest.
	 *
	 * THIS METHOD DOES NOT ESCAPE FOR SQL, whatever the name suggests. It decides
	 * what a given author is allowed to store, not how to put a value in a query
	 * safely. Binding or escaping stays the database layer's job, and a value that
	 * has been through toDB() still has to be bound like any other.
	 *
	 * WHAT IT STORES DEPENDS ON WHO IS POSTING, which is the fact most likely to
	 * catch a reader out. Two different shapes come out of this method:
	 *
	 * - An author who holds the 'post_html' class gets HTML stored AS MARKUP, with
	 *   only $ " ' \ and '<?' entity-encoded.
	 * - Everyone else gets htmlspecialchars() over the lot, so HTML is stored AS
	 *   TEXT, and unless $mod is 'pReFs' strip_tags() has already removed the tags
	 *   in any case.
	 *
	 * So the same field holds markup on one row and encoded text on another,
	 * depending on the permissions of whoever wrote it. Nothing reading the column
	 * back can assume either shape.
	 *
	 * $no_encode IS NOT A REQUEST, IT IS A STARTING POINT. It is forced TRUE for
	 * any author holding 'post_html', so passing FALSE does not guarantee the
	 * value comes back encoded. A numeric $parm names the author instead of the
	 * current user: the user with that id decides both the strip and the
	 * encoding, either way. If a caller needs a value encoded no matter who is
	 * posting, it has to encode at the sink.
	 *
	 * Note also that it fails permissive on an unrecognised $mod: the modifiers
	 * are matched with strpos(), so a misspelt 'no_htlm' silently permits HTML.
	 *
	 * @param mixed          $data      string or array. An array is walked
	 *                                  recursively and its KEYS have " and '
	 *                                  replaced as well. Any other type is
	 *                                  returned untouched, as is the string '0'.
	 * @param boolean        $nostrip   [optional] only consulted when the server
	 *                                  has magic quotes on. Data is assumed to
	 *                                  come from $_GET/$_POST/$_COOKIE and is
	 *                                  stripslashes()'d; pass TRUE for data that
	 *                                  did not, so its backslashes survive.
	 * @param boolean        $no_encode [optional] skip the entity encoding and
	 *                                  store markup. Overridden by the 'post_html'
	 *                                  check both ways; see above.
	 * @param boolean|string $mod       [optional] one of:
	 *                                  'no_html'  strip HTML whatever the author
	 *                                             may post. Used by logging.
	 *                                  'no_php'   neutralise the [php] bbcode
	 *                                             whatever the author may post.
	 *                                  'model'    admin-ui usage. The ONLY value
	 *                                             that runs the e_parse plugin
	 *                                             hooks, which may rewrite the
	 *                                             result again after this method
	 *                                             is done with it.
	 *                                  'pReFs'    internal, for saving prefs.
	 *                                             Skips preFilter(), cleanHtml()
	 *                                             and the strip_tags() branch
	 *                                             entirely, so a caller passing it
	 *                                             is asserting the data is already
	 *                                             trusted.
	 * @param mixed          $parm      [optional] two unrelated shapes. Numeric: the
	 *                                  id of the author whose 'post_html' decides,
	 *                                  in place of the current user's, and
	 *                                  without whose 'post_script' no script is
	 *                                  kept; 0 is a guest, with a guest's classes.
	 *                                  Array with
	 *                                  'type' and 'field' keys: passed to the
	 *                                  e_parse hooks, and only read when $mod is
	 *                                  'model'.
	 * @return mixed the converted string, an array converted the same way, or
	 *               $data unchanged when it is neither
	 * @see e_parse::cleanHtml() for the HTML cleaning this applies
	 * @see e_parse::toHTML() for the way back out, which is not an encoder either
	 */
	public function toDB($data = null, $nostrip = false, $no_encode = false, $mod = false, $parm = null)
	{

		$variableType = gettype($data);

		if (($variableType !== 'string' && $variableType !== 'array') || $data === '0')
		{
			return $data;
		}

		if ($variableType === 'array')
		{
			$ret = array();

			foreach ($data as $key => $var)
			{
				//Fix - sanitize keys as well
				$key = str_replace(['"', "'"], ['&quot;', '&#039;'], $key);
				$ret[$key] = $this->toDB($var, $nostrip, $no_encode, $mod, $parm);
			}

			return $ret;
		}


		if (MAGIC_QUOTES_GPC === true && $nostrip === false)
		{
			$data = stripslashes($data);
		}

		$core_pref = e107::getConfig();
		$author = ($parm !== null && is_numeric($parm)) ? e107::getSystemUser((int) $parm, false) : null;
		$authorPostsHtml = ($author !== null) ? $author->checkClass($core_pref->get('post_html', e_UC_MAINADMIN), false) : null;
		$authorPostsScript = ($author !== null) ? $author->checkClass($core_pref->get('post_script', e_UC_NOBODY), false) : null;

		if ($this->storingWithoutScript)
		{
			$authorPostsHtml = true;
			$authorPostsScript = false;
		}

		if ($mod !== 'pReFs') //XXX We're not saving prefs.
		{

			$data = $this->preFilter($data); // used by bb_xxx.php toDB() functions. bb_code.php toDB() allows us to properly bypass HTML cleaning below.
			$data = ($authorPostsScript === false) ? $this->cleanHtmlWithoutScript($data) : $this->cleanHtml($data); // clean it regardless of if it is text or html. (html could have missing closing tags)

			if (($this->isHtml($data)) && strpos($mod, 'no_html') === false)
			{
				$this->isHtml = true;
				//	$data = $this->cleanHtml($data); // sanitize all html. (moved above to include everything)

				$data = str_replace(array('%7B', '%7D'), array('{', '}'), $data); // fix for {e_XXX} paths.
			}
			//		else // caused double-encoding of '&'
			{
				//	$data = str_replace('&amp;','&',$data);
				//		$data = str_replace('<','&lt;',$data);
				//		$data = str_replace('>','&gt;',$data);
				//	$data = str_replace('&','&amp;',$data);

			}


			if ($authorPostsHtml === false || ($authorPostsHtml === null && !check_class($core_pref->get('post_html', e_UC_MAINADMIN))))
			{
				$data = strip_tags($data); // remove tags from cleaned html.
				$data = str_replace(array('[html]', '[/html]'), array('&#91;html]', '&#91;/html]'), $data);
			}

			//  $data = html_entity_decode($data, ENT_QUOTES, 'utf-8');	// Prevent double-entities. Fix for [code]  - see bb_code.php toDB();
		}


		if ($authorPostsHtml !== null)
		{
			$no_encode = $authorPostsHtml;
		}
		elseif (check_class($core_pref->get('post_html'))) /*$core_pref->is('post_html') && XXX preformecd by cleanHtml() */
		{
			$no_encode = true;
		}

		if ($no_encode === true && strpos($mod, 'no_html') === false)
		{
			if ($mod !== 'pReFs')
			{
				$data = str_replace('&quot;', '&amp;quot;', $data);
			}

			$search = array('$', '"', "'", '\\', '<?');
			$replace = array('&#036;', '&quot;', '&#039;', '&#092;', '&lt;?');
			$ret = str_replace($search, $replace, $data);
		}
		else // add entities for everything. we want to save the code.
		{

			$search = array('&gt;', '&lt;');
			$replace = array('>', '<');
			$data = str_replace($search, $replace, $data); // prevent &amp;gt; etc.

			if ($mod !== 'pReFs')
			{
				$data = preg_replace('/&amp;(?!#\d*;)/', '&', $data);
			}

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
		if ($mod !== 'model')
		{
			return $ret;
		}


		/**
		 * e_parse hook
		 */
		$eParseList = $core_pref->get('e_parse_list');
		if (!empty($eParseList))
		{

			$opts = array(
				'nostrip'  => $nostrip,
				'noencode' => $no_encode,
				'type'     => $parm['type'],
				'field'    => $parm['field']
			);

			foreach ($eParseList as $plugin)
			{
				$hookObj = e107::getAddon($plugin, 'e_parse');
				if ($tmp = e107::callMethod($hookObj, 'toDB', $ret, $opts))
				{
					$ret = $tmp;
				}

			}

		}


		return $ret;
	}

	/**
	 * Convert submitted content as {@see e_parse::toDB()} stores it for an author who may post HTML but not script.
	 *
	 * @param mixed $data string or array, as {@see e_parse::toDB()} takes it
	 * @return mixed the converted string, an array converted the same way, or $data unchanged when it is neither
	 */
	public function toDBWithoutScript($data)
	{
		$was = $this->storingWithoutScript;
		$this->storingWithoutScript = true;

		try
		{
			return $this->toDB($data, false, false, 'no_php');
		}
		finally
		{
			$this->storingWithoutScript = $was;
		}
	}


	/**
	 *    Check for umatched 'dangerous' HTML tags
	 *        (these can destroy page layout where users are able to post HTML)
	 *
	 * @param string $data
	 * @param string $tagList - if empty, uses default list of input tags. Otherwise a CSV list of tags to check (any type)
	 *
	 * @return string TRUE if an unopened closing tag found
	 *                    FALSE if nothing found
	 * @deprecated
	 */
	public function htmlAbuseFilter($data, $tagList = '')
	{

		trigger_error('<b>' . __METHOD__ . ' is deprecated. Use $tp->cleanHtml() instead.</b>', E_USER_WARNING); // NO LAN

		return $data;
	}


	/**
	 * @deprecated
	 *    Checks a string for potentially dangerous HTML tags, including malformed tags
	 *
	 */
	public function dataFilter($data, $mode = 'bbcode')
	{

		trigger_error('$tp->dateFilter() is deprecated. Use $tp->filter() instead.', E_USER_WARNING);

		return $data;
	}


	/**
	 *    Processes data as needed before its written to the DB.
	 *    Currently gives bbcodes the opportunity to do something
	 *
	 * @param $data string - data about to be written to DB
	 * @return string - modified data
	 */
	public function preFilter($data)
	{

		if (!$this->isBBcode($data))
		{
			return $data;
		}

		return e107::getBB()->parseBBCodes($data, defset('USERID'), 'default', 'PRE');            // $postID = logged in user here
	}


	/**
	 * Takes a multi-dimensional array and converts the keys to a list of routing paths.
	 * paths are the key and value are the top most key.
	 *
	 * @param array $array
	 * @return array
	 */
	public function toRoute($array)
	{
		$res = $this->_processRoute($array);
		$tmp = explode("_#_", $res);
		$ret = [];
		foreach ($tmp as $v)
		{
			list($k) = explode('/', $v);
			$ret[$v] = $k;
		}

		return $ret;
	}

	/**
	 * @param array $array
	 * @param string $prefix
	 * @return string
	 */
	private function _processRoute($array, $prefix = '')
	{
		$text = [];

		if (is_array($array))
		{
			foreach ($array as $key => $val)
			{
				if ($tag = $this->_processRoute($val, $key . '/'))
				{
					$add = $tag;
				}
				else
				{
					$add = $key;
				}

				$text[] = $prefix . $add;
			}
		}

		return implode('_#_', $text);

	}


	/**
	 * @param string $text
	 * @return array|string|string[]
	 */
	public function toForm($text)
	{

		if (empty($text)) // fix - handle proper 0, Space etc values.
		{
			return $text;
		}


		if (is_string($text) && strpos($text, '[html]') === 0)
		{
			// $text = $this->toHTML($text,true);
			$search = array('&quot;', '&#039;', '&#092;', '&amp;quot;', '&amp;#039;', '&',); // '&' must be last.
			$replace = array('"', "'", "\\", '&quot;', '&#039;', '&amp;');

			//	return htmlspecialchars_decode($text);
			$text = str_replace($search, $replace, $text);
			//	return $text;
			//$text  = htmlentities($text,ENT_NOQUOTES, "UTF-8");

			//	return $text;

		}
		//	return htmlentities($text);

		$search = array('&#036;', '&quot;', '<', '>', '+');
		$replace = array('$', '"', '&lt;', '&gt;', '%2B');
		$text = str_replace($search, $replace, $text);

		if (is_string($text) && e107::wysiwyg() !== true)
		{
			// fix for utf-8 issue with html_entity_decode(); ???
			$text = urldecode(str_ireplace(array('%3C', '%3E', '%27'), array('&lt;', '&gt;', '&#039;'), $text));
			//	$text = str_replace("&nbsp;", " ", $text);
		}

		return $text;
	}

	/**
	 * @param $text
	 * @return array|string
	 */
	public function post_toForm($text)
	{

		if (is_array($text))
		{
			$arr = array();
			foreach ($text as $key => $value)
			{
				$key = $this->post_toForm($key);
				$arr[$key] = $this->post_toForm($value);
			}

			return $arr;
		}

		$text = (string) $text;

		if (MAGIC_QUOTES_GPC == true)
		{
			$text = stripslashes($text);
		}

		return str_replace(array("'", '"', '<', '>'), array('&#039;', '&quot;', '&lt;', '&gt;'), $text);
	}


	/**
	 * @param string $text
	 * @param $original_author
	 * @param string $extra
	 * @param bool $mod
	 * @return string
	 */
	public function post_toHTML($text, $original_author = false, $extra = '', $mod = false)
	{

		$text = $this->toDB($text, false, false, $mod, $original_author);

		return $this->toHTML($text, true, $extra);
	}

	/**
	 * @param              $text         - template to parse.
	 * @param boolean      $parseSCFiles - parse core 'single' shortcodes
	 * @param object|array $extraCodes   - shortcode class containing sc_xxxxx methods or an array of key/value pairs or legacy shortcode content (eg. content within .sc)
	 * @param object       $eVars        - XXX more info needed.
	 * @return string
	 */
	public function parseTemplate($text, $parseSCFiles = true, $extraCodes = null, $eVars = null)
	{

		if (!is_bool($parseSCFiles))
		{
			trigger_error('$parseSCFiles in parseTemplate() was given incorrect data');
		}

		return e107::getScParser()->parseCodes($text, $parseSCFiles, $extraCodes, $eVars);
	}


	/**
	 * Parses a JSON schema template, processes placeholders, and reconstructs the JSON with optional main entity and extra codes.
	 *
	 * @param string      $text         The JSON schema template to be parsed.
	 * @param bool        $parseSCFiles Whether to enable the parsing of shortcode files. Defaults to true.
	 * @param object|null $extraCodes   Optional extra codes object for placeholder parsing.
	 * @param array|null  $mainEntity   Optional data array to replace the 'mainEntity' structure in the schema.
	 * @return string|false The processed JSON schema string on success, or false if the input JSON is invalid.
	 */
	public function parseSchemaTemplate($text, $parseSCFiles = true, $extraCodes = null, $mainEntity = null)
	{

		// Initialize the parser
		$parse = e107::getScParser();
		$parse->setMode('schema'); // Set parsing mode for schema

		// Step 1: Decode the JSON input into an array
		$jsonArray = json_decode($text, true);

		// Step 2: Validate JSON decoding
		if(json_last_error() !== JSON_ERROR_NONE)
		{
			 error_log('Invalid JSON: ' . json_last_error_msg());
			 return false;

		}

		// Step 3: Recursive function to process the JSON structure
		$processItems = function (&$item) use (&$processItems, $parse, $parseSCFiles, $extraCodes, $mainEntity)
		{

			if(is_array($item))
			{
				// Check if the current item contains 'mainEntity', the target of our processing
				if(isset($item['mainEntity']) && is_array($mainEntity))
				{
					// Get the first template item from the 'mainEntity' array to use as the structure
					$schemaTemplate = $item['mainEntity'][0];
					$item['mainEntity'] = []; // Reset the 'mainEntity' array to prevent duplication

					foreach($mainEntity as $dataRow)
					{

						// Create a fresh copy of the schema template for this specific dataRow
						$duplicatedItem = json_decode(json_encode($schemaTemplate), true);

						// Update the extraCodes for the current data row
						if(method_exists($extraCodes, 'setVars'))
						{
							$extraCodes->setVars($dataRow); // Inject new placeholders from this row
						}

						// Process placeholders in the duplicated item
						foreach($duplicatedItem as &$value)
						{
							if(is_string($value) && strpos($value, '{') !== false)
							{
								// Parse placeholders for current dataRow
								$value = $parse->parseCodes($value, $parseSCFiles, $extraCodes);
								$value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
								$value = strip_tags($value);
							}
							elseif(is_array($value))
							{
								// Recursively process arrays (e.g., nested structures)
								$processItems($value);
							}
						}

						// Append the processed item to the 'mainEntity' array
						$item['mainEntity'][] = $duplicatedItem;
					}
				}
				else
				{
					// Recursively process other parts of the JSON structure
					foreach($item as &$value)
					{
						$processItems($value);
					}
				}
			}
			elseif(is_string($item))
			{
				// Parse string placeholders, if any
				if(strpos($item, '{') !== false)
				{

					$item = $parse->parseCodes($item, $parseSCFiles, $extraCodes);
					$item = str_replace('&amp;', '&', $item);
					$item = html_entity_decode($item, ENT_QUOTES | ENT_HTML5, 'UTF-8');
					 $item = strip_tags($item);


				}
			}
		};

		// Step 4: Initiate processing for the entire JSON structure
		$processItems($jsonArray);

		// Reset the parse mode after processing
		$parse->setMode('default');

		// Step 5: Encode the final result back into JSON
		return json_encode($jsonArray, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	}




	/**
	 * Simple parser
	 *
	 * @param string       $template
	 * @param e_vars|array $vars
	 * @param string       $replaceUnset string to be used if replace variable is not set, false - don't replace
	 * @return string parsed content
	 */
	public function simpleParse($template, $vars, $replaceUnset = '')
	{

		$this->replaceVars = $vars;
		$this->replaceUnset = $replaceUnset;

		return preg_replace_callback("#\{([\w]+)\}#", array($this, 'simpleReplace'), $template);
	}


	/**
	 * @param $tmp
	 * @return mixed|string|null
	 */
	protected function simpleReplace($tmp)
	{

		$unset = ($this->replaceUnset !== false ? $this->replaceUnset : $tmp[0]);

		if (is_array($this->replaceVars))
		{
			$this->replaceVars = new e_vars($this->replaceVars);
			//return ($this->replaceVars[$key] !== null ? $this->replaceVars[$key]: $unset);
		}
		$key = $tmp[1]; // PHP7 fix.

		return (!empty($this->replaceVars) && ($this->replaceVars->$key !== null)) ? $this->replaceVars->$key : $unset; // Doesn't work.
	}

	/**
	 * @param        $str
	 * @param        $width
	 * @param string $break
	 * @param string $nobreak
	 * @param string $nobr
	 * @param false  $utf
	 * @return string
	 * @todo find a modern replacement
	 */
	public function htmlwrap($str, $width, $break = "\n", $nobreak = 'a', $nobr = 'pre', $utf = false)
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
		$width = (int) $width;
		// And trap stupid wrap counts
		if ($width < 6)
		{
			return $str;
		}

		// Transform protected element lists into arrays
		$nobreak = explode(' ', strtolower($nobreak));

		// Variable setup

		$innbk = array();
		$drain = '';

		// List of characters it is "safe" to insert line-breaks at
		// It is not necessary to add < and > as they are automatically implied
		$lbrks = "/?!%)-}]\\\"':;&";

		// Is $str a UTF8 string?
		if ($utf || strtolower(CHARSET) === 'utf-8')
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
		$content = preg_split('#(<.*?' . '>)#mis', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		foreach ($content as $value)
		{
			if ($value[0] === '<')
			{
				// We are within an HTML tag
				// Create a lowercase copy of this tag's contents
				$lvalue = strtolower((string) substr($value, 1, -1));
				if ($lvalue)
				{
					// Tag of non-zero length
					// If the first character is not a / then this is an opening tag
					if ($lvalue[0] !== '/')
					{
						// Collect the tag name
						preg_match("/^(\w*?)(\s|$)/", $lvalue, $t);

						// If this is a protected element, activate the associated protection flag
						if (in_array($t[1], $nobreak))
						{
							array_unshift($innbk, $t[1]);
						}
					}
					else
					{
						// Otherwise this is a closing tag
						// If this is a closing tag for a protected element, unset the flag
						if (in_array(substr($lvalue, 1), $nobreak))
						{
							reset($innbk);
							foreach ($innbk as $key => $tag)
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
					$value = str_replace("\x06", '', $value);
					preg_match_all("/&([a-z\d]{2,7}|#\d{2,5}|#x[\da-f]{1,6});/i", $value, $ents);
					$value = preg_replace("/&([a-z\d]{2,7}|#\d{2,5}|#x[\da-f]{1,6});/i", "\x06", $value);
					//			echo "Found block length ".strlen($value).': '.substr($value,20).'<br />';
					// Split at spaces - note that this will fail if presented with invalid utf-8 when doing the regex whitespace search
					//			$split = preg_split('#(\s)#'.$utf8, $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
					$split = preg_split($whiteSpace, $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
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
								if (preg_match('#^((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $width . '})(.?).*#s', $sp, $matches) == 0)
								{
									// Make any problems obvious for now
									$value .= '[!<b>invalid utf-8: ' . $sp . '<b>!]';
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
									for ($i = strlen($matches[1]) - 1; $i >= 0; $i--)
									{
										if (strpos($lbrks, $matches[1][$i]) !== false)
										{
											break;
										}
									}
									if ($i < 0)
									{
										// No 'special' break character found - break at the word boundary
										$pulled = $matches[1];
									}
									else
									{
										$pulled = (string) substr($sp, 0, $i + 1);
									}
								}
								$loopCount++;
								if ($loopCount > 20)
								{
									// Make any problems obvious for now
									$value .= '[!<b>loop count exceeded: ' . $sp . '</b>!]';
									$sp = '';
								}
							}
							else
							{
								for ($i = min($width, strlen($sp)); $i > 0; $i--)
								{
									// No speed advantage to defining match character
									if (strpos($lbrks, $sp[$i - 1]) !== false)
									{
										break;
									}
								}
								if ($i == 0)
								{
									// No 'special' break boundary character found - break at the word boundary
									$pulled = (string) substr($sp, 0, $width);
								}
								else
								{
									$pulled = (string) substr($sp, 0, $i);
								}
							}
							if ($pulled)
							{
								$value .= $pulled . $break;
								// Shorten $sp by whatever we've processed (will work even for utf-8)
								$sp = (string) substr($sp, strlen($pulled));
							}
						}
						// Add in any residue
						$value .= $sp;
					}
					// Put captured HTML entities back into the string
					foreach ($ents[0] as $ent)
					{
						$value = preg_replace("/\x06/", $ent, $value, 1);
					}
				}
			}
			// Send the modified segment down the drain
			$drain .= $value;
		}

		// Return contents of the drain
		return $drain;
	}


	/**
	 * Universal text/bbcode/html truncate method.
	 * new in v2.3.1
	 *
	 * An optional fourth argument, bool $exact (default true), cuts at the last space instead of mid-word when false.
	 *
	 * @param        $text
	 * @param int    $length
	 * @param string $ending
	 * @return string
	 * @throws InvalidArgumentException when called with more than four arguments.
	 */
	public function truncate($text, $length = 100, $ending = '...')
	{
		if (func_num_args() > 4)
		{
			throw new InvalidArgumentException('e_parse::truncate() takes at most four arguments ($text, $length, $ending, $exact), ' . func_num_args() . ' given.');
		}

		$exact = func_num_args() > 3 ? (bool) func_get_arg(3) : true;

		if ($this->isHtml($text))
		{
			return $this->html_truncate($text, $length, $ending, $exact);
		}

		if ($this->isBBcode($text))
		{
			$text = $this->toText($text);
		}

		$truncated = $this->text_truncate($text, $length, $ending);
		if ($exact || $truncated === $text)
		{
			return $truncated;
		}

		$cut = (string) substr($truncated, 0, strlen($truncated) - strlen($ending));
		if ($cut . $ending !== $truncated)
		{
			return $truncated;
		}

		list($kept) = $this->splitAtLastSpace($cut);

		return $kept . $ending;
	}

	/**
	 * @param string $text
	 * @return array $text before and from its last space above position 0, or $text and '' when there is none.
	 */
	private function splitAtLastSpace($text)
	{
		$spacepos = $this->ustrrpos($text, ' ');
		if ($spacepos > 0)
		{
			return array($this->usubstr($text, 0, $spacepos), $this->usubstr($text, $spacepos));
		}

		return array($text, '');
	}

	/**
	 * @param string  $text   String to truncate.
	 * @param int     $length Length of returned string, including ellipsis.
	 * @param string  $ending It will be used as Ending and appended to the trimmed string.
	 * @param boolean $exact  If false, $text will not be cut mid-word
	 * @return string Trimmed string.
	 * @deprecated v2.3.1 Use {@see e_parse::truncate()}, which takes the same four arguments but sends text without tags to
	 *             {@see e_parse::text_truncate()}, so its answer for such text can differ. Avoid in new code and migrate existing
	 *             call sites when refactoring; this method remains supported and tested, with no removal planned.
	 *                        CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
	 *                        Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
	 *
	 * Truncate a HTML string
	 *
	 * Cuts a string to the length of $length and adds the value of $ending if the text is longer than length.
	 */
	public function html_truncate($text, $length = 100, $ending = '...', $exact = true)
	{

		if (count($this->visibleUnits(preg_replace('/<.*?>/', '', $text), (int) (float) $length + 1)) <= $length)
		{
			return $text;
		}
		$totalLength = 0;
		$openTags = array();
		$truncate = '';
		preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);

		foreach ($tags as $tag)
		{
			if (!$tag[2] || !preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/i', $tag[2]))
			{
				if (preg_match('/<[\w]+[^>]*>/', $tag[0]))
				{
					array_unshift($openTags, $tag[2]);
				}
				elseif (preg_match('/<\/([\w]+)[^>]*>/', $tag[0], $closeTag))
				{
					$pos = array_search($closeTag[1], $openTags);
					if ($pos !== false)
					{
						array_splice($openTags, $pos, 1);
					}
				}
			}
			$truncate .= $tag[1];
			$units = $this->visibleUnits($tag[3], (int) (float) $length - $totalLength + 1);
			$contentLength = count($units);

			if ($contentLength + $totalLength > $length)
			{
				$truncate .= implode('', array_slice($units, 0, max(0, $length - $totalLength)));
				break;
			}

			$truncate .= $tag[3];
			$totalLength += $contentLength;
			if ($totalLength >= $length)
			{
				break;
			}
		}
		if (!$exact)
		{
			list($truncate, $bits) = $this->splitAtLastSpace($truncate);
			preg_match_all('/<\/([a-z]+)>/i', $bits, $droppedTags, PREG_SET_ORDER);
			if (!empty($droppedTags))
			{
				foreach ($droppedTags as $closingTag)
				{
					if (!in_array($closingTag[1], $openTags))
					{
						array_unshift($openTags, $closingTag[1]);
					}
				}
			}
		}
		$truncate .= $ending;
		foreach ($openTags as $tag)
		{
			$truncate .= '</' . $tag . '>';
		}

		return $truncate;
	}


	/**
	 * @param string  $text string to process
	 * @param int     $len  length of characters to be truncated
	 * @param string  $more string which will be added if truncation
	 * @return string Always returns text. The cut itself never decodes, though bbcode
	 *                      and HTML input still goes through {@see e_parse::toText()} first.
	 * @deprecated v2.3.1 Use {@see e_parse::truncate()}, passing the length and ending since its defaults differ; it keeps
	 *             HTML that this method flattens to text. Avoid in new code and migrate existing call sites when refactoring;
	 *             this method remains supported and tested, with no removal planned.
	 *                      Truncate a string of text to a maximum length $len append the string $more if it was truncated
	 *                      Uses current CHARSET  for utf-8, returns $len characters rather than $len bytes
	 *
	 */
	public function text_truncate($text, $len = 200, $more = ' ... ')
	{

		if ($this->ustrlen($text) <= $len)
		{
			return $text;
		}

		if ($this->isBBcode($text) || $this->isHtml($text))
		{
			$text = $this->toText($text);
		}

		$units = $this->visibleUnits($text, (int) (float) $len + 1);

		if (count($units) <= $len)
		{
			return $text;
		}

		$keep = $len - count($this->visibleUnits($more, (int) (float) $len + 1));

		return implode('', array_slice($units, 0, max(0, $keep))) . $more;

	}


	/**
	 * Split up to $limit units a reader sees off the start of text, each one HTML entity or one character.
	 *
	 * @param string $text
	 * @param int    $limit
	 * @return array
	 */
	private function visibleUnits($text, $limit)
	{

		$limit = max(0, (int) $limit);
		$text = (string) $text;

		if ($limit < strlen($text))
		{
			$text = (string) substr($text, 0, $limit * $this->longestVisibleUnit);
		}

		$pattern = '/' . $this->htmlEntityRegexp . '|';
		$units = array();

		if (preg_match_all($pattern . './su', $text, $units) === false)
		{
			$units = array();
			preg_match_all($pattern . $this->utf8MultibyteRegexp . '|./s', $text, $units);
		}

		return isset($units[0]) ? array_slice($units[0], 0, $limit) : array();

	}


	/**
	 * @param $text
	 * @param $wrap
	 * @return array|string|string[]
	 */
	public function textclean($text, $wrap = 100)
	{

		$text = str_replace("\n\n\n", "\n\n", $text);
		$text = $this->htmlwrap($text, $wrap);
		$text = str_replace(array('<br /> ', ' <br />', ' <br /> '), '<br />', $text);

		/* we can remove any linebreaks added by htmlwrap function as any \n's will be converted later anyway */

		return $text;
	}


	/**
	 * Test for text highlighting, and determine the text highlighting transformation
	 * @return bool Returns TRUE if highlighting is active for this page display
	 */
	public function checkHighlighting()
	{

		global $pref;

		if (!defined('e_SELF'))
		{
			// Still in startup, so can't calculate highlighting
			return false;
		}

		if (!isset($this->e_highlighting))
		{
			$this->e_highlighting = false;
			$shr = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
			if (!empty($pref['search_highlight']) && (strpos(e_SELF, 'search.php') === false) && ((strpos($shr, 'q=') !== false) || (strpos($shr, 'p=') !== false)))
			{
				$this->e_highlighting = true;
				if (!isset($this->e_query))
				{
					preg_match('#(q|p)=(.*?)(&|$)#', $shr, $matches);
					$this->e_query = str_replace(array('+', '*', '"', ' '), array('', '.*?', '', '\b|\b'), trim(urldecode($matches[2])));
				}
			}
		}

		return $this->e_highlighting;
	}


	/**
	 * Replace text represenation of website urls and email addresses with clickable equivalents.
	 * A well formed HTML tag, and the text between an <a> and its </a>, is left as it was found.
	 *
	 * @param string $text
	 * @param string $type email|url
	 * @param array  $opts options.
	 *  $opts = [
	 *      'sub'   => (string) substitute text within links
	 *      'ext'   => (bool) load link in new window (not for email)
	 * ]
	 * @return string
	 */
	public function makeClickable($text = '', $type = 'email', $opts = array())
	{

		if (empty($text))
		{
			return '';
		}

		$textReplace = (!empty($opts['sub'])) ? $opts['sub'] : '';

		if (substr($textReplace, -6) === '.glyph')
		{
			$textReplace = $this->toGlyph($textReplace, '');
		}

		$segments = $this->splitOnTags($text);

		if ($segments === false)
		{
			return $text;
		}

		$depth = 0;

		foreach ($segments as $i => $segment)
		{
			if ($i % 2)
			{
				if (preg_match('#^<(/?)a\b#i', $segment, $tag))
				{
					$depth = ($tag[1] === '') ? $depth + 1 : max(0, $depth - 1);
				}

				continue;
			}

			if ($depth === 0 && $segment !== '')
			{
				$segments[$i] = ($type === 'url')
					? $this->clickableUrls($segment, $textReplace, !empty($opts['ext']), $i === 0)
					: $this->clickableEmails($segment, $textReplace);
			}
		}

		return implode('', $segments);
	}


	/**
	 * Split text into alternating plain text and HTML tags, the tags landing on the odd offsets.
	 * A tag whose attribute values are quoted keeps whatever the quotes contain, '>' included.
	 *
	 * @param string $text
	 * @return array|false false when the text defeats the pattern engine
	 */
	private function splitOnTags($text)
	{

		return preg_split('#(<(?:[^<>"\']++|"[^"]*+"|\'[^\']*+\')*+>)#s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	}


	/**
	 * Obfuscate every email address in a fragment that is known to carry no markup.
	 *
	 * @param string $text
	 * @param string $textReplace substitute text within links
	 * @return string
	 */
	private function clickableEmails($text, $textReplace)
	{

		if (strpos($text, '@') === false)
		{
			return $text;
		}

		preg_match_all("#(?:[\n\r ]|^)?([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", $text, $match);

		if (empty($match[0]))
		{
			return $text;
		}

		$srch = array();
		$repl = array();

		foreach ($match[0] as $eml)
		{
			$email = trim($eml);
			$srch[] = $email;
			$repl[] = $this->emailObfuscate($email, $textReplace);
		}

		return str_replace($srch, $repl, $text);
	}


	/**
	 * Link every bare url in a fragment that is known to carry no markup.
	 *
	 * @param string $text
	 * @param string $textReplace substitute text within links
	 * @param bool   $external    load link in new window
	 * @param bool   $atStart     whether this fragment opens the text, so a url may sit at its very start
	 * @return string
	 */
	private function clickableUrls($text, $textReplace, $external, $atStart)
	{

		if (strpos($text, '://') === false && stripos($text, 'www.') === false && stripos($text, 'ftp.') === false)
		{
			return $text;
		}

		$target = $external ? 'target="_blank"' : '';
		$break = '\n' . E_NL;
		$start = $atStart ? '^|' : '';

		$anchor = function ($href, $address) use ($textReplace, $target)
		{
			return '<a class="e-url" href="' . $href . '" ' . $target . '>' . (!empty($textReplace) ? $textReplace : $address) . '</a>';
		};

		$text = preg_replace_callback("/(" . $start . "[" . $break . " \(])([\w]*?)([\w]*?:\/\/[\w]+[^ \,\"" . $break . "\r\t<]*)/is", function ($match) use ($anchor)
		{
			if ($this->toUrlAttribute($match[3]) === '')
			{
				return $match[0];
			}

			return $match[1] . $match[2] . $anchor($match[3], $match[3]);
		}, $text);
		$text = preg_replace_callback("/(" . $start . "[" . $break . " \(])([\w]*?)((www)\.[^ \,\"\t" . $break . "\r\)<]*)/is", function ($match) use ($anchor)
		{
			return $match[1] . $match[2] . $anchor('http://' . $match[3], $match[3]);
		}, $text);
		$text = preg_replace_callback("/(" . $start . "[" . $break . " ])([\w]*?)((ftp)\.[^ \,\"\t" . $break . "\r<]*)/is", function ($match) use ($anchor)
		{
			return $match[1] . $match[2] . $anchor($match[4] . '://' . $match[3], $match[3]);
		}, $text);

		return $text;
	}


	/**
	 * @param string $text
	 * @param $postID
	 * @return string
	 */
	public function parseBBCodes($text, $postID)
	{

		return $this->parseBBCodesBeforeShortcodes($text, $postID, self::$bbcodeShortcodes);
	}

	/**
	 * Parse bbcodes, telling {@see e_parse::toBBCodeUrlAttribute()} whether a shortcode pass will run over their output.
	 *
	 * @param string $text
	 * @param mixed  $postID
	 * @param bool   $shortcodes
	 * @param string $mode
	 * @param mixed  $strip
	 * @return string
	 */
	private function parseBBCodesBeforeShortcodes($text, $postID, $shortcodes, $mode = 'default', $strip = false)
	{
		$outer = self::$bbcodeShortcodes;
		self::$bbcodeShortcodes = (bool) $shortcodes;

		try
		{
			return e107::getBB()->parseBBCodes($text, $postID, $mode, $strip);
		}
		finally
		{
			self::$bbcodeShortcodes = $outer;
		}
	}

	/**
	 * Strips block tags from html.
	 * ie. <p> <div> <blockquote> <h1> <h2> <h3> etc are removed.
	 *
	 * @param string $html
	 * @return string
	 */
	public function stripBlockTags($html)
	{
		$diff = array_diff($this->allowedTags, $this->blockTags);

		$parm = '';
		foreach ($diff as $tag)
		{
			$parm .= '<' . $tag . '>';
		}

		return strip_tags($html, $parm !== null && is_array($parm) ? '<' . implode('><', $parm) . '>' : $parm);
	}

	/**
	 * @param $s
	 * @param $allowedattr
	 * @return array|mixed|string|string[]
	 */
	public function stripAttributes($s, $allowedattr = array())
	{

		if (preg_match_all("/<[^>]*\\s([^>]*)\\/*>/msiU", $s, $res, PREG_SET_ORDER))
		{
			foreach ($res as $r)
			{
				$tag = $r[0];
				$attrs = array();
				preg_match_all("/\\s.*=(['\"]).*\\1/msiU", " " . $r[1], $split, PREG_SET_ORDER);
				foreach ($split as $spl)
				{
					$attrs[] = $spl[0];
				}
				$newattrs = array();
				foreach ($attrs as $a)
				{
					$tmp = explode("=", $a);
					if (trim($a) != "" && (!isset($tmp[1]) || (trim($tmp[0]) != "" && !in_array(strtolower(trim($tmp[0])), $allowedattr))))
					{

					}
					else
					{
						$newattrs[] = $a;
					}
				}
				$attrs = implode(" ", $newattrs);
				$rpl = str_replace($r[1], $attrs, $tag);
				$s = str_replace($tag, $rpl, $s);
			}
		}

		return $s;
	}


	/** @var bool[] whether each author id {@see e_parse::shortcodeModifiersForAuthor()} has looked up is an administrator */
	private $adminAuthors = array();


	/**
	 * Add 'scparse_off' to $modifiers unless the author of the stored text is an administrator.
	 *
	 * @param string $modifiers {@see e_parse::toHTML()} modifiers for the field
	 * @param int    $authorId  user id stored with the text
	 * @return string
	 */
	public function shortcodeModifiersForAuthor($modifiers, $authorId)
	{
		$authorId = (int) $authorId;

		if (!isset($this->adminAuthors[$authorId]))
		{
			$author = ($authorId > 0) ? e107::user($authorId) : array();
			$this->adminAuthors[$authorId] = !empty($author['user_admin']);
		}

		if ($this->adminAuthors[$authorId])
		{
			return $modifiers;
		}

		return ltrim($modifiers . ',scparse_off', ',');
	}


	/**
	 * Render stored content for display: bbcode, shortcodes, {e_XXX} constants,
	 * emotes, clickable links and any hooked parser the site has enabled.
	 *
	 * THIS METHOD IS NOT A SANITISER AND NOT AN OUTPUT ENCODER.
	 *
	 * It renders content the site already trusts. Two properties in particular
	 * surprise people, and both have produced vulnerabilities in this codebase:
	 *
	 * 1. Script blocks are passed through by default. 'scripts' is TRUE in
	 *    {@see e_parse::$e_optDefault}, and a permitted <script> block is emitted
	 *    after html_entity_decode(), so any encoding the caller applied earlier is
	 *    undone. Only the USER_* and E_* contexts, and the 'scripts_off' modifier,
	 *    turn that off. Every other context, and the empty modifier string, leave
	 *    scripts enabled.
	 * 2. Nothing here is context aware. The result is a fragment of HTML, not an
	 *    attribute value, not a URL and not JavaScript. Placing it inside
	 *    title='...' or href='...' is unsafe however the modifiers are set.
	 *
	 * So for content that came from a visitor, a remote feed or any other party
	 * the site does not control, do NOT reach for toHTML() to make it safe:
	 *
	 * - Rendering it as body text: pass a USER_* context, which strips scripts.
	 * - Putting it in an attribute: use {@see e_parse::toAttribute()}.
	 * - Putting it in a URL: encode for the URL, not for HTML.
	 * - Wanting plain text out: use TITLE_PLAIN or the 'no_tags' modifier.
	 *
	 * MODIFIER GRAMMAR. $modifiers is a comma-separated list with no spaces. The
	 * first item may be a CONTEXT in UPPER CASE, which selects a whole option set
	 * from {@see e_parse::$e_SuperMods}; anything after it is a lower-case
	 * modifier from {@see e_parse::$e_Modifiers} overriding one option at a time.
	 * 'defaults_off' starts from the NODEFAULT set rather than the usual defaults.
	 * An unrecognised modifier is ignored silently, so a typo fails open.
	 *
	 * CONTEXTS, and what each is for:
	 *
	 *   TITLE, TITLE_PLAIN  a title the site authored. TITLE_PLAIN strips tags.
	 *   SUMMARY, DESCRIPTION, BODY   site-authored content of increasing length.
	 *   WYSIWYG             content round-tripping through the editor.
	 *   USER_TITLE, USER_BODY        content a visitor supplied. Scripts stripped.
	 *   E_TITLE, E_BODY, E_BODY_PLAIN   content leaving the site in mail, so no
	 *                       clickable links, no emotes and no scripts.
	 *   LINKTEXT, RAWTEXT   see $e_SuperMods for the exact option sets.
	 *
	 * Site preferences can still switch individual features off underneath a
	 * context (smileys, clickable links, the profanity filter), so a context is a
	 * ceiling on what is enabled, not a guarantee that it is.
	 *
	 * @param string  $text      content to render. Non-strings and empty values
	 *                           are returned unchanged.
	 * @param boolean|string $parseBB [optional] parse bbcode in $text, or a
	 *                           comma-separated list of codes to strip while
	 *                           parsing the rest
	 * @param string  $modifiers [optional] see MODIFIER GRAMMAR above
	 * @param mixed   $postID    [optional] id passed on to bbcode handlers that
	 *                           need to know which item they are rendering
	 * @param boolean $wrap      [optional] apply the site's word wrap preference
	 * @return string rendered HTML, or $text unchanged when it is empty or not a
	 *                string
	 * @see e_parse::toAttribute() for attribute context
	 * @see e_parse::toText() to go the other way
	 */
	public function toHTML($text, $parseBB = false, $modifiers = '', $postID = '', $wrap = false)
	{
		return $this->renderText($text, $parseBB, $modifiers, $postID, 0, null);
	}


	/**
	 * {@see e_parse::toHTML()} for a text $depth [hide] blocks deep: 0 for the text toHTML() was given; 1 for the content of a block set aside from that text, or one the hide bbcode put in a block's place, which is read for its own blocks; from 2, a region of a block nested in such a content, whose blocks are markers already and whose line-break fix and trim the caller makes.
	 *
	 * @param string      $text
	 * @param bool|string $parseBB
	 * @param string      $modifiers
	 * @param mixed       $postID
	 * @param int         $depth
	 * @param bool|null   $isHtml from depth 2, whether the region's source holds markup; null to tell from $text
	 * @return string
	 */
	private function renderText($text, $parseBB, $modifiers, $postID, $depth, $isHtml)
	{

		if (empty($text) || !is_string($text))
		{
			return $text;
		}

		if (strpos($text, '<!--') !== false || stripos($text, '<iframe') !== false)
		{
			$text = $this->inertHiddenText($text);
		}

		if (empty($this->pref)) // cache the prefs.
		{
			$prefsUsed = array('smiley_activate', 'make_clickable', 'link_replace', 'main_wordwrap', 'link_text',
				'email_text', 'links_new_window', 'profanity_filter', 'tohtml_hook', 'e_tohtml_list', 'e_parse_list'
			);

			$cfg = e107::getConfig();
			foreach ($prefsUsed as $v)
			{
				$this->pref[$v] = $cfg->get($v);
			}
		}

		global $fromadmin;

		if (($parseBB !== false) && ($depth < 2))
		{
			$text = $this->inertInsideTags($text, '#\[(?=/?(?:table|html|php|code|scode))#i', '&#91;', true);
		}

		// Set default modifiers to start
		$opts = $this->getModifiers($modifiers);
		$isHtml = ($isHtml === null) ? $this->isHtml($text) : $isHtml;
		$hideBlocks = array();
		$parseBlocksWith = $parseBB;
		$stripCodes = is_string($parseBB) ? explode(',', $parseBB) : array();

		if (($parseBB !== false) && ($depth < 2) && !in_array('hide', $stripCodes, true))
		{
			$text = $this->setAsideHideBlocks($text, $hideBlocks);
		}

		if ($isHtml) //BC FIx for when HTML is saved without [html][/html]
		{
			$opts['nobreak'] = true;
			$text = trim($text);

			if (strpos($text, '[center]') === 0) // quick bc fix TODO Find a better solution. [center][/center] containing HTML.
			{
				$text = str_replace(array('[center]', '[/center]'), array("<div style='text-align:center'>", '</div>'), $text);
			}
		}

		$fromadmin = $opts['fromadmin'];

		// Convert a language term named by the entire text, eg. LAN_XXXX (i.e. not embedded)
		if ($opts['defs'] && (strlen($text) < 35) && ($term = self::languageTerm($text)) !== null)
		{
			$text = $term; // don't return yet, words could be hooked with linkwords etc.
		}

		if ($opts['no_tags'])
		{
			$text = strip_tags($text);
		}
		/*
				if(MAGIC_QUOTES_GPC === true) // precaution for badly saved data.
				{
					$text = stripslashes($text);
				}
		*/

//		$text = " ".$text;


		// Now get on with the parsing
		$ret_parser = '';
		$last_bbcode = '';
		// So we can change them on each loop
		$saveOpts = $opts;


		$keyCodes = implode('|', array_diff(array('table', 'html', 'php', 'code', 'scode', 'hide'), $stripCodes));

		if ($parseBB == false || $keyCodes === '')
		{
			$content = array($text);
		}
		else
		{
			// Split each text block into bits which are either within one of the 'key' bbcodes, or outside them
			// (Because we have to match end words, the 'extra' capturing subpattern gets added to output array. We strip it later)
			$content = preg_split('#(\[(' . $keyCodes . ').*?\[\/(?:\\2)\])#mis', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		}


		// Use $full_text variable so its available to special bbcodes if required
		foreach ($content as $full_text)
		{
			$proc_funcs = true;
			$convertNL = true;
			$blockRendered = false;

			// We may have 'captured' a bbcode word - strip it if so
			if ($last_bbcode == $full_text)
			{
				$last_bbcode = '';
				$proc_funcs = false;
				$full_text = '';
			}
			else
			{
				// Set the options for this pass

				$opts = $saveOpts;


				// Have to have a good test in case a 'non-key' bbcode starts the block
				// - so pull out the bbcode parameters while we're there
				if (($parseBB !== false) && ($keyCodes !== '') && preg_match('#(^\[(' . $keyCodes . ')(.*?)\])(.*?)(\[/\\2\]$)#is', $full_text, $matches))
				{

					$proc_funcs = false;
					$full_text = '';
					$code_text = $matches[4];
					$bbcodes = function ($value) use ($postID)
					{
						return $this->parseBBCodesBeforeShortcodes($value, $postID, false);
					};
					//	$parm = $matches[3] ? substr($matches[3], 1) : '';
					$last_bbcode = $matches[2];

					switch ($matches[2])
					{
						case 'php' :

							$proc_funcs = false;
							$code_text = '';
							break;

						case 'html' : // This overrides and deprecates html.bb
							$proc_funcs = true;


							//	$code_text = str_replace("\r\n", " ", $code_text);
							//	$code_text = html_entity_decode($code_text, ENT_QUOTES, CHARSET);
							//	$code_text = str_replace('&','&amp;',$code_text); // validation safe.
							$html_start = '<!-- bbcode-html-start -->'; // markers for html-to-bbcode replacement. 
							$html_end = '<!-- bbcode-html-end -->';
							$full_text = str_replace(array('[html]', '[/html]'), '', $code_text); // quick fix.. security issue?

							$full_text = $this->parseBBCodesBeforeShortcodes($this->expandInsideTags($this->restoreEntitiesOutsideCode($full_text, $opts['value']), '[', $bbcodes), $postID, $opts['parse_sc']); // parse any embedded bbcodes eg. [img]
							$full_text = $this->replaceConstants($full_text, 'abs'); // parse any other paths using {e_....
							$full_text = $html_start . $full_text . $html_end;
							$full_text = $this->parseBBTags($full_text); // strip <bbcode> tags. 
							$opts['nobreak'] = true;
							$parseBB = false; // prevent further bbcode processing.
							$blockRendered = true;


							break;

						case 'table' : // strip <br /> from inside of <table>		
							$convertNL = false;
						//	break;

						case 'hide' :
							$proc_funcs = true;
							$full_text = $this->restoreEntitiesOutsideCode($matches[1], $opts['value']) . $this->expandInsideTags($this->restoreEntitiesOutsideCode($matches[4], $opts['value']), '[', $bbcodes) . $matches[5];
							$full_text = $this->parseBBCodesBeforeShortcodes($full_text, $postID, $opts['parse_sc']);
							$blockRendered = true;
							break;

						case 'scode':
						case 'code' :
							$full_text = $this->parseBBCodes($this->expandInsideTags($this->restoreEntitiesOutsideCode($matches[0], $opts['value']), '[', $bbcodes), $postID);
							break;
					}

				}
			}


			// Do the 'normal' processing - in principle, as previously - but think about the order.
			if ($proc_funcs && !empty($full_text)) // some more speed
			{
				// Split out <script>/<style> blocks so they are stripped or passed
				// through verbatim, never run through the normal text processing.
				foreach ($this->splitScriptStyle($full_text) as $block)
				{
					list($type, $sub_blk) = $block;

					if ($type === 'script') // Strip scripts unless permitted
					{
						if ($opts['scripts'])
						{
							$ret_parser .= html_entity_decode($sub_blk, ENT_QUOTES);
						}
					}
					elseif ($type === 'style')
					{
						// Its a style block - just pass it through unaltered - except, do we need the line break stuff? - QUERY XXX-01
						$ret_parser .= $sub_blk;
					}
					else
					{
						// Do 'normal' processing on a chunk

						//	Link substitution
						// Convert URL's to clickable links, unless modifiers or prefs override
						$sub_blk = $this->processModifiers($opts, $sub_blk, $convertNL, $blockRendered ? false : $parseBB, $modifiers, $postID, !$blockRendered);

						$ret_parser .= $sub_blk;
					}    // End of 'normal' processing for a block of text

				}        // End of 'foreach() on each block of non-script text

			}        // End of 'normal' parsing (non-script text)
			else
			{
				// Text block that needed no processing at all
				$ret_parser .= $full_text;
			}
		}

		if (!empty($hideBlocks))
		{
			$ret_parser = $this->renderHideBlocks($ret_parser, $hideBlocks, $parseBlocksWith, $modifiers, $postID, $depth);
		}

		if ($depth >= 2)
		{
			return $ret_parser;
		}

		// Quick Fix - Remove trailing <br /> on block-level elements (eg. div, pre, table, etc. )
		return trim($this->withFewerBreaks($ret_parser, 1));
	}


	/**
	 * Replace each [hide] block {@see e_bbcode::hideBlocks()} finds in $text, and that lies either within one tag or wholly outside tags, with a marker shaped as a tag, so that no other step of toHTML() can make or break one, and none can carry it into an attribute unencoded; write as an entity the bracket of every other [hide] or [/hide] a tag holds, and look once more for the blocks a refused one held. Each block keeps those {@see e_bbcode::hideBlockTree()} finds nested in its content.
	 *
	 * @param string $text
	 * @param array  $blocks receives the blocks and their markers
	 * @return string
	 */
	private function setAsideHideBlocks($text, &$blocks)
	{
		$bracket = '#\[(?=/?hide)#i';

		if (!preg_match($bracket, $text))
		{
			return $text;
		}

		for ($pass = 1; ; $pass++)
		{
			$pieces = $this->tagPieces($text, true);
			$tree = ($pieces === false) ? array() : e107::getBB()->hideBlockTree($text);
			$taken = array();
			$refused = false;
			$number = -1;

			foreach ($tree as $index => $block)
			{
				if ($block['parent'] !== -1)
				{
					continue;
				}

				$number++;

				if ($this->straddlesTag($pieces, $block))
				{
					$refused = true;
					continue;
				}

				$taken[$number] = $index;
			}

			$edits = array();
			$spans = array_values($taken);
			$next = 0;
			preg_match_all($bracket, $text, $brackets, PREG_OFFSET_CAPTURE);

			foreach ($brackets[0] as $match)
			{
				$at = $match[1];

				while (isset($spans[$next]) && ($tree[$spans[$next]]['start'] + $tree[$spans[$next]]['length'] <= $at))
				{
					$next++;
				}

				if ((!isset($spans[$next]) || ($at < $tree[$spans[$next]]['start'])) && ($pieces === false || $pieces[$this->tagPieceAt($pieces, $at)]['inTag']))
				{
					$edits[$at] = array(1, '&#91;');
				}
			}

			if (!$refused || $pass === 2 || empty($edits))
			{
				break;
			}

			$text = $this->spliceText($text, $edits);
		}

		if (!empty($taken))
		{
			$set = ++self::$hideMarkers;
		}

		foreach ($taken as $number => $index)
		{
			$block = $tree[$index];
			list($contentStart, $contentEnd) = $this->hideContentRange($block);
			$blocks[$number] = array(
				'start'   => $block['start'],
				'length'  => $block['length'],
				'open'    => $block['open'],
				'close'   => $block['close'],
				'content' => (string) substr($text, $contentStart, $contentEnd - $contentStart),
				'marker'  => '<e_hide_' . $set . '_' . $number . '>',
				'blocks'  => $this->nestedHideBlocks($tree, $index, $contentStart),
			);
			$edits[$block['start']] = array($block['length'], $blocks[$number]['marker']);
		}

		return $this->spliceText($text, $edits);
	}


	/**
	 * Put each block {@see e_parse::setAsideHideBlocks()} set aside back into $html, rendered by the hide bbcode, where its marker stands as a tag of its own, and put a space in place of every marker left anywhere else, so that the text either side cannot join; the bbcode may put other text in the block's place, such as a sealed block opened. A shown block's content is rendered as a text of its own at $depth 0, and from the blocks found nested in it below that.
	 *
	 * @param string      $html
	 * @param array       $blocks
	 * @param bool|string $parseBB
	 * @param string      $modifiers
	 * @param mixed       $postID
	 * @param int         $depth as {@see e_parse::renderText()} takes it
	 * @return string
	 */
	private function renderHideBlocks($html, $blocks, $parseBB, $modifiers, $postID, $depth)
	{
		$markers = array();

		foreach ($blocks as $number => $block)
		{
			$markers[$block['marker']] = $number;
		}

		$parts = $this->splitOnTags($html);

		if ($parts === false)
		{
			$parts = array($html);
		}

		foreach ($parts as $i => $part)
		{
			if (!isset($markers[$part]))
			{
				continue;
			}

			$block = $blocks[$markers[$part]];
			list($box, $content) = $this->hideBlockBox($block, $part, $postID);

			if (strpos($box, $part) !== false)
			{
				$box = str_replace($part, (($depth === 0) || ($content !== $block['content']))
					? $this->renderOwnText($content, $parseBB, $modifiers, $postID)
					: $this->renderNestedBlocks($block, $parseBB, $modifiers, $postID), $box);
			}

			$parts[$i] = $box;
		}

		return $this->withoutMarkers(implode('', $parts), array($this->markerSet($blocks) => array_flip(array_keys($blocks))));
	}


	/**
	 * The content of $block, set aside from the content of another, rendered region by region from the blocks found nested in it: the text of each region once, with a marker for each block in it that does not straddle a tag; each block's box from the hide bbcode before anything inside it is processed; a block whose content would cut a tag, or a [code] block holding one, read in a view of its own once the view it was found in is done with; the whole joined once.
	 *
	 * @param array       $block as {@see e_parse::setAsideHideBlocks()} sets it aside
	 * @param bool|string $parseBB
	 * @param string      $modifiers
	 * @param mixed       $postID
	 * @return string
	 */
	private function renderNestedBlocks($block, $parseBB, $modifiers, $postID)
	{
		$views = array($this->nestedView($block['content'], $block['blocks']));
		$pending = array(1);
		$regions = array();
		$work = array(array(0, -1, 2, 0));
		$later = array();
		$laterAt = 0;
		$nextRegion = 1;
		$depths = array(2);
		$regionSets = array();

		while ($work || isset($later[$laterAt]))
		{
			if ($work)
			{
				list($view, $index, $depth, $region) = array_pop($work);
			}
			else
			{
				list($view, $index, $depth, $region) = $later[$laterAt];
				unset($later[$laterAt++]);
			}

			$content = $views[$view]['content'];
			$nested = $views[$view]['nested'];
			$read = $views[$view]['read'];

			list($start, $end) = ($index < 0) ? array(0, strlen($content)) : $this->hideContentRange($nested[$index]);
			$markers = array();
			$text = '';
			$at = $start;
			$set = 0;

			foreach (isset($read['children'][$index]) ? $read['children'][$index] : array() as $number => $child)
			{
				if (!$set)
				{
					$set = ++self::$hideMarkers;
				}

				$marker = '<e_hide_' . $set . '_' . $number . '>';
				$markers[$marker] = $child;
				$regionSets[$region][$set][$number] = true;
				$text .= $this->editedSlice($content, $read['edits'], $at, $nested[$child]['start']) . $marker;
				$at = $nested[$child]['start'] + $nested[$child]['length'];
			}

			$text = $this->withoutPairedHideTags($text . $this->editedSlice($content, $read['edits'], $at, $end));

			$html = $this->renderText($text, $parseBB, $modifiers, $postID, $depth, $markers ? $this->sourceIsHtml($read, $start, $end) : null);
			$parts = $markers ? $this->splitOnTags($html) : false;
			$out = array();
			$buffer = '';
			$children = array();

			foreach (($parts === false) ? array($html) : $parts as $part)
			{
				if (!isset($markers[$part]))
				{
					$buffer .= $part;
					continue;
				}

				$child = $nested[$markers[$part]];
				list($childStart, $childEnd) = $this->hideContentRange($child);
				$child['content'] = (string) substr($content, $childStart, $childEnd - $childStart);
				list($box, $shown) = $this->hideBlockBox($child, $part, $postID);

				if (strpos($box, $part) === false)
				{
					$buffer .= $box;
					continue;
				}

				if ($shown !== $child['content'])
				{
					$buffer .= str_replace($part, $this->renderOwnText($shown, $parseBB, $modifiers, $postID), $box);
					continue;
				}

				$id = $nextRegion++;
				$depths[$id] = $depth + 1;

				if (isset($read['own'][$markers[$part]]))
				{
					$childView = count($pending);
					$views[$childView] = $this->nestedView($child['content'], $this->nestedHideBlocks($nested, $markers[$part], $childStart));
					$pending[$childView] = 1;
					$later[] = array($childView, -1, $depth + 1, $id);
				}
				else
				{
					$pending[$view]++;
					$children[] = array($view, $markers[$part], $depth + 1, $id);
				}

				foreach (explode($part, $box) as $j => $slice)
				{
					if ($j)
					{
						$out[] = $buffer;
						$out[] = $id;
						$buffer = '';
					}

					$buffer .= $slice;
				}
			}

			$out[] = $buffer;
			$regions[$region] = $out;

			foreach (array_reverse($children) as $child)
			{
				$work[] = $child;
			}

			if (--$pending[$view] === 0)
			{
				unset($views[$view]);
			}
		}

		$output = array();
		$stack = array(array(0, 0));
		$sets = isset($regionSets[0]) ? $regionSets[0] : array();

		while ($stack)
		{
			$top = count($stack) - 1;
			list($region, $i) = $stack[$top];
			$last = count($regions[$region]) - 1;

			if ($i > $last)
			{
				if (isset($regionSets[$region]))
				{
					unset($sets[key($regionSets[$region])]);
				}

				array_pop($stack);
				continue;
			}

			$stack[$top][1] = $i + 1;

			if ($i % 2)
			{
				$child = $regions[$region][$i];
				$sets += isset($regionSets[$child]) ? $regionSets[$child] : array();
				$stack[] = array($child, 0);
				continue;
			}

			$part = $this->withFewerBreaks($this->withoutMarkers($regions[$region][$i], $sets), $depths[$region] - 1);
			$part = ($i === 0) ? ltrim($part) : $part;
			$output[] = ($i === $last) ? rtrim($part) : $part;
		}

		return implode('', $output);
	}


	/**
	 * What {@see e_parse::renderNestedBlocks()} needs to know of $content once: the brackets of [hide] and [/hide] a tag holds, which each region writes as entities; for each nested block, those of the blocks in its content it sets aside, in order, leaving as typed one that straddles a tag; which of them begin or end inside a tag, or inside a [code] block while their content holds a < and letter, and so read tags or [code] blocks otherwise than $content does; and where markup lies, to tell whether a region's source holds any.
	 *
	 * @param string  $content
	 * @param array[] $nested
	 * @return array with the keys edits (offsets, ascending), children (block index, -1 for $content itself => block indexes), own (block index => true), html (offsets of [html], ascending) and markup (the starts and the ends of each tag the markup test reads, ascending)
	 */
	private function readNestedContent($content, $nested)
	{
		$read = array('edits' => array(), 'children' => array(), 'own' => array(), 'html' => array(), 'markup' => array(array(), array()));

		if (!preg_match('#\[(?=/?hide)#i', $content))
		{
			return $read;
		}

		$pieces = $this->tagPieces($content, true);
		preg_match_all('#\[(?=/?hide)#i', $content, $brackets, PREG_OFFSET_CAPTURE);

		foreach ($brackets[0] as $match)
		{
			if ($pieces === false || $pieces[$this->tagPieceAt($pieces, $match[1])]['inTag'])
			{
				$read['edits'][] = $match[1];
			}
		}

		if ($pieces === false || empty($nested))
		{
			return $read;
		}

		$spans = array(array(), array());
		$tags = array();

		foreach ($pieces as $piece)
		{
			if ($piece['tag'])
			{
				continue;
			}

			if ((stripos($piece['text'], '[code') !== false) && preg_match_all(self::$codeBlockPattern, $piece['text'], $found, PREG_OFFSET_CAPTURE))
			{
				foreach ($found[0] as $span)
				{
					$spans[0][] = $piece['offset'] + $span[1];
					$spans[1][] = $piece['offset'] + $span[1] + strlen($span[0]);
				}
			}

			if (preg_match_all('#<[a-z]#i', $piece['text'], $found, PREG_OFFSET_CAPTURE))
			{
				foreach ($found[0] as $tag)
				{
					$tags[] = $piece['offset'] + $tag[1];
				}
			}
		}

		$refused = array();

		foreach ($nested as $index => $block)
		{
			if (($block['parent'] >= 0 && isset($refused[$block['parent']])) || $this->straddlesTag($pieces, $block))
			{
				$refused[$index] = true;
				continue;
			}

			$read['children'][$block['parent']][] = $index;
			list($start, $end) = $this->hideContentRange($block);
			$tag = $this->lastBefore($tags, $start);

			if ((isset($tags[$tag]) && ($tags[$tag] + 1 < $end) && ($this->insideSpan($spans, $start) || $this->insideSpan($spans, $end)))
				|| $this->insideTag($pieces, $start) || $this->insideTag($pieces, $end))
			{
				$read['own'][$index] = true;
			}
		}

		preg_match_all('#\[html\]#', $content, $html, PREG_OFFSET_CAPTURE);
		preg_match_all('#(?<=<)\w+(?=([^<]*?)>)#', $content, $witnesses, PREG_OFFSET_CAPTURE);

		foreach ($html[0] as $match)
		{
			$read['html'][] = $match[1];
		}

		foreach ($witnesses[0] as $i => $match)
		{
			$read['markup'][0][] = $match[1] - 1;
			$read['markup'][1][] = $witnesses[1][$i][1] + strlen($witnesses[1][$i][0]);
		}

		return $read;
	}


	/**
	 * $text with the bracket of every [hide] and [/hide] held by the opening or the closing tag of a [hide] pair {@see e_bbcode::hideBlockTree()} finds in it written as an entity.
	 *
	 * @param string $text
	 * @return string
	 */
	private function withoutPairedHideTags($text)
	{
		$edits = array();

		foreach (e107::getBB()->hideBlockTree($text) as $block)
		{
			$close = $block['start'] + $block['length'] - strlen($block['close']);

			foreach (array($block['start'] => $block['open'], $close => $block['close']) as $at => $tag)
			{
				preg_match_all('#\[(?=/?hide)#i', $tag, $brackets, PREG_OFFSET_CAPTURE);

				foreach ($brackets[0] as $bracket)
				{
					$edits[$at + $bracket[1]] = array(1, '&#91;');
				}
			}
		}

		return $this->spliceText($text, $edits);
	}


	/**
	 * Whether offset $at lies strictly inside a tag of $pieces, so that a content beginning or ending there would read the tag as text.
	 *
	 * @param array[] $pieces as {@see e_parse::tagPieces()} cuts the text
	 * @param int     $at
	 * @return bool
	 */
	private function insideTag($pieces, $at)
	{
		$piece = $pieces[$this->tagPieceAt($pieces, $at)];

		return $piece['tag'] && ($piece['offset'] < $at) && ($at < $piece['offset'] + strlen($piece['text']));
	}


	/**
	 * Whether offset $at lies strictly inside one of the [code] spans {@see e_parse::readNestedContent()} found.
	 *
	 * @param int[][] $spans the starts and the ends of the spans, ascending
	 * @param int     $at
	 * @return bool
	 */
	private function insideSpan($spans, $at)
	{
		$span = $this->lastBefore($spans[0], $at) - 1;

		return ($span >= 0) && ($spans[1][$span] > $at);
	}


	/**
	 * The number of $sorted (ascending) that are less than $at.
	 *
	 * @param int[] $sorted
	 * @param int   $at
	 * @return int
	 */
	private function lastBefore($sorted, $at)
	{
		$low = 0;
		$high = count($sorted);

		while ($low < $high)
		{
			$middle = ($low + $high) >> 1;

			if ($sorted[$middle] < $at)
			{
				$low = $middle + 1;
			}
			else
			{
				$high = $middle;
			}
		}

		return $low;
	}


	/**
	 * The hide bbcode's box for $block, with $marker in place of its content, and the content the bbcode shows there, which it may put in place of the block's own, such as a sealed block opened.
	 *
	 * @param array  $block with the keys start, length, open, close and content
	 * @param string $marker
	 * @param mixed  $postID
	 * @return string[] the box, then the content
	 */
	private function hideBlockBox($block, $marker, $postID)
	{
		global $e_hide_block;

		$was = $e_hide_block;
		$e_hide_block = array(
			'start'   => $block['start'],
			'length'  => $block['length'],
			'open'    => $block['open'],
			'close'   => $block['close'],
			'content' => $block['content'],
			'marker'  => $marker,
		);
		$box = e107::getBB()->parseBBCodes('[hide]' . $marker . '[/hide]', $postID);
		$content = $e_hide_block['content'];
		$e_hide_block = $was;

		return array($box, $content);
	}


	/**
	 * The blocks of $tree nested in the content of block $index, which begins at byte $offset, each with its start counted from there and its parent counted among them, -1 for the block itself.
	 *
	 * @param array[] $tree as {@see e_bbcode::hideBlockTree()} returns it, or a part of it so counted
	 * @param int     $index
	 * @param int     $offset
	 * @return array[]
	 */
	private function nestedHideBlocks($tree, $index, $offset)
	{
		$nested = array();
		$end = $tree[$index]['start'] + $tree[$index]['length'];

		for ($i = $index + 1; isset($tree[$i]) && ($tree[$i]['start'] < $end); $i++)
		{
			$block = $tree[$i];
			$block['start'] -= $offset;
			$block['parent'] = ($block['parent'] === $index) ? -1 : $block['parent'] - $index - 1;
			$nested[] = $block;
		}

		return $nested;
	}


	/**
	 * Whether $block begins and ends in different pieces of $pieces, either of them a tag.
	 *
	 * @param array[] $pieces as {@see e_parse::tagPieces()} cuts the text
	 * @param array   $block with the keys start and length
	 * @return bool
	 */
	private function straddlesTag($pieces, $block)
	{
		$first = $this->tagPieceAt($pieces, $block['start']);
		$last = $this->tagPieceAt($pieces, $block['start'] + $block['length'] - 1);

		return ($first !== $last) && ($pieces[$first]['tag'] || $pieces[$last]['tag']);
	}


	/**
	 * $text with each of $edits made, offset => array(length, replacement), the spans they replace apart from one another.
	 *
	 * @param string  $text
	 * @param array[] $edits
	 * @return string
	 */
	private function spliceText($text, $edits)
	{
		ksort($edits);
		$parts = array();
		$at = 0;

		foreach ($edits as $offset => $edit)
		{
			$parts[] = (string) substr($text, $at, $offset - $at);
			$parts[] = $edit[1];
			$at = $offset + $edit[0];
		}

		$parts[] = (string) substr($text, $at);

		return implode('', $parts);
	}


	/**
	 * The bytes of $content from $from to $to, the bracket at each of $edits (offsets, ascending) written as an entity.
	 *
	 * @param string $content
	 * @param int[]  $edits
	 * @param int    $from
	 * @param int    $to
	 * @return string
	 */
	private function editedSlice($content, $edits, $from, $to)
	{
		$low = $this->lastBefore($edits, $from);
		$slice = '';

		for ($at = $from; isset($edits[$low]) && ($edits[$low] < $to); $low++)
		{
			$slice .= substr($content, $at, $edits[$low] - $at) . '&#91;';
			$at = $edits[$low] + 1;
		}

		return $slice . (string) substr($content, $at, $to - $at);
	}


	/**
	 * Whether the source from $from to $to of the content {@see e_parse::readNestedContent()} read holds markup as {@see e_parse::isHtml()} tells it.
	 *
	 * @param array $read
	 * @param int   $from
	 * @param int   $to
	 * @return bool
	 */
	private function sourceIsHtml($read, $from, $to)
	{
		$html = $this->lastBefore($read['html'], $from);
		$markup = $this->lastBefore($read['markup'][0], $from);

		return (isset($read['html'][$html]) && ($read['html'][$html] + 6 <= $to)) || (isset($read['markup'][1][$markup]) && ($read['markup'][1][$markup] < $to));
	}


	/**
	 * $nested, read in $before, with each offset and tag moved to where it lies in $after, which is $before with some of its < written as &lt;.
	 *
	 * @param array[] $nested
	 * @param string  $before
	 * @param string  $after
	 * @return array[]
	 */
	private function movedBlocks($nested, $before, $after)
	{
		$moved = array();
		$shift = 0;

		for ($i = 0, $j = 0, $length = strlen($before); $i < $length; $i++, $j++)
		{
			$moved[$i] = $shift;

			if (($before[$i] === '<') && ($after[$j] === '&'))
			{
				$j += 3;
				$shift += 3;
			}
		}

		$moved[$length] = $shift;

		foreach ($nested as $index => $block)
		{
			$start = $block['start'] + $moved[$block['start']];
			list($contentStart, $contentEnd) = $this->hideContentRange($block);
			$end = $block['start'] + $block['length'];
			$nested[$index]['start'] = $start;
			$nested[$index]['length'] = $end + $moved[$end] - $start;
			$nested[$index]['open'] = (string) substr($after, $start, $contentStart + $moved[$contentStart] - $start);
			$nested[$index]['close'] = (string) substr($after, $contentEnd + $moved[$contentEnd], $end + $moved[$end] - $contentEnd - $moved[$contentEnd]);
		}

		return $nested;
	}


	/**
	 * $html with a space in place of every marker of $sets left in it, as typed, entity-encoded or percent-encoded, so that the text either side cannot join.
	 *
	 * @param string $html
	 * @param array  $sets set number => the numbers of its markers, as keys
	 * @return string
	 */
	private function withoutMarkers($html, $sets)
	{
		if (empty($sets) || (strpos($html, 'e_hide_') === false))
		{
			return $html;
		}

		return preg_replace_callback('#<e_hide_(\d+)_(\d+)>|&lt;e_hide_(\d+)_(\d+)(?:&gt;|&\#x3E;)|%3Ce_hide_(\d+)_(\d+)%3E#', function ($match) use ($sets)
		{
			$at = ($match[1] !== '') ? 1 : ((isset($match[3]) && $match[3] !== '') ? 3 : 5);

			return isset($sets[$match[$at]][$match[$at + 1]]) ? ' ' : $match[0];
		}, $html);
	}


	/**
	 * The set number the markers of $blocks share.
	 *
	 * @param array[] $blocks
	 * @return int
	 */
	private function markerSet($blocks)
	{
		$block = reset($blocks);

		return (int) substr($block['marker'], 8);
	}


	/**
	 * $html with up to $times line breaks taken from each run that follows the end of a block element, which carries a line break of its own.
	 *
	 * @param string $html
	 * @param int    $times
	 * @return string
	 */
	private function withFewerBreaks($html, $times)
	{
		if (($times < 1) || (strpos($html, '><br />') === false))
		{
			return $html;
		}

		$ends = array();

		foreach ($this->blockTags as $tag)
		{
			$ends[] = '</' . $tag . '>';
		}

		$out = '';
		$at = 0;

		for ($gt = strpos($html, '><br />'); $gt !== false; $gt = strpos($html, '><br />', $run))
		{
			$run = $gt + 1;

			foreach ($ends as $end)
			{
				if (($run >= strlen($end)) && ((string) substr($html, $run - strlen($end), strlen($end)) === $end))
				{
					for ($breaks = 0; (string) substr($html, $run, 6) === '<br />'; $breaks++)
					{
						$run += 6;
					}

					$out .= substr($html, $at, $gt + 1 - $at) . str_repeat('<br />', max(0, $breaks - $times));
					$at = $run;
					break;
				}
			}
		}

		return $out . (string) substr($html, $at);
	}


	/**
	 * The offsets of the content of $block, from its start up to its closing tag.
	 *
	 * @param array $block with the keys start, length, open and close
	 * @return int[]
	 */
	private function hideContentRange($block)
	{
		return array($block['start'] + strlen($block['open']), $block['start'] + $block['length'] - strlen($block['close']));
	}


	/**
	 * $content, shown in a [hide] block, rendered as a text of its own, with every < of a tag the parser cannot read written as &lt;.
	 *
	 * @param string      $content
	 * @param bool|string $parseBB
	 * @param string      $modifiers
	 * @param mixed       $postID
	 * @return string
	 */
	private function renderOwnText($content, $parseBB, $modifiers, $postID)
	{
		return $this->renderText($this->inertInsideTags($content, '#<#', '&lt;', false), $parseBB, $modifiers, $postID, 1, null);
	}


	/**
	 * The view {@see e_parse::renderNestedBlocks()} renders regions from: $source with every < of a tag the parser cannot read written as &lt;, the blocks $nested in it moved to match, and what it reads of them.
	 *
	 * @param string  $source
	 * @param array[] $nested counted from the start of $source
	 * @return array with the keys content, nested and read
	 */
	private function nestedView($source, $nested)
	{
		$content = $this->inertInsideTags($source, '#<#', '&lt;', false);
		$nested = ($content === $source) ? $nested : $this->movedBlocks($nested, $source, $content);

		return array('content' => $content, 'nested' => $nested, 'read' => $this->readNestedContent($content, $nested));
	}


	/**
	 * Separate top-level <script> and <style> blocks from the surrounding markup.
	 *
	 * {@see e_parse::toHTML()} must strip or pass these blocks through verbatim
	 * instead of running the normal text processing over their contents. This
	 * replaces a single fragile regex (`<script[^>]+>`) that silently missed
	 * attribute-less tags such as a bare `<script>` (and, because the downstream
	 * check was case-sensitive, any `<SCRIPT>`), letting them leak through the
	 * normal-text path even when scripts were disabled.
	 *
	 * @param string $html
	 * @return array[] ordered [type, chunk] pairs where type is 'script', 'style' or 'text'
	 */
	private function splitScriptStyle($html)
	{
		$blocks = array();
		$length = strlen($html);
		$pos = 0;        // scan cursor
		$textStart = 0;  // start of the pending run of normal text

		while (($lt = strpos($html, '<', $pos)) !== false)
		{
			$tag = $this->rawTextStartTag($html, $lt, array('script', 'style'));
			if ($tag === false)
			{
				$pos = $lt + 1;
				continue;
			}

			$end = $this->rawTextEnd($html, $lt, $tag);

			if ($lt > $textStart)
			{
				$blocks[] = array('text', (string) substr($html, $textStart, $lt - $textStart));
			}
			$blocks[] = array($tag, (string) substr($html, $lt, $end - $lt));

			$pos = $end;
			$textStart = $end;
		}

		if ($textStart < $length)
		{
			$blocks[] = array('text', (string) substr($html, $textStart));
		}

		return $blocks;
	}


	/**
	 * Test whether the `<` at $offset opens a start tag with one of the given names.
	 *
	 * The tag name must be followed by a delimiter (`>`, `/` or whitespace) so a
	 * name such as `<scripting>` is not mistaken for a script tag.
	 *
	 * @param string   $html
	 * @param int      $offset position of a `<` within $html
	 * @param string[] $tags   names of elements whose content a browser reads as raw text
	 * @return string|false the name found, or false
	 */
	private function rawTextStartTag($html, $offset, $tags)
	{
		foreach ($tags as $tag)
		{
			if (strcasecmp((string) substr($html, $offset + 1, strlen($tag)), $tag) !== 0)
			{
				continue;
			}
			if ($this->endsTagName($html, $offset + 1 + strlen($tag)))
			{
				return $tag;
			}
		}
		return false;
	}

	/**
	 * @param string $html
	 * @param int    $offset just past a tag name
	 * @return bool whether a browser ends the tag name there: at '>', '/', space, tab, line feed, form feed or carriage return
	 */
	private function endsTagName($html, $offset)
	{
		$next = (string) substr($html, $offset, 1);

		return $next !== '' && strpos(" \t\n\f\r/>", $next) !== false;
	}


	/**
	 * @param string $html
	 * @param int    $offset where the start tag begins
	 * @param string $tag    the name rawTextStartTag() found there
	 * @return int the offset just past its closing tag, or the end of $html when it has none
	 */
	private function rawTextEnd($html, $offset, $tag)
	{
		$close = $this->rawTextClose($html, $offset, $tag);
		$gt = ($close < strlen($html)) ? strpos($html, '>', $close) : false;

		return ($gt === false) ? strlen($html) : $gt + 1;
	}

	/**
	 * @param string $html
	 * @param int    $offset where the start tag begins
	 * @param string $tag    the name rawTextStartTag() found there
	 * @return int where the closing tag that ends it begins, or the end of $html when it has none
	 */
	private function rawTextClose($html, $offset, $tag)
	{
		$close = $offset;
		while (($close = stripos($html, '</' . $tag, $close + 1)) !== false)
		{
			if ($this->endsTagName($html, $close + 2 + strlen($tag)))
			{
				return $close;
			}
		}

		return strlen($html);
	}

	/**
	 * @param string $text
	 * @return string $text with & < > [ { encoded, so that nothing later reads markup, a bbcode or a shortcode in it
	 */
	private function hiddenText($text)
	{
		return strtr($text, array('&' => '&amp;', '<' => '&lt;', '>' => '&#62;', '[' => '&#91;', '{' => '&#123;'));
	}

	/**
	 * @param string $text stored HTML
	 * @return string $text with the text of each comment and each iframe encoded, as hiddenText() does; script and style blocks unchanged
	 */
	private function inertHiddenText($text)
	{
		$out = '';
		$copied = 0;
		$pos = 0;
		$iframeClose = null;

		while (true)
		{
			$lt = strpos($text, '<', $pos);

			if ($iframeClose !== null && ($lt === false || $lt >= $iframeClose))
			{
				$out .= $this->hiddenText((string) substr($text, $copied, $iframeClose - $copied));
				$copied = $pos = $iframeClose;
				$iframeClose = null;
				continue;
			}

			if ($lt === false)
			{
				break;
			}

			if ((string) substr($text, $lt, 4) === '<!--')
			{
				$close = strpos($text, '-->', $lt + 4);
				$innerEnd = ($close === false) ? strlen($text) : $close;
				$before = (string) substr($text, $copied, $lt - $copied);

				$out .= (($iframeClose === null) ? $before : $this->hiddenText($before))
					. '<!--' . $this->hiddenText((string) substr($text, $lt + 4, $innerEnd - $lt - 4)) . '-->';
				$copied = $pos = ($close === false) ? strlen($text) : $close + 3;

				if ($iframeClose !== null && $pos > $iframeClose)
				{
					$iframeClose = $this->rawTextClose($text, $pos - 1, 'iframe');
				}
				continue;
			}

			$tag = ($iframeClose === null) ? $this->rawTextStartTag($text, $lt, array('script', 'style', 'iframe')) : false;

			if ($tag === 'iframe')
			{
				$gt = strpos($text, '>', $lt);
				if ($gt === false)
				{
					break;
				}
				$out .= substr($text, $copied, $gt + 1 - $copied);
				$copied = $pos = $gt + 1;
				$iframeClose = max($pos, $this->rawTextClose($text, $lt, 'iframe'));
				continue;
			}

			$pos = ($tag === false) ? $lt + 1 : $this->rawTextEnd($text, $lt, $tag);
		}

		return $out . substr($text, $copied);
	}

	/**
	 * Check if a string begins with a preformatter flag.
	 *
	 * @param $str
	 * @return bool
	 */
	private function preformatted($str)
	{

		foreach ($this->preformatted as $type)
		{
			$code = '[' . $type . ']';
			if (strpos($str, $code) === 0)
			{
				return true;
			}

		}

		return false;
	}


	/**
	 * @param $mixed
	 * @return array|false|string
	 */
	public function toUTF8($mixed)
	{

		if (is_array($mixed))
		{
			foreach ($mixed as $k => $v)
			{
				unset($mixed[$k]);
				$mixed[$this->toUTF8($k)] = $this->toUTF8($v);
			}
		}
		elseif (is_object($mixed))
		{
			$objVars = get_object_vars($mixed);
			foreach ($objVars as $key => $value)
			{
				$mixed->$key = $this->toUTF8($value);
			}
		}
		elseif (is_string($mixed))
		{
			if (function_exists('mb_convert_encoding'))
			{
				$converted = mb_convert_encoding($mixed, 'UTF-8', 'ISO-8859-1');

				return function_exists('iconv') ? iconv('UTF-8', 'UTF-8//IGNORE', $converted) : $converted;
			}

			if (function_exists('iconv'))
			{
				return iconv('ISO-8859-1', 'UTF-8', $mixed);
			}

			return $mixed;
		}

		return $mixed;
	}


	/**
	 * @param string $text
	 * @return string
	 */
	public function toASCII($text)
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
			'Ş' => 'S', 'İ' => 'I', /*'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O',*/
			'Ğ' => 'G',
			'ş' => 's', 'ı' => 'i', /*'ç' => 'c', 'ü' => 'u', 'ö' => 'o',*/
			'ğ' => 'g',
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
			'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', /*'Ó' => 'o',*/
			'Ś' => 'S', 'Ź' => 'Z',
			'Ż' => 'Z',
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',/* 'ó' => 'o',*/
			'ś' => 's', 'ź' => 'z',
			'ż' => 'z',
			// Latvian
			'Ā' => 'A',/* 'Č' => 'C',*/
			'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
			/*'Š' => 'S',*/
			'Ū' => 'u',
			'ā' => 'a', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
			'ū' => 'u',

			'ľ' => 'l', 'ŕ' => 'r', 'Ľ' => 'l',
		);

		return str_replace(array_keys($char_map), $char_map, $text);

	}


	/**
	 * Use it on html attributes to avoid breaking markup .
	 *
	 * @param string $text
	 * @param bool   $pure True to leave web path constants such as {e_PLUGIN} unexpanded
	 * @example echo "<a href='#' title='".$tp->toAttribute($text)."'>Hello</a>";
	 */
	public function toAttribute($text, $pure = false)
	{

		// URLs posted without HTML access may have an &amp; in them.

		// Xhtml compliance.
		$text = htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');

		if (!$pure)
		{
			if (!preg_match('/&#|\'|"|<|>/s', $text))
			{
				$text = $this->expandConstants($text, self::$webPathConstants);
			}

			$text = $this->disarmConstantPlaceholders($text);
		}

		return $text;
	}

	/**
	 * Encode a URL for an href or src attribute, refusing any scheme that can execute.
	 *
	 * {@see toAttribute()} makes a value safe to sit between quotes, which is only half
	 * the job for a URL: `javascript:` and `data:text/html` contain no character
	 * htmlspecialchars() touches, so an encoded attribute still fires on click. Remote
	 * and visitor-supplied URLs belong here rather than in toAttribute().
	 *
	 * Relative and protocol-relative URLs are kept: they cannot name a scheme, and
	 * feeds and stored request paths legitimately use both.
	 *
	 * @param string $url
	 * @param string $fallback stands in for a URL whose scheme is not allowed
	 * @return string encoded, ready to place between quotes
	 */
	public function toUrlAttribute($url, $fallback = '')
	{
		$url = (string) $url;

		// Browsers skip control characters and whitespace while reading a scheme.
		$probe = eShims::strtolower(preg_replace('/[\x00-\x20\x7F]/', '', $url));

		if (preg_match('#^([a-z][a-z0-9+.\-]*):#', $probe, $match)
			&& !in_array($match[1], array('http', 'https', 'ftp', 'ftps', 'mailto'), true))
		{
			$url = $fallback;
		}

		return $this->disarmConstantPlaceholders($this->toAttribute($url, true));
	}

	/**
	 * Encode a bbcode's URL for an href or src attribute once the constants and shortcodes toHTML() expands in it are expanded, leaving the calling bbcode's globals as they were.
	 *
	 * @param string $url
	 * @return string encoded with every opening brace as an entity, or '' when there is nothing {@see e_parse::toUrlAttribute()} will link
	 */
	public function toBBCodeUrlAttribute($url)
	{
		global $code_text, $parm, $postID, $fromadmin;

		$url = $this->expandConstants((string) $url, self::$webPathConstants);

		if (self::$bbcodeShortcodes && strpos($url, '{') !== false)
		{
			$bbcode = array($code_text, $parm, $postID, $fromadmin);

			try
			{
				$url = (string) $this->parseTemplate($this->disarmShortcodeParameters($url), true);
			}
			finally
			{
				list($code_text, $parm, $postID, $fromadmin) = $bbcode;
			}
		}

		return str_replace('{', '&#123;', $this->toUrlAttribute($url));
	}

	/**
	 * Encode a value as a complete JavaScript string literal, safe inside an HTML attribute
	 * the browser decodes before compiling it, which {@see e_parse::toAttribute()} is not.
	 *
	 * @param string $text
	 * @return string a complete JavaScript string literal, its delimiting quotes included
	 */
	public function toJsString($text)
	{
		$encoded = json_encode((string) $text, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP);

		return $encoded === false ? '""' : $encoded;
	}

	/**
	 * Write as $entity each match of $pattern in text holding a tag the parser cannot read, and in the tags it reads when $tagsToo, so that the match can neither cut a tag in two nor open one; in all of $text when it defeats the pattern engine.
	 *
	 * @param string $text
	 * @param string $pattern
	 * @param string $entity
	 * @param bool   $tagsToo
	 * @return string
	 */
	private function inertInsideTags($text, $pattern, $entity, $tagsToo)
	{
		if (!preg_match($pattern, $text))
		{
			return $text;
		}

		$pieces = $this->tagPieces($text, true);

		if ($pieces === false)
		{
			return preg_replace($pattern, $entity, $text);
		}

		$text = '';

		foreach ($pieces as $piece)
		{
			$text .= ($piece['inTag'] && ($tagsToo || !$piece['tag'])) ? preg_replace($pattern, $entity, $piece['text']) : $piece['text'];
		}

		return $text;
	}

	/**
	 * The pieces {@see e_parse::splitOnTags()} cuts $text into, each with its byte offset, whether it is a tag, and whether a browser may read it as part of one: a tag, or text holding a tag the pattern cannot read, outside the [code] blocks when the bbcode parser reads them.
	 *
	 * @param string $text
	 * @param bool   $codeRead whether the bbcode parser reads the text's [code] blocks, which escape what they hold
	 * @return array[]|false each with the keys text, offset, tag and inTag; false when the text defeats the pattern engine
	 */
	private function tagPieces($text, $codeRead)
	{
		$segments = $this->splitOnTags($text);

		if ($segments === false)
		{
			return false;
		}

		$pieces = array();
		$offset = 0;

		foreach ($segments as $i => $segment)
		{
			$tag = ($i % 2 === 1);
			$pieces[] = array(
				'text'   => $segment,
				'offset' => $offset,
				'tag'    => $tag,
				'inTag'  => $tag || (preg_match('#<[a-z]#i', $segment) === 1 && (!$codeRead || preg_match('#<[a-z]#i', (string) preg_replace(self::$codeBlockPattern, '', $segment)) === 1)),
			);
			$offset += strlen($segment);
		}

		return $pieces;
	}

	/**
	 * The index of the piece {@see e_parse::tagPieces()} made that holds byte $at.
	 *
	 * @param array[] $pieces
	 * @param int     $at
	 * @return int
	 */
	private function tagPieceAt($pieces, $at)
	{
		$low = 0;
		$high = count($pieces) - 1;

		while ($low < $high)
		{
			$middle = (int) (($low + $high + 1) / 2);

			if ($pieces[$middle]['offset'] <= $at)
			{
				$low = $middle;
			}
			else
			{
				$high = $middle - 1;
			}
		}

		return $low;
	}

	/**
	 * Expand the codes $marker opens in each quoted attribute value of a tag, one value at a time and encoded for that attribute, and leave $marker inert anywhere else a tag holds it.
	 *
	 * @param string   $text
	 * @param string   $marker the character that opens a code
	 * @param callable $expand expands one attribute value as it is written
	 * @return string
	 */
	private function expandInsideTags($text, $marker, $expand)
	{
		if (strpos($text, $marker) === false)
		{
			return $text;
		}

		$inert = '&#' . ord($marker) . ';';
		$pieces = $this->tagPieces($text, $marker === '[');

		if ($pieces === false)
		{
			return str_replace($marker, $inert, $text);
		}

		$segments = array();

		foreach ($pieces as $i => $piece)
		{
			$segment = $segments[$i] = $piece['text'];

			if (strpos($segment, $marker) === false || !$piece['inTag'] || !preg_match('#<[a-z]#i', $segment))
			{
				continue;
			}

			if (!$piece['tag'] || !preg_match('#^</?[a-z]#i', $segment))
			{
				$segments[$i] = str_replace($marker, $inert, $segment);
				continue;
			}

			$parts = preg_split('#("[^"]*"|\'[^\']*\'|&quot;.*?&quot;|&\#0?39;.*?&\#0?39;)#s', $segment, -1, PREG_SPLIT_DELIM_CAPTURE);

			foreach ($parts as $j => $part)
			{
				if ($j % 2 === 1 && strpos($part, $marker) !== false && preg_match('#([^\s"\'=<>/;]+)\s*=\s*$#', $parts[$j - 1], $name)
					&& preg_match('#^("|\'|&quot;|&\#0?39;)(.*)\1$#s', $part, $quoted))
				{
					$value = html_entity_decode($expand($quoted[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
					$value = in_array(strtolower($name[1]), array('action', 'background', 'cite', 'data', 'formaction', 'href', 'poster', 'src', 'xlink:href'), true)
						? $this->toUrlAttribute($value, '#')
						: $this->toAttribute($value, true);
					$part = $quoted[1] . strtr($value, array('&quot;' => '&#x22;', '&#039;' => '&#x27;', '&gt;' => '&#x3E;')) . $quoted[1];
				}

				$parts[$j] = str_replace($marker, $inert, $part);
			}

			$segments[$i] = implode('', $parts);
		}

		return implode('', $segments);
	}

	/**
	 * Build a series of HTML attributes from the provided array
	 *
	 * Because of legacy loose typing client code usages, values that are {@see empty()} will not be added to the
	 * concatenated HTML attribute string except when the key is `alt` or `value`, the key begins with `data-`, or the
	 * value is a number.
	 *
	 * @param array $attributes Key-value pairs of HTML attributes. The value must not be HTML-encoded. If the value is
	 *                          boolean true, the value will be set to the key (e.g. `['required' => true]` becomes
	 *                          "required='required'").
	 * @param bool  $pure       True to leave web path constants such as {e_PLUGIN} unexpanded
	 * @return string The HTML attributes to concatenate inside an HTML tag
	 * @see e_parseTest::testToAttributesMixedPureAndReplaceConstants() for an example of how to use this method
	 */
	public function toAttributes($attributes, $pure = false)
	{
		$stringifiedAttributes = [];

		foreach ($attributes as $key => $value)
		{
			if ($value === true && (strpos($key, 'data-') !== 0))
			{
				$value = $key;
			}
			if (!empty($value) || is_numeric($value) || in_array($key, array('alt', 'value'), true) || strpos($key, 'data-') === 0)
			{
				$stringifiedAttributes[] = $key . "='" . $this->toAttribute($value, $pure) . "'";
			}
		}

		return count($stringifiedAttributes) > 0 ? " " . implode(" ", $stringifiedAttributes) : "";
	}

	/**
	 * Spell a Bootstrap JavaScript behaviour's data attributes for Bootstrap 3, 4 and 5 at once.
	 *
	 * Pass the behaviour, not the prefix: `data-` and `data-bs-` both come back. State classes cannot be doubled up
	 * like this; use {@see e_parse::bootstrapShowClass()}.
	 *
	 * @param array $attributes Behaviour attributes without their prefix, e.g. `['toggle' => 'collapse', 'target' => '#sub-1']`
	 * @return array Attribute name-value pairs, ready for {@see e_parse::toAttributes()}
	 * @deprecated v2.3.12 Stopgap while core markup is still framework-coupled. Framework spellings belong in template
	 *             packs (issue #5909); reach for a template override before calling this from new code.
	 */
	public function bootstrapData($attributes)
	{
		$prefixed = [];

		foreach ($attributes as $key => $value)
		{
			$prefixed['data-' . $key] = $value;
			$prefixed['data-bs-' . $key] = $value;
		}

		return $prefixed;
	}

	/**
	 * The class Bootstrap puts on a shown `.collapse` or a faded-in `.fade` element: `in` on Bootstrap 3, `show` from
	 * Bootstrap 4 on.
	 *
	 * Emit one or the other, never both: Bootstrap 3 also ships `.show` as a `display: block !important` utility.
	 *
	 * @return string
	 * @deprecated v2.3.12 Stopgap while core markup is still framework-coupled. Framework spellings belong in template
	 *             packs (issue #5909); reach for a template override before calling this from new code.
	 */
	public function bootstrapShowClass()
	{
		return $this->bootstrap > 3 ? 'show' : 'in';
	}

	/**
	 * Flatten a multi-dimensional associative array with slashes.
	 *
	 * Based on Illuminate\Support\Arr::dot()
	 *
	 * @param        $array
	 * @param string $prepend
	 * @return array
	 * @license   https://github.com/illuminate/support/blob/master/LICENSE.md MIT License
	 * @copyright Copyright (c) Taylor Otwell
	 */
	public static function toFlatArray($array, $prepend = '')
	{
		$results = [];

		foreach ($array as $key => $value)
		{
			if (is_array($value) && !empty($value))
			{
				$results = array_merge($results, static::toFlatArray($value, $prepend . $key . '/'));
			}
			else
			{
				$results[$prepend . $key] = $value;
			}
		}

		return $results;
	}


	/**
	 * Convert a flattened slash-delimited multi-dimensional array back into an actual multi-dimensional array
	 *
	 * Inverse of {@link e_parse::toFlatArray()}
	 *
	 * @param        $array
	 * @param string $unprepend
	 * @return array
	 */
	public static function fromFlatArray($array, $unprepend = '')
	{
		$output = [];
		foreach ($array as $key => $value)
		{
			if (!empty($unprepend) && substr($key, 0, strlen($unprepend)) == $unprepend)
			{
				$key = (string) substr($key, strlen($unprepend));
			}
			$parts = explode('/', $key);
			$nested = &$output;
			while (count($parts) > 1)
			{
				$nested = &$nested[array_shift($parts)];
				if (!is_array($nested)) $nested = [];
			}
			$nested[array_shift($parts)] = $value;
		}

		return $output;
	}


	/**
	 * Convert text blocks which are to be embedded within JS
	 *
	 * @param string|array $stringarray
	 * @return string
	 * @deprecated v2.3.1 This method will not escape a string properly for use as a JavaScript or JSON string. Use
	 *             {@see e_parse::toJSON()} instead. When using {@see e_parse::toJSON()}, do not surround its output
	 *             with quotation marks, and do not attempt to escape sequences like "\n" as "\\n". If HTML tags need to
	 *             be removed, consider {@see e_parse::toText()} separately. If the text needs to be used in an HTML
	 *             tag attribute (e.g. &lt;a onclick="ATTRIBUTE"&gt;&lt;/a&gt;), surround the string with
	 *             {@see e_parse::toAttribute()} and either single-quote or double-quote the attribute value.
	 */
	public function toJS($stringarray)
	{
		trigger_error('<b>' . __METHOD__ . ' is deprecated. See method DocBlock for alternatives.</b>', E_USER_WARNING); // NO LAN

		$search = array("\r\n", "\r", '<br />', "'");
		$replace = array("\\n", '', "\\n", "\'");
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
	 * @param bool  $force_object
	 *  True: Outputs an object rather than an array when a non-associative
	 *  array is used. Especially useful when the recipient of the output
	 *  is expecting an object and the array is empty.
	 *
	 * @return string
	 */
	public function toJSON($var, $force_object = false)
	{
		if ($force_object === true)
		{
			// Encode <, >, ', &, and " using the json_encode() options parameter.
			return json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_FORCE_OBJECT);
		}

		// Encode <, >, ', &, and " using the json_encode() options parameter.
		return json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

	}


	/**
	 * Convert Text for RSS/XML use.
	 *
	 * @param string  $text
	 * @param boolean $tags [optional]
	 * @return string
	 * @see e_parse::toRssForAuthor() for text a user wrote
	 */
	public function toRss($text, $tags = false)
	{
		return $this->rssText($text, $tags, null);
	}


	/**
	 * Convert text a user wrote for RSS/XML use; its shortcodes expand only if that user is an administrator.
	 *
	 * @param string  $text
	 * @param int     $authorId user ID stored with the text
	 * @param boolean $tags     [optional]
	 * @return string
	 */
	public function toRssForAuthor($text, $authorId, $tags = false)
	{
		return $this->rssText($text, $tags, $this->shortcodeModifiersForAuthor('parse_sc, no_make_clickable', $authorId));
	}


	/**
	 * @param string      $text
	 * @param boolean     $tags
	 * @param string|null $emailModifiers {@see e_parse::toEmail()} modifiers, NULL for its defaults
	 * @return string
	 */
	private function rssText($text, $tags, $emailModifiers)
	{

		if ($tags != true)
		{
			$text = $this->toHTML($text, true);
			$text = strip_tags($text);

		}

		$text = ($emailModifiers === null) ? $this->toEmail($text) : $this->toEmail($text, '', $emailModifiers);

		$search = array('&amp;#039;', '&amp;#036;', '&#039;', '&#036;', e_BASE, "href='request.php", '<!-- bbcode-html-start -->', '<!-- bbcode-html-end -->');
		$replace = array("'", '$', "'", '$', SITEURL, "href='" . SITEURL . 'request.php', '', '');
		$text = str_replace($search, $replace, $text);

		$text = $this->ampEncode($text);

		// if CDATA happens to be quoted in the text.
		$text = str_replace(['<![CDATA', ']]>'], ['&lt;![CDATA', ']]&gt;'], $text);

		if ($tags === true)
		{
			$text = !empty($text) ? '<![CDATA[' . $text . ']]>' : '';
		}
		else
		{
			$text = str_replace(['<', '>'], ['&lt;', '&gt;'], $text);
		}

		return $text;
	}


	/**
	 * Convert a string to a number (int/float)
	 *
	 * @param string $value
	 * @return int|float
	 */
	public function toNumber($value)
	{

		// adapted from: https://secure.php.net/manual/en/function.floatval.php#114486
		$dotPos = strrpos($value, '.');
		$commaPos = strrpos($value, ',');
		$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
			((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

		if (!$sep)
		{
			return (int) preg_replace('/[^-0-9]/', '', $value);
		}

		return (float) (
			preg_replace('/[^-0-9]/', '', (string) substr($value, 0, $sep)) . '.' .
			preg_replace('/[^0-9]/', '', (string) substr($value, $sep + 1, strlen($value)))
		);
	}


	/**
	 * Clean and Encode Ampersands '&' for output to browser.
	 *
	 * @param string $text
	 * @return array|string|string[]
	 */
	public function ampEncode($text = '')
	{

		// Fix any left-over '&'
		//first revert any previously converted.
		$text = str_replace(array('&amp;', '&'), array('&', '&amp;'), $text);

		return $text;
	}


	/**
	 * Convert any string back to plain text.
	 *
	 * @param $text
	 * @return array|string|string[]
	 */
	public function toText($text)
	{
		$text = (string) $text;

		if ($this->isBBcode($text) === true) // convert any bbcodes to html
		{
			$text = $this->toHTML($text, true);
		}

		if ($this->isHtml($text) === true) // strip any html.
		{
			$text = $this->toHTML($text, true);
			$text = str_replace("\n", '', $text); // clean-out line-breaks.
			$text = str_ireplace(array('<br>', '<br />', '<br/>'), "\n", $text);
			$text = strip_tags($text);
		}

		$search = array('&amp;#039;', '&amp;#036;', '&#039;', '&#036;', '&#092;', '&amp;#092;');
		$replace = array("'", '$', "'", '$', "\\", "\\");

		return str_replace($search, $replace, $text);


	}


	/**
	 * Set the dimensions of a thumbNail (generated by thumbUrl)
	 */
	public function setThumbSize($w = null, $h = null, $crop = null)
	{

		if ($w !== null)
		{
			$this->thumbWidth = (int) $w;
		}

		if ($h !== null)
		{
			$this->thumbHeight = (int) $h;
		}

		if ($crop !== null)
		{
			$this->thumbCrop = (int) $crop;
		}

	}

	/**
	 * @param $val
	 * @return int|null
	 */
	public function thumbEncode($val = null)
	{

		if ($val !== null)
		{
			$this->thumbEncode = (int) $val;

			return null;
		}

		return $this->thumbEncode;
	}


	/**
	 * Retrieve img tag width and height attributes for current thumbnail.
	 *
	 * @return string|null
	 */
	public function thumbDimensions($type = 'single')
	{

		if (!empty($this->thumbCrop) && !empty($this->thumbWidth) && !empty($this->thumbHeight)) // dimensions are known.
		{
			return ($type === 'double') ? 'width="' . $this->thumbWidth . '" height="' . $this->thumbHeight . '"' : "width='" . $this->thumbWidth . "' height='" . $this->thumbHeight . "'";
		}

		return null;
	}


	/**
	 * Set or Get the value of the thumbNail Width.
	 *
	 * @param $width (optional)
	 */
	public function thumbWidth($width = null)
	{

		if ($width !== null)
		{
			$this->thumbWidth = (int) $width;
		}

		return $this->thumbWidth;
	}

	/**
	 * Set or Get the value of the thumbNailbCrop.
	 *
	 * @param bool $status = true/false
	 */
	public function thumbCrop($status = false)
	{

		if ($status !== false)
		{
			$this->thumbCrop = (int) $status;
		}

		return $this->thumbCrop;
	}


	/**
	 * Set or Get the value of the thumbNail height.
	 *
	 * @param $height (optional)
	 */
	public function thumbHeight($height = null)
	{

		if ($height !== null)
		{
			$this->thumbHeight = (int) $height;
		}

		return $this->thumbHeight;

	}

	/**
	 * Sets a cache file for storing alternate text data for an image.
	 *
	 * @param string $path  The file path of the image whose alternate text is being cached.
	 * @param string $value The alternate text value to cache.
	 * @return string|false String or false on failure
	 */
	public function setImageAltCacheFile($path, $value)
	{
		$cache_str = sha1($path);

		$pre = 'alt_';
		$post = '.cache.txt';

		$file = e_CACHE_IMAGE .'alt_' . sha1($path) . '.cache.txt';

		if (file_put_contents($file, $value) !== false)
		{
            return $file;
        }

        return false;

	}

	/**
	 * Retrieves the content of the alternative cache file for the specified path if it exists.
	 *
	 * @param string $path The file path to generate the cache filename for.
	 * @return string|null The content of the cache file if it exists, or null if the file does not exist.
	 */
	public function getImageAltCacheFile($path)
	{
	    $file = e_CACHE_IMAGE .'alt_' . sha1($path) . '.cache.txt';

	    if (file_exists($file))
	    {
	        return file_get_contents($file);
	    }

	    return null;
	}


	/**
	 * Generated a Thumb Cache File Name from path and options.
	 *
	 * @param string $path
	 * @param array  $options
	 * @param string $log (optional) - log file name
	 * @return null|string
	 */
	public function thumbCacheFile($path, $options = null, $log = null)
	{

		if (empty($path))
		{
			return null;
		}

		if (is_string($options))
		{
			parse_str($options, $options);
		}

		$path = str_replace($this->getUrlConstants('raw'), $this->getUrlConstants('sc'), $path);
		$path = $this->replaceConstants(str_replace('..', '', $path));

		$filename = basename($path);
		$tmp = explode('.', $filename);
		$ext = end($tmp);
		$len = strlen($ext) + 1;
		$start = (string) substr($filename, 0, -$len);

		// cleanup.
		$newOpts = array(
			'w'  => isset($options['w']) ? (string) intval($options['w']) : '',
			'h'  => isset($options['h']) ? (string) intval($options['h']) : '',
			'aw' => isset($options['aw']) ? (string) intval($options['aw']) : '',
			'ah' => isset($options['ah']) ? (string) intval($options['ah']) : '',
			'c'  => strtoupper(vartrue($options['c'], '0')),
		);

		if (!empty($options['type']))
		{
			$newOpts['type'] = $options['type'];
			$ext = $newOpts['type'];
		}


		if (!empty($options['aw']))
		{
			$options['w'] = $options['aw'];
		}

		if (!empty($options['ah']))
		{
			$options['h'] = $options['ah'];
		}


		$size = varset($options['w'], 0) . 'x' . varset($options['h'], 0);

		$thumbQuality = e107::getPref('thumbnail_quality', 65);

		$cache_str = md5(serialize($newOpts) . $path . $thumbQuality);

		$pre = 'thumb_';
		$post = '.cache.bin';

		//	$cache_str = http_build_query($newOpts,null,'_'); // testing files.

		if (defined('e_MEDIA_STATIC')) // experimental - subject to change.
		{
			$pre = '';
			$post = '';
		}

		$fname = $pre . strtolower($start . '_' . $cache_str . '_' . $size . '.' . $ext) . $post;


		if ($log !== null)
		{
			file_put_contents(e_LOG . $log, "\n++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n\n", FILE_APPEND);
			$message = 'Name: ' . $fname . "\n";
			$message .= $path . "\n" . var_export($newOpts, true) . "\n\n\n";
			file_put_contents(e_LOG . $log, $message, FILE_APPEND);

			//	file_put_contents(e_LOG.$log, "\t\tFOUND!!\n\n\n", FILE_APPEND);
		}


		return $fname;
	}


	/**
	 * @param bool|int $val
	 * @return int
	 */
	private function staticCount($val = false)
	{

		$count = $this->staticCount;

		if ($val === 0)
		{
			$this->staticCount = 0;
		}
		elseif ($val !== false)
		{
			$this->staticCount += (int) $val;
		}

		return $count;

	}


	/**
	 * @param string $path - absolute path or e107 path {e_PLUGIN} etc.
	 * @param array  $opts - when $opts['full'] is true, a full siteurl will be used instead of an absolute path. (unless static url is found)
	 * @return string - full path or static path.
	 * @todo Move to e107_class ?
	 */
	public function staticUrl($path = null, $opts = array())
	{

		if (empty($this->staticUrl) || deftrue('e_ADMIN_AREA'))
		{
			// e107::getDebug()->log("e_HTTP_STATIC not defined");
			if ($path === null)
			{
				return !empty($opts['full']) ? SITEURL : e_HTTP;
			}

			return !empty($opts['full']) ? $this->replaceConstants($path, 'full') : $this->replaceConstants($path, 'abs'); // self::replaceConstants($path, 'full');
		}

		$staticArray = $this->staticUrl; // e_HTTP_STATIC;
		$path = $this->replaceConstants($path, 'abs'); // replace any {THEME} etc.
		$key = ltrim(eHelper::dasherize($path), '/');

		if (is_array($staticArray))
		{
			if(!empty($this->staticUrlMap[$key]))
			{
				$http = $this->staticUrlMap[$key];
			}
			else
			{
				$cnt = count($staticArray);
				$staticCount = $this->staticCount();
				if ($staticCount > ($cnt - 1))
				{
					$staticCount = 0;
					$this->staticCount(0);
				}

				$http = !empty($staticArray[$staticCount]) ? $staticArray[$staticCount] : e_HTTP;
				$this->staticCount(1);
			}
		}
		else
		{
			$http = $this->staticUrl;
		}

		$this->staticUrlMap[$key] = $http;


		if (empty($path))
		{
			return $http;
		}

		$base = '';



		$srch = array(
			e_PLUGIN_ABS,
			e_THEME_ABS,
			e_WEB_ABS,
			e_CACHE_IMAGE_ABS,
		);


		$repl = array(
			$http . $base . e107::getFolder('plugins'),
			$http . $base . e107::getFolder('themes'),
			$http . $base . e107::getFolder('web'),
			$http . $base . str_replace('../', '', e_CACHE_IMAGE),
		);

		// Quickfix for any '/./' that may occur.
		$srch[] = '/./';
		$repl[] = '/';

		$ret = str_replace($srch, $repl, $path);

		if (strpos($ret, 'http') !== 0) // if not converted, check media folder also.
		{
			$ret = str_replace(e_MEDIA_ABS, $http . $base . e107::getFolder('media'), $ret);
		}



		return $ret;

	}

	/**
	 * Used internally to store e_HTTP_STATIC.
	 *
	 * Discards the state {@see e_parse::staticUrl()} derived from the previous
	 * value: the map of domains already issued per path, and the round-robin
	 * position within the configured domains. Both describe the configuration
	 * being replaced, so carrying them over would serve a path a domain the
	 * caller has just stopped configuring.
	 *
	 * @param string|string[]|null $url The static URL ie. e_HTTP_STATIC
	 */
	public function setStaticUrl($url)
	{
		$this->staticUrl = $url;
		$this->staticUrlMap = [];
		$this->staticCount(0);
	}

	public function getStaticUrlMap()
	{
		return $this->staticUrlMap;
	}

	/**
	 * Generate an auto-sized Image URL.
	 *
	 * @param             $url     - path to image or leave blank for a placeholder. eg. {e_MEDIA}folder/my-image.jpg
	 * @param array       $options = [ width and height, but leaving this empty and using $this->thumbWidth() and $this->thumbHeight() is preferred. ie. {SETWIDTH: w=x&y=x}
	 *  'w'         => int         width (optional)
	 *  'h'         => int         height (optional)
	 *  'crop'      => bool|string true/false or A(auto) or T(op) or B(ottom) or C(enter) or L(eft) or R(right)
	 *  'scale'     => string      '2x' (optional)
	 *  'x'         => bool        encode/mask the url parms (optional)
	 *  'nosef'     => bool        when set to true disabled SEF Url being returned (optional)
	 *  ]
	 * @param bool        $raw     set to true when the $url does not being with an e107 variable ie. "{e_XXXX}" eg. {e_MEDIA} (optional)
	 * @param bool        $full    when true returns full http:// url. (optional)
	 * ]
	 * @return string
	 */
	public function thumbUrl($url = null, $options = array(), $raw = false, $full = false)
	{
		$url = (string) $url;

		$ext = pathinfo($url, PATHINFO_EXTENSION);

		if ($ext === 'svg')
		{
			return $this->replaceConstants($url, 'abs');
		}

		if (strpos($url, '{e_') === 0) // Fix for broken links that use {e_MEDIA} etc.
		{
			//$url = $this->replaceConstants($url,'abs');	
			// always switch to 'nice' urls when SC is used	
			$url = str_replace($this->getUrlConstants('sc'), $this->getUrlConstants('raw'), $url);
		}

		if (is_string($options))
		{
			parse_str($options, $options);
		}

		if (!empty($options['scale'])) // eg. scale the width height 2x 3x 4x. etc.
		{
			$options['return'] = 'src';
			$options['size'] = $options['scale'];
			unset($options['scale']);

			return $this->thumbSrcSet($url, $options);
		}


		if (strpos($url, e_MEDIA) !== false || strpos($url, e_SYSTEM) !== false) // prevent disclosure of 'hashed' path.
		{
			$raw = true;
		}

		if ($raw)
		{
			$url = $this->createConstants($url, 'mix');
		}

		// Again, because createConstants() above has just minted the very form
		// the check at the top of this method converts away. e107::set_request()
		// strips { and } out of every query string, so a src= that still carries
		// braces reaches thumb.php as e_IMAGEgeneric/blank_avatar.jpg and names
		// nothing at all.
		if (strpos($url, '{e_') === 0)
		{
			$url = str_replace($this->getUrlConstants('sc'), $this->getUrlConstants('raw'), $url);
		}

		$baseurl = ($full ? SITEURL : e_HTTP) . 'thumb.php?';

		if (!empty($this->staticUrl))
		{
			$baseurl = $this->staticUrl() . 'thumb.php?';
		}

		$thurl = 'src=' . urlencode($url) . '&amp;';

		//	e107::getDebug()->log("Thumb: ".basename($url). print_a($options,true), E107_DBG_BASIC);

		if (!empty($options) && (isset($options['w']) || isset($options['aw']) || isset($options['h'])))
		{
			$options['w'] = varset($options['w']);
			$options['h'] = varset($options['h']);
			$options['crop'] = (isset($options['aw']) || isset($options['ah'])) ? 1 : varset($options['crop']);
			$options['aw'] = varset($options['aw']);
			$options['ah'] = varset($options['ah']);
			$options['x'] = varset($options['x']);
		}
		else
		{
			$options['w'] = $this->thumbWidth;
			$options['h'] = $this->thumbHeight;
			$options['crop'] = $this->thumbCrop;
			$options['aw'] = null;
			$options['ah'] = null;
			$options['x'] = $this->thumbEncode;

		}


		if (!empty($options['crop']))
		{
			if (!empty($options['aw']) || !empty($options['ah']))
			{
				$options['w'] = $options['aw'];
				$options['h'] = $options['ah'];
			}

			$thurl .= 'aw=' . (int) $options['w'] . '&amp;ah=' . (int) $options['h'];

			if (!is_numeric($options['crop']))
			{
				$thurl .= '&amp;c=' . $options['crop'];
				$options['nosef'] = true;
			}

		}
		else
		{

			$thurl .= 'w=' . (int) $options['w'] . '&amp;h=' . (int) $options['h'];

		}

		if (!empty($options['type']))
		{
			$thurl .= '&amp;type=' . $options['type'];
		}


		if (defined('e_MEDIA_STATIC')) // experimental - subject to change.
		{
			$opts = str_replace('&amp;', '&', $thurl);

			$staticFile = $this->thumbCacheFile($url, $opts);


			if (!empty($staticFile) && is_readable(e_CACHE_IMAGE . $staticFile))
			{
				return $this->staticUrl(e_CACHE_IMAGE_ABS . $staticFile);

			}

			//	echo "<br />static-not-found: ".$staticFile;

			$options['nosef'] = true;
			$options['x'] = null;
			// file_put_contents(e_LOG."thumb.log", "\n++++++++++++++++++++++++++++++++++\n\n", FILE_APPEND);
		}


		if ($this->modRewriteMedia && empty($options['nosef']))//  SEF URL support.
		{
			$options['full'] = $full;
			$options['ext'] = pathinfo($url, PATHINFO_EXTENSION);
			$options['thurl'] = $thurl;
			//	$options['x'] = $this->thumbEncode();

			if ($sefUrl = $this->thumbUrlSEF($url, $options))
			{
				return $sefUrl;
			}
		}

		if (!empty($options['x']))//base64 encode url
		{
			$thurl = 'id=' . base64_encode($thurl);
		}

		return $baseurl . $thurl;
	}


	/**
	 * Split a thumb.php url into an array which can be parsed back into the thumbUrl method. .
	 *
	 * @param $src
	 * @return array
	 */
	public function thumbUrlDecode($src)
	{

		list($url, $qry) = array_pad(explode('?', $src), 2, null);

		$ret = array();

		if (!empty($qry) && strpos($url, 'thumb.php') !== false) // Regular
		{
			parse_str($qry, $val);
			$ret = $val;
		}
		elseif (preg_match('/media\/img\/(a)?([\d]*)x(a)?([\d]*)\/(.*)/', $url, $match)) // SEF
		{
			$wKey = $match[1] . 'w';
			$hKey = $match[3] . 'h';

			$ret = array(
				'src' => 'e_MEDIA_IMAGE/' . $match[5],
				$wKey => $match[2],
				$hKey => $match[4]
			);
		}
		elseif (preg_match('/theme\/img\/(a)?([\d]*)x(a)?([\d]*)\/(.*)/', $url, $match)) // Theme-image SEF Urls
		{
			$wKey = $match[1] . 'w';
			$hKey = $match[3] . 'h';

			$ret = array(
				'src' => 'e_THEME/' . $match[5],
				$wKey => $match[2],
				$hKey => $match[4]
			);

		}
		elseif (defined('TINYMCE_DEBUG'))
		{
			print_a('thumbUrlDecode: No Matches');

		}


		return $ret;
	}


	/**
	 * Experimental: Generate a Thumb URL for use in the img srcset attribute.
	 *
	 * @param string           $src   eg. {e_MEDIA_IMAGE}myimage.jpg
	 * @param int|string|array $width - desired size in px or '2x' or '3x' or null for all or array (
	 * @return string
	 */
	public function thumbSrcSet($src = '', $width = null)
	{

		$multiply = null;
		$encode = false;
		$parm = array();

		if (is_array($width))
		{
			$parm = $width;
			$multiply = $width['size'];
			$encode = (!empty($width['x'])) ? $width['x'] : false;
			$width = $width['size'];
		}


		//	$encode =  $this->thumbEncode();;
		if ($width == null || $width === 'all')
		{
			$links = array();
			$mag = ($width == null) ? array(1, 2) : array(160, 320, 460, 600, 780, 920, 1100);
			foreach ($mag as $v)
			{
				$w = ($this->thumbWidth * $v);
				$h = ($this->thumbHeight * $v);

				$att = (!empty($this->thumbCrop)) ? array('aw' => $w, 'ah' => $h) : compact('w', 'h');
				$att['x'] = $encode;

				$add = ($width == null) ? ' ' . $v . 'x' : ' ' . $v . 'w';
				$links[] = $this->thumbUrl($src, $att) . $add; // " w".$width; //
			}

			return implode(', ', $links);

		}
		elseif ($multiply === '2x' || $multiply === '3x' || $multiply === '4x')
		{
			$multiInt = (int) $multiply;

			if (empty($parm['w']) && isset($parm['h']))
			{
				$parm['h'] = ($parm['h'] * $multiInt);

				return $this->thumbUrl($src, $parm) . ' ' . $multiply;
			}

			if (isset($parm['w']) && !isset($parm['h'])) // if w set, assume h value of 0 is set.
			{
				$parm['h'] = 0;
			}

			$width = !empty($parm['w']) ? (intval($parm['w']) * $multiInt) : ($this->thumbWidth * $multiInt);
			$height = isset($parm['h']) ? (intval($parm['h']) * $multiInt) : ($this->thumbHeight * $multiInt);

		}
		else
		{
			$height = (($this->thumbHeight * $width) / $this->thumbWidth);

		}


		if (!isset($parm['aw']))
		{
			$parm['aw'] = null;
		}

		if (!isset($parm['ah']))
		{
			$parm['ah'] = null;
		}

		if (!isset($parm['x']))
		{
			$parm['x'] = null;
		}

		if (!isset($parm['crop']))
		{
			$parm['crop'] = null;
		}

		$parms = array('w' => $width, 'h' => $height, 'crop' => $parm['crop'], 'x' => $parm['x'], 'aw' => $parm['aw'], 'ah' => $parm['ah']);

		if (!empty($parm['type']))
		{
			$parms['type'] = $parm['type'];
		}
		//	$parms = !empty($this->thumbCrop) ? array('aw' => $width, 'ah' => $height, 'x'=>$encode) : array('w'  => $width,	'h'  => $height, 'x'=>$encode	);

		// $parms['x'] = $encode;

		if (!empty($parm['return']) && $parm['return'] === 'src')
		{
			return $this->thumbUrl($src, $parms);
		}

		$ret = $this->thumbUrl($src, $parms);

		$ret .= ($multiply) ? ' ' . $multiply : ' ' . $width . 'w';

		return $ret;

	}



	/**
	 * Used by thumbUrl when SEF Image URLS is active. @param $url
	 *
	 * @param array $options
	 * @return string
	 * @see e107.htaccess
	 */
	public function thumbUrlSEF($url = '', $options = array())
	{

		if (!empty($options['full']))
		{
			$base = SITEURL;
		}
		else
		{
			$base = (!empty($options['ebase'])) ? '{e_BASE}' : e_HTTP;
		}

		if (!empty($this->staticUrl))
		{
			$base = $this->staticUrl();
		}
		//	$base = (!empty($options['full'])) ? SITEURL : e_HTTP;

		if (!empty($options['x']) && !empty($options['ext'])) // base64 encoded. Build URL for:  RewriteRule ^media\/img\/([-A-Za-z0-9+/]*={0,3})\.(jpg|gif|png)?$ thumb.php?id=$1
		{
			$ext = strtolower($options['ext']);

			return $base . 'media/img/' . base64_encode($options['thurl']) . '.' . str_replace('jpeg', 'jpg', $ext);
		}
		elseif (strpos($url, 'e_MEDIA_IMAGE') !== false) // media images.
		{
			$sefPath = 'media/img/';
			$clean = array('{e_MEDIA_IMAGE}', 'e_MEDIA_IMAGE/');
		}
		elseif (strpos($url, 'e_AVATAR') !== false) // avatars
		{
			$sefPath = 'media/avatar/';
			$clean = array('{e_AVATAR}', 'e_AVATAR/');
		}
		elseif (strpos($url, 'e_THEME') !== false) // theme folder images.
		{
			$sefPath = 'theme/img/';
			$clean = array('{e_THEME}', 'e_THEME/');
		}
		else
		{
			// e107::getDebug()->log("SEF URL False: ".$url);
			return false;
		}

		// Build URL for ReWriteRule ^media\/img\/(a)?([\d]*)x(a)?([\d]*)\/(.*)?$ thumb.php?src=e_MEDIA_IMAGE/$5&$1w=$2&$3h=$4
		$sefUrl = $base . $sefPath;

		if (!empty($options['scale']))
		{
			$multiInt = (int) $options['scale'];
			$options['w'] = $options['w'] * $multiInt;
			$options['h'] = $options['h'] * $multiInt;
		}


		if (!empty($options['aw']) || !empty($options['ah']))
		{
			$sefUrl .= 'a' . intval($options['aw']) . 'xa' . intval($options['ah']);
		}
		elseif (!empty($options['crop']))
		{

			if (!is_numeric($options['crop']))
			{
				$sefUrl .= strtolower($options['crop']) . intval($options['w']) . 'x' . strtolower($options['crop']) . intval($options['h']);
			}
			else
			{
				$sefUrl .= 'a' . intval($options['w']) . 'xa' . intval($options['h']);
			}


		}
		else
		{
			$sefUrl .= intval($options['w']) . 'x' . intval($options['h']);
		}

		$sefUrl .= '/';
		$sefUrl .= str_replace($clean, '', $url);

		return $sefUrl;

	}

	/**
	 * Help for converting to more safe URLs
	 * e.g. {e_MEDIA_FILE}path/to/video.flv => e_MEDIA_FILE/path/to/video.flv
	 *
	 * @param string $type sc|raw|rev|all
	 * @return array
	 * @todo support for ALL URL shortcodes (replacement methods)
	 */
	public function getUrlConstants($type = 'sc')
	{

		// sub-folders first!
		static $array = array(
			'e_MEDIA_FILE/'     => '{e_MEDIA_FILE}',
			'e_MEDIA_VIDEO/'    => '{e_MEDIA_VIDEO}',
			'e_MEDIA_IMAGE/'    => '{e_MEDIA_IMAGE}',
			'e_MEDIA_ICON/'     => '{e_MEDIA_ICON}',
			'e_AVATAR/'         => '{e_AVATAR}',
			'e_AVATAR_DEFAULT/' => '{e_AVATAR_DEFAULT}',
			'e_AVATAR_UPLOAD/'  => '{e_AVATAR_UPLOAD}',
			'e_WEB_JS/'         => '{e_WEB_JS}',
			'e_WEB_CSS/'        => '{e_WEB_CSS}',
			'e_WEB_IMAGE/'      => '{e_WEB_IMAGE}',
			'e_IMPORT/'         => '{e_IMPORT}',
			//	'e_WEB_PACK/' 		=> '{e_WEB_PACK}',

			'e_BASE/'    => '{e_BASE}',
			'e_ADMIN/'   => '{e_ADMIN}',
			'e_IMAGE/'   => '{e_IMAGE}',
			'e_THEME/'   => '{e_THEME}',
			'e_PLUGIN/'  => '{e_PLUGIN}',
			'e_HANDLER/' => '{e_HANDLER}', // BC
			'e_MEDIA/'   => '{e_MEDIA}',
			'e_WEB/'     => '{e_ADMIN}',
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


	/**
	 * @return array
	 */
	public function getEmotes()
	{

		return e107::getEmote()->getList();
	}


	/**
	 * Replace e107 path constants
	 * Note: only an ADMIN user can convert {e_ADMIN}
	 * TODO - runtime cache of search/replace arrays (object property) when $mode !== ''
	 *
	 * @param string $text
	 * @param string $mode                [optional]    abs|full "full" = produce absolute URL path, e.g. http://sitename.com/e107_plugins/etc
	 *                                    'abs' = produce truncated URL path, e.g. e107plugins/etc
	 *                                    "" (default) = URL's get relative path e.g. ../e107_plugins/etc
	 * @param mixed  $all                 [optional]    if TRUE, then when $mode is "full" or TRUE, USERID is also replaced...
	 *                                    when $mode is "" (default), ALL other e107 constants are replaced
	 * @return string|array
	 */
	public function replaceConstants($text, $mode = '', $all = false)
	{

		if (is_array($text))
		{
			$new = array();
			foreach ($text as $k => $v)
			{
				$new[$k] = $this->replaceConstants($v, $mode, $all);
			}

			return $new;
		}

		$replace_absolute = array();

		if (!empty($mode))
		{
			$e107 = e107::getInstance();

			if (empty($this->relativePaths)) // prevent multiple lookups.
			{

				$this->relativePaths = array(
					$e107::getFolder('media_files'),
					$e107::getFolder('media_video'),
					$e107::getFolder('media_image'),
					$e107::getFolder('media_icon'),
					$e107::getFolder('avatars'),
					$e107::getFolder('web_js'),
					$e107::getFolder('web_css'),
					$e107::getFolder('web_image'),
					//$e107->getFolder('web_pack'),
					e_IMAGE_ABS,
					e_THEME_ABS,
					$e107::getFolder('images'),
					$e107::getFolder('plugins'),
					$e107::getFolder('files'),
					$e107::getFolder('themes'),
					//	$e107->getFolder('downloads'),
					$e107::getFolder('handlers'),
					$e107::getFolder('media'),
					$e107::getFolder('web'),
					$e107->site_theme ? $e107::getFolder('themes') . $e107->site_theme . '/' : '',
					defset('THEME_ABS'),
					(deftrue('ADMIN') ? $e107::getFolder('admin') : ''),
					'',
					$e107::getFolder('core'),
					$e107::getFolder('system'),
				);
			}

			$replace_relative = $this->relativePaths;

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
						SITEURLBASE . e_MEDIA_FILE_ABS,
						SITEURLBASE . e_MEDIA_VIDEO_ABS,
						SITEURLBASE . e_MEDIA_IMAGE_ABS,
						SITEURLBASE . e_MEDIA_ICON_ABS,
						SITEURLBASE . e_AVATAR_ABS,
						SITEURLBASE . e_JS_ABS,
						SITEURLBASE . e_CSS_ABS,
						SITEURLBASE . e_WEB_IMAGE_ABS,
						//		SITEURLBASE.e_PACK_ABS,
						SITEURLBASE . e_IMAGE_ABS,
						SITEURLBASE . e_THEME_ABS,
						SITEURLBASE . e_IMAGE_ABS,
						SITEURLBASE . e_PLUGIN_ABS,
						SITEURLBASE . e_FILE_ABS, // deprecated
						SITEURLBASE . e_THEME_ABS,
						//SITEURL.$e107->getFolder('downloads'),
						'', //  handlers - no ABS path available
						SITEURLBASE . e_MEDIA_ABS,
						SITEURLBASE . e_WEB_ABS,
						defset('THEME_ABS') ? SITEURLBASE . THEME_ABS : '',
						defset('THEME_ABS') ? SITEURLBASE . THEME_ABS : '',
						(deftrue('ADMIN') ? SITEURLBASE . e_ADMIN_ABS : ''),
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
				'{e_IMAGE_ABS}',
				'{e_THEME_ABS}',
				'{e_IMAGE}',
				'{e_PLUGIN}',
				'{e_FILE}',
				'{e_THEME}',
				//,"{e_DOWNLOAD}"
				'{e_HANDLER}',
				'{e_MEDIA}',
				'{e_WEB}',
				'{THEME}',
				'{THEME_ABS}',
				'{e_ADMIN}',
				'{e_BASE}',
				'{e_CORE}',
				'{e_SYSTEM}',
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
				$search[] = '{USERID}';
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

			$replace = ((string) $mode === 'full' || (string) $mode === 'abs') ? $replace_absolute : $replace_relative;

			return !empty($text) ? str_replace($search, $replace, $text) : $text;
		}

		return $this->expandConstants($text, $all ? null : array_merge(self::$webPathConstants, self::$serverPathConstants));
	}

	/**
	 * Expand the {e_...} constants named in $names, or every {CONSTANT} when $names is null, and {THEME}.
	 *
	 * @param string        $text
	 * @param string[]|null $names
	 * @return string
	 */
	private function expandConstants($text, $names)
	{
		$pattern = ($names === null ? '#\{([A-Za-z_0-9]*)\}#s' : self::$constantPlaceholder);
		$text = preg_replace_callback($pattern, function ($matches) use ($names)
		{
			return $this->doReplace($matches, $names);
		}, $text);

		if (!defined('THEME'))
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


	/**
	 * Encode the brace of each {e_...} placeholder that names no web path constant, so that no later constants pass expands it.
	 *
	 * @param string $text
	 * @return string
	 */
	private function disarmConstantPlaceholders($text)
	{
		return preg_replace_callback(self::$constantPlaceholder, function ($matches)
		{
			return in_array($matches[1], self::$webPathConstants, true) ? $matches[0] : '&#123;'.substr($matches[0], 1);
		}, $text);
	}

	/**
	 * @param array         $matches
	 * @param string[]|null $names the constants that may expand, or null for any
	 * @return mixed|string
	 */
	private function doReplace($matches, $names)
	{

		if (defined($matches[1]) && (deftrue('ADMIN') || strpos($matches[1], 'ADMIN') === false))
		{
			if ($names !== null && !in_array($matches[1], $names, true))
			{
				return $matches[0];
			}

			return constant($matches[1]);
		}

		return $matches[1];
	}

	/**
	 * Create and substitute e107 constants in passed URL
	 *
	 * @param string  $url
	 * @param int     $mode 0-folders, 1-relative ('rel'), 2-absolute ('abs'), 3-full ('full') (with domain), 4-absolute & relative ('mix') (combination of 1,2,3)
	 * @return string
	 */
	public function createConstants($url, $mode = 0)
	{

		//FIXME - create constants for absolute paths and site URL's
		if (!is_numeric($mode))
		{
			switch ($mode)
			{
				case 'rel' :
					$mode = 1;
					break;
				case 'abs' :
					$mode = 2;
					break;
				case 'full' :
					$mode = 3;
					break;
				case 'mix' :
					$mode = 4;
					break;
				case 'nice':
					$mode = 5;
					break;
			}
		}
		$e107 = e107::getInstance();
		switch ($mode)
		{
			case 0: // folder name only.
				$tmp = array(
					'{e_MEDIA_FILE}'  => $e107::getFolder('media_files'),
					'{e_MEDIA_VIDEO}' => $e107::getFolder('media_videos'),
					'{e_MEDIA_IMAGE}' => $e107::getFolder('media_images'),
					'{e_MEDIA_ICON}'  => $e107::getFolder('media_icons'),
					'{e_AVATAR}'      => $e107::getFolder('avatars'),
					'{e_WEB_JS}'      => $e107::getFolder('web_js'),
					'{e_WEB_CSS}'     => $e107::getFolder('web_css'),
					'{e_WEB_IMAGE}'   => $e107::getFolder('web_images'),
					//		'{e_WEB_PACK}'		=> $e107::getFolder('web_packs'),

					'{e_IMAGE}'    => $e107::getFolder('images'),
					'{e_PLUGIN}'   => $e107::getFolder('plugins'),
					'{e_FILE}'     => $e107::getFolder('files'),
					'{e_THEME}'    => $e107::getFolder('themes'),
					'{e_DOWNLOAD}' => $e107::getFolder('downloads'),
					'{e_ADMIN}'    => $e107::getFolder('admin'),
					'{e_HANDLER}'  => $e107::getFolder('handlers'),
					'{e_MEDIA}'    => $e107::getFolder('media'),
					'{e_WEB}'      => $e107::getFolder('web'),
					'{e_UPLOAD}'   => $e107::getFolder('uploads'),
				);

				break;


			case 1: // relative path only
				$tmp = array(
					'{e_MEDIA_FILE}'  => e_MEDIA_FILE,
					'{e_MEDIA_VIDEO}' => e_MEDIA_VIDEO,
					'{e_MEDIA_IMAGE}' => e_MEDIA_IMAGE,
					'{e_MEDIA_ICON}'  => e_MEDIA_ICON,
					'{e_AVATAR}'      => e_AVATAR,
					'{e_IMPORT}'      => e_IMPORT,
					'{e_WEB_JS}'      => e_WEB_JS,
					'{e_WEB_CSS}'     => e_WEB_CSS,
					'{e_WEB_IMAGE}'   => e_WEB_IMAGE,
					//	'{e_WEB_PACK}'		=> e_WEB_PACK,

					'{e_IMAGE}'    => e_IMAGE,
					'{e_PLUGIN}'   => e_PLUGIN,
					'{e_FILE}'     => e_FILE,
					'{e_THEME}'    => e_THEME,
					'{e_DOWNLOAD}' => e_DOWNLOAD,
					'{e_ADMIN}'    => e_ADMIN,
					'{e_HANDLER}'  => e_HANDLER,
					'{e_MEDIA}'    => e_MEDIA,
					'{e_WEB}'      => e_WEB,
					'{e_UPLOAD}'   => e_UPLOAD,
				);
				break;

			case 2: // absolute path only
				$tmp = array(
					'{e_MEDIA_FILE}'  => e_MEDIA_FILE_ABS,
					'{e_MEDIA_VIDEO}' => e_MEDIA_VIDEO_ABS,
					'{e_MEDIA_IMAGE}' => e_MEDIA_IMAGE_ABS,
					'{e_MEDIA_ICON}'  => e_MEDIA_ICON_ABS,
					'{e_AVATAR}'      => e_AVATAR_ABS,
					'{e_WEB_JS}'      => e_JS_ABS,
					'{e_WEB_CSS}'     => e_CSS_ABS,
					'{e_WEB_IMAGE}'   => e_WEB_IMAGE_ABS,
					//		'{e_WEB_PACK}'		=> e_PACK_ABS,

					'{e_IMAGE}'    => e_IMAGE_ABS,
					'{e_PLUGIN}'   => e_PLUGIN_ABS,
					'{e_FILE}'     => e_FILE_ABS, // deprecated
					'{e_THEME}'    => e_THEME_ABS,
					'{e_DOWNLOAD}' => e_HTTP . 'request.php?',// FIXME - we need solution!
					'{e_ADMIN}'    => e_ADMIN_ABS,
					//'{e_HANDLER}'	=> e_HANDLER_ABS, - no ABS path available
					'{e_MEDIA}'    => e_MEDIA_ABS,
					'{e_WEB}'      => e_WEB_ABS,
					'{e_BASE}'     => e_HTTP,
				);
				break;

			case 3: // full path (e.g http://domain.com/e107_images/)
				$tmp = array(
					'{e_MEDIA_FILE}'  => SITEURLBASE . e_MEDIA_FILE_ABS,
					'{e_MEDIA_VIDEO}' => SITEURLBASE . e_MEDIA_VIDEO_ABS,
					'{e_MEDIA_IMAGE}' => SITEURLBASE . e_MEDIA_IMAGE_ABS,
					'{e_MEDIA_ICON}'  => SITEURLBASE . e_MEDIA_ICON_ABS,
					'{e_AVATAR}'      => SITEURLBASE . e_AVATAR_ABS,
					'{e_WEB_JS}'      => SITEURLBASE . e_JS_ABS,
					'{e_WEB_CSS}'     => SITEURLBASE . e_CSS_ABS,
					'{e_WEB_IMAGE}'   => SITEURLBASE . e_WEB_IMAGE_ABS,
					//		'{e_WEB_PACK}'		=> SITEURLBASE.e_PACK_ABS,

					'{e_IMAGE}'    => SITEURLBASE . e_IMAGE_ABS,
					'{e_PLUGIN}'   => SITEURLBASE . e_PLUGIN_ABS,
					'{e_FILE}'     => SITEURLBASE . e_FILE_ABS, // deprecated
					'{e_THEME}'    => SITEURLBASE . e_THEME_ABS,
					'{e_DOWNLOAD}' => SITEURLBASE . e_HTTP . 'request.php?',// FIXME - we need solution!
					'{e_ADMIN}'    => SITEURLBASE . e_ADMIN_ABS,
					//'{e_HANDLER}'	=> e_HANDLER_ABS, - no ABS path available
					'{e_MEDIA}'    => SITEURLBASE . e_MEDIA_ABS,
					'{e_WEB}'      => SITEURLBASE . e_WEB_ABS,
					'{e_BASE}'     => SITEURL,
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

		foreach ($tmp as $key => $val)
		{
			// Fix - don't break the CDN '//cdn.com' URLs
			if ($hasCDN && $val === '/')
			{
				continue;
			}

			$len = strlen($val);
			if (substr($url, 0, $len) == $val)
			{
				// replace the first instance only
				return substr_replace($url, $key, 0, $len);
			}
		}

		return $url;
	}


	//FIXME - $match not used?

	/**
	 * @param $text
	 * @param $match
	 * @return array|string|string[]|null
	 */
	public function e_highlight($text, $match)
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
	 *
	 * @param string  $text
	 * @param boolean $posted - if the text has been posted. (uses stripslashes etc)
	 * @param string  $mods   - flags for text transformation.
	 */
	public function toEmail($text, $posted = '', $mods = 'parse_sc, no_make_clickable')
	{

		if ($posted === true)
		{
			if (MAGIC_QUOTES_GPC)
			{
				$text = stripslashes($text);
			}
			$text = preg_replace('#\[(php)#i', '&#91;\\1', $text);
		}

		$text = (strtolower($mods) !== 'rawtext') ? $this->replaceConstants($text, 'full') : $text;

		if ($this->isHtml($text))
		{
			$text = str_replace(array('[html]', '[/html]'), '', $text);
			$text = $this->decodeEntitiesKeepingMarkup($text);
		}
		else
		{

			$text = $this->toHTML($text, true, $mods);
		}

		return $text;
	}

	/**
	 * html_entity_decode() for HTML that adds no element and no attribute: entities stay as they are in a comment and a tag name, '<' and '>' everywhere, and '"' anywhere but in text or between a tag's attributes; '' if the text cannot be searched.
	 *
	 * @param string $text
	 * @return string
	 */
	private function decodeEntitiesKeepingMarkup($text)
	{
		$inside = '';

		$html = preg_replace_callback('/<!---?>|<!--|--!?>|<\/?[a-z!&][^\s\/<>"\']*|>|"|\'|&(?:#\d+|#x[0-9a-f]+|[a-z][a-z0-9]*);/i', function ($match) use (&$inside)
		{
			$token = $match[0];

			if ($token[0] === '&')
			{
				$character = html_entity_decode($token, ENT_COMPAT, 'UTF-8');
				$keep = $inside === '<!--' || $character === '<' || $character === '>' || ($character === '"' && $inside !== '' && $inside !== '<');

				return $keep ? $token : $character;
			}

			if ($inside === '<!--')
			{
				if ($token === '-->' || $token === '--!>')
				{
					$inside = '';
				}
			}
			elseif ($inside === '')
			{
				if ($token === '<!--')
				{
					$inside = '<!--';
				}
				elseif ($token[0] === '<' && $token !== '<!-->' && $token !== '<!--->')
				{
					$inside = '<';
				}
			}
			elseif ($inside === '<')
			{
				if (substr($token, -1) === '>')
				{
					$inside = '';
				}
				elseif ($token === '"' || $token === "'")
				{
					$inside = $token;
				}
			}
			elseif ($token === $inside)
			{
				$inside = '<';
			}

			return $token;
		}, $text);

		return ($html === null) ? '' : $html;
	}


	/**
	 * Given an email address, returns a link including with obfuscated text.
	 * e-email css in e107.css inserts the user/domain data for display.
	 *
	 * @param string $email
	 * @param string $words   [optional] text to display
	 * @param null   $subject [optional] default subject for email.
	 * @return string
	 */
	public function emailObfuscate($email, $words = null, $subject = null)
	{

		if (strpos($email, '@') === false)
		{
			return '';
		}

		if ($subject)
		{
			$subject = '?subject=' . $subject;
		}

		list($name, $address) = explode('@', $email, 2);

		if (empty($words))
		{
			$words = '&#64;';
			$user = "data-user='" . $this->obfuscate($name) . "'";
			$dom = "data-dom='" . $this->obfuscate($address) . "'";
		}
		else
		{
			$user = '';
			$dom = '';
		}

		$url = 'mailto:' . $email . $subject;

		$safe = $this->obfuscate($url);

		return "<a class='e-email' {$user} {$dom} rel='external' href='" . $safe . "'>" . $words . '</a>';
	}


	/**
	 * Obfuscate text from bots using Randomized encoding.
	 *
	 * @param $text
	 * @return string
	 */
	public function obfuscate($text)
	{

		$ret = '';
		foreach (str_split($text) as $letter)
		{
			switch (mt_rand(1, 3))
			{
				// HTML entity code
				case 1:
					$ret .= '&#' . ord($letter) . ';';
					break;

				// Hex character code
				case 2:
					$ret .= '&#x' . dechex(ord($letter)) . ';';
					break;

				// Raw (no) encoding
				case 3:
					$ret .= $letter;
			}
		}

		return $ret;
	}


	/**
	 * @param $name
	 * @return array|e_parse_shortcode|null
	 */
	public function __get($name)
	{

		switch ($name)
		{
			case 'e_sc':
				$ret = e107::getScParser();
				break;


			default:
				//	trigger_error('$e107->$'.$name.' not defined', E_USER_WARNING);
				return null;
				break;
		}


		$this->$name = $ret;

		return $ret;
	}

	// Formerly located in e_parser --------------------------


	/**
	 * Merge default 'global' attributes into assigned tags.
	 */
	private function compileAttributeDefaults()
	{

		foreach ($this->allowedAttributes as $tag => $array)
		{
			if ($tag === 'default')
			{
				continue;
			}

			foreach ($this->allowedAttributes['default'] as $def)
			{
				$this->allowedAttributes[$tag][] = $def;
			}

		}

	}

	/**
	 * Used by e_parse to start
	 */
	public function init()
	{

		if (defined('FONTAWESOME'))
		{
			$this->fontawesome = (int) FONTAWESOME;
		}

		if (defined('BOOTSTRAP'))
		{
			$this->bootstrap = (int) BOOTSTRAP;
		}

		$this->modRewriteMedia = deftrue('e_MOD_REWRITE_MEDIA');

		if (defined('e_HTTP_STATIC'))
		{
			$this->staticUrl = e_HTTP_STATIC;
		}

		// Preprocess the supermods to be useful default arrays with all values
		foreach ($this->e_SuperMods as $key => $val)
		{
			// precalculate super defaults
			$this->e_SuperMods[$key] = array_merge($this->e_optDefault, $this->e_SuperMods[$key]);
			$this->e_SuperMods[$key]['context'] = $key;
		}

		$this->allowedTags = array('html', 'body', 'div', 'a', 'img', 'table', 'tr', 'td', 'th', 'tbody', 'thead', 'colgroup', 'b',
			'i', 'pre', 'code', 'strong', 'u', 'em', 'ul', 'ol', 'li', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p',
			'section', 'article', 'blockquote', 'hgroup', 'aside', 'figure', 'figcaption', 'abbr', 'span', 'audio', 'video', 'source', 'br',
			'small', 'caption', 'noscript', 'hr', 'section', 'iframe', 'sub', 'sup', 'cite', 'x-bbcode', 'label', 'dl', 'dt', 'dd',
		);

		$this->scriptTags = array('script', 'applet', 'form', 'input', 'button', 'embed', 'object', 'ins', 'select', 'textarea'); //allowed when $pref['post_script'] is enabled.

		$this->allowedAttributes = array(
			'default'  => array('id', 'style', 'class', 'title', 'lang', 'accesskey'),
			'img'      => array('src', 'alt', 'width', 'height'),
			'a'        => array('href', 'target', 'rel'),
			'script'   => array('type', 'src', 'language', 'async'),
			'iframe'   => array('src', 'frameborder', 'width', 'height', 'allowfullscreen', 'allow'),
			'input'    => array('type', 'name', 'value'),
			'form'     => array('action', 'method', 'target'),
			'audio'    => array('src', 'controls', 'autoplay', 'loop', 'muted', 'preload'),
			'video'    => array('autoplay', 'controls', 'height', 'loop', 'muted', 'poster', 'preload', 'src', 'width'),
			'table'    => array('border', 'cellpadding', 'cellspacing'), // BC Fix.
			'td'       => array('colspan', 'rowspan', 'name', 'bgcolor'),
			'th'       => array('colspan', 'rowspan'),
			'col'      => array('span'),
			'embed'    => array('src', 'wmode', 'type', 'width', 'height'),
			'x-bbcode' => array('alt'),
			'label'    => array('for'),
			'source'   => array('media', 'sizes', 'src', 'srcset', 'type'),

		);

		$this->scriptAttributes = array('onclick', 'onchange', 'onblur', 'onload', 'onfocus', 'onkeydown', 'onkeypress', 'onkeyup',
			'ondblclick', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel',
			'onwheel', 'oncopy', 'oncut', 'onpaste'
		);

		$this->blockTags = array('p', 'pre', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote'); // element includes its own line-break.

		$this->badAttrValues = array('javascript[\s]*?:', 'alert\(', 'vbscript[\s]*?:', 'data:text\/html', 'mhtml[\s]*?:', 'data:[\s]*?image');

		$this->replaceAttrValues = array(
			'default' => array()
		);

	}

	/**
	 * Add Allowed Tags.
	 *
	 * @param string
	 */
	public function addAllowedTag($tag)
	{

		$this->allowedTags[] = $tag;
	}


	/**
	 * @param $tag      - html tag.
	 * @param $attArray - array of attributes. eg. array('style', 'id', 'class') etc.
	 */
	public function addAllowedAttribute($tag, $attArray)
	{

		$this->allowedAttributes[$tag] = (array) $attArray;
	}


	/**
	 * Set Allowed Tags.
	 *
	 * @param $array
	 */
	public function setAllowedTags($array = array())
	{

		$this->allowedTags = $array;
	}

	/**
	 * Set Script Access
	 *
	 * @param $val int e_UC_MEMBER, e_UC_NOBODY, e_UC_MAINADMIN or userclass number.
	 */
	public function setScriptAccess($val)
	{

		$this->scriptAccess = $val;
	}

	/**
	 * @param array $arr
	 * @return void
	 */
	public function setScriptAttibutes($arr)
	{
		$this->scriptAttributes = (array) $arr;
	}

	/**
	 * @return array
	 */
	public function getAllowedTags()
	{
		return $this->allowedTags;
	}

	/**
	 * @return array
	 */
	public function getAllowedAttributes()
	{
		return $this->allowedAttributes;
	}


	/**
	 * @return bool
	 */
	public function getScriptAccess()
	{
		return $this->scriptAccess;
	}

	/**
	 * @return array
	 */
	public function getRemoved()
	{
		return $this->removedList;
	}

	/**
	 * Set Allowed Attributes.
	 *
	 * @param $array
	 */
	public function setAllowedAttributes($array = array())
	{

		$this->allowedAttributes = $array;
	}

	/**
	 * Set Script Tags.
	 *
	 * @param $array
	 */
	public function setScriptTags($array = array())
	{

		$this->scriptTags = $array;
	}


	/**
	 * @param int $version
	 */
	public function setFontAwesome($version)
	{
		$this->fontawesome = (int) $version;
	}

	/**
	 * @param int $version
	 */
	public function setBootstrap($version)
	{
		$this->bootstrap = (int) $version;
	}

	/**
	 * The major Bootstrap version the active theme declared, or null when it declared none.
	 *
	 * @return int|null
	 */
	public function getBootstrap()
	{
		return $this->bootstrap;
	}

	public function setmodRewriteMedia($bool)
	{
		$this->modRewriteMedia = (bool) $bool;
	}

	/**
	 * Add leading zeros to a number. eg. 3 might become 000003
	 *
	 * @param $num       int
	 * @param $numDigits - total number of digits
	 * @return string number with leading zeros.
	 */
	public function leadingZeros($num, $numDigits)
	{
		return (string) sprintf('%0' . $numDigits . 'd', $num);
	}

	/**
	 * The value of the language term a name in content refers to, or null when it names no defined language term.
	 *
	 * @param string $name
	 * @return string|null
	 */
	public static function languageTerm($name)
	{
		$name = trim((string) $name);

		if (preg_match('/^[A-Z][A-Za-z0-9_]*$/', $name) && preg_match('/LAN|\d$/', $name) && defined($name))
		{
			return constant($name);
		}

		return null;
	}

	/**
	 * Generic variable translator for LAN definitions.
	 *
	 * @param string $lan  - string LAN or LAN constant.
	 * @param string | array $vals - either a single value, which will replace '[x]' or an array with key=>value pairs.
	 * @return string
	 * @example $tp->lanVars("My name is [x] and I own a [y]", array("John","Cat"));
	 * @example $tp->lanVars("My name is [x] and I own a [y]", array('x'=>"John", 'y'=>"Cat"));
	 */
	public function lanVars($lan, $vals, $bold = false)
	{
		$lan = defset($lan, $lan);
		$array = (!is_array($vals)) ? array('x' => $vals) : $vals;

		$search = array();
		$replace = array();

		$defaults = array('x', 'y', 'z');

		foreach ($array as $k => $v)
		{
			if (is_numeric($k)) // convert array of numeric to x,y,z
			{
				$k = $defaults[$k];
			}

			$search[] = '[' . $k . ']';
			$replace[] = ($bold === true) ? '<strong>' . $v . '</strong>' : $v;
		}

		return str_replace($search, $replace, $lan);
	}


	public function lanLink($lan, $url, $options=[])
	{
		$srch =["[", "]"];
		$repl = ["<a target='_blank' href='" .$url . "'>", "</a>"];

		$text = defset($lan, $lan);

		return str_replace($srch, $repl, $text);
	}

	/**
	 * Return an Array of all specific tags found in an HTML document and their attributes.
	 *
	 * @param $html    - raw html code
	 * @param $taglist - comma separated list of tags to search or '*' for all.
	 * @param $header  - if the $html includes the html head or body tags - it should be set to true.
	 */
	public function getTags($html, $taglist = '*', $header = false)
	{

		if ($header == false)
		{
			$html = '<html><body>' . $html . '</body></html>';
		}

		$doc = $this->domObj;

		$doc->preserveWhiteSpace = true;
		libxml_use_internal_errors(true);
		$doc->loadHTML($html);

		$tg = explode(',', $taglist);
		$ret = array();

		foreach ($tg as $find)
		{
			$tmp = $doc->getElementsByTagName($find);

			/**
			 * @var             $k
			 * @var DOMDocument $node
			 */
			foreach ($tmp as $k => $node)
			{
				$tag = $node->nodeName;
				$inner = $node->C14N();
				$inner = str_replace('&#xD;', '', $inner);

				foreach ($node->attributes as $attr)
				{
					$name = $attr->nodeName;
					$value = $attr->nodeValue;
					$ret[$tag][$k][$name] = $value;
				}

				$ret[$tag][$k]['@value'] = $inner;


			}
		}

		if ($header == false)
		{
			unset($ret['html'], $ret['body']);
		}


		return $ret;
	}

	/**
	 * Glyph Embed Method Direct from svg file.
	 *
	 * @param string $cat  far|fab|fas
	 * @param string $id   eg. fa-search
	 * @param array  $parm eg. ['fw'=>true]
	 * @return string|false
	 */
	private function toGlyphEmbed($cat, $id, $parm = array())
	{
		$dirs = ['far' => 'regular', 'fab' => 'brands', 'fas' => 'solid'];

		if (!isset($dirs[$cat]) || !preg_match('/^[a-z0-9-]+$/', $id))
		{
			return false;
		}

		$path = e_WEB . 'lib/font-awesome/5/svgs/';
		$path .= $dirs[$cat] . '/';
		$path .= str_replace('fa-', '', $id) . ".svg";


		if ($ret = file_get_contents($path))
		{
			$class = 'svg-inline--fa ';
			$class .= $id;
			$class .= ' fa-w-16';
			$class .= !empty($parm['fw']) ? ' fa-fw' : '';

			return str_replace('<svg', '<svg class="' . $class . '" role="img" aria-hidden="true" ', $ret);
		}

		return false;

	}


	/**
	 * Parse xxxxx.glyph file to bootstrap glyph format.
	 *
	 * @param string       $text    ie. fa-xxxx, fab-xxx, fas-xxxx
	 * @param array|string $options = [
	 *      'size'  => (string)     2x, 3x, 4x, or 5x
	 *      'fw'    => (bool)       Fixed-Width
	 *      'spin'  => (bool)       Spin
	 *      'rotate'=> (int)        Rotate in Degrees.
	 *  ]
	 * @example $tp->toGlyph('fab-mailchimp');
	 * @example $tp->toGlyph('fas-camera');
	 * @example $tp->toGlyph('fa-spinner', 'spin=1');
	 * @example $tp->toGlyph('fa-spinner', array('spin'=>1));
	 * @example $tp->toGlyph('fa-shield', array('rotate'=>90, 'size'=>'2x'));
	 */
	public function toGlyph($text, $options = ' ')
	{

		if (empty($text))
		{
			return false;
		}

		if (is_array($options))
		{
			$parm = $options;
			$options = varset($parm['space'], '');
		}
		elseif (is_string($options) && strpos($options, '='))
		{
			parse_str($options, $parm);
			$options = varset($parm['space'], '');
		}
		else
		{
			$parm = array();
		}

		$cat = '';
		$name = '';
		list($id) = explode('.glyph', $text, 2); // trim .glyph from the end.
		$origID = $id;
		if (strpos($id, '-') !== false)
		{
			list($cat, $name) = explode('-', $id, 2);

			if (empty($name)) // eg. missing something after 'fa-'
			{
				return null;
			}
		}


		$spin = null;
		$rotate = null;
		$fixedW = null;
		$prefix = 'glyphicon glyphicon-'; // fallback
		$size = null;
		$tag = 'i';


		// FontAwesome General settings.
		switch ($cat)
		{
			// Core eg. e-database-32
			case 'e':

				$size = (substr($text, -3) === '-32') ? 'S32' : 'S16';

				if (substr($text, -3) === '-24')
				{
					$size = 'S24';
				}

				return "<i class='" . $size . ' ' . $text . "'></i>";

				break;


			case "far":
			case "fab":
			case "fas":
				$prefix = $cat . ' ';
				$id = str_replace($cat . '-', 'fa-', $id);

				/** @experimental - subject to removal at any time. */
				if (!empty($parm['embed']))
				{
					if ($ret = $this->toGlyphEmbed($cat, $id, $parm))
					{
						return $ret;
					}
				}

				break;

			case "fa":
			default:
				if ($this->fontawesome === 5 || $this->fontawesome === 6)
				{
					$vr = 'fa'.$this->fontawesome.'-';
					$fab = e107::getMedia()->getGlyphs($vr.'fab');
					$fas = e107::getMedia()->getGlyphs($vr.'fas');
					$far = e107::getMedia()->getGlyphs($vr.'far');
					$shims = e107::getMedia()->getGlyphs($vr.'shims');
					$fa4 = e107::getMedia()->getGlyphs('fa4');

					list($tmp) = explode('-', $id);
					$code = str_replace($tmp . '-', '', $id);

					if (isset($shims[$code]))
					{
						$prefix = '';
						$id = $shims[$code];
					}
					elseif (isset($fab[$code]))
					{
						$prefix = 'fab ';
					}
					elseif (isset($fas[$code]))
					{
						$prefix = 'fas '; // 'fa-solid' for fa6?
						$id = 'fa-' . $code;
					}
					elseif (in_array($code, $far))
					{
						$prefix = 'far ';
					}
					elseif (in_array($code, $fa4))
					{
						$prefix = 'fa ';
						$id = 'fa-' . $code;
					}
					else
					{
						$prefix = ($this->bootstrap === 3) ? 'glyphicon glyphicon-' : 'fa fa-';
					}

					/** @experimental - subject to removal at any time. */
					if (!empty($parm['embed']))
					{
						$cat = trim($prefix);

						if ($ret = $this->toGlyphEmbed($cat, $id, $parm))
						{
							return $ret;
						}
					}

					$cat = trim($prefix);

				}
				elseif ($this->fontawesome === 4)
				{
					$fa4 = e107::getMedia()->getGlyphs('fa4');
					if (isset($fa4[$name]))
					{
						$prefix = 'fa ';
						$id = 'fa-' . $name;
					}


				}
				elseif (strpos($text, 'glyphicon-') === 0) // Bootstrap 3
				{
					$prefix = 'glyphicon ';
					$tag = 'span';

				}
				elseif (strpos($text, 'icon-') === 0) // Bootstrap 2
				{
					if ($this->bootstrap !== 2) // bootrap 2 icon but running bootstrap3.
					{
						$prefix = 'glyphicon ';
						$tag = 'span';
						$id = str_replace('icon-', 'glyphicon-', $id);
					}
					else
					{
						$prefix = '';
						$tag = 'i';
					}

				}
		}


		if ($custom = e107::getThemeGlyphs()) // Custom Glyphs
		{
			foreach ($custom as $glyphConfig)
			{
				if (strpos($text, $glyphConfig['prefix']) === 0)
				{
					$prefix = $glyphConfig['class'] . ' ';
					$tag = $glyphConfig['tag'];
					$id = $origID;
					continue;
				}
			}
		}
		else // FontAwesome shouldn't hurt legacy icons.
		{
			$size = !empty($parm['size']) ? ' fa-' . $parm['size'] : '';
			$spin = !empty($parm['spin']) ? ' fa-spin' : '';
			$rotate = !empty($parm['rotate']) ? ' fa-rotate-' . (int) $parm['rotate'] : '';
			$fixedW = !empty($parm['fw']) ? ' fa-fw' : '';
		}


		$idAtt = (!empty($parm['id'])) ? "id='" . $parm['id'] . "' " : '';
		$style = (!empty($parm['style'])) ? "style='" . $parm['style'] . "' " : '';
		$class = (!empty($parm['class'])) ? $parm['class'] . ' ' : '';
		$placeholder = isset($parm['placeholder']) ? $parm['placeholder'] : '';
		$title = (!empty($parm['title'])) ? " title='" . $this->toAttribute($parm['title']) . "' " : '';

		$text = '<' . $tag . " {$idAtt}class='" . $class . $prefix . $id . $size . $spin . $rotate . $fixedW . "' " . $style . $title . '>' . $placeholder . '</' . $tag . '>';
		$text .= ($options !== false) ? $options : '';

		return $text;


	}


	/**
	 * Return a Bootstrap Badge tag
	 *
	 * @param      $text
	 * @param null $parm
	 * @return string
	 */
	public function toBadge($text, $parm = null)
	{

		$class = !empty($parm['class']) ? ' ' . $parm['class'] : ' bg-secondary rounded-pill badge-secondary';

		return "<span class='badge" . $class . "'>" . $text . '</span>';
	}


	/**
	 * Return a Bootstrap Label tag
	 *
	 * @param      $text
	 * @param null $type
	 * @return string
	 */
	public function toLabel($text, $type = null)
	{

		if ($type === null)
		{
			$type = 'default';
		}

		$tmp = explode(',', $text);

		$opt = array();
		foreach ($tmp as $v)
		{
			$opt[] = "<span class='label label-" . $type . "'>" . $v . '</span>';
		}

		return implode(' ', $opt);
	}

	/**
	 * Take a file-path and convert it to a download link.
	 *
	 * @param       $text
	 * @param array $parm
	 * @return string
	 */
	public function toFile($text, $parm = array())
	{

		$srch = array(
			'{e_MEDIA_FILE}' => 'e_MEDIA_FILE/',
			'{e_PLUGIN}'     => 'e_PLUGIN/'
		);

		$link = e_HTTP . 'request.php?file=' . str_replace(array_keys($srch), $srch, $text);

		if (!empty($parm['raw']))
		{
			return $link;
		}

		return "<a href='" . $link . "'>-attachment-</a>"; //TODO Add pref for this.
	}

	/**
	 * Resolve a user_image value to the avatar file it names, without checking that the file is there.
	 *
	 * @param string $image user_image as stored, with or without the -upload- prefix
	 * @return string path under e_AVATAR_UPLOAD or e_AVATAR_DEFAULT, '' when the value names no local
	 *                file (a remote url, or anything carrying a path); never the generic avatar
	 */
	public function toAvatarPath($image)
	{

		if (!is_string($image) || strpbrk($image, "/\\\0") !== false)
		{
			return '';
		}

		$directory = e_AVATAR_DEFAULT;

		if (strpos($image, '-upload-') === 0)
		{
			$directory = e_AVATAR_UPLOAD;
			$image = (string) substr($image, 8);
		}

		if ($image === '' || trim($image, '.') === '')
		{
			return '';
		}

		return $directory . $image;
	}


	/**
	 * Render an avatar based on supplied user data or current user when missing.
	 *
	 * @param array    $userData - user data from e107_user. ie. user_image, user_id etc.
	 * @param array    $options = [
	 * 'w'          => (int)        image width in px
	 * 'h'          => (int)        image height in px
	 * 'crop'       => *int|bool)   enables cropping when true
	 * 'shape'		=> (string)		(optional) rounded|circle|thumbnail
	 * 'id'		    => (string)		'id' attribute will be added to tag.
	 * 'class'		=> (string)		override default 'class' attribute in tag.
	 * 'alt'		=> (string)		override default 'alt' attribute in tag.
	 * 'base64'		=> (bool)		use embedded base64 for image src.
	 * 'hd'		    => (bool)		double the resolution of the image. Useful for retina displays.
	 * 'type'		=> (string)		when set to 'url' returns the URL value instead of the tag.
	 * 'style'		=> (string)		sets the style attribute.
	 * 'mode'		=> (string)		'full' url mode.
	 * ]
	 * @return string <img> tag of avatar.
	 */
	public function toAvatar($userData = null, $options = array())
	{

		$tp = e107::getParser();
		$width = !empty($options['w']) ? intval($options['w']) : $tp->thumbWidth;
		$height = $tp->thumbHeight;
		$crop = isset($options['crop']) ? $options['crop'] : $tp->thumbCrop;
		$linkStart = '';
		$linkEnd = '';
		$full = !empty($options['base64']) ? true : false;
		$file = '';
		$remote = false;

		if (!empty($options['mode']) && $options['mode'] === 'full')
		{
			$full = true;
		}

		if (!empty($options['h']))
		{
			$height = intval($options['h']);
		}

		if (!empty($options['hd'])) // Fix resolution on Retina display.
		{
			$width *= 2;
			$height *= 2;
		}


		if ($userData === null && USERID)
		{
			$userData = array();
			$userData['user_id'] = USERID;
			$userData['user_image'] = deftrue('USERIMAGE');
			$userData['user_name'] = deftrue('USERNAME');
			$userData['user_currentvisit'] = deftrue('USERCURRENTVISIT');
		}


		$image = (!empty($userData['user_image'])) ? varset($userData['user_image']) : null;

		$genericFile = e_IMAGE . 'generic/blank_avatar.jpg';
		$genericImg = $tp->thumbUrl($genericFile, 'w=' . $width . '&h=' . $height, true, $full);

		if (!empty($image))
		{

			if (strpos($image, '://') !== false) // Remote Image
			{
				$url = $image;
				$remote = true;
			}
			else
			{
				$file = $this->toAvatarPath($image);

				if ($file !== '')
				{
					$image = basename($file);
				}

				if (file_exists($file))
				{
					$url = $tp->thumbUrl($file, 'w=' . $width . '&h=' . $height . '&crop=' . $crop, false, $full);
				}
				else // Image Missing.
				{
					$file = $genericFile;
					$url = $genericImg;
				}
			}
		}
		else // No image provided - so send generic.
		{
			$url = $genericImg;
			$file = $genericFile;
		}

		if (!empty($options['base64'])) // embed image data into URL.
		{
			$content = '';

			if(!empty($file))
			{
				$content = file_get_contents($file);
				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
			}
			else
			{
				// Use the shared (singleton) file handler so the remote fetch
				// can be stubbed in tests via the registry, and so repeated
				// avatar rendering reuses one e_file instance.
				$content = e107::getFile(true)->getRemoteContent($url);
				$ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
			}

			if (!empty($content))
			{
				$url = 'data:image/' . $ext . ';base64,' . base64_encode($content);
			}
		}

		if (!empty($options['hd'])) // Fix resolution on Retina display.
		{
			$width = $width / 2;
			$height = ($height / 2);
		}

		if (($url == $genericImg) && !empty($userData['user_id']) && (($userData['user_id'] == USERID)) && !empty($options['link']))
		{
			$linkStart = "<a class='e-tip' title=\"" . LAN_EDIT . "\" href='" . e107::getUrl()->create('user/myprofile/edit') . "'>";
			$linkEnd = '</a>';
		}

		$title = (ADMIN) ? $image : (isset($userData['user_name']) ? $userData['user_name'] : '');
		$shape = (!empty($options['shape'])) ? 'img-' . $options['shape'] : 'img-rounded rounded';

		if ($shape === 'img-circle')
		{
			$shape .= ' rounded-circle';
		}

		if (!empty($options['type']) && $options['type'] === 'url')
		{
			return $url;
		}

		if (!empty($options['alt']))
		{
			$title = $options['alt'];
		}

		$classOnline = (!empty($userData['user_currentvisit']) && intval($userData['user_currentvisit']) > (time() - 300)) ? ' user-avatar-online' : '';

		$class = !empty($options['class']) ? $options['class'] : $shape . ' user-avatar';

		$attributes = array(
			'id'      => !empty($options['id']) ? $options['id'] : null,
			'class'   => $class . $classOnline,
			'alt'     => $title,
			'src'     => html_entity_decode($url, ENT_QUOTES, 'UTF-8'),
			'width'   => $width,
			'height'  => empty($height) ? null : $height,
			'style'   => !empty($options['style']) ? $options['style'] : null,
			'loading' => !empty($options['loading']) ? $options['loading'] : 'lazy',
		);

		if ($remote && strpos($url, 'data:') !== 0)
		{
			$attributes['onerror'] = "this.onerror=null;this.src='" . html_entity_decode($genericImg, ENT_QUOTES, 'UTF-8') . "';";
		}

		$text = $linkStart;
		$text .= '<img' . $tp->toAttributes($attributes, true) . ' />';
		$text .= $linkEnd;

		//	return $url;
		return $text;

	}


	/**
	 * Display an icon.
	 *
	 * @param string $icon
	 * @example $tp->toIcon("{e_IMAGES}icons/something.png");
	 */
	public function toIcon($icon = '', $parm = array())
	{

		if (empty($icon))
		{
			return null;
		}

		//	if(strpos($icon,'e_MEDIA_IMAGE')!==false)
		//	{
		//	return "<div class='alert alert-danger'>Use \$tp->toImage() instead of toIcon() for ".$icon."</div>"; // debug info only.
		//	}

		if (strpos($icon, '<i ') === 0) // if it's html (ie. css sprite) return the code.
		{
			return $icon;
		}

		$ext = pathinfo($icon, PATHINFO_EXTENSION);
		$dimensions = null;

		if (!$ext || $ext === 'glyph') // Bootstrap or Font-Awesome.
		{
			return $this->toGlyph($icon, $parm);
		}

		if (strpos($icon, 'e_MEDIA_IMAGE') !== false)
		{
			$path = $this->thumbUrl($icon);
			$dimensions = $this->thumbDimensions();
		}
		elseif ($icon[0] === '{')
		{
			$path = $this->replaceConstants($icon, 'abs');
		}
		elseif (!empty($parm['legacy']))
		{
			$legacyList = (!is_array($parm['legacy'])) ? array($parm['legacy']) : $parm['legacy'];

			foreach ($legacyList as $legPath)
			{
				$legacyPath = $legPath . $icon;
				$filePath = $this->replaceConstants($legacyPath);

				if (is_readable($filePath))
				{
					$path = $this->replaceConstants($legacyPath, 'full');
					break;
				}

			}

			if (empty($path))
			{
				$log = e107::getLog();
				$log->addDebug('Broken Icon Path: ' . $icon . "\n" . print_r(debug_backtrace(null, 2), true), false)->save('IMALAN_00');
				e107::getDebug()->log('Broken Icon Path: ' . $icon);

				return null;
			}

		}
		else
		{
			$path = $icon;
		}


		$alt = (!empty($parm['alt'])) ? $this->toAttribute($parm['alt']) : basename($path);
		$class = (!empty($parm['class'])) ? $parm['class'] : 'icon';

		if ($ext === 'svg')
		{
			$class .= ' icon-svg fa-2x';
			if (!empty($parm['size']))
			{
				$class .= ' icon-svg-' . $parm['size'];
			}
		}

		return "<img class='" . $class . "' src='" . $path . "' alt='" . $alt . "' " . $dimensions . ' />';
	}


	/**
	 * Render an img tag.
	 *
	 * @param string $file
	 * @param array  $parm = [
	 *  'w'         => (int)       Width in px
	 *  'h'         => (int)       Height in px
	 *  'alt'       => (string)    Alt text.
	 *  'class'     => (string)
	 *  'id'        => (string)
	 *  'loading'   => (string)
	 *  'legacy'    => (array)		 Usually a legacy path like {e_FILE}
	 *  'type'		=> (array)		 Force the returned image to be a jpg, webp etc.
	 * ]
	 *
	 * @example $tp->toImage('welcome.png', array('legacy'=>{e_IMAGE}newspost_images/','w'=>200));
	 * @return string|null
	 */
	public function toImage($file, $parm = array())
	{
		if (strpos($file, 'e_AVATAR') !== false)
		{
			return "<div class='alert alert-danger'>Use \$tp->toAvatar() instead of toImage() for " . $file . '</div>'; // debug info only.

		}

		if (empty($file) && empty($parm['placeholder']))
		{
			return null;
		}

		if (!empty($file) && (strpos($file, 'http') === false))
		{
			$srcset = null;
			$path = null;
			$file = trim($file);
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			$accepted = array('jpg', 'gif', 'png', 'jpeg', 'svg', 'webp');


			if (!in_array($ext, $accepted))
			{
				return null;
			}
		}

		$tp = $this;

		if(($alt = $this->getImageAltCacheFile($file)) && empty($parm['alt']))
		{
			$parm['alt'] = $alt;
		}

		//		e107::getDebug()->log($file);
		//	e107::getDebug()->log($parm);

		if (strpos($file, 'http') === 0)
		{
			$path = $file;
		}
		elseif (strpos($file, 'e_MEDIA') !== false || strpos($file, 'e_THEME') !== false || strpos($file, 'e_PLUGIN') !== false || strpos($file, '{e_IMAGE}') !== false) //v2.x path.
		{

			if (!isset($parm['w']) && !isset($parm['h']))
			{
				$parm = (array) $parm;
				$parm['w'] = $tp->thumbWidth();
				$parm['h'] = $tp->thumbHeight();
				$parm['crop'] = $tp->thumbCrop();
				$parm['x'] = $tp->thumbEncode();
			}

			unset($parm['src']);
			$path = $tp->thumbUrl($file, $parm);


			if (empty($parm['w']) && empty($parm['h']))
			{
				$parm['srcset'] = false;
			}
			elseif (!isset($parm['srcset']))
			{
				$srcSetParm = $parm;

				if (!isset($parm['size']))
				{
					$srcSetParm['size'] = (varset($parm['w']) < 100) ? '4x' : '2x';
				}
				else
				{
					unset($parm['size']);
				}

				$parm['srcset'] = $tp->thumbSrcSet($file, $srcSetParm);
			}

		}
		elseif (!empty($file) && $file[0] === '{') // Legacy v1.x path. Example: {e_PLUGIN}myplugin/images/fixedimage.png
		{
			$path = $tp->replaceConstants($file, 'abs');
		}
		elseif(!empty($parm['legacy']) && !empty($file)) // Search legacy path for image in a specific folder. No path, only file name provided.
		{

			$legacyPath = rtrim($parm['legacy'], '/') . '/' . $file;
			$filePath = $tp->replaceConstants($legacyPath);

			if (is_readable($filePath))
			{
				$path = $tp->replaceConstants($legacyPath, 'abs');
			}
			else
			{
				$log = e107::getLog();
				$log->addDebug('Broken Image Path: ' . $legacyPath . "\n" . print_r(debug_backtrace(0, 2), true), false)->save('IMALAN_00');
				e107::getDebug()->log('Broken Image Path: ' . $legacyPath);
			}

		}
		else // usually http://....
		{
			$path = $file;
		}

		if (empty($path) && !empty($parm['placeholder']))
		{
			$path = $tp->thumbUrl($file, $parm);
		}



		$id = (!empty($parm['id'])) ? 'id="' . $this->attributeValue($parm['id']) . '" ' : '';
		$class = (!empty($parm['class'])) ? $this->attributeValue($parm['class']) : 'img-responsive img-fluid';
		$alt = (!empty($parm['alt'])) ? $tp->toAttribute($parm['alt']) : $this->attributeValue(basename($file));
		$style = (!empty($parm['style'])) ? 'style="' . $this->attributeValue($parm['style']) . '" ' : '';
		$srcset = (!empty($parm['srcset'])) ? 'srcset="' . $this->attributeValue($parm['srcset']) . '" ' : '';
		$width = (!empty($parm['w'])) ? 'width="' . (int) $parm['w'] . '" ' : '';
		$title = (!empty($parm['title'])) ? 'title="' . $this->attributeValue($parm['title']) . '" ' : '';
		$height = !empty($parm['h']) ? 'height="' . (int) $parm['h'] . '" ' : '';
		$loading = !empty($parm['loading']) ? 'loading="' . $this->attributeValue($parm['loading']) . '" ' : ''; // eg. lazy, eager, auto

		if (isset($parm['width'])) // width attribute override (while retaining w)
		{
			$width = 'width="' . $this->attributeValue($parm['width']) . '" ';
		}

		if (isset($parm['height'])) // height attribute override (while retaining h)
		{
			$height = 'height="' . $this->attributeValue($parm['height']) . '" ';
		}

		$html = '';
		/*
				if($this->convertToWebP)
				{
					$source = $tp->thumbUrl($file, $parm);
					$html = "<picture class=\"{$class}\">\n";

					if(!empty($parm['srcset']))
					{
						list($webPSourceSet, $webPSize) = explode(' ', $parm['srcset']);
						$html .= '<source type="image/webp" srcset="' . $webPSourceSet . ' ' . $webPSize . '">';
						$html .= "\n";
						$html .= '<source type="image/' . str_replace('jpg', 'jpeg', $ext) . '" srcset="' . $parm['srcset'] . '">';
						$html .= "\n";
						$srcset = ''; // remove it from the img tag below.
					}

					$html .= '<source type="image/webp" srcset="' . $source . '">';
					$html .= "\n";
				}*/

		if (empty($path))
		{
			return null;
		}

		if(varset($parm['return']) === 'url')
		{
			$path = $tp->createConstants($path, 'mix');
			return $tp->replaceConstants($path, 'full');
		}

		$html .= "<img {$id}class=\"{$class}\" src=\"" . $this->attributeValue($path) . '" alt="' . $alt . '" ' . $srcset . $width . $height . $style . $loading . $title . ' />';

		//	$html .= ($this->convertToWebP) ? "\n</picture>" : '';

		return $html;

	}


	/**
	 * Encode a value for markup the browser parses once, as a quoted attribute or element text, leaving an entity already in it alone.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function attributeValue($value)
	{
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
	}


	/**
	 * Whether a value a form posted back is the stored value the form showed, unchanged.
	 *
	 * @param mixed      $posted  the value as posted, or an array of them
	 * @param mixed      $shown   the stored value as the form showed it, after the form's own decoding, or an array of them
	 * @param array|null $element the element that showed it, as array('type' => an e_form element type, 'maxItems' => the tag widget's limit as {@see e_form::tags()} takes it), to compare as a browser posts that element back; null to compare as stored
	 * @return bool
	 * @throws InvalidArgumentException when $element is neither null nor an array naming a type
	 */
	public function isUnchangedPost($posted, $shown, $element = null)
	{
		if ($element !== null && (!is_array($element) || !isset($element['type']) || !is_string($element['type'])))
		{
			throw new InvalidArgumentException("isUnchangedPost() takes the element that showed the value as array('type' => an e_form element type), or null.");
		}

		if (is_array($posted) || is_array($shown))
		{
			if (!is_array($posted) || !is_array($shown) || count($posted) !== count($shown))
			{
				return false;
			}

			foreach ($shown as $key => $value)
			{
				if (!array_key_exists($key, $posted) || !$this->isUnchangedPost($posted[$key], $value, $element))
				{
					return false;
				}
			}

			return true;
		}

		if ((!is_scalar($posted) && $posted !== null) || (!is_scalar($shown) && $shown !== null))
		{
			return false;
		}

		$posted = str_replace(array("\r\n", "\r"), "\n", (string) $posted);
		$shown = str_replace(array("\r\n", "\r"), "\n", (string) $shown);

		if ($element === null)
		{
			return $posted === $shown;
		}

		$type = $element['type'];
		$limit = empty($element['maxItems']) ? 7 : (is_numeric($element['maxItems']) ? (int) ceil($element['maxItems']) : null);

		return $this->postedBack($posted, $type) === $this->postedBack($shown, $type)
			|| ($type === 'tags' && $posted === $this->tagsPostedBack($shown, $limit));
	}

	/**
	 * @param string $value a value with its line breaks as LF
	 * @param string $type  the e_form element type that shows it
	 * @return string the value as a browser posts it back from that element: a NUL as U+FFFD, without the line breaks that open a text area, without any line break from a one-line input, and an email input's addresses without the white space around them
	 */
	private function postedBack($value, $type)
	{
		$value = str_replace("\0", "\xEF\xBF\xBD", $value);

		if ($type === 'textarea' || $type === 'bbarea')
		{
			return ltrim($value, "\n");
		}

		$value = str_replace("\n", '', $value);

		return ($type === 'email') ? preg_replace('/[\t\f ]*,[\t\f ]*/', ',', trim($value, " \t\f")) : $value;
	}

	/**
	 * @param string   $value a tag list with its line breaks as LF
	 * @param int|null $limit how many tags the widget keeps, null for no limit
	 * @return string|null the list as the tag widget posts it back: trimmed as JavaScript trims, each tag once, none named '__proto__', none past $limit; null when it is not UTF-8
	 */
	private function tagsPostedBack($value, $limit)
	{
		$space = '[\x{9}-\x{d}\x{20}\x{a0}\x{1680}\x{2000}-\x{200a}\x{2028}\x{2029}\x{202f}\x{205f}\x{3000}\x{feff}]+';
		$value = preg_replace('/^'.$space.'|'.$space.'$/u', '', $this->postedBack($value, 'tags'));

		if (!is_string($value))
		{
			return null;
		}

		$tags = array();

		foreach (($value === '') ? array() : explode(',', $value) as $tag)
		{
			if ($tag !== '__proto__' && ($limit === null || count($tags) < $limit) && !in_array($tag, $tags, true))
			{
				$tags[] = $tag;
			}
		}

		return implode(',', $tags);
	}


	/**
	 * Check if a string contains bbcode.
	 *
	 * @param $text
	 * @return bool
	 */
	public function isBBcode($text)
	{

		if (!is_string($text))
		{
			return false;
		}

		if (strpos($text, '[/') === false)
		{
			return false;
		}

		if (!preg_match_all('#\[/([^\[\]]+)]#', $text, $matches))
		{
			return false;
		}

		$bbcode = e107::getBB();

		foreach ($matches[1] as $bbword)
		{
			if ($bbcode->isRegistered($bbword))
			{
				return true;
			}

		}

		return false;


	}


	/**
	 * Check if a string is HTML
	 *
	 * @param $text
	 * @return bool
	 */
	public function isHtml($text)
	{

		if (!is_string($text))
		{
			return false;
		}

		if (strpos($text, '[html]') !== false)
		{
			return true;
		}

		if (preg_match('#(?<=<)\w+(?=[^<]*?>)#', $text))
		{
			return true;
		}

		return false;


	}


	/**
	 * Check if string is json and parse or return false.
	 *
	 * @param $text
	 * @return bool|mixed return false if not json, and json values if true.
	 */
	public function isJSON($text)
	{

		if (!is_string($text))
		{
			return false;
		}

		if (strpos($text, '{') === 0 || strpos($text, '[') === 0) // json
		{
			$dat = json_decode($text, true);

			if (json_last_error() != JSON_ERROR_NONE)
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

		return (bool) preg_match('%(?:' . $this->utf8MultibyteRegexp . ')+%s', $string);

	}


	/**
	 * Check if a file is an video or not.
	 *
	 * @param $file string
	 * @return boolean
	 */
	public function isVideo($file)
	{

		if (!is_string($file))
		{
			return false;
		}

		$ext = pathinfo($file, PATHINFO_EXTENSION);

		return $ext === 'youtube' || $ext === 'youtubepl' || $ext === 'mp4';

	}

	/**
	 * Check if a file is an image or not.
	 *
	 * @param $file string
	 * @return boolean
	 */
	public function isImage($file)
	{

		if (!is_string($file))
		{
			return false;
		}

		if (strpos($file, '{e_') === 0)
		{
			$file = $this->replaceConstants($file);
		}

		$ext = pathinfo($file, PATHINFO_EXTENSION);

		return ($ext === 'jpg' || $ext === 'png' || $ext === 'gif' || $ext === 'jpeg' || $ext === 'webp');
	}


	/**
	 * @param       $file
	 * @param array $parm
	 * @return string
	 */
	public function toAudio($file, $parm = array())
	{

		$file = $this->replaceConstants($file, 'abs');

	    $ext = pathinfo($file, PATHINFO_EXTENSION);

	    switch (strtolower($ext))
	    {

	        case 'wav':
	            $mime = 'audio/wav';
	            break;
	        case 'ogg':
	            $mime = 'audio/ogg';
	            break;
	        case 'mp3':
	        default:
	             $mime = 'audio/mpeg';
	            break;
	    }

		if(!empty($parm['mime']))
		{
			$mime = $parm['mime'];
		}

		$autoplay = !empty($parm['autoplay']) ? 'autoplay ' : '';
		$controls = !empty($parm['controls']) ? 'controls' : '';

		$text = '<audio controls style="max-width:100%" ' . $autoplay . $controls . '>';
		$text .= "\n";
		$text .= '<source src="' . $this->attributeValue($file) . '" type="' . $mime . '">';
		$text .= "\n";
		$text .= 'Your browser does not support the audio tag.';
		$text .= "\n";
		$text .= '</audio>';

		return $text;

	}


	/**
	 * Display a Video file.
	 *
	 * @param string  $file      - format: id.type eg. x123dkax.youtube
	 * @param array $parm - set to 'tag' to return an image thumbnail and 'src' to return the src url or 'video' for a small video thumbnail.
	 */
	public function toVideo($file, $parm = array())
	{

		if (empty($file))
		{
			return false;
		}

		$type = pathinfo($file, PATHINFO_EXTENSION);

		$id = str_replace('.' . $type, '', $file);

		if (($type === 'youtube' || $type === 'youtubepl') && !preg_match('#^[\w\-]+$#', $id))
		{
			return false;
		}

		$thumb = vartrue($parm['thumb']);
		$mode = varset($parm['mode'], false); // tag, url


		$pref = e107::getPref();
		$ytpref = array();
		foreach ($pref as $k => $v) // Find all Youtube Prefs.
		{
			if (strpos($k, 'youtube_') === 0)
			{
				$key = (string) substr($k, 8);
				$ytpref[$key] = $v;
			}
		}

		unset($ytpref['bbcode_responsive']); // do not include in embed code.

		if (!empty($ytpref['cc_load_policy']))
		{
			$ytpref['cc_lang_pref'] = e_LAN; // switch captions with chosen user language.
		}

		$ytqry = http_build_query($ytpref, '', '&amp;');

		$defClass = !empty($this->bootstrap) ? 'embed-responsive embed-responsive-16by9 ratio ratio-16x9' : 'video-responsive'; // levacy backup.


		if ($type === 'youtube')
		{

			//	$thumbSrc = "https://i1.ytimg.com/vi/".$id."/0.jpg";
			$thumbSrc = 'https://i1.ytimg.com/vi/' . $id . '/mqdefault.jpg';
			$video = '<iframe class="embed-responsive-item" width="560" height="315" src="//www.youtube.com/embed/' . $id . '?' . $ytqry . '" style="background-size: 100%;background-image: url(' . $thumbSrc . ');border:0px" allowfullscreen></iframe>';
			$url = 'http://youtu.be/' . $id;


			if ($mode === 'url')
			{
				return $url;
			}


			if ($thumb === 'tag')
			{
				return "<img class='img-responsive img-fluid' src='" . $thumbSrc . "' alt='Youtube Video' style='width:" . vartrue($parm['w'], '80') . "px'/>";
			}

			if ($thumb === 'email')
			{
				$thumbSrc = 'http://i1.ytimg.com/vi/' . $id . '/maxresdefault.jpg'; // 640 x 480
				$filename = 'temp/yt-thumb-' . md5($id) . '.jpg';
				$filepath = e_MEDIA . $filename;


				if (!file_exists($filepath))
				{
					e107::getFile()->getRemoteFile($thumbSrc, $filename, 'media');
				}

				return "<a href='" . $url . "'><img class='video-responsive video-thumbnail' src='{e_MEDIA}" . $filename . "' alt='" . LAN_YOUTUBE_VIDEO . "' title='" . LAN_CLICK_TO_VIEW . "' />
				<div class='video-thumbnail-caption'><small>" . LAN_CLICK_TO_VIEW . '</small></div></a>';
			}

			if ($thumb === 'src')
			{
				return $thumbSrc;
			}


			if ($thumb === 'video')
			{
				return '<div class="' . $defClass . ' video-thumbnail thumbnail">' . $video . '</div>';
			}

			return '<div class="' . $defClass . ' ' . vartrue($parm['class']) . '">' . $video . '</div>';
		}


		if ($type === 'youtubepl')
		{

			if ($thumb === 'tag')
			{
				$thumbSrc = e107::getMedia()->getThumb($id);

				if (empty($thumbSrc))
				{
					$thumbSrc = e_IMAGE_ABS . 'generic/playlist_120.png';
				}

				return "<img class='img-responsive img-fluid' src='" . $thumbSrc . "' alt='" . LAN_YOUTUBE_PLAYLIST . "' style='width:" . vartrue($parm['w'], '80') . "px'/>";

			}

			if ($thumb === 'src')
			{
				$thumb = e107::getMedia()->getThumb($id);
				if (!empty($thumb))
				{
					return $thumb;
				}

// return "https://cdn0.iconfinder.com/data/icons/internet-2-2/64/youtube_playlist_videos_vid_web_online_internet-256.png";
				return e_IMAGE_ABS . 'generic/playlist_120.png';
			}

			$video = '<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=' . $id . '" style="border:0" allowfullscreen></iframe>';

			return '<div class="' . $defClass . ' ' . vartrue($parm['class']) . '">' . $video . '</div>';
		}

		if ($type === 'mp4')
		{
			$file = $this->replaceConstants($file, 'abs');

			if ($mode === 'url')
			{
				return $file;
			}


			$width = varset($parm['w'], 320);
			$height = varset($parm['h'], 240);
			$mime = varset($parm['mime'], 'video/mp4');

			if($height === 0)
			{
				$height = 'auto';
			}

			return '
			<div class="video-responsive">
			<video width="' . $width . '" height="' . $height . '" controls>
			  <source src="' . $this->attributeValue($file) . '" type="' . $mime . '">
		
			  Your browser does not support the video tag.
			</video>
			</div>';
		}


		return false;
	}


	/**
	 * Display a Date in the browser.
	 * Includes support for 'livestamp' (http://mattbradley.github.io/livestampjs/)
	 *
	 * @param int     $datestamp - unix timestamp
	 * @param string  $format    - short | long | relative
	 * @return string|null converted date (html)
	 */
	public function toDate($datestamp = null, $format = 'short')
	{

		if (!is_numeric($datestamp))
		{
			return null;
		}

		$value = e107::getDate()->convert_date($datestamp, $format);

		$inc = ($format === 'relative') ? ' data-livestamp="' . $datestamp . '"' : '';

		return '<span' . $inc . '>' . $value . '</span>';
	}


	/**
	 * Parse new <x-bbcode> tags into bbcode output.
	 *
	 * @param bool $retainTags : when you want to replace html and retain the <bbcode> tags wrapping it.
	 * @return string html
	 */
	public function parseBBTags($text, $retainTags = false)
	{

		$stext = str_replace('&quot;', '"', $text);

		$bbcodes = $this->getTags($stext, 'x-bbcode');

		foreach ($bbcodes as $v)
		{
			foreach ($v as $val)
			{
				$tag = base64_decode($val['alt']);
				$repl = ($retainTags == true) ? '$1' . $tag . '$2' : $tag;
				//	$text = preg_replace('/(<x-bbcode[^>]*>).*(<\/x-bbcode>)/i',$repl, $text);
				$text = preg_replace('/(<x-bbcode alt=(?:&quot;|")' . $val['alt'] . '(?:&quot;|")>).*(<\/x-bbcode>)/i', $repl, $text);

			}
		}

		return $text;
	}


	/**
	 * Filters/Validates using the PHP5 filter_var() method.
	 *
	 * @param string|array $text
	 * @param string       $type str|int|email|url|w|wds|file
	 *
	 *                     If the type is "str" (default), HTML tags are stripped, and quotation marks are escaped for
	 *                     HTML with the intention of making the string safe to use in both concatenated SQL queries and
	 *                     HTML code.
	 *
	 *                     Despite the intention, strings returned by this function should still be specified as values
	 *                     in SQL prepared statements or surrounded by {@see mysqli_real_escape_string()} if the string
	 *                     is to be written to the database.
	 * @return string|boolean| array
	 */
	public function filter($text, $type = 'str', $validate = false)
	{

		if (empty($text))
		{
			return $text;
		}

		$regex = array(
			'w'       => '/[^\w]/',
			'd'       => '/[^\d]/',
			'wd'      => '/[^\w]/',
			'wds'     => '/[^\w ]/',
			'file'    => '/[^\w_\.-]/',
			'version' => '/[^\d_\.]/',
		);

		$ret = '';

		switch ($type)
		{
			case 'w':
			case 'd':
			case 'wd':
			case 'wds':
			case 'version':

				if ($validate === true)
				{
					trigger_error("Unsupported type '" . $type . "' for validation used in e107::getParser()->filter().", E_USER_WARNING);
				}
				else
				{
					$reg = $regex[$type];
					$ret = preg_replace($reg, '', $text);
				}
				break;

			case 'file':

				if ($validate === true)
				{
					trigger_error("Unsupported type '" . $type . "' used in e107::getParser()->filter().", E_USER_WARNING);
				}
				else
				{
					$reg = $regex['file'];
					$ret = preg_replace('/[^\w_\.-]/', '-', $text);
				}
				break;

			default:

				if ($validate === false)
				{
					$filterTypes = array(
						'int'   => FILTER_SANITIZE_NUMBER_INT,
						'str'   => function($input)
						{
							return htmlspecialchars(strip_tags($input), ENT_QUOTES);
						},
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

				if (!isset($filterTypes[$type]))
				{
					trigger_error("Unsupported type '" . $type . "' used in e107::getParser()->filter().", E_USER_WARNING);
				}

				$filter = $filterTypes[$type];
				$filter = function($element) use ($filter)
				{
					$element = (string) $element;

					return is_callable($filter) ? $filter($element) : filter_var($element, $filter);
				};
				if (is_array($text))
				{
					$ret = filter_var($text, FILTER_CALLBACK, ['options' => $filter]);
				}
				else
				{
					$ret = $filter($text);
				}

		}

		return $ret;
	}


	/**
	 * @return void
	 */
	private function grantScriptAccess()
	{

		if (!in_array('script', $this->allowedTags))
		{
			$this->allowedTags = array_merge($this->allowedTags, $this->scriptTags);
		}

		foreach ($this->allowedAttributes as $tag => $att)
		{
			foreach ($this->scriptAttributes as $new)
			{
				if (in_array($new, $this->allowedAttributes[$tag]))
				{
					continue;
				}

				$this->allowedAttributes[$tag][] = $new;
			}
		}

	}


	/**
	 * Clean HTML as cleanHtml() does for a user without script access, whatever the current user's.
	 *
	 * @param string $html
	 * @return string
	 */
	private function cleanHtmlWithoutScript($html)
	{
		$access = $this->scriptAccess;
		$tags = $this->allowedTags;
		$attributes = $this->allowedAttributes;
		$scriptTags = $this->scriptTags;
		$scriptAttributes = $this->scriptAttributes;

		$this->scriptAccess = e_UC_NOBODY;
		$this->allowedTags = array_values(array_diff($tags, $scriptTags));

		foreach ($attributes as $tag => $list)
		{
			$this->allowedAttributes[$tag] = array_values(array_diff($list, $scriptAttributes));
		}

		$this->scriptTags = array();
		$this->scriptAttributes = array();

		try
		{
			return $this->cleanHtml($html);
		}
		finally
		{
			$this->scriptAccess = $access;
			$this->allowedTags = $tags;
			$this->allowedAttributes = $attributes;
			$this->scriptTags = $scriptTags;
			$this->scriptAttributes = $scriptAttributes;
		}
	}

	/**
	 * Process and clean HTML from user input.
	 * TODO Html5 tag support.
	 *
	 * @param string  $html raw HTML
	 * @param boolean $checkPref
	 * @return string
	 */
	public function cleanHtml($html = '', $checkPref = true)
	{

		if (empty($html))
		{
			return '';
		}

		if ($this->isHtml($html) === false)
		{
			$html = str_replace(array('<', '>'), array('&lt;', '&gt;'), $html);
		}

		// prevent replacement of &nbsp; with spaces.
		// Workaround for https://bugs.php.net/bug.php?id=76285
		//  Part 1 of 2
		// clean out windows line-breaks.
		$html = str_replace(array('&nbsp;', "\r", "\n", '{', '}'), array('__E_PARSER_CLEAN_HTML_NON_BREAKING_SPACE__', '', '__E_PARSER_CLEAN_HTML_LINE_BREAK__', '__E_PARSER_CLEAN_HTML_CURLY_OPEN__', '__E_PARSER_CLEAN_HTML_CURLY_CLOSED__'), $html);
		$html = str_replace('<?', '&lt;?', $html);


		if (strpos($html, '<body') === false) // HTML Fragment
		{
			$html = '<body>' . $html . '</body>';
		}


		if ($this->scriptAccess === false)
		{
			$this->scriptAccess = e107::getConfig()->get('post_script', e_UC_NOBODY); // Pref to Allow <script> tags11;
		}

		$scriptAccess = check_class($this->scriptAccess);

		if ($scriptAccess)
		{
			$this->grantScriptAccess();
		}
		elseif(!$this->storingWithoutScript && deftrue('ADMIN') && (strpos($html, '</script>') !== false))
		{
			$lan1 = defset('LAN_NO_SCRIPT_ACCESS', "You don't have permission to use [script] tags.");
			$lan2 = defset('', "If you believe this is an error, please ask the main administrator to grant you script access via [b]Preferences > Content Filters[/b]");
			$srch = ['[', ']'];
			$repl = ['<b>&lt;', '&gt;</b>'];

			e107::getMessage()->addWarning(str_replace($srch,$repl,$lan1));
			e107::getMessage()->addWarning(e107::getParser()->toHTML($lan2,true));
		}

		// Set it up for processing.

		libxml_use_internal_errors(true);
		$html = mb_encode_numericentity($html, [0x80, 0xffff, 0, 0xffff], 'UTF-8');

		//	$fragment = $doc->createDocumentFragment();
		//	$fragment->appendXML($html);
		//	$doc->appendChild($fragment);
		//	$doc->encoding = 'utf-8';
		$doc = $this->domObj;
		$opts = defined('LIBXML_HTML_NOIMPLIED') ? LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD : 0;
		$this->loadHtmlKeepingBareLessThan($doc, $html, $opts);

		$this->nodesToConvert = array(); // required.
		$this->nodesToDelete = array(); // required.
		$this->nodesToDisableSC = array(); // required.
		$this->pathList = array(); // required.
		$this->removedList = array();

		$tmp = $doc->getElementsByTagName('*');

		/** @var DOMElement $node */
		foreach ($tmp as $node)
		{
			$path = $node->getNodePath();

			//	echo "<br />Path = ".$path;
			//   $tag = strval(basename($path));


			if (strpos($path, '/code') !== false || strpos($path, '/pre') !== false) //  treat as html.
			{
				$this->pathList[] = $path;
				//     $this->nodesToConvert[] =  $node->parentNode; // $node;
				$this->nodesToDisableSC[] = $node;
				continue;
			}


			$tag = preg_replace('/([a-z0-9\[\]\/]*)?\/([\w\-]*)(\[(\d)*\])?$/i', '$2', $path);
			if (!in_array($tag, $this->allowedTags))
			{

				$this->removedList['tags'][] = $tag;
				$this->nodesToDelete[] = $node;
				continue;
			}

			$removeAttributes = array();
			foreach ($node->attributes as $attr)
			{
				$name = $attr->nodeName;
				$value = $attr->nodeValue;

				$allow = isset($this->allowedAttributes[$tag]) ? $this->allowedAttributes[$tag] : $this->allowedAttributes['default'];

				if (!in_array($name, $allow))
				{

					if ($scriptAccess && strpos($name, 'data-') === 0)
					{
						continue;
					}

					$removeAttributes[] = $name;
					//$node->removeAttribute($name);
					$this->removedList['attributes'][] = $name . ' from <' . $tag . '>';
					continue;
				}

				if ($this->invalidAttributeValue($value)) // Check value against blacklisted values.
				{
					//$node->removeAttribute($name);
					$node->setAttribute($name, '#---sanitized---#');
					$this->removedList['sanitized'][] = $tag . '[' . $name . ']';
				}
				else
				{
					$_value = $this->secureAttributeValue($name, $value);

					$node->setAttribute($name, $_value);
					if ($_value !== $value)
					{
						$this->removedList['sanitized'][] = $tag . '[' . $name . '] converted "' . $value . '" -> "' . $_value . '"';
					}
				}
			}

			// required - removing attributes in a loop breaks the loop
			if (!empty($removeAttributes))
			{
				foreach ($removeAttributes as $name)
				{
					$node->removeAttribute($name);
				}
			}


		}

		// Remove some stuff.
		foreach ($this->nodesToDelete as $node)
		{
			$node->parentNode->removeChild($node);
		}

		// Disable Shortcodes in pre/code

		foreach ($this->nodesToDisableSC as $key => $node)
		{
			$value = $node->C14N();

			if (empty($value))
			{
				continue;
			}

			$value = str_replace('&#xD;', "\r", $value);

			if ($node->nodeName === 'pre')
			{
				$value = preg_replace('/^<pre[^>]*>/', '', $value);
				$value = str_replace(array('</pre>', '<br></br>'), array('', '__E_PARSER_CLEAN_HTML_LINE_BREAK__'), $value);
			}
			elseif ($node->nodeName === 'code')
			{
				$value = preg_replace('/^<code[^>]*>/', '', $value);
				$value = str_replace(array('</code>', '<br></br>'), array('', '__E_PARSER_CLEAN_HTML_LINE_BREAK__'), $value);
			}

			// temporarily change {e_XXX} to {{{e_XXX}}}
			$value = str_replace(array('__E_PARSER_CLEAN_HTML_CURLY_OPEN__', '__E_PARSER_CLEAN_HTML_CURLY_CLOSED__'), array('{{{', '}}}'), $value); // temporarily change {e_XXX} to {{{e_XXX}}}


			$newNode = $doc->createElement($node->nodeName);
			$newNode->nodeValue = $value;

			if ($class = $node->getAttribute('class'))
			{
				$newNode->setAttribute('class', $class);
			}

			if ($style = $node->getAttribute('style'))
			{
				$newNode->setAttribute('style', $style);
			}

			$node->parentNode->replaceChild($newNode, $node);
		}


		// Normalize HTML5 void elements so serialization is identical
		// across libxml versions. libxml < 2.13 treated elements like
		// <source> as non-void and swallowed following text as their
		// content; the HTML5-spec-compliant output keeps such text as
		// a sibling and omits any closing tag.
		$this->normalizeVoidElements($doc);

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

		$cleaned = $this->stripVoidClosingTags($cleaned);

		$cleaned = str_replace(
			array("\n", '__E_PARSER_CLEAN_HTML_LINE_BREAK__', '__E_PARSER_CLEAN_HTML_NON_BREAKING_SPACE__', '{{{', '}}}', '__E_PARSER_CLEAN_HTML_CURLY_OPEN__', '__E_PARSER_CLEAN_HTML_CURLY_CLOSED__', '<body>', '</body>', '<html>', '</html>'),
			array('', "\n", '&nbsp;', '&#123;', '&#125;', '{', '}', '', '', '', ''),
			$cleaned
		); // filter out tags.

		return trim($cleaned);
	}

	/**
	 * Load $html into $doc so that every libxml2 version keeps a "<" that cannot open markup as text.
	 *
	 * @param DOMDocument $doc
	 * @param string      $html
	 * @param int         $options
	 * @return void
	 */
	private function loadHtmlKeepingBareLessThan($doc, $html, $options)
	{
		$reference = '&#0000060;';
		$escaped = 0;

		if (strpos(preg_replace('/[\x01-\x08\x0B\x0C\x0E-\x1F]/', '', $html), $reference) === false)
		{
			$html = preg_replace('#<(?![A-Za-z/!?])#', $reference, $html, -1, $escaped);
		}

		$doc->loadHTML($html, $options);

		if ($escaped === 0)
		{
			return;
		}

		$xpath = new DOMXPath($doc);
		$holds = '[contains(., "' . $reference . '")]';

		foreach ($xpath->query('//text()' . $holds . ' | //comment()' . $holds . ' | //processing-instruction()' . $holds) as $node)
		{
			if ($node->nodeType !== XML_TEXT_NODE)
			{
				$node->nodeValue = str_replace($reference, '<', $node->nodeValue);
			}
		}
	}

	/**
	 * HTML5 void elements that may not have a closing tag.
	 *
	 * @var string[]
	 */
	private static $voidTags = array(
		'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
		'link', 'meta', 'source', 'track', 'wbr',
	);

	/**
	 * Move children out of HTML5 void elements and into sibling positions.
	 *
	 * Older libxml2 (< 2.13) parses elements like <source> as non-void and
	 * sucks up following content as the element's child nodes. Newer libxml2
	 * correctly treats them as void. Running this pass before serialization
	 * normalizes the DOM so saveHTML() output is spec-compliant on every
	 * libxml version.
	 *
	 * @param DOMDocument $doc
	 * @return void
	 */
	private function normalizeVoidElements($doc)
	{
		$voidTags = self::$voidTags;

		foreach ($voidTags as $tagName)
		{
			$nodes = $doc->getElementsByTagName($tagName);
			$toFix = array();
			foreach ($nodes as $node)
			{
				if ($node->hasChildNodes())
				{
					$toFix[] = $node;
				}
			}

			foreach ($toFix as $node)
			{
				$parent = $node->parentNode;
				$after = $node->nextSibling;
				while ($node->hasChildNodes())
				{
					$child = $node->firstChild;
					$node->removeChild($child);
					if ($after === null)
					{
						$parent->appendChild($child);
					}
					else
					{
						$parent->insertBefore($child, $after);
					}
				}
			}
		}
	}

	/**
	 * Strip empty closing tags for HTML5 void elements from a serialized
	 * HTML string. libxml2 < 2.13 emits a closing `</source>` etc. for any
	 * element it does not recognize as void; once `normalizeVoidElements()`
	 * has moved their children out, the closing tag is always empty and
	 * safe to remove.
	 *
	 * @param string $html
	 * @return string
	 */
	private function stripVoidClosingTags($html)
	{
		$pattern = '#</(?:' . implode('|', self::$voidTags) . ')>#i';
		return preg_replace($pattern, '', $html);
	}

	/**
	 * @param $attribute
	 * @param $value
	 * @return array|mixed|string|string[]
	 */
	public function secureAttributeValue($attribute, $value)
	{

		$search = isset($this->replaceAttrValues[$attribute]) ? $this->replaceAttrValues[$attribute] : $this->replaceAttrValues['default'];
		if (!empty($search))
		{
			$value = str_replace($search, '', $value);
		}

		return $value;
	}


	/**
	 * Check for Invalid Attribute Values
	 *
	 * @param $value string
	 * @return bool true/false
	 */
	public function invalidAttributeValue($value)
	{
		$probe = eShims::strtolower($value);

		foreach ($this->badAttrValues as $v) // global list because a bad value is bad regardless of the attribute it's in. ;-)
		{
			if (preg_match('/' . $v . '/i', $probe) == true)
			{
				$this->removedList['blacklist'][] = "Match found for '{$v}' in '{$value}'";

				return true;
			}

		}

		return false;
	}

	/**
	 * @param $modifiers
	 * @return array
	 */
	private function getModifiers($modifiers)
	{
		$opts = $this->e_optDefault;

		if (strpos($modifiers, 'defaults_off') !== false)
		{
			$opts = $this->e_SuperMods['NODEFAULT'];
		}
		// Now process any modifiers that are specified
		$aMods = explode(',', $modifiers);

		// If there's a supermodifier, it must be first, and in uppercase
		$psm = trim($aMods[0]);
		if (isset($this->e_SuperMods[$psm]))
		{
			// Supermodifier found - override default values where necessary
			$opts = array_merge($opts, $this->e_SuperMods[$psm]);
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

		// Turn off a few things if not enabled in options
		if (empty($this->pref['smiley_activate']))
		{
			$opts['emotes'] = false;
		}

		if (empty($this->pref['make_clickable']))
		{
			$opts['link_click'] = false;
		}

		if (empty($this->pref['link_replace']))
		{
			$opts['link_replace'] = false;
		}


		return $opts;
	}

	/**
	 * Apply the entity table {@see e_parse::$search} toHTML() runs over body text, or its reverse for attribute values.
	 *
	 * @param string $text
	 * @param bool   $forAttributeValue
	 * @return string
	 */
	private function restoreEntities($text, $forAttributeValue = false)
	{
		return $forAttributeValue
			? str_replace($this->replace, $this->search, $text)
			: strtr($text, $this->bodyEntities());
	}

	/**
	 * Apply {@see e_parse::restoreEntities()} to body text only, skipping <script> and <style> blocks and [code] blocks, whose `<` is written as `&lt;` unless it opens a [hide] block's marker.
	 *
	 * @param string $text
	 * @param bool   $forAttributeValue
	 * @return string
	 */
	private function restoreEntitiesOutsideCode($text, $forAttributeValue)
	{
		$parts = preg_split(self::$codeBlockPattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$result = '';

		foreach ($parts as $index => $part)
		{
			if ($index % 3 === 1)
			{
				$result .= preg_replace('#<(?!e_hide_\d+_\d+>)#', '&lt;', $part);
			}
			elseif ($index % 3 === 0)
			{
				foreach ($this->splitScriptStyle($part) as $block)
				{
					list($type, $chunk) = $block;
					$result .= ($type === 'text') ? $this->restoreEntities($chunk, $forAttributeValue) : $chunk;
				}
			}
		}

		return $result;
	}

	/**
	 * Encode the quotes and angle brackets, typed or percent-encoded, in the parameters of each shortcode named in text that is not a template, so that no shortcode can print them as markup.
	 * An opening brace, typed or percent-encoded, and a percent-encoded closing brace are encoded the same way.
	 *
	 * @param string $text
	 * @return string
	 */
	private function disarmShortcodeParameters($text)
	{
		$encoded = array('%27' => '%26%23x27%3B', '%22' => '%26%23x22%3B', '%3C' => '%26lt%3B', '%3E' => '%26%23x3E%3B', '%7B' => '%26%23x7B%3B', '%7D' => '%26%23x7D%3B');

		return preg_replace_callback('#\{([A-Z][^\x02]*?\S)\}#', function ($match) use ($encoded)
		{
			if (preg_match('/^([A-Z_]*\d?):(.*)$/s', $match[1], $form))
			{
				$typed = array("'" => $encoded['%27'], '"' => $encoded['%22'], '<' => $encoded['%3C'], '>' => $encoded['%3E'], '{' => $encoded['%7B']);
				$separator = ':';
			}
			elseif (strpos($match[1], '=') !== false)
			{
				$form = array(0, strstr($match[1], '=', true), (string) substr(strstr($match[1], '='), 1));
				$typed = array("'" => '&#x27;', '"' => '&#x22;', '<' => '&lt;', '>' => '&#x3E;', '{' => '&#x7B;');
				$separator = '=';
			}
			else
			{
				return $match[0];
			}

			$parameters = str_ireplace(array_keys($encoded), array_values($encoded), strtr($form[2], $typed));

			return '{'.$form[1].$separator.$parameters.'}';
		}, $text);
	}

	/**
	 * @return array the entity table toHTML() applies to body text, built once
	 */
	private function bodyEntities()
	{
		if ($this->bodyEntities === null)
		{
			$this->bodyEntities = array('&amp;quot;' => '&quot;', '&amp;#039;' => '&#039;') + array_combine($this->search, $this->replace);
		}

		return $this->bodyEntities;
	}

	/**
	 * @param array       $opts
	 * @param string      $text
	 * @param bool        $convertNL
	 * @param bool|string $parseBB
	 * @param             $modifiers
	 * @param int         $postID
	 * @param bool        $restoreEntities restore entity-encoded quotes; false when the caller restored them before rendering bbcode
	 * @return array|bool|mixed|string|null
	 */
	private function processModifiers($opts, $text, $convertNL, $parseBB, $modifiers, $postID, $restoreEntities = true)
	{

		// Convert emoticons to graphical icons, if enabled
		if ($opts['emotes'])
		{
			$text = e107::getEmote()->filterEmotes($text);
		}


		// Reduce newlines in all forms to a single newline character (finds '\n', '\r\n', '\n\r')
		if (!$opts['nobreak'])
		{
			if ($convertNL && ($this->preformatted($text) === false)) // eg. html or markdown
			{
				// We may need to convert to <br /> later
				$text = preg_replace("#[\r]*\n[\r]*#", E_NL, $text);
			}
			else
			{
				// Not doing any more - its HTML or Markdown so keep it as is.
				$text = preg_replace("#[\r]*\n[\r]*#", "\n", $text);
			}
		}


		//	Entity conversion
		// Restore entity form of quotes and such to single characters, except for text destined for tag attributes or JS.
		if ($restoreEntities)
		{
			$text = $this->restoreEntities($text, $opts['value']);
		}


		//   BBCode processing (other than the four already done, which shouldn't appear at all in the text)
		if ($parseBB !== false)
		{
			$bbcodes = function ($text, $shortcodes = false) use ($parseBB, $modifiers, $postID)
			{
				if ($parseBB === true)
				{
					// 'Normal' or 'legacy' processing
					if ($modifiers === 'WYSIWYG')
					{
						return $this->parseBBCodesBeforeShortcodes($text, $postID, $shortcodes, 'wysiwyg');
					}

					return $this->parseBBCodesBeforeShortcodes($text, $postID, $shortcodes);
				}

				if ($parseBB === 'STRIP') // Need to strip all BBCodes
				{
					return $this->parseBBCodesBeforeShortcodes($text, $postID, $shortcodes, 'default', true);
				}

				// Need to strip just some BBCodes
				return $this->parseBBCodesBeforeShortcodes($text, $postID, $shortcodes, 'default', $parseBB);
			};

			$text = $bbcodes($this->expandInsideTags($text, '[', $bbcodes), $opts['parse_sc']);
		}


		if ($opts['link_click'])
		{

			if ($opts['link_replace'] && defset('ADMIN_AREA') !== true)
			{

				$link_text = $this->pref['link_text'];
				$email_text = ($this->pref['email_text']) ? $this->replaceConstants($this->pref['email_text']) : LAN_EMAIL_SUBS;

				$text = $this->makeClickable($text, 'url', array('sub' => $link_text, 'ext' => $this->pref['links_new_window']));
				$text = $this->makeClickable($text, 'email', array('sub' => $email_text));
			}
			else
			{

				$text = $this->makeClickable($text, 'url', array('ext' => true));
				$text = $this->makeClickable($text, 'email');

			}
		}


		// replace all {e_XXX} constants with their e107 value. modifier determines relative/absolute conversion
		// (Moved to after bbcode processing by Cameron)
		if ($opts['constants'])
		{
			$text = $this->replaceConstants($text, $opts['constants']);        // Now decodes text values
		}

		// profanity filter
		if ($this->pref['profanity_filter'])
		{
			$text = e107::getProfanity()->filterProfanities($text);
		}

		// Optional short-code conversion
		if ($opts['parse_sc'])
		{
			$text = $this->parseTemplate($this->disarmShortcodeParameters($this->expandInsideTags($text, '{', function ($value)
			{
				return $this->parseTemplate($this->disarmShortcodeParameters($value), true);
			})), true);
		}

		/**
		 * / @deprecated
		 */
		if ($opts['hook']) //Run any hooked in parsers
		{

			if (!empty($this->pref['tohtml_hook']))
			{
				//		trigger_error('<b>tohtml_hook is deprecated.</b> Use e_parse.php instead.', E_USER_DEPRECATED); // NO LAN

				//Process the older tohtml_hook pref (deprecated)
				foreach (explode(',', $this->pref['tohtml_hook']) as $hook)
				{
					if (!is_object($this->e_hook[$hook]) && is_readable(e_PLUGIN . $hook . '/' . $hook . '.php'))
					{
						require_once(e_PLUGIN . $hook . '/' . $hook . '.php');
						$hook_class = 'e_' . $hook;
						$this->e_hook[$hook] = new $hook_class;
					}

					if (is_object($this->e_hook[$hook])) // precaution for old plugins.
					{
						$text = $this->e_hook[$hook]->$hook($text, $opts['context']);
					}
				}
			}

			/**
			 * / @deprecated
			 */
			if (isset($this->pref['e_tohtml_list']) && is_array($this->pref['e_tohtml_list']))
			{

				foreach ($this->pref['e_tohtml_list'] as $hook)
				{
					if (empty($hook))
					{
						continue;
					}

					if (empty($this->e_hook[$hook]) && is_readable(e_PLUGIN . $hook . '/e_tohtml.php') /*&& !is_object($this->e_hook[$hook])*/)
					{
						require_once(e_PLUGIN . $hook . '/e_tohtml.php');

						$hook_class = 'e_tohtml_' . $hook;
						if (class_exists($hook_class))
						{
							$this->e_hook[$hook] = new $hook_class;
						}
					}

					if (isset($this->e_hook[$hook]) && is_object($this->e_hook[$hook]))
					{
						/** @var e_tohtml_linkwords $deprecatedHook */
						$deprecatedHook = $this->e_hook[$hook];
						$text = $deprecatedHook->to_html($text, $opts['context']);
					}
				}
			}

			/**
			 * / Preferred 'hook'
			 */
			if (!empty($this->pref['e_parse_list']))
			{
				foreach ($this->pref['e_parse_list'] as $plugin)
				{
					$hookObj = e107::getAddon($plugin, 'e_parse');
					if ($tmp = e107::callMethod($hookObj, 'toHTML', $text, $opts['context']))
					{
						$text = $tmp;
					}

				}

			}


		}


		// 	Word wrap
		if (!empty($this->pref['main_wordwrap']) && !$opts['nobreak'])
		{
			$text = $this->textclean($text, $this->pref['main_wordwrap']);
		}


		//	Search highlighting
		if ($opts['emotes'] && $this->checkHighlighting())            // Why??
		{
			$text = $this->e_highlight($text, $this->e_query);
		}


		if ($convertNL == true)
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

			$text = str_replace(E_NL, $nl_replace, $text);

		}

		return $text;
	}


}


