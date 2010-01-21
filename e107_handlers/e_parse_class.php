<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|      Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/e_parse_class.php,v $
|     $Revision: 1.223 $
|     $Date: 2010-01-21 03:57:44 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

define ("E_NL", chr(2));
class e_parse
{
	var $e_sc;
	var $e_bb;
	var $e_pf;
	var $e_emote;
	var $e_hook;
	var $search = array('&amp;#039;', '&#039;', '&#39;', '&quot;', 'onerror', '&gt;', '&amp;quot;', ' & ');
	var $replace = array("'", "'", "'", '"', 'one<i></i>rror', '>', '"', ' &amp; ');
	var $e_highlighting;		// Set to TRUE or FALSE once it has been calculated
	var $e_query;			// Highlight query

		// toHTML Action defaults. For now these match existing convention.
		// Let's reverse the logic on the first set ASAP; too confusing!
	var $e_modSet = array();
	var	$e_optDefault = array(
		'context' => 'olddefault',			// default context: all "opt-out" conversions :(
		'fromadmin' => FALSE,

			// Enabled by Default
		'value'	=> FALSE,					// Restore entity form of quotes and such to single characters - TRUE disables

		'nobreak' => FALSE,					// Line break compression - TRUE removes multiple line breaks
		'retain_nl' => FALSE,				// Retain newlines - wraps to \n instead of <br /> if TRUE

		'no_make_clickable' => FALSE,		// URLs etc are clickable - TRUE disables
		'no_replace' => FALSE,				// Replace clickable links - TRUE disables (only if no_make_clickable not set)

	  	'emotes_off' => FALSE,				// Convert emoticons to graphical icons - TRUE disables conversion
		'emotes_on'  => FALSE,				// FORCE conversion to emotes, even if syspref is disabled

		'no_hook' => FALSE,					// Hooked parsers (TRUE disables completely)

		// Disabled by Default
		'defs' => FALSE,					// Convert defines(constants) within text.
		'constants' => FALSE,				// replace all {e_XXX} constants with their e107 value
		'parse_sc' => FALSE,			   	// Parse shortcodes - TRUE enables parsing
		'no_tags' => FALSE                  // remove HTML tags.
		);

		// Super modifiers adjust default option values
		// First line of adjustments change default-ON options
		// Second line changes default-OFF options
	var	$e_SuperMods = array(
				'title' =>				//text is part of a title (e.g. news title)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'no_make_clickable'=>TRUE,'emotes_off'=>TRUE,
						'defs'=>TRUE,'parse_sc'=>TRUE),

				'user_title' =>				//text is user-entered (i.e. untrusted) and part of a title (e.g. forum title)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'no_make_clickable'=>TRUE,'emotes_off'=>TRUE,'no_hook'=>TRUE
						),

				'summary' =>			// text is part of the summary of a longer item (e.g. content summary)
					array(
						// no changes to default-on items
						'defs'=>TRUE, 'constants'=>TRUE, 'parse_sc'=>TRUE),

				'description' =>	// text is the description of an item (e.g. download, link)
					array(
						// no changes to default-on items
						'defs'=>TRUE, 'constants'=>TRUE, 'parse_sc'=>TRUE),

				'body' =>					// text is 'body' or 'bulk' text (e.g. custom page body, content body)
					array(
						// no changes to default-on items
						'defs'=>TRUE, 'constants'=>TRUE, 'parse_sc'=>TRUE),

				'user_body' =>					// text is user-entered (i.e. untrusted)'body' or 'bulk' text (e.g. custom page body, content body)
					array(
						'constants'=>TRUE
						),

				'linktext' =>			// text is the 'content' of a link (A tag, etc)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'no_make_clickable'=>TRUE,'emotes_off'=>TRUE,'no_hook'=>TRUE,
						'defs'=>TRUE,'parse_sc'=>TRUE),

				'rawtext' =>			// text is used (for admin edit) without fancy conversions or html.
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'no_make_clickable'=>TRUE,'emotes_off'=>TRUE,'no_hook'=>TRUE,'no_tags'=>TRUE
						// leave opt-in options off
						)
		);

	var $replaceVars = array();

	function e_parse()
	{
		// Preprocess the supermods to be useful default arrays with all values
		foreach ($this->e_SuperMods as $key=>$val)
		{
			$this->e_SuperMods[$key] = array_merge($this->e_optDefault,$this->e_SuperMods[$key]); // precalculate super defaults
			$this->e_SuperMods[$key]['context']=$key;
		}
		foreach ($this->e_optDefault as $key=>$val)
		{
			$this->e_modSet[$key] = TRUE;
		}

	}

	function toDB($data, $nostrip = false, $no_encode = false, $mod = false)
	{
		global $pref;
		if (is_array($data)) {
			// recursively run toDB (for arrays)
			foreach ($data as $key => $var) {
				$ret[$key] = $this -> toDB($var, $nostrip, $no_encode, $mod);
			}
		} else {
			if (MAGIC_QUOTES_GPC == TRUE && $nostrip == false) {
				$data = stripslashes($data);
			}
			if(isset($pref['post_html']) && check_class($pref['post_html']))
			{
				$no_encode = TRUE;
			}
			if ($no_encode === TRUE && $mod != 'no_html')
			{
				$search = array('$', '"', "'", '\\', '<?');
				$replace = array('&#036;','&quot;','&#039;', '&#092;', '&lt;?');
				$ret = str_replace($search, $replace, $data);
			} else {
				$data = htmlspecialchars($data, ENT_QUOTES, CHARSET);
				$data = str_replace('\\', '&#092;', $data);
				$ret = preg_replace("/&amp;#(\d*?);/", "&#\\1;", $data);
			}
			//If user is not allowed to use [php] change to entities
			if(!check_class($pref['php_bbcode']))
			{
				$ret = preg_replace("#\[(php)#i", "&#91;\\1", $ret);
			}

		}

		return $ret;
	}

	function toForm($text, $single_quotes = FALSE, $convert_lt_gt = false)
	{
		if($text == "") { return ""; }
		$mode = ($single_quotes ? ENT_QUOTES : ENT_COMPAT);
		$search = array('&#036;');
		$replace = array('$');
		$text = str_replace($search, $replace, $text);
		if(e_WYSIWYG !== TRUE){
	   	  	$text = str_replace("&nbsp;"," ",$text); // fix for utf-8 issue with html_entity_decode();
		}
		if($convert_lt_gt)
		{
			//need to convert < > to entities if this text will be in a textarea, to prevent injection
			$text = str_replace(array("<", ">"), array("&lt;", "&gt;"), $text);
		}
		return $text;
	}


	function post_toForm($text) {
		if (defined("MAGIC_QUOTES_GPC") && (MAGIC_QUOTES_GPC == TRUE)) {
			$text = stripslashes($text);
		}
		// ensure apostrophes are properly converted, or else the form item could break
		return str_replace(array( "'", '"'), array("&#039;", "&quot;"), $text);
	}


	function post_toHTML($text, $modifier = true, $extra = '') {
		/*
		changes by jalist 30/01/2005:
		description had to add modifier to /not/ send formatted text on to this->toHTML at end of method, this was causing problems when MAGIC_QUOTES_GPC == TRUE.
		*/
		global $pref;

		$no_encode = FALSE;
		if(isset($pref['post_html']) && check_class($pref['post_html'])) {
			$no_encode = true;
		}

		if (ADMIN === true || $no_encode === true) {
			$search = array('$', '"', "'", '\\', "'&#092;'");
			$replace = array('&#036;','&quot;','&#039;','&#092;','&#039;');
			$text = str_replace($search, $replace, $text);
			/*
			changes by jalist 30/01/2005:
			description dirty fix for servers with magic_quotes_gpc == true
			*/
			if (MAGIC_QUOTES_GPC) {
				$search = array('&#092;&#092;&#092;&#092;', '&#092;&#039;', '&#092;&quot;');
				$replace = array('&#092;&#092;','&#039;', '&quot;');
				$text = str_replace($search, $replace, $text);
			}
		} else {

			if (MAGIC_QUOTES_GPC) {
				$text = stripslashes($text);
			}
		  	$text = htmlentities($text, ENT_QUOTES, CHARSET);
		}

		$text = $this->replaceConstants($text);

		//If user is not allowed to use [php] change to entities
		if(!check_class($pref['php_bbcode']))
		{
			$text = preg_replace("#\[(php)#i", "&#91;\\1", $text);
		}

		return ($modifier ? $this->toHTML($text, true, $extra) : $text);
	}

	function parseTemplate($text, $parseSCFiles = TRUE, $extraCodes = "") {
		// Start parse {XXX} codes
		if (!is_object($this->e_sc))
		{
			require_once(e_HANDLER."shortcode_handler.php");
			$this->e_sc = new e_shortcode;
		}
		return $this->e_sc->parseCodes($text, $parseSCFiles, $extraCodes);
		// End parse {XXX} codes
	}
	
	function simpleParse(&$template, &$vars=false, $replaceUnset=true)
	{
		if($vars === false)
		{
			$this->replaceVars = &$GLOBALS;
		}
		else
		{
			$this->replaceVars = &$vars;
		}
		$this->replaceUnset = $replaceUnset;
		return preg_replace_callback("#\{([a-zA-Z0-9_]+)\}#", array($this, simpleReplace), $template);
	}
	
	function simpleReplace($tmp) {
		$unset = ($this->replaceUnset ? '' : $tmp[0]);
		return (isset($this->replaceVars[$tmp[1]]) ? $this->replaceVars[$tmp[1]] : $unset);
//		return (isset($this->replaceVars[$tmp[1]]) && is_string($this->replaceVars[$tmp[1]]) ? $this->replaceVars[$tmp[1]] : '');
	}



	function htmlwrap($str, $width, $break = "\n", $nobreak = "a", $nobr = "pre", $utf = false)
	{
		/*
		Pretty well complete rewrite to try and handle utf-8 properly.
		Breaks each utf-8 'word' every $width characters max. If possible, breaks after 'safe' characters.
		$break is the character inserted to flag the break.
		$nobreak is a list of tags within which word wrap is to be inactive
		*/

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





	function html_truncate ($text, $len = 200, $more = ' ... ')
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
	function text_truncate($text, $len = 200, $more = ' ... ')
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
		$text = $this -> htmlwrap($text, $wrap);
		$text = str_replace (array ("<br /> ", " <br />", " <br /> "), "<br />", $text);
		/* we can remove any linebreaks added by htmlwrap function as any \n's will be converted later anyway */
		return $text;
	}

	//
	// Test for text highlighting, and determine the text highlighting transformation
	// Returns TRUE if highlighting is active for this page display
	//
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


	function toHTML($text, $parseBB = FALSE, $modifiers = "", $postID = "", $wrap=FALSE) {
		if ($text == '')
		{
			return $text;
		}
		global $pref, $fromadmin;

		//
		// SET MODIFIERS
		//

		// Get modifier strings for toHTML
		// "super" modifiers set a baseline. Recommend entering in UPPER CASE to highlight
		// other modifiers override
		// modifiers SHOULD be delimited with commas (eventually this will be 'MUST')
		// modifiers MAY have spaces in between as desired

		$opts = $this->e_optDefault;
		if (strlen($modifiers))
		{
			//
			// Yes, the following code is strangely-written. It is one of the MOST used bits in
			// all of e107. We "inlined" the assignments to optimize speed through
			// some careful testing (19 Jan 2007).
			//
			// Some alternatives that do NOT speed things up (they make it slower)
			//  - use of array_intersect, array_walk, preg_replace, intermediate variables, etc etc etc.
			//

			if (1) // php 4 code
			{
				$opts = $this->e_optDefault;
				$aMods = explode( ',',
										// convert blanks to comma, then comma-comma (from blank-comma) to single comma
										str_replace(array(' ', ',,'),	array(',', ',' ),
											// work with all lower case
											strtolower($modifiers)
										)
				);

				foreach ($aMods as $mod)
				{
					if (isset($this->e_SuperMods[$mod]))
					{
						  	$opts = $this->e_SuperMods[$mod];
					}
				}

				// Find any regular mods
				foreach ($aMods as $mod)
				{
					$opts[$mod] = TRUE;  // Change mods as spec'd
				}
			}

			if (0) // php 5 code - not tested, and may not be faster anyway
			{
				$aMods = array_flip(
									explode( ',',
										// convert blanks to comma, then comma-comma (from blank-comma) to single comma
										str_replace(array(' ', ',,'),	array(',', ',' ),
											// work with all lower case
											strtolower($modifiers)
										)
									)
								 );
				$opts = array_merge($opts, array_intersect_key($this->e_SuperMods,$aMods)); // merge in any supermods found
				$opts = array_merge($opts, array_intersect_key($this->modSet, $aMods)); // merge in any other mods found
			}
		}

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


		if(!$wrap && $pref['main_wordwrap']) $wrap = $pref['main_wordwrap'];
        $text = " ".$text;


		// Prepare for line-break compression. Avoid compressing newlines in embedded scripts and CSS
		if (!$opts['nobreak'])
		{
            $text = preg_replace("#>\s*[\r]*\n[\r]*#", ">", $text);
            preg_match_all("#<(script|style)[^>]+>.*?</(script|style)>#is", $text, $embeds);
            $text = preg_replace("#<(script|style)[^>]+>.*?</(script|style)>#is", "<|>", $text);
        }


			// Convert URL's to clickable links, unless modifiers or prefs override
        if ($pref['make_clickable'] && !$opts['no_make_clickable'])
		{
            if ($pref['link_replace'] && !$opts['no_replace'])
			{
              $_ext = ($pref['links_new_window'] ? " rel=\"external\"" : "");
// 			  $text = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<,]*)#is", "\\1<a href=\"\\2\" {$_ext}>".$pref['link_text']."</a>", $text);
              $text = preg_replace("#(^|[\s])([\w]+?://(?:[\w-%]+?)(?:\.[\w-%]+?)+.*?)(?=$|[\s()[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a href=\"\\2\" {$_ext}>".$pref['link_text']."</a>", $text);
//			  $text = preg_replace("#(^|[\n \]])((www|ftp)\.[\w+-]+?\.[\w+\-.]*(?(?=/)(/.+?(?=\s|,\s))|(?=\W)))#is", "\\1<a href=\"http://\\2\" {$_ext}>".$pref['link_text']."</a>", $text);
			  $text = preg_replace("#(^|[\s])((?:www|ftp)(?:\.[\w-%]+?){2}.*?)(?=$|[\s()[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a href=\"http://\\2\" {$_ext}>".$pref['link_text']."</a>", $text);

			  $email_text = ($pref['email_text']) ? $this->replaceConstants($pref['email_text']) : LAN_EMAIL_SUBS;
              $text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\"; return true;' onmouseout='window.status=\"\";return true;'>".$email_text."</a>", $text);

			}
			else
			{
	           	$email_text = (CHARSET != "utf-8" && CHARSET != "UTF-8") ? "\\1\\2&copy;\\3" : "\\1\\2©\\3";
	//          $text = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<,]*)#is", "\\1<a href=\"\\2\" rel=\"external\">\\2</a>", $text);
				$text = preg_replace("#(^|[\s])([\w]+?://(?:[\w-%]+?)(?:\.[\w-%]+?)+.*?)(?=$|[\s()[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a href=\"\\2\" rel=\"external\">\\2</a>", $text);
	//			$text = preg_replace("#(^|[\n \]])((www|ftp)\.[\w+-]+?\.[\w+\-.]*(?(?=/)(/.+?(?=\s|,\s))|(?=\W)))#is", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $text);
				$text = preg_replace("#(^|[\s])((?:www|ftp)(?:\.[\w-%]+?){2}.*?)(?=$|[\s()[\]<]|\.\s|\.$|,\s|,$)#is", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $text);
				$text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\"; return true;' onmouseout='window.status=\"\";return true;'>".$email_text."</a>", $text);
			}
        }


			// Convert emoticons to graphical icons, unless modifiers override
        if (!$opts['emotes_off'])
		{
            if ($pref['smiley_activate'] || $opts['emotes_on'])
			{
                if (!is_object($this->e_emote)) {
                    require_once(e_HANDLER.'emote_filter.php');
                    $this->e_emote = new e_emoteFilter;
                }
                $text = $this->e_emote->filterEmotes($text);
            }
        }


			// Reduce multiple newlines in all forms to a single newline character, except for embedded scripts and CSS
        if (!$opts['nobreak'])
		{
            $text = preg_replace("#[\r]*\n[\r]*#", E_NL, $text);
            foreach ($embeds[0] as $embed) {
                $text = preg_replace("#<\|>#", $embed, $text, 1);
            }
        }


		// Restore entity form of quotes and such to single characters, except for text destined for tag attributes or JS.
		if (!$opts['value'])
		{ // output not used for attribute values.
	       	$text = str_replace($this -> search, $this -> replace, $text);
        }
		else
		{   									// output used for attribute values.
            $text = str_replace($this -> replace, $this -> search, $text);
		}




        // Start parse [bb][/bb] codes
        if ($parseBB === TRUE)
		{
            if (!is_object($this->e_bb)) {
                require_once(e_HANDLER.'bbcode_handler.php');
                $this->e_bb = new e_bbcode;
            }
            $text = $this->e_bb->parseBBCodes($text, $postID);
        }
        // End parse [bb][/bb] codes


		// replace all {e_XXX} constants with their e107 value AFTER the bbcodes have been parsed.
		if ($opts['constants'])
		{
		   	$text = $this->replaceConstants($text);
		}

		// profanity filter
        if ($pref['profanity_filter']) {
            if (!is_object($this->e_pf)) {
                require_once(e_HANDLER."profanity_filter.php");
                $this->e_pf = new e_profanityFilter;
            }
            $text = $this->e_pf->filterProfanities($text);
        }


			// Optional short-code conversion
        if ($opts['parse_sc'])
        {
            $text = $this->parseTemplate($text, TRUE);
        }


        //Run any hooked in parsers
		if (!$opts['no_hook'] && varset($pref['tohtml_hook']))
        {
          foreach(explode(",",$pref['tohtml_hook']) as $hook)
          {
            if (!is_object($this->e_hook[$hook]))
            {
              require_once(e_PLUGIN.$hook."/".$hook.".php");
              $hook_class = "e_".$hook;
              $this->e_hook[$hook] = new $hook_class;
            }
          $text = $this->e_hook[$hook]->$hook($text,$opts['context']);
          }
        }


        if (!$opts['nobreak'])
		{
            $text = $this -> textclean($text, $wrap);
        }


        // Search Highlight
        if (!$opts['emotes_off'])
		{
          if ($this->checkHighlighting())
          {
			$text = $this -> e_highlight($text, $this -> e_query);
		  }
		}


        $nl_replace = "<br />";
        if ($opts['nobreak'])
        {
            $nl_replace = '';
        }
        elseif ($opts['retain_nl'])
        {
            $nl_replace = "\n";
        }
        $text = str_replace(E_NL, $nl_replace, $text);

		return trim($text);
	}


	function toAttribute($text) {
		$text = str_replace("&amp;","&",$text); // URLs posted without HTML access may have an &amp; in them.
		$text = htmlspecialchars($text, ENT_QUOTES, CHARSET); // Xhtml compliance.
		if (!preg_match('/&#|\'|"|\(|\)|<|>/s', $text))
		{
		  $text = $this->replaceConstants($text);
		  return $text;
		} else {
			return '';
		}
	}

	function toJS($stringarray) {
		$search = array("\r\n","\r","<br />","'");
		$replace = array("\\n","","\\n","\'");
		$stringarray = str_replace($search, $replace, $stringarray);
        $stringarray = strip_tags($stringarray);

		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);

		return strtr ($stringarray, $trans_tbl);
	}

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

//
// $nonrelative:
//   "full" = produce absolute URL path, e.g. http://sitename.com/e107_plugins/etc
//   TRUE = produce truncated URL path, e.g. e107plugins/etc
//   "" (default) = URL's get relative path e.g. ../e107_plugins/etc
//
// $all - if TRUE, then
//		when $nonrelative is "full" or TRUE, USERID is also replaced...
//		when $nonrelative is "" (default), ALL other e107 constants are replaced
//
// only an ADMIN user can convert {e_ADMIN}
//
	function replaceConstants($text, $nonrelative = "", $all = false)
	{
		if($nonrelative != "")
		{
			global $IMAGES_DIRECTORY, $PLUGINS_DIRECTORY, $FILES_DIRECTORY, $THEMES_DIRECTORY,$DOWNLOADS_DIRECTORY,$ADMIN_DIRECTORY;
			$replace_relative = array("",
									SITEURL.$IMAGES_DIRECTORY,
									SITEURL.$THEMES_DIRECTORY,
									$IMAGES_DIRECTORY,
									$PLUGINS_DIRECTORY,
									$FILES_DIRECTORY,
									$THEMES_DIRECTORY,
									$DOWNLOADS_DIRECTORY);
			$replace_absolute = array(SITEURL,
									SITEURL.$IMAGES_DIRECTORY,
									SITEURL.$THEMES_DIRECTORY,
									SITEURL.$IMAGES_DIRECTORY,
									SITEURL.$PLUGINS_DIRECTORY,
									SITEURL.$FILES_DIRECTORY,
									SITEURL.$THEMES_DIRECTORY,
									SITEURL.$DOWNLOADS_DIRECTORY);
			$search = array("{e_BASE}","{e_IMAGE_ABS}","{e_THEME_ABS}","{e_IMAGE}","{e_PLUGIN}","{e_FILE}","{e_THEME}","{e_DOWNLOAD}");
			if (ADMIN) {
				$replace_relative[] = $ADMIN_DIRECTORY;
				$replace_absolute[] = SITEURL.$ADMIN_DIRECTORY;
				$search[] = "{e_ADMIN}";
			}
			if ($all) {
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
			$replace = ((string)$nonrelative == "full" ) ? $replace_absolute : $replace_relative;
			return str_replace($search,$replace,$text);
		}
//		$pattern = ($all ? "#\{([A-Za-z_0-9]*)\}#s" : "#\{(e_[A-Z]*)\}#s");
		$pattern = ($all ? "#\{([A-Za-z_0-9]*)\}#s" : "#\{(e_[A-Z]*(?:_ABS){0,1})\}#s");
	 	$text = preg_replace_callback($pattern, array($this, 'doReplace'), $text);
		$theme_path = (defined("THEME")) ? constant("THEME") : "";
		$text = str_replace("{THEME}",$theme_path,$text);

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

    function createConstants($url,$mode=0){
        global $IMAGES_DIRECTORY,$PLUGINS_DIRECTORY,$FILES_DIRECTORY,$THEMES_DIRECTORY,$DOWNLOADS_DIRECTORY,$ADMIN_DIRECTORY;

        if($mode == 0) // folder name only.
		{
			$tmp = array(
				"{"."e_IMAGE"."}"=>$IMAGES_DIRECTORY,
				"{"."e_PLUGIN"."}"=>$PLUGINS_DIRECTORY,
				"{"."e_FILE"."}"=>$FILES_DIRECTORY,
				"{"."e_THEME"."}"=>$THEMES_DIRECTORY,
				"{"."e_DOWNLOAD"."}"=>$DOWNLOADS_DIRECTORY,
				"{"."e_ADMIN"."}"=>$ADMIN_DIRECTORY,
  			);
        }
		elseif($mode == 1)  // relative path
		{
			$tmp = array(
				"{"."e_IMAGE"."}"=>e_IMAGE,
				"{"."e_PLUGIN"."}"=>e_PLUGIN,
				"{"."e_FILE"."}"=>e_FILE,
				"{"."e_THEME"."}"=>e_THEME,
				"{"."e_DOWNLOAD"."}"=>e_DOWNLOAD,
				"{"."e_ADMIN"."}"=>e_ADMIN
			);
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


	function e_highlight($text, $match) {
		preg_match_all("#<[^>]+>#", $text, $tags);
		$text = preg_replace("#<[^>]+>#", "<|>", $text);
		$text = preg_replace("#(\b".$match."\b)#i", "<span class='searchhighlight'>\\1</span>", $text);
		foreach ($tags[0] as $tag) {
			$text = preg_replace("#<\|>#", $tag, $text, 1);
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

}

?>
