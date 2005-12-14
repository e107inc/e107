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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/profanity_filter.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-12-14 17:37:34 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class e_profanityFilter {
	var $profanityList;
	 
	function e_profanityFilter() {
		global $pref;

		$words = explode(",", $pref['profanity_words']);
		
		foreach($words as $word) {
			$word = trim($word);
			if($word != "")
			{
				$word_array[] = $word;
			}
		}
		$this->profanityList = implode("\b|\b", $word_array);
		unset($words);
		return TRUE;
	}
	 
	function filterProfanities($text) {
		global $pref;
		if (!$this->profanityList) {
			return $text;
		}
		if ($pref['profanity_replace']) {
			return preg_replace("#\b".$this->profanityList."\b#is", $pref['profanity_replace'], $text);
		} else {
			return preg_replace_callback("#\b".$this->profanityList."\b#is", array($this, 'replaceProfanities'), $text);
		}
	}
	 
	function replaceProfanities($matches) {
		/*!
		@function replaceProfanities callback
		@abstract replaces vowels in profanity words with stars
		@param text string - text string to be filtered
		@result filtered text
		*/
		 
		return preg_replace("#a|e|i|o|u#i", "*" , $matches[0]);
	}
}
	
?>