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
|     $Revision: 1.18 $
|     $Date: 2005-02-03 22:11:39 $
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
		if (ADMIN || $no_encode) {
			$search = array('$', '"', "'", '\\');
				$replace = array('&#036;','&quot;','&#039;','&#092;');
			$text = str_replace($search, $replace, $text);
		} else {
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
	 
	function post_toHTML($text, $modifier=TRUE) {

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
		return ($modifier ? $this->toHTML($text, TRUE) : $text);
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

	function textclean ($text, $wrap=100)
	{
		$text = trim ($text);
		$text = str_replace ("\n\n\n", "\n\n", $text);

		list ($words) = array (explode (" ", $text));
		$text = "";

		foreach ($words as $word)
		{ 
			if (strlen ($word) > $wrap and !ereg ("[[|]|//|href|<img|&#]", $word))
			{
				$word = wordwrap ($word, $wrap, "<br />", 1); 
			}
			$text .= " " . $word; 
		}
		$text = str_replace (array ('<br> ', ' <br>', ' <br> '), '<br>', $text);

		return trim ($text); 
		
	}

	function linewrap ($text, $wrap=60)
	{
		$wrap -= 2;
		preg_match_all("/bw.{1,$wrap}wb/", $text, $parts);
		return (implode("<br>", $parts[0]));
	}

	 
	function toHTML($text, $parseBB = FALSE, $modifiers = "", $postID = "", $wrap=100) {
		if ($text == '')
		{
			return $text;
		}
		global $pref;
		$text = " ".$text;
		if($pref['link_replace']) {
			$text = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<]*)#is", "\\1<a href=\"\\2\" rel=\"external\">".$pref['link_text']."</a>", $text);
			$text = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", "\\1<a href=\"http://\\2\" rel=\"external\">".$pref['link_text']."</a>", $text);
			$text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">".$pref['email_text']."</a>", $text);
		} else {
			$text = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<]*)#is", "\\1<a href=\"\\2\" rel=\"external\">\\2</a>", $text);
			$text = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", "\\1<a href=\"http://\\2\" rel=\"external\">\\2</a>", $text);
			$text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $text);
		}
		$text = $this -> textclean($text, $wrap);
		 
		$search = array('&#039;', '&#036;', '&quot;', 'onerror');
		$replace = array("'", '$', '"', 'one<i></i>rror');
		$text = str_replace($search, $replace, $text);
		if (strpos($modifiers, 'nobreak') == FALSE) {
			$text = preg_replace("#[\r]*\n[\r]*#", "[E_NL]", $text);
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
		 
		$nl_replace = (strpos($modifiers, 'nobreak') === FALSE) ? "<br />" :
		 "";
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