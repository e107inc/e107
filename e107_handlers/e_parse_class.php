<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/e_parse_class.php,v $
|     $Revision: 1.41 $
|     $Date: 2005-03-08 09:11:32 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
	
class e_parse {
	var $e_sc;
	var $e_bb;
	var $e_pf;
	var $e_emote;
	 
	function toDB($text, $no_encode = FALSE) {
		if (MAGIC_QUOTES_GPC == TRUE) {
			$text = stripslashes($text);
		}
		if(isset($pref['post_html']) && check_class($pref['post_html']))
		{
			$no_encode == TRUE;
		}
		if (getperms("0") || $no_encode)
		{
			$search = array('$', '"', "'", '\\');
			$replace = array('&#036;','&quot;','&#039;','&#092;');
			$text = str_replace($search, $replace, $text);
		}
		else
		{
			$text = htmlentities($text, ENT_QUOTES, CHARSET);
		}
		return $text;
	}
	 
	function toForm($text, $single_quotes = FALSE) {
		$mode = ($single_quotes ? ENT_QUOTES :ENT_COMPAT);
		if (MAGIC_QUOTES_GPC == TRUE) {
			$text = stripslashes($text);
		}
		$search = array('&#036;', '&quot;');
		$replace = array('$', '"');
		$text = str_replace($search, $replace, $text);
		return html_entity_decode($text, $mode, CHARSET);
	}
	 
	function post_toHTML($text, $modifier=TRUE, $extra='') {

		/*
		changes by jalist 30/01/2005:
		description had to add modifier to /not/ send formatted text on to this->toHTML at end of method, this was causing problems when MAGIC_QUOTES_GPC == TRUE.
		*/

		if (ADMIN === TRUE || $no_encode === TRUE) {
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
			$text = htmlentities($text, ENT_QUOTES, CHARSET);
		}
		return ($modifier ? $this->toHTML($text, TRUE, $extra) : $text);
	}
	 
	function post_toForm($text) {
		// ensure apostrophes are properly converted, or else the form item could break
		return str_replace("'", "&#039;", $text);
		return $text;
	}
	 
	function parseTemplate($text, $parseSCFiles = TRUE, $extraCodes = "") {
		// Start parse {XXX} codes
		if (!is_object($this->e_sc)) {
			require_once(e_HANDLER."shortcode_handler.php");
			$this->e_sc = new e_shortcode;
		}
		return $this->e_sc->parseCodes($text, $parseSCFiles, $extraCodes);
		// End parse {XXX} codes
	}



function htmlwrap($str, $width, $break = "\n", $nobreak = "", $nobr = "pre", $utf = false)
{
	/*
	* htmlwrap() function - v1.1
	* Copyright (c) 2004 Brian Huisman AKA GreyWyvern
	* http://www.greywyvern.com/code/php/htmlwrap_1.1.php.txt
	*
	* This program may be distributed under the terms of the GPL
	*   - http://www.gnu.org/licenses/gpl.txt
	*/

	$content = preg_split("/([<>])/", $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	$nobreak = explode(" ", $nobreak);
	$nobr = explode(" ", $nobr);
	$intag = false;
	$innbk = array();
	$innbr = array();
	$drain = "";
	$utf = ($utf) ? "u" : "";
	$lbrks = "/?!%)-}]\\\"':;";
	if ($break == "\r")
	{
		$break = "\n";
	}
	while (list(, $value) = each($content))
	{
		switch ($value)
		{
			case "<": $intag = true; break;
			case ">": $intag = false; break;
			default:
				if ($intag)
				{
					if ($value{0} != "/")
					{
						preg_match("/^(.*?)(\s|$)/$utf", $value, $t);
						if ((!count($innbk) && in_array($t[1], $nobreak)) || in_array($t[1], $innbk)) $innbk[] = $t[1];
						if ((!count($innbr) && in_array($t[1], $nobr)) || in_array($t[1], $innbr)) $innbr[] = $t[1];
					} else {
						if (in_array(substr($value, 1), $innbk)) unset($innbk[count($innbk)]);
						if (in_array(substr($value, 1), $innbr)) unset($innbr[count($innbr)]);
					}
				} else if ($value)
				{
					if (!count($innbr)) $value = str_replace("\n", "\r", str_replace("\r", "", $value));
						if (!count($innbk))
						{
							do
							{
								$store = $value;
								if (preg_match("/^(.*?\s|^)(([^\s&]|&(\w{2,5}|#\d{2,4});){".$width."})(?!(".preg_quote($break, "/")."|\s))(.*)$/s$utf", $value, $match)) 
								{
									for ($x = 0, $ledge = 0; $x < strlen($lbrks); $x++) $ledge = max($ledge, strrpos($match[2], $lbrks{$x}));
									if (!$ledge) $ledge = strlen($match[2]) - 1;
									$value = $match[1].substr($match[2], 0, $ledge + 1).$break.substr($match[2], $ledge + 1).$match[6];
								}
							}
							while ($store != $value);
						}
					if (!count($innbr)) $value = str_replace("\r", "<br />\n", $value);
				}
			}
			$drain .= $value;
		}
		return $drain;
	}


	function textclean ($text, $wrap=100)
	{
		$text = str_replace ("\n\n\n", "\n\n", $text);
		$text = $this -> htmlwrap($text, $wrap);
		$text = str_replace (array ("<br /> ", " <br />", " <br /> ", "<br />"), "", $text);	
		/* we can remove any linebreaks added by htmlwrap function as any \n's will be converted later anyway */
		return $text; 
	}
	 
	function toHTML($text, $parseBB = FALSE, $modifiers = "", $postID = "", $wrap=FALSE) {
		if ($text == '')
		{
			return $text;
		}
		global $pref;
		if(!$wrap) $wrap = $pref['main_wordwrap'];
		$text = " ".$text;
		if($pref['make_clickable']) {
			if($pref['link_replace'] && strpos($modifiers, 'no_replace') === FALSE) {
				$text = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<]*)#is", "\\1<a href=\"\\2\" rel=\"external\">".$pref['link_text']."</a>", $text);
				$text = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", "\\1<a href=\"http://\\2\" rel=\"external\">".$pref['link_text']."</a>", $text);
				$text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href='javascript:window.location=\"mail\"+\"to:\"+\"\\2\"+\"@\"+\"\\3\";' onmouseover='window.status=\"mail\"+\"to:\"+\"\\2\"+\"@\"+\"\\3\"; return true;' onmouseout='window.status=\"\";return true;'>".$pref['email_text']."</a>", $text);
			} else {
				$text = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<]*)#is", "\\1<a href=\"\\2\" rel=\"external\">\\2</a>", $text);
				$text = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $text);
				$text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href='javascript:window.location=\"mail\"+\"to:\"+\"\\2\"+\"@\"+\"\\3\";' onmouseover='window.status=\"mail\"+\"to:\"+\"\\2\"+\"@\"+\"\\3\"; return true;' onmouseout='window.status=\"\";return true;'>-email-</a>", $text);
			}
		}

		if(!strstr($modifiers, 'nobreak')) {
			$text = $this -> textclean($text, $wrap);
		}

		if (strpos($modifiers,'emotes_off') === FALSE) {
			if ($pref['smiley_activate'] || strpos($modifiers,'emotes_on') !== FALSE) {
				if (!is_object($this->e_emote)) {
					require_once(e_HANDLER.'emote_filter.php');
					$this->e_emote = new e_emoteFilter;
				}
				$text = $this->e_emote->filterEmotes($text);
			}
		}

		$search = array('&#39;', '&#039;', '&#036;', '&quot;', 'onerror', '&gt;', '&amp;#039;', '&amp;quot;');
		$replace = array("'", "'", '$', '"', 'one<i></i>rror', '>', "'", '"');
		$text = str_replace($search, $replace, $text);
		if (strpos($modifiers, 'nobreak') == FALSE) {
			$text = preg_replace("#[\r]*\n[\r]*#", "[E_NL]", $text);
		}
		
		 
		if (strpos($modifiers,'parse_sc') !== FALSE)
		{
			$text = $this->parseTemplate($text, TRUE);
		}

		// Start parse [bb][/bb] codes
		if ($parseBB === TRUE) {
			if (!is_object($this->e_bb)) {
				require_once(e_HANDLER.'bbcode_handler.php');
				$this->e_bb = new e_bbcode;
			}
			$text = $this->e_bb->parseBBCodes($text, $postID);
		}
		// End parse [bb][/bb] codes
		 
		if ($pref['profanity_filter']) {
			if (!is_object($this->e_pf)) {
				require_once(e_HANDLER."profanity_filter.php");
				$this->e_pf = new e_profanityFilter;
			}
			$text = $this->e_pf->filterProfanities($text);
		}
		 
		$nl_replace = "<br />";
		if (strpos($modifiers, 'nobreak') !== FALSE)
		{
			$nl_replace = '';
		}
		elseif (strpos($modifiers, 'retain_nl') !== FALSE)
		{
			$nl_replace = "\n";
		}
		$text = str_replace('[E_NL]', $nl_replace, $text);
		return $text;
	}
	 
	function toJS($stringarray) {
		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);
		return strtr ($stringarray, $trans_tbl);
	}
	 
	 
	 
}
?>