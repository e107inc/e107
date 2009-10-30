<?php
/*
* e107 website system
*
* Copyright (C) 2001-2008 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Text processing and parsing functions
*
* $Source: /cvs_backup/e107_0.8/e107_handlers/e_parse_class.php,v $
* $Revision: 1.69 $
* $Date: 2009-10-30 09:13:31 $
* $Author: e107coders $
*
*/
if (!defined('e107_INIT')) { exit; }

define('E_UTF8_PACK',e_HANDLER.'utf8/');		// Directory for the hard-coded utf-8 handling routines

define ("E_NL", chr(2));
class e_parse
{
	var	$isutf8 = FALSE;	// Flag for global use indicates whether utf-8 character set
	var	$utfAction;			// Determine how to handle utf-8.   0 = 'do nothing'    1 = 'use mb_string'    2 = emulation
	// Shortcode processor - see __get()
	//var $e_sc;				
	var $e_bb;				// BBCode processor
	var $e_pf;				// Profanity filter
	var $e_emote;			// Emote filter
	var $e_hook;			// 'Hooked' parsers (array)
	var $search = array('&#39;', '&#039;', '&quot;', 'onerror', '&gt;', '&amp;#039;', '&amp;quot;', ' & ');
	var $replace = array("'", "'", '"', 'one<i></i>rror', '>', "'", '"', ' &amp; ');
	var $e_highlighting;	// Set to TRUE or FALSE once it has been calculated
	var $e_query;			// Highlight query

		// Set up the defaults
	var $e_optDefault = array(
		'context' 		=> 'OLDDEFAULT',	// default context: reflects legacy settings (many items enabled)
		'fromadmin' 	=> FALSE,
		'emotes'		=> TRUE,			// Enable emote display
		'defs' 			=> FALSE,			// Convert defines(constants) within text.
		'constants' 	=> FALSE,			// replace all {e_XXX} constants with their e107 value - 'rel' or 'abs'
		'hook'			=> TRUE,			// Enable hooked parsers
		'scripts'		=> TRUE,			// Allow scripts through (new for 0.8)
		'link_click'	=> TRUE,			// Make links clickable
		'link_replace'	=> TRUE,			// Substitute on clickable links (only if link_click == TRUE)
		'parse_sc' 		=> FALSE,			// Parse shortcodes - TRUE enables parsing
		'no_tags' 		=> FALSE,			// remove HTML tags.
		'value'			=> FALSE,			// Restore entity form of quotes and such to single characters - TRUE disables

		'nobreak' 		=> FALSE,			// Line break compression - TRUE removes newline characters
		'retain_nl' 	=> FALSE			// Retain newlines - wraps to \n instead of <br /> if TRUE (for non-HTML email text etc)
		);

		// Super modifiers override default option values
	var	$e_SuperMods = array(
				'TITLE' =>				//text is part of a title (e.g. news title)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'emotes'=>FALSE, 'defs'=>TRUE, 'parse_sc'=>TRUE
						),

				'USER_TITLE' =>				//text is user-entered (i.e. untrusted) and part of a title (e.g. forum title)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'scripts' => FALSE, 'emotes'=>FALSE, 'hook'=>FALSE
						),

				'E_TITLE' =>					// text is 'body' of email or similar - being sent 'off-site' so don't rely on server availability
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'defs'=>TRUE, 'parse_sc'=>TRUE, 'emotes'=>FALSE, 'scripts' => FALSE, 'link_click' => FALSE
						),

				'SUMMARY' =>			// text is part of the summary of a longer item (e.g. content summary)
					array(
						'defs'=>TRUE, 'constants'=>'rel', 'parse_sc'=>TRUE
						),

				'DESCRIPTION' =>	// text is the description of an item (e.g. download, link)
					array(
						'defs'=>TRUE, 'constants'=>'rel', 'parse_sc'=>TRUE
						),

				'BODY' =>					// text is 'body' or 'bulk' text (e.g. custom page body, content body)
					array(
						'defs'=>TRUE, 'constants'=>'rel', 'parse_sc'=>TRUE
						),

				'USER_BODY' =>					// text is user-entered (i.e. untrusted)'body' or 'bulk' text (e.g. custom page body, content body)
					array(
						'constants'=>TRUE, 'scripts' => FALSE
						),

				'E_BODY' =>					// text is 'body' of email or similar - being sent 'off-site' so don't rely on server availability
					array(
						'defs'=>TRUE, 'constants'=>'abs', 'parse_sc'=>TRUE, 'emotes'=>FALSE, 'scripts' => FALSE, 'link_click' => FALSE
						),

				'LINKTEXT' =>			// text is the 'content' of a link (A tag, etc)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'emotes_on'=>FALSE, 'hook'=>FALSE, 'defs'=>TRUE, 'parse_sc'=>TRUE
						),

				'RAWTEXT' =>			// text is used (for admin edit) without fancy conversions or html.
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'link_click' => FALSE, 'emotes'=>FALSE, 'hook'=>FALSE, 'no_tags'=>TRUE
						)
		);

	// Individual modifiers change the current context
	var $e_Modifiers = array(
				'emotes_off'	=> array('emotes_on' => FALSE),
				'emotes_on'		=> array('emotes_on' => TRUE),
				'no_hook'		=> array('hook' => FALSE),
				'do_hook'		=> array('hook' => TRUE),
				'scripts_off'	=> array('scripts' => FALSE),			// New for 0.8
				'scripts_on'	=> array('scripts' => TRUE),			// New for 0.8
				'no_make_clickable' => array('link_click' => FALSE),
				'make_clickable' => array('link_click' => TRUE),
				'no_replace' 	=> array('link_replace' => FALSE),
				'replace' 		=> array('link_replace' => TRUE),		// Replace text of clickable links (only if make_clickable option set)
				'consts_off'	=> array('constants' => FALSE),			// No path replacement
				'consts_rel'	=> array('constants' => 'rel'),			// Relative path replacement
				'consts_abs'	=> array('constants' => 'abs'),			// Absolute path replacement
				'scparse_off'	=> array('parse_sc'	=> FALSE),			// No shortcode parsing
				'scparse_on'	=> array('parse_sc'	=> TRUE),
				'no_tags' 		=> array('no_tags' 	=> TRUE),			// Strip tags
				'do_tags' 		=> array('no_tags' 	=> FALSE),			// Leave tags
				'fromadmin'		=> array('fromadmin' => TRUE),
				'notadmin'		=> array('fromadmin' => FALSE),
				'er_off'		=> array('value' => FALSE),				// entity replacement
				'er_on'			=> array('value' => TRUE),
				'defs_off'		=> array('defs' => FALSE),				// Decode constant if exists
				'defs_on'		=> array('defs' => TRUE),

				'dobreak'		=> array('nobreak' => TRUE),
				'nobreak'		=> array('nobreak' => FALSE),
				'lb_nl'			=> array('retain_nl' => TRUE),				// Line break using \n
				'lb_br'			=> array('retain_nl' => FALSE),				// Line break using <br />

				// Legacy option names below here - discontinue later
				'retain_nl'		=> array('retain_nl' => TRUE),
				'defs'			=> array('defs' => TRUE),
				'parse_sc'		=> array('parse_sc'	=> TRUE),
				'constants'		=> array('constants' => 'rel'),
				'value'			=> array('value' => TRUE)
		);


	function e_parse()
	{
		// Preprocess the supermods to be useful default arrays with all values
		foreach ($this->e_SuperMods as $key=>$val)
		{
			$this->e_SuperMods[$key] = array_merge($this->e_optDefault,$this->e_SuperMods[$key]); // precalculate super defaults
			$this->e_SuperMods[$key]['context']=$key;
		}
	}



	// This has to be a separate function - can't be called until CHARSET known
	//@TODO deprecated
	function initCharset()
	{
		// Start by working out what, if anything, we do about utf-8 handling.
		$this->utfAction = 0;			// 'Do nothing' is the simple option
		if (strtolower(CHARSET) == 'utf-8')
		{
			$this->isutf8 = TRUE;
			if (version_compare(PHP_VERSION, '6.0.0') < 1)
			{	// Need to do something here
				if(extension_loaded('mbstring'))
				{
					$temp = ini_get('mbstring.func_overload');		// Check for function overloading
					if (($temp & MB_OVERLOAD_STRING) == 0)			// Just check the string functions - will be non-zero if overloaded
					{
						$this->utfAction = 1;			// Can use the mb_string routines
					}
					mb_internal_encoding('UTF-8');		// Set the default encoding, so we don't have to specify every time
				}
				else
				{
					$this->utfAction = 2;			// Must use emulation - will probably be slow!
					require(E_UTF8_PACK.'utils/unicode.php');
					require(E_UTF8_PACK.'native/core.php');			// Always load the core routines - bound to need some of them!
				}
			}
		}
	}


	function uStrLen($str)
	{
		switch ($this->utfAction)
		{
			case 0 : return strlen($str);
			case 1 : return mb_strlen($str);
		}
		// Default case shouldn't happen often
		return strlen(utf8_decode($str));		// Save a call - invoke the function directly
	}


	function uStrToLower($str)
	{
		switch ($this->utfAction)
		{
			case 0 : return strtolower($str);
			case 1 : return mb_strtolower($str);
		}
		// Default case shouldn't happen often

		return utf8_strtolower($str);
	}


	function uStrToUpper($str)
	{
		switch ($this->utfAction)
		{
			case 0 : return strtoupper($str);
			case 1 : return mb_strtoupper($str);
		}
		// Default case shouldn't happen often

		return utf8_strtoupper($str);
	}


	function uStrPos($haystack, $needle, $offset = 0)
	{
		switch ($this->utfAction)
		{
			case 0 : return strpos($haystack, $needle, $offset);
			case 1 : return mb_strpos($haystack, $needle, $offset);
		}
		utf8_strpos($haystack, $needle, $offset);
	}


	function uStrrPos($haystack, $needle, $offset = 0)
	{
		switch ($this->utfAction)
		{
			case 0 : return strrpos($haystack, $needle, $offset);
			case 1 : return mb_strrpos($haystack, $needle, $offset);
		}
		utf8_strrpos($haystack, $needle, $offset);
	}


	// May be subtle differences in return values dependent on which routine is used.
	//   native substr() routine can return FALSE. mb_substr and utf8_substr just return an empty string.
	function uSubStr($str, $start, $length = NULL)
	{
		switch ($this->utfAction)
		{
			case 0 : return strrpos($str, $start, $length);
			case 1 :
				if (is_null($length))
				{
					return mb_strrpos($haystack, $needle);
				}
				else
				{
					return mb_strrpos($haystack, $needle, $offset);
				}
		}
		utf8_substr($str, $start, $length);
	}



	// Initialise the shortcode handler - has to be done when $prefs valid, so can't be done in constructor ATM
	/*function sch_load($noCore=false)
	{
		if (!is_object($this->e_sc))
		{
			require_once(e_HANDLER."shortcode_handler.php");
			$this->e_sc = new e_shortcode;
			if(!$noCore)
			{
				$this->e_sc->loadCoreShortcodes();
			}
		}
	}*/



	function toDB($data, $nostrip = false, $no_encode = false, $mod = false, $original_author = false)
	{
		/**
		* $nostrip: toDB() assumes all data is GPC ($_GET, $_POST, $_COOKIE) unless you indicate otherwise by setting this var to true.
		* If magic quotes is enabled on the server and you do not tell toDB() that the data is non GPC then slashes will be stripped when they should not be.
		* $no_encode: This var should nearly always be false. It is used by the save_prefs() function to preserve html content within prefs even when
		* the save_prefs() function has been called by a non admin user / user without html posting permissions.
		* $mod: the 'no_html' and 'no_php' modifiers blanket prevent html and php posting regardless of posting permissions. (used in logging)
		*/
		global $pref;
		if (is_array($data))
		{
			foreach ($data as $key => $var)
			{
				//Fix - sanitize keys as well
				$ret[$this->toDB($key, $nostrip, $no_encode, $mod, $original_author)] = $this->toDB($var, $nostrip, $no_encode, $mod, $original_author);
			}
		}
		else
		{
			if (MAGIC_QUOTES_GPC == true && $nostrip == false)
			{
				$data = stripslashes($data);
			}
			if (isset($pref['post_html']) && check_class($pref['post_html']))
			{
				$no_encode = true;
			}
			if (is_numeric($original_author) && !check_class($pref['post_html'], '', $original_author))
			{
				$no_encode = false;
			}
			if ($no_encode === true && strpos($mod, 'no_html') === false)
			{
				$search = array('$', '"', "'", '\\', '<?');
				$replace = array('&#036;','&quot;','&#039;', '&#092;', '&lt;?');
				$ret = str_replace($search, $replace, $data);
			}
			else
			{
				$data = htmlspecialchars($data, ENT_QUOTES, CHARSET);
				$data = str_replace('\\', '&#092;', $data);
				$ret = preg_replace("/&amp;#(\d*?);/", "&#\\1;", $data);
			}
			if (strpos($mod, 'no_php') !== false)
			{
				$ret = str_replace(array("[php]", "[/php]"), array("&#91;php&#93;", "&#91;/php&#93;"), $ret);
			}
		}
		return $ret;
	}


	function toForm($text)
	{
		if ($text == '') { return ''; }
		$search = array('&#036;', '&quot;', '<', '>');
		$replace = array('$', '"', '&lt;', '&gt;');
		$text = str_replace($search, $replace, $text);
		if (e_WYSIWYG !== true)
		{
	   	  	$text = str_replace("&nbsp;", " ", $text); // fix for utf-8 issue with html_entity_decode();
		}
		return $text;
	}


	function post_toForm($text)
	{
		if(is_array($text))
		{
			foreach ($text as $key => $value)
			{
				$text[$this->post_toForm($key)] = $this->post_toForm($value);
			}
			return $text;
		}
		if (MAGIC_QUOTES_GPC == true)
		{
			$text = stripslashes($text);
		}
		return str_replace(array( "'", '"', "<", ">"), array("&#039;", "&quot;", "&lt;", "&gt;"), $text);
	}


	function post_toHTML($text, $original_author = false, $extra = '', $mod = false)
	{
		$text = $this -> toDB($text, false, false, $mod, $original_author);
		return $this -> toHTML($text, true, $extra);
	}


	function parseTemplate($text, $parseSCFiles = TRUE, $extraCodes = "")
	{
		//$this->sch_load();
		return $this->e_sc->parseCodes($text, $parseSCFiles, $extraCodes);
	}



	function htmlwrap($str, $width, $break = "\n", $nobreak = "a", $nobr = "pre", $utf = false)
	{
		/*
		Pretty well complete rewrite to try and handle utf-8 properly.
		Breaks each utf-8 'word' every $width characters max. If possible, breaks after 'safe' characters.
		$break is the character inserted to flag the break.
		$nobreak is a list of tags within which word wrap is to be inactive
		*/

		//TODO handle htmlwrap somehow
		return $str;

		// Don't wrap if non-numeric width
		$width = intval($width);
		// And trap stupid wrap counts
		if ($width < 6)
			return $str;

		// Transform protected element lists into arrays
		$nobreak = explode(" ", strtolower($nobreak));

		// Variable setup
		$intag = false;
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
				$lvalue = strtolower(substr($value,1,-1));
				if ($lvalue)
				{	// Tag of non-zero length
					// If the first character is not a / then this is an opening tag
					if ($lvalue[0] != "/")
					{
						// Collect the tag name
						preg_match("/^(\w*?)(\s|$)/", $lvalue, $t);

						// If this is a protected element, activate the associated protection flag
						if (in_array($t[1], $nobreak)) array_unshift($innbk, $t[1]);
					}
					else
					{  // Otherwise this is a closing tag
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
								{  // utf-8 length is less than specified - treat as a special case
									$value .= $sp;
									$sp = '';
								}
								else
								{		// Need to find somewhere to break the string
									for ($i = strlen($matches[1])-1; $i >= 0; $i--)
									{
										if (strpos($lbrks,$matches[1][$i]) !== FALSE) break;
									}
									if ($i < 0)
									{	// No 'special' break character found - break at the word boundary
										$pulled = $matches[1];
									}
									else
									{
										$pulled = substr($sp,0,$i+1);
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
								for ($i = min($width,strlen($sp)); $i > 0; $i--)
								{
									// No speed advantage to defining match character
									if (strpos($lbrks,$sp[$i-1]) !== FALSE)
										break;
								}
								if ($i == 0)
								{
									// No 'special' break boundary character found - break at the word boundary
									$pulled = substr($sp,0,$width);
								}
								else
								{
									$pulled = substr($sp,0,$i);
								}
							}
							if ($pulled)
							{
								$value .= $pulled.$break;
								// Shorten $sp by whatever we've processed (will work even for utf-8)
								$sp = substr($sp,strlen($pulled));
							}
						}
						// Add in any residue
						$value .= $sp;
					}
					// Put captured HTML entities back into the string
					foreach ($ents[0] as $ent) $value = preg_replace("/\x06/", $ent, $value, 1);
				}
			}
			// Send the modified segment down the drain
			$drain .= $value;
		}
		// Return contents of the drain
		return $drain;
	}





	function html_truncate ($text, $len = 200, $more = "[more]")
	{
		$pos = 0;
		$curlen = 0;
		$tmp_pos = 0;
		$intag = FALSE;
		while($curlen < $len && $curlen < strlen($text))
		{
			switch($text{$pos})
			{
				case "<" :
				if($text{$pos+1} == "/")
				{
					$closing_tag = TRUE;
				}
				$intag = TRUE;
				$tmp_pos = $pos-1;
				$pos++;
				break;

				case ">" :
				if($text{$pos-1} == "/")
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

				case "&" :
				if($text{$pos+1} == "#")
				{
					$end = strpos(substr($text, $pos, 7), ";");
					if($end !== FALSE)
					{
						$pos+=($end+1);
						if(!$intag) {$curlen++;}
						break;
					}
				}
				else
				{
					$pos++;
					if(!$intag) {$curlen++;}
					break;
				}
				default:
				$pos++;
				if(!$intag) {$curlen++;}
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


	// Truncate a string to a maximum length $len - append the string $more if it was truncated
	// Uses current CHARSET - for utf-8, returns $len characters rather than $len bytes
	function text_truncate($text, $len = 200, $more = "[more]")
	{
		if (strlen($text) <= $len) return $text; 		// Always valid
		if (strtolower(CHARSET) !== 'utf-8')
		{
			$ret = substr($text,0,$len);	// Non-utf-8 - one byte per character - simple (unless there's an entity involved)
		}
		else
		{	  // Its a utf-8 string here - don't know whether its longer than allowed length yet
			preg_match('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,0}'.
				'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'})(.{0,1}).*#s',$text,$matches);

			if (empty($matches[2])) return $text;			// return if utf-8 length is less than max as well
			$ret = $matches[1];
		}
		// search for possible broken html entities
		// - if an & is in the last 8 chars, removing it and whatever follows shouldn't hurt
		// it should work for any characters encoding
		$leftAmp = strrpos(substr($ret,-8), '&');
		if($leftAmp) $ret = substr($ret,0,strlen($ret)-8+$leftAmp);
		return $ret.$more;
	}



	function textclean ($text, $wrap=100)
	{
		$text = str_replace ("\n\n\n", "\n\n", $text);
		$text = $this->htmlwrap($text, $wrap);
		$text = str_replace (array ('<br /> ', ' <br />', ' <br /> '), '<br />', $text);
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
			return FALSE;	// Still in startup, so can't calculate highlighting
		}

		if (!isset($this->e_highlighting))
		{
			$this->e_highlighting = FALSE;
			$shr = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
			if ($pref['search_highlight'] && (strpos(e_SELF, 'search.php') === FALSE) && ((strpos($shr, 'q=') !== FALSE) || (strpos($shr, 'p=') !== FALSE)))
			{
				$this->e_highlighting = TRUE;
				if (!isset($this -> e_query))
				{
					$query = preg_match('#(q|p)=(.*?)(&|$)#', $shr, $matches);
					$this -> e_query = str_replace(array('+', '*', '"', ' '), array('', '.*?', '', '\b|\b'), trim(urldecode($matches[2])));
				}
			}
		}
		return $this->e_highlighting;
	}



	/**
	 * 
	 * @param string $text
	 * @param boolean $parseBB [optional]
	 * @param string $modifiers [optional] TITLE|SUMMARY|DESCRIPTION|BODY|RAW|LINKTEXT etc.
	 * @param mixed $postID [optional]
	 * @param boolean $wrap [optional]
	 * @return 
	 */
	function toHTML($text, $parseBB = FALSE, $modifiers = "", $postID = "", $wrap=FALSE)
	{
		if ($text == '') return $text;

		global $pref, $fromadmin;

		// Set default modifiers to start
		$opts = $this->e_optDefault;

		// Now process any modifiers that are specified
		if ($modifiers)
		{
			$aMods = explode(',',$modifiers);

			// If there's a supermodifier, it must be first, and in uppercase
			$psm = trim($aMods[0]);
			if (isset($this->e_SuperMods[$psm]))
			{	// Supermodifier found - it simply overrides the default
				$opts = $this->e_SuperMods[$psm];
				$opts['context'] = $psm;
				unset($aMods[0]);
			}


			// Now find any regular modifiers; use them to modify the context (there should only be one or two out of the list of possibles)
			foreach ($aMods as $mod)
			{
				$mod = trim($mod);					// Slight concession to varying coding styles
				if (isset($this->e_Modifiers[$mod]))
				{
					foreach ($this->e_Modifiers[$mod] as $k => $v)		// This is probably quicker than array_merge - especially as usually only one or two loops
					{
						$opts[$k] = $v;				// Update our context-specific options
					}
				}
			}
		}

		// Turn off a few things if not enabled in options
		if (!varsettrue($pref['smiley_activate'])) $opts['emotes'] = FALSE;
		if (!varsettrue($pref['make_clickable'])) $opts['link_click'] = FALSE;
		if (!varsettrue($pref['link_replace'])) $opts['link_replace'] = FALSE;

		$fromadmin = $opts['fromadmin'];

		// Convert defines(constants) within text. eg. Lan_XXXX - must be the entire text string (i.e. not embedded)
		// The check for '::' is a workaround for a bug in the Zend Optimiser 3.3.0 and PHP 5.2.4 combination - causes crashes if '::' in site name
		if ($opts['defs'] && (strlen($text) < 25) && ((strpos($text,'::') === FALSE) && defined(trim($text))))
		{
			return constant(trim($text));
		}

		

		if ($opts['no_tags'])
		{
			$text = strip_tags($text);
		}


		// Make sure we have a valid count for word wrapping
		if (!$wrap && $pref['main_wordwrap'])
		{
			$wrap = $pref['main_wordwrap'];
		}
//		$text = " ".$text;



// Now get on with the parsing
		$ret_parser = '';
		$last_bbcode = '';
		$saveOpts = $opts;				// So we can change them on each loop
		if ($parseBB == FALSE)
		{
			$content = array($text);
		}
		else
		{
			// Split each text block into bits which are either within one of the 'key' bbcodes, or outside them
			// (Because we have to match end words, the 'extra' capturing subpattern gets added to output array. We strip it later)
			$content = preg_split('#(\[(html|php|code|scode|hide).*?\[/(?:\\2)\])#mis', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
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
				$opts = $saveOpts;			// Set the options for this pass

				// (Have to have a good test in case a 'non-key' bbcode starts the block - so pull out the bbcode parameters while we're there
				if (($parseBB !== FALSE) && preg_match('#(^\[(html|php|code|scode|hide)(.*?)\])(.*?)(\[/\\2\]$)#is', $full_text, $matches ))
				{  // It's one of the 'key' bbcodes
					$proc_funcs = FALSE;			// Usually don't want 'normal' processing if its a 'special' bbcode
					// $matches[0] - complete block from opening bracket of opening tag to closing bracket of closing tag
					// $matches[1] - complete opening tag (inclusive of brackets)
					// $matches[2] - bbcode word
					// $matches[3] - parameter, including '='
					// $matches[4] - bit between the tags (i.e. text to process)
					// $matches[5] - closing tag
					$bbFile = e_FILE.'bbcode/'.strtolower(str_replace('_', '', $matches[2])).'.bb';		// In case we decide to load a file
					$bbcode = '';
					$code_text = $matches[4];
					$parm = $matches[3] ? substr($matches[3],1) : '';
					$last_bbcode = $matches[2];
					switch ($matches[2])
					{
						case 'php' :
							if (DB_INF_SHOW) echo "PHP decode: ".htmlentities($matches[4])."<br /><br />";
							$proc_funcs = TRUE;		// Probably run the output through the normal processing functions - but put here so the PHP code can disable if desired
							// This is just the contents of the php.bb file pulled in - its short, so will be quicker
			//				$search = array("&quot;", "&#039;", "&#036;", '<br />', E_NL, "-&gt;", "&lt;br /&gt;");
			//				$replace = array('"', "'", "$", "\n", "\n", "->", "<br />");
							// Shouldn't have any parameter on this bbcode
			//				if (!$matches[3]) $bbcode = str_replace($search, $replace, $matches[4]);			// Not sure whether checks are necessary now we've reorganised
							// Because we're bypassing most of the initial parser processing, we should be able to just reverse the effects of toDB() and execute the code
							if (!$matches[3]) $bbcode = html_entity_decode($matches[4], ENT_QUOTES, CHARSET);
							if (DB_INF_SHOW) echo "PHP after decode: ".htmlentities($bbcode)."<br /><br />";
							break;
						case 'html' :
							$proc_funcs = TRUE;
							$convertNL = FALSE;
							break;
						case 'hide' :
							$proc_funcs = TRUE;
						default :		// Most bbcodes will just execute their normal file
							$bbcode = file_get_contents($bbFile);		// Just read in the code file and execute it
					}   // end - switch ($matches[2])
					if ($bbcode)
					{	// Execute the file
						ob_start();
						$bbcode_return = eval($bbcode);
						$bbcode_output = ob_get_contents();
						ob_end_clean();
						// added to remove possibility of nested bbcode exploits ...
						//   (same as in bbcode_handler - is it right that it just operates on $bbcode_return and not on $bbcode_output? - QUERY XXX-02
						if(strpos($bbcode_return, "[") !== FALSE)
						{
							$exp_search = array("eval", "expression");
							$exp_replace = array("ev<b></b>al", "expres<b></b>sion");
							$bbcode_return = str_replace($exp_search, $exp_replace, $bbcode_return);
						}
						$full_text = $bbcode_output.$bbcode_return;
					}
				}
			}



			if ($proc_funcs)
			{  // Do the 'normal' processing - in principle, as previously - but think about the order.

				// Split out and ignore any scripts and style blocks. With just two choices we can match the closing tag in the regex
				$subcon = preg_split('#((?:<s)(?:cript[^>]+>.*?</script>|tyle[^>]+>.*?</style>))#mis', $full_text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
				foreach ($subcon as $sub_blk)
				{
					if (substr($sub_blk,0,7) == '<script')
					{
						if ($opts['scripts'])
						{
							$ret_parser .= $sub_blk;	// Strip scripts unless permitted
						}
					}
					elseif (substr($sub_blk,0,6) == '<style')
					{  // Its a style block - just pass it through unaltered - except, do we need the line break stuff? - QUERY XXX-01
						if (DB_INF_SHOW) echo "Processing stylesheet: {$sub_blk}<br />";
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
							if ($opts['link_replace'])
							{
								$_ext = ($pref['links_new_window'] ? " rel=\"external\"" : "");
								$sub_blk = preg_replace("#(^|[\s])([\w]+?://(?:[\w-%]+?)(?:\.[\w-%]+?)+.*?)(?=$|[\s()[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a href=\"\\2\" {$_ext}>".$pref['link_text']."</a>", $sub_blk);
								$sub_blk = preg_replace("#(^|[\s])((?:www|ftp)(?:\.[\w-%]+?){2}.*?)(?=$|[\s()[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a href=\"http://\\2\" {$_ext}>".$pref['link_text']."</a>", $sub_blk);
                                $email_text = ($pref['email_text']) ? $this->replaceConstants($pref['email_text']) : LAN_EMAIL_SUBS;
								$sub_blk = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\"; return true;' onmouseout='window.status=\"\";return true;'>".$email_text."</a>", $sub_blk);
							}
							else
							{
								$email_text = ($this->isutf8) ? "\\1\\2©\\3" : "\\1\\2&copy;\\3";
								$sub_blk = preg_replace("#(^|[\s])([\w]+?://(?:[\w-%]+?)(?:\.[\w-%]+?)+.*?)(?=$|[\s()[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a href=\"\\2\" rel=\"external\">\\2</a>", $sub_blk);
								$sub_blk = preg_replace("#(^|[\s])((?:www|ftp)(?:\.[\w-%]+?){2}.*?)(?=$|[\s()[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $sub_blk);
								$sub_blk = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\"; return true;' onmouseout='window.status=\"\";return true;'>".$email_text."</a>", $sub_blk);
							}
						}


						// Convert emoticons to graphical icons, if enabled
						if ($opts['emotes'])
						{
							if (!is_object($this->e_emote))
							{
								require_once(e_HANDLER.'emote_filter.php');
								$this->e_emote = new e_emoteFilter;
							}
							$sub_blk = $this->e_emote->filterEmotes($sub_blk);
						}



						// Reduce newlines in all forms to a single newline character (finds '\n', '\r\n', '\n\r')
						if (!$opts['nobreak'])
						{
							if ($convertNL)
							{
								$sub_blk = preg_replace("#[\r]*\n[\r]*#", E_NL, $sub_blk);		// We may need to convert to <br /> later
							}
							else
							{
								$sub_blk = preg_replace("#[\r]*\n[\r]*#", "\n", $sub_blk);		// Not doing any more - its HTML so keep \n so HTML is formatted
							}
						}



						//	Entity conversion
						// Restore entity form of quotes and such to single characters, except for text destined for tag attributes or JS.
						if ($opts['value'])
						{   									// output used for attribute values.
							$sub_blk = str_replace($this -> replace, $this -> search, $sub_blk);
						}
						else
						{ // output not used for attribute values.
							$sub_blk = str_replace($this -> search, $this -> replace, $sub_blk);
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
								$sub_blk = $this->e_bb->parseBBCodes($sub_blk, $postID);		// 'Normal' or 'legacy' processing
							}
							elseif ($parseBB === 'STRIP')
							{
								$sub_blk = $this->e_bb->parseBBCodes($sub_blk, $postID, 'default', TRUE);		// Need to strip all BBCodes
							}
							else
							{
								$sub_blk = $this->e_bb->parseBBCodes($sub_blk, $postID, 'default', $parseBB);		// Need to strip just some BBCodes
							}
						}



						// replace all {e_XXX} constants with their e107 value. modifier determines relative/absolute conversion
						// (Moved to after bbcode processing by Cameron)
						if ($opts['constants'])
						{
							$sub_blk = $this->replaceConstants($sub_blk, ($opts['constants'] == 'abs' ? 'full' : ''));
						}



						// profanity filter
						if ($pref['profanity_filter'])
						{
							if (!is_object($this->e_pf))
							{
								require_once(e_HANDLER."profanity_filter.php");
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



					  //Run any hooked in parsers
						if ($opts['hook'])
						{
							if ( varset($pref['tohtml_hook']))
							{	//Process the older tohtml_hook pref (deprecated)
								foreach(explode(",",$pref['tohtml_hook']) as $hook)
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
									$sub_blk = $this->e_hook[$hook]->to_html($sub_blk, $opts['context']);
								}
							}
						}


						// 	Word wrap
						if ($wrap && !$opts['nobreak'])
						{
							$sub_blk = $this -> textclean($sub_blk, $wrap);
						}



						//	Search highlighting
						if ($opts['emotes'])			// Why??
						{
							if ($this->checkHighlighting())
							{
								$sub_blk = $this -> e_highlight($sub_blk, $this -> e_query);
							}
						}


						if ($convertNL)
						{
							$nl_replace = '<br />';			// Default replaces all \n with <br /> for HTML display
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
				$ret_parser .= $full_text;			// Text block that needed no processing at all
			}
		}
		return trim($ret_parser);
	}



	function toAttribute($text)
	{
		$text = str_replace("&amp;","&",$text); // URLs posted without HTML access may have an &amp; in them.
		$text = htmlspecialchars($text, ENT_QUOTES, CHARSET); // Xhtml compliance.
		if (!preg_match('/&#|\'|"|\(|\)|<|>/s', $text))
		{
			$text = $this->replaceConstants($text);
			return $text;
		}
		else
		{
			return '';
		}
	}


	/**
	 * Convert text blocks which are to be embedded within JS
	 * @param object $stringarray
	 * @return 
	 */
	function toJS($stringarray)
	{
		$search = array("\r\n","\r","<br />","'");
		$replace = array("\\n","","\\n","\'");
		$stringarray = str_replace($search, $replace, $stringarray);
        $stringarray = strip_tags($stringarray);

		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);

		return strtr ($stringarray, $trans_tbl);
	}


	/**
	 * Convert Text for RSS/XML use. 
	 * @param object $text
	 * @param object $tags [optional]
	 * @return 
	 */
	function toRss($text,$tags=FALSE)
	{
		if($tags != TRUE)
		{
			$text = $this -> toHTML($text,TRUE);
			$text = strip_tags($text);
		}

		$text = $this->toEmail($text);
   		$search = array("&amp;#039;", "&amp;#036;", "&#039;", "&#036;"," & ", e_BASE, "href='request.php");
   		$replace = array("'", '$', "'", '$',' &amp; ', SITEURL, "href='".SITEURL."request.php" );
   		$text = str_replace($search, $replace, $text);

		if($tags == TRUE && ($text))
		{
        	$text = "<![CDATA[".$text."]]>";
		}

		return $text;
	}

	//Convert specific characters back to original form, for use in storing code (or regex) values in the db.
	function toText($text)
	{
		$search = array("&amp;#039;", "&amp;#036;", "&#039;", "&#036;", "&#092;", "&amp;#092;");
		$replace = array("'", '$', "'", '$', "\\", "\\");
		$text = str_replace($search, $replace, $text);
		return $text;
	}


	/**
	 * Replace e107 path constants
	 * Note: only an ADMIN user can convert {e_ADMIN}	
	 * @param string $text
	 * @param string $mode [optional]  	abs|full "full" = produce absolute URL path, e.g. http://sitename.com/e107_plugins/etc
	 * 									TRUE = produce truncated URL path, e.g. e107plugins/etc
	 * 									"" (default) = URL's get relative path e.g. ../e107_plugins/etc
	 * @param mixed $all [optional] 	if TRUE, then when $mode is "full" or TRUE, USERID is also replaced...
	 * 									when $mode is "" (default), ALL other e107 constants are replaced
	 * @return string
	 */
	function replaceConstants($text, $mode = '', $all = false)
	{
		if($mode != "")
		{
			$e107 = e107::getInstance();
			
			$replace_relative = array(
				'',
				SITEURL.$e107->getFolder('images'),
				SITEURL.$e107->getFolder('themes'),
				$e107->getFolder('images'),
				$e107->getFolder('plugins'),
				$e107->getFolder('files'),
				$e107->getFolder('themes'),
			//	$e107->getFolder('downloads'),
				$e107->getFolder('handlers')
			);

			switch ($mode) 
			{
				case 'abs':
	            	$replace_absolute = array(
	            		$e107->server_path,
						e_IMAGE_ABS,
						e_THEME_ABS,
						e_IMAGE_ABS,
						e_PLUGIN_ABS,
						e_FILE_ABS,
						e_THEME_ABS,
				//		e_DOWNLOAD_ABS, //impossible when download is done via php.
						e_HANDLER_ABS
					);
				break;
				
				case 'full':
					$replace_absolute = array(
						SITEURL,
						SITEURL.$e107->getFolder('images'),
						SITEURL.$e107->getFolder('themes'),
						SITEURL.$e107->getFolder('images'),
						SITEURL.$e107->getFolder('plugins'),
						SITEURL.$e107->getFolder('files'),
						SITEURL.$e107->getFolder('themes'),
					//	SITEURL.$e107->getFolder('downloads'),
						SITEURL.$e107->getFolder('handlers')
					);
				break;
			}

			$search = array("{e_BASE}","{e_IMAGE_ABS}","{e_THEME_ABS}","{e_IMAGE}","{e_PLUGIN}","{e_FILE}","{e_THEME}","{e_HANDLER}");//,"{e_DOWNLOAD}"

			if (ADMIN)
			{
				$replace_relative[] = $e107->getFolder('admin');
				$replace_absolute[] = SITEURL.$e107->getFolder('admin');
				$search[] = "{e_ADMIN}";
			}

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

			$replace = ((string)$mode == "full" || (string)$mode=='abs' ) ? $replace_absolute : $replace_relative;
			return str_replace($search,$replace,$text);
		}

//		$pattern = ($all ? "#\{([A-Za-z_0-9]*)\}#s" : "#\{(e_[A-Z]*)\}#s");
		$pattern = ($all ? '#\{([A-Za-z_0-9]*)\}#s' : '#\{(e_[A-Z]*(?:_ABS){0,1})\}#s');
	 	$text = preg_replace_callback($pattern, array($this, 'doReplace'), $text);
	 	
	 	if(!defined('THEME'))
	 	{
	 		//if not already parsed by doReplace
	 		$text = str_replace(array('{THEME}', '{THEME_ABS}'), '', $text);
	 	}

		return $text;
	}


	function doReplace($matches)
	{
		if(defined($matches[1]) && ($matches[1] != 'e_ADMIN' || ADMIN))
		{
			return constant($matches[1]);
		}
		return $matches[1];
	}

	/**
	 * Create and substitute e107 constants in passed URL
	 * 
	 * @param string $url
	 * @param string $mode 0-folders, 1-relative, 2-absolute, 3-full (with domain), 4-absolute & relative (combination of 1,2,3)
	 * @return 
	 */
    function createConstants($url, $mode=0)
    {
    	//FIXME - create constants for absolute paths and site URL's
		$e107 = e107::getInstance();
		switch($mode)
		{
			case 0: // folder name only.
				$tmp = array(
					'{e_IMAGE}' 	=> $e107->getFolder('images'),
					'{e_PLUGIN}'	=> $e107->getFolder('plugins'),
					'{e_FILE}'		=> $e107->getFolder('files'),
					'{e_THEME}'		=> $e107->getFolder('themes'),
					'{e_DOWNLOAD}'	=> $e107->getFolder('downloads'),
					'{e_ADMIN}'		=> $e107->getFolder('admin'),
					'{e_HANDLER}'	=> $e107->getFolder('handlers')
	  			);
			break;
		
			case 1: // relative path only
				$tmp = array(
					'{e_IMAGE}'		=> e_IMAGE,
					'{e_PLUGIN}'	=> e_PLUGIN,
					'{e_FILE}'		=> e_FILE,
					'{e_THEME}'		=> e_THEME,
					'{e_DOWNLOAD}'	=> e_DOWNLOAD,
					'{e_ADMIN}'		=> e_ADMIN,
					'{e_HANDLER}'	=> e_HANDLER
				);
			break;
			
			case 2: // absolute path only
				$tmp = array(
					'{e_IMAGE}'		=> e_IMAGE_ABS,
					'{e_PLUGIN}'	=> e_PLUGIN_ABS,
					'{e_FILE}'		=> e_FILE_ABS,
					'{e_THEME}'		=> e_THEME_ABS,
					'{e_DOWNLOAD}'	=> e_BASE.'request.php?',
					'{e_ADMIN}'		=> e_ADMIN_ABS,
					'{e_HANDLER}'	=> e_HANDLER_ABS
				);
			break;
			
			case 3: // full path (e.g http://domain.com/e107_images/)
				$tmp = array(
					'{e_IMAGE}' 	=> SITEURL.$e107->getFolder('images'),
					'{e_PLUGIN}'	=> SITEURL.$e107->getFolder('plugins'),
					'{e_FILE}'		=> SITEURL.$e107->getFolder('files'),
					'{e_THEME}'		=> SITEURL.$e107->getFolder('themes'),
					'{e_DOWNLOAD}'	=> SITEURL.$e107->getFolder('downloads'),
					'{e_ADMIN}'		=> SITEURL.$e107->getFolder('admin'),
					'{e_HANDLER}'	=> SITEURL.$e107->getFolder('handlers')
				);
			break;
			
			case 4: // absolute & relative paths
				$url = $this->createConstants($url, 3);
				$url = $this->createConstants($url, 2);
				$url = $this->createConstants($url, 1);
				return $url;
			break;
			
			default:
				$tmp = array();
			break;
		}

		foreach($tmp as $key=>$val)
		{
        	$len = strlen($val);
			if(substr($url,0,$len) == $val)
			{
            	return substr_replace($url,$key,0,$len); // replace the first instance only
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


    function toEmail($text,$posted="",$mods="parse_sc, no_make_clickable")
	{
		if ($posted === TRUE && MAGIC_QUOTES_GPC)
		{
			$text = stripslashes($text);
		}

	  	$text = (strtolower($mods) != "rawtext") ? $this->replaceConstants($text,"full") : $text;
    	$text = $this->toHTML($text,TRUE,$mods);
        return $text;
	}


	// Given an email address, returns a link including js-based obfuscation
	function emailObfuscate($email, $words='', $subject='')
	{
		if (strpos($email,'@') === FALSE)
		{
			return '';
		}
		if ($subject)
		{
			$subject = '?subject='.$subject;
		}
		list($name,$address) = explode('@',$email,2);
		$reassembled = '"'.$name.'"+"@"+"'.$address.'"';
		return "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+".$reassembled.$subject.";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+".$reassembled."; return true;' onmouseout='window.status=\"\";return true;'>".$words.'</a>';
	}

	public function __get($name)
	{ 
		switch ($name) 
		{
			case 'e_sc':
				$ret = e107::getScParser(); 
			break;
			
			default:
				trigger_error('$e107->$'.$name.' not defined', E_USER_WARNING);
				return null; 
			break;
		}
		
		$this->$name = $ret;
		return $ret;
	}
}

?>