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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/e_parse_class.php,v $
|     $Revision: 1.40 $
|     $Date: 2008-11-01 18:00:30 $
|     $Author: e107steved $
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
	var $search = array('&#39;', '&#039;', '&quot;', 'onerror', '&gt;', '&amp;#039;', '&amp;quot;', ' & ');
	var $replace = array("'", "'", '"', 'one<i></i>rror', '>', "'", '"', ' &amp; ');
	var $e_highlighting;		// Set to TRUE or FALSE once it has been calculated
	var $e_query;			// Highlight query

		// toHTML Action defaults. For now these match existing convention. 
		// Let's reverse the logic on the first set ASAP; too confusing!
	var	$e_optDefault = array(
		'context' 		=> 'olddefault',	// default context: all "opt-out" conversions :(
		'fromadmin' 	=> FALSE,

		// Enabled by Default
		'value'			=> FALSE,			// Restore entity form of quotes and such to single characters - TRUE disables

		'nobreak' 		=> FALSE,			// Line break compression - TRUE removes multiple line breaks
		'retain_nl' 	=> FALSE,			// Retain newlines - wraps to \n instead of <br /> if TRUE

		'no_make_clickable' => FALSE,		// URLs etc are clickable - TRUE disables
		'no_replace' 	=> FALSE,			// Replace clickable links - TRUE disables (only if no_make_clickable not set)
		  
	  	'emotes_off' 	=> FALSE,			// Convert emoticons to graphical icons - TRUE disables conversion
		'emotes_on'  	=> FALSE,			// FORCE conversion to emotes, even if syspref is disabled

		'no_hook' 		=> FALSE,			// Hooked parsers (TRUE disables completely) (deprecated)
			
		// Disabled by Default
		'defs' 			=> FALSE,			// Convert defines(constants) within text.
		'constants' 	=> FALSE,			// replace all {e_XXX} constants with their e107 value
		'abs_links' 	=> FALSE,			// Convert constants to absolute paths if TRUE
		'parse_sc' 		=> FALSE,			// Parse shortcodes - TRUE enables parsing
		'no_tags' 		=> FALSE			// remove HTML tags.
		);
		
		// Super modifiers adjust default option values
		// First line of adjustments change default-ON options
		// Second line changes default-OFF options
	var	$e_SuperMods = array(
				'TITLE' =>				//text is part of a title (e.g. news title)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'no_make_clickable'=>TRUE,'emotes_off'=>TRUE,
						'defs'=>TRUE,'parse_sc'=>TRUE),

				'USER_TITLE' =>				//text is user-entered (i.e. untrusted) and part of a title (e.g. forum title)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'no_make_clickable'=>TRUE,'emotes_off'=>TRUE,'no_hook'=>TRUE
						),

				'SUMMARY' =>			// text is part of the summary of a longer item (e.g. content summary)
					array(
						// no changes to default-on items
						'defs'=>TRUE, 'constants'=>TRUE, 'parse_sc'=>TRUE),

				'DESCRIPTION' =>	// text is the description of an item (e.g. download, link)
					array(
						// no changes to default-on items
						'defs'=>TRUE, 'constants'=>TRUE, 'parse_sc'=>TRUE),

				'BODY' =>					// text is 'body' or 'bulk' text (e.g. custom page body, content body)
					array(
						// no changes to default-on items
						'defs'=>TRUE, 'constants'=>TRUE, 'parse_sc'=>TRUE),

				'USER_BODY' =>					// text is user-entered (i.e. untrusted)'body' or 'bulk' text (e.g. custom page body, content body)
					array(
						'constants'=>TRUE
						),

				'LINKTEXT' =>			// text is the 'content' of a link (A tag, etc)
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'no_make_clickable'=>TRUE,'emotes_off'=>TRUE,'no_hook'=>TRUE,
						'defs'=>TRUE,'parse_sc'=>TRUE),

				'RAWTEXT' =>			// text is used (for admin edit) without fancy conversions or html.
					array(
						'nobreak'=>TRUE, 'retain_nl'=>TRUE, 'no_make_clickable'=>TRUE,'emotes_off'=>TRUE,'no_hook'=>TRUE,'no_tags'=>TRUE
						// leave opt-in options off
						)
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


	// Initialise the shortcode handler - has to be done when $prefs valid, so can't be done in constructor ATM
	function sch_load()
	{
	  if (!is_object($this->e_sc))
	  {
		require_once(e_HANDLER."shortcode_handler.php");
		$this->e_sc = new e_shortcode;
	  } 
	}


	
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
		if (is_array($data)) {
			foreach ($data as $key => $var) {
				$ret[$key] = $this -> toDB($var, $nostrip, $no_encode, $mod, $original_author);
			}
		} else {
			if (MAGIC_QUOTES_GPC == true && $nostrip == false) {
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
			} else {
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
		if (e_WYSIWYG !== true){
	   	  	$text = str_replace("&nbsp;", " ", $text); // fix for utf-8 issue with html_entity_decode();
		}
		return $text;
	}


	function post_toForm($text) {
		if (MAGIC_QUOTES_GPC == true) {
			$text = stripslashes($text);
		}
		return str_replace(array( "'", '"', "<", ">"), array("&#039;", "&quot;", "&lt;", "&gt;"), $text);
	}


	function post_toHTML($text, $original_author = false, $extra = '', $mod = false) {
		$text = $this -> toDB($text, false, false, $mod, $original_author);
		return $this -> toHTML($text, true, $extra);
	}


	function parseTemplate($text, $parseSCFiles = TRUE, $extraCodes = "") {
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

  if (!ctype_digit($width)) return $str;		// Don't wrap if non-numeric width
  if ($width < 6) return $str;					// Trap stupid wrap counts, as well

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
	{	// 0x1680, 0x180e, 0x2000-0x200a, 0x2028, 0x205f, 0x3000 are 'non-ASCII' Unicode UCS-4 codepoints - see http://www.unicode.org/Public/UNIDATA/UnicodeData.txt
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
		$whiteSpace = '#(\s+)#';		// For non-utf-8, can use a simple match string
	}
	

// Start of the serious stuff - split into HTML tags and text between
	  $content = preg_split('#(<.*?>)#mis', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	  foreach($content as $value)
	  {
		if ($value[0] == "<")
		{  // We are within an HTML tag
          // Create a lowercase copy of this tag's contents
          $lvalue = strtolower(substr($value,1,-1));
		  if ($lvalue)
		  {	// Tag of non-zero length
			// If the first character is not a / then this is an opening tag
            if ($lvalue[0] != "/") 
			{            // Collect the tag name   
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
		    $value = '';		// Eliminate any empty tags altogether
		  }
        // Else if we're outside any tags, and with non-zero length string...
        } 
		elseif ($value) 
		{    // If unprotected...
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
			  {	// Enough characters that we may need to do something.
				$pulled = '';
				if ($utf8)
				{
				  // Pull out a piece of the maximum permissible length
				  if (preg_match('#^((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$width.'})(.{0,1}).*#s',$sp,$matches) == 0)
				  {
					$value .= '[!<b>invalid utf-8: '.$sp.'<b>!]';		// Make any problems obvious for now
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
					$value .= '[!<b>loop count exceeded: '.$sp.'</b>!]';		// Make any problems obvious for now
					$sp = '';
				  }
				}
				else
				{
					for ($i = min($width,strlen($sp)); $i > 0; $i--)
					{
					  if (strpos($lbrks,$sp[$i-1]) !== FALSE) break;		// No speed advantage to defining match character
					}
					if ($i == 0)
					{	// No 'special' break boundary character found - break at the word boundary
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
				  $sp = substr($sp,strlen($pulled));			// Shorten $sp by whatever we've processed (will work even for utf-8)
				}
			  }
			  $value .= $sp;		// Add in any residue
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
		$ret = ($tmp_pos > 0 ? substr($text, 0, $tmp_pos) : substr($text, 0, $pos));
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


	function toHTML($text, $parseBB = FALSE, $modifiers = "", $postID = "", $wrap=FALSE) 
	{
	  if ($text == '') return $text;

	  global $pref, $fromadmin;
		
	  // Set default modifiers to start
	  $opts = $this->e_optDefault;

	  // Now process any modifiers that are specified
	  if (strlen($modifiers)) 
	  {
	    $aMods = explode( ',',$modifiers);
		
		// If there's a supermodifier, it must be first, and in uppercase
		$psm = trim($aMods[0]);
		if (isset($this->e_SuperMods[$psm]))
		{
	  	  $opts = array_merge($this->e_optDefault,$this->e_SuperMods[$psm]);
		  $opts['context'] = $psm;
		  unset($aMods[0]);
		}
		else
		{
		// Set default modifiers
		$opts = $this->e_optDefault;
		}

		// Now find any regular mods (could check each exists, but unnecessary processing really)
		foreach ($aMods as $mod)
		{
		  $opts[trim($mod)] = TRUE;  // Change mods as spec'd
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


	  // Make sure we have a valid count for word wrapping
	  if(!$wrap && $pref['main_wordwrap']) $wrap = $pref['main_wordwrap'];
	  $text = " ".$text;



// Now get on with the parsing
	  $ret_parser = '';
	  $last_bbcode = '';
	  if ($parseBB == FALSE)
	  {
	    $content = array($text);
	  }
	  else
	  {
		// Split each text block into bits which are either within one of the 'key' bbcodes, or outside them
		// (Because we have to match end words, the 'extra' capturing subpattern gets added to output array. We strip it later)
		$content = preg_split('#(\[(php|code|scode|hide).*?\[/(?:\\2)\])#mis', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	  }


	  // Use $full_text variable so its available to special bbcodes if required
	  foreach ($content as $full_text)
	  {
		$proc_funcs = TRUE;
		
		// We may have 'captured' a bbcode word - strip it if so
		if ($last_bbcode == $full_text)
		{
		  $last_bbcode = '';
		  $proc_funcs = FALSE;
		  $full_text = '';
		}
		else
		{
		  // (Have to have a good test in case a 'non-key' bbcode starts the block - so pull out the bbcode parameters while we're there
		  if (($parseBB !== FALSE) && preg_match('#(^\[(php|code|scode|hide)(.*?)\])(.*?)(\[/\\2\]$)#is', $full_text, $matches ))
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
//			if (preg_match('#^<(script|style)[^>]+>#',$sub_blk))		// 
			if ((substr($sub_blk,0,7) == '<script') || (substr($sub_blk,0,6) == '<style'))
			{  // Its a script/style block - just pass it through unaltered - except, do we need the line break stuff? - QUERY XXX-01
			  if (DB_INF_SHOW) echo "Processing script: {$sub_blk}<br />";
			  if (!$opts['nobreak'])
			  {
				$sub_blk = preg_replace("#>\s*[\r]*\n[\r]*#", ">", $sub_blk);
			  }
			  $ret_parser .= $sub_blk;
			}
			else
			{
				// Do 'normal' processing on a chunk


				// Could put tag stripping in here


				//	Line break compression (why?)
				// Prepare for line-break compression. Avoid compressing newlines in embedded scripts and CSS
			  if (!$opts['nobreak'])
			  {
				$sub_blk = preg_replace("#>\s*[\r]*\n[\r]*#", ">", $sub_blk);
			  }


			   //	Link substitution
				// Convert URL's to clickable links, unless modifiers or prefs override
			  if ($pref['make_clickable'] && !$opts['no_make_clickable'])
			  {
				if ($pref['link_replace'] && !$opts['no_replace'])
				{
				  $_ext = ($pref['links_new_window'] ? " rel=\"external\"" : "");
//				  $sub_blk = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<,]*)#is", "\\1<a href=\"\\2\" {$_ext}>".$pref['link_text']."</a>", $sub_blk);
				  $sub_blk = preg_replace("#(^|[\s\]=])([\w]+?://(?:[\w-%]+?)(?:\.[\w-%]+?)+.*?)(?=$|[\s,()[\]]|\.\s)#is", "\\1<a href=\"\\2\" {$_ext}>".$pref['link_text']."</a>", $sub_blk);
//				  $sub_blk = preg_replace("#(^|[\n \]])((www|ftp)\.[\w+-]+?\.[\w+\-.]*(?(?=/)(/.+?(?=\s|,\s))|(?=\W)))#is", "\\1<a href=\"http://\\2\" {$_ext}>".$pref['link_text']."</a>", $sub_blk);
				  $sub_blk = preg_replace("#(^|[\s\]=])((?:www|ftp)(?:\.[\w-%]+?){2}.*?)(?=$|[\s,()[\]]|\.\s)#is", "\\1<a href=\"http://\\2\" {$_ext}>".$pref['link_text']."</a>", $sub_blk);
				  if(CHARSET != "utf-8" && CHARSET != "UTF-8")
				  {
					$email_text = ($pref['email_text']) ? $this->replaceConstants($pref['email_text']) : "\\1\\2&copy;\\3";
				  }
				  else
				  {
					$email_text = ($pref['email_text']) ? $this->replaceConstants($pref['email_text']) : "\\1\\2©\\3";
				  }
				  $sub_blk = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\"; return true;' onmouseout='window.status=\"\";return true;'>".$email_text."</a>", $sub_blk);
				}
				else
				{
//				  $sub_blk = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<,]*)#is", "\\1<a href=\"\\2\" rel=\"external\">\\2</a>", $sub_blk);
				  $sub_blk = preg_replace("#(^|[\s\]=])([\w]+?://(?:[\w-%]+?)(?:\.[\w-%]+?)+.*?)(?=$|[\s,()[\]]|\.\s)#is", "\\1<a href=\"\\2\" rel=\"external\">\\2</a>", $sub_blk);
//				  $sub_blk = preg_replace("#(^|[\n \]])((www|ftp)\.[\w+-]+?\.[\w+\-.]*(?(?=/)(/.+?(?=\s|,\s))|(?=\W)))#is", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $sub_blk);
				  $sub_blk = preg_replace("#(^|[\s\]=])((?:www|ftp)(?:\.[\w-%]+?){2}.*?)(?=$|[\s,()[\]]|\.\s)#is", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $sub_blk);
				  $sub_blk = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\2\"+\"@\"+\"\\3\"; return true;' onmouseout='window.status=\"\";return true;'>".LAN_EMAIL_SUBS."</a>", $sub_blk);
				}
			  }


			   //	Emoticons
				// Convert emoticons to graphical icons, unless modifiers override
			  if (!$opts['emotes_off'] && ($pref['smiley_activate'] || $opts['emotes_on']))
			  {
				if (!is_object($this->e_emote)) 
				{
				  require_once(e_HANDLER.'emote_filter.php');
				  $this->e_emote = new e_emoteFilter;
				}
				$sub_blk = $this->e_emote->filterEmotes($sub_blk);
			  }



			   //	Newline processing (more)
			// Reduce multiple newlines in all forms to a single newline character, except for embedded scripts and CSS
			  if (!$opts['nobreak'])
			  {
				$sub_blk = preg_replace("#[\r]*\n[\r]*#", E_NL, $sub_blk);
			  }



			   //	Entity conversion
				// Restore entity form of quotes and such to single characters, except for text destined for tag attributes or JS.
			  if (!$opts['value'])
			  { // output not used for attribute values.
				$sub_blk = str_replace($this -> search, $this -> replace, $sub_blk);
			  }
			  else
			  {   									// output used for attribute values.
				$sub_blk = str_replace($this -> replace, $this -> search, $sub_blk);
			  }


			   //   BBCode processing (other than the four already done, which shouldn't appear at all in the text)
				// Start parse [bb][/bb] codes
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
			  // End parse [bb][/bb] codes



			  // replace all {e_XXX} constants with their e107 value. modifier determines relative/absolute conversion
			  // (Moved to after bbcode processing by Cameron)
			  if ($opts['constants'])
			  {
				$sub_blk = $this->replaceConstants($sub_blk, ($opts['abs_links'] ? 'full' : ''));
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
			  if (!$opts['no_hook'])
			  {
				if ( varset($pref['tohtml_hook']))
				{	//Process the older tohtml_hook pref (deprecated)
					foreach(explode(",",$pref['tohtml_hook']) as $hook)
					{
						if (!is_object($this->e_hook[$hook]))
						{
							require_once(e_PLUGIN.$hook."/".$hook.".php");
							$hook_class = "e_".$hook;
							$this->e_hook[$hook] = new $hook_class;
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
					  require_once(e_PLUGIN.$hook."/e_tohtml.php");
					  $hook_class = "e_tohtml_".$hook;
					  $this->e_hook[$hook] = new $hook_class;
					}
					$sub_blk = $this->e_hook[$hook]->to_html($sub_blk, $opts['context']);
				  }
				}
			  }



			   //  	Word wrap
			  if (!$opts['nobreak'])
			  {
				$sub_blk = $this -> textclean($sub_blk, $wrap);
			  }



			   //	Search highlighting
			  // Search Highlight
			  if (!$opts['emotes_off'])
			  {
				if ($this->checkHighlighting())
				{
				  $sub_blk = $this -> e_highlight($sub_blk, $this -> e_query);
				}
			  }


			// Purpose of this block?
			  $nl_replace = "<br />";
			  if ($opts['nobreak'])
			  {
				$nl_replace = '';
			  }
			  elseif ($opts['retain_nl'])
			  {
				$nl_replace = "\n";
			  }
			  $sub_blk = str_replace(E_NL, $nl_replace, $sub_blk);


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

}

?>